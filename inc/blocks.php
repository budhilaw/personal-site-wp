<?php
/**
 * Custom homepage blocks (Hero, Focus, Selected Work, Recent Posts, CTA).
 *
 * Each block is dynamic: the editor UI lives in assets/js/blocks.js and the
 * front-end markup is produced by the render callbacks here, so what ships
 * always matches the theme. No build step is required.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add a "Personal Site" block category.
 *
 * @param array $categories Existing categories.
 * @return array
 */
function personal_site_block_category( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'personal-site',
			'title' => __( 'Personal Site', 'personal-site' ),
			'icon'  => null,
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'personal_site_block_category' );

/**
 * Register the editor script and the dynamic blocks.
 */
function personal_site_register_blocks() {
	$rel = '/assets/js/blocks.js';
	wp_register_script(
		'personal-site-blocks',
		PERSONAL_SITE_URI . $rel,
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
		filemtime( PERSONAL_SITE_DIR . $rel ),
		true
	);
	wp_set_script_translations( 'personal-site-blocks', 'personal-site' );

	$text = array( 'type' => 'string' );

	register_block_type(
		'personal-site/hero',
		array(
			'editor_script'   => 'personal-site-blocks',
			'render_callback' => 'personal_site_render_block_hero',
			'attributes'      => array(
				'heading'        => array( 'type' => 'string', 'default' => __( 'I build things for the web.', 'personal-site' ) ),
				'text'           => $text,
				'primaryLabel'   => array( 'type' => 'string', 'default' => __( 'View work', 'personal-site' ) ),
				'primaryUrl'     => $text,
				'secondaryLabel' => array( 'type' => 'string', 'default' => __( 'Read writing', 'personal-site' ) ),
				'secondaryUrl'   => $text,
			),
		)
	);

	register_block_type(
		'personal-site/focus',
		array(
			'editor_script'   => 'personal-site-blocks',
			'render_callback' => 'personal_site_render_block_focus',
			'attributes'      => array(
				'eyebrow' => array( 'type' => 'string', 'default' => __( 'Focus', 'personal-site' ) ),
				'heading' => array( 'type' => 'string', 'default' => __( 'What I focus on', 'personal-site' ) ),
			),
		)
	);

	register_block_type(
		'personal-site/focus-item',
		array(
			'editor_script'   => 'personal-site-blocks',
			'render_callback' => 'personal_site_render_block_focus_item',
			'attributes'      => array(
				'title' => $text,
				'text'  => $text,
			),
		)
	);

	register_block_type(
		'personal-site/selected-work',
		array(
			'editor_script'   => 'personal-site-blocks',
			'render_callback' => 'personal_site_render_block_selected_work',
			'attributes'      => array(
				'eyebrow' => array( 'type' => 'string', 'default' => __( 'Selected work', 'personal-site' ) ),
				'heading' => array( 'type' => 'string', 'default' => __( 'Recent projects', 'personal-site' ) ),
				'count'   => array( 'type' => 'number', 'default' => 4 ),
			),
		)
	);

	register_block_type(
		'personal-site/recent-posts',
		array(
			'editor_script'   => 'personal-site-blocks',
			'render_callback' => 'personal_site_render_block_recent_posts',
			'attributes'      => array(
				'eyebrow' => array( 'type' => 'string', 'default' => __( 'Writing', 'personal-site' ) ),
				'heading' => array( 'type' => 'string', 'default' => __( 'Recent writing', 'personal-site' ) ),
				'count'   => array( 'type' => 'number', 'default' => 5 ),
			),
		)
	);

	register_block_type(
		'personal-site/cta',
		array(
			'editor_script'   => 'personal-site-blocks',
			'render_callback' => 'personal_site_render_block_cta',
			'attributes'      => array(
				'heading' => array( 'type' => 'string', 'default' => __( 'Want to work together?', 'personal-site' ) ),
				'label'   => array( 'type' => 'string', 'default' => __( 'Get in touch', 'personal-site' ) ),
				'url'     => $text,
			),
		)
	);
}
add_action( 'init', 'personal_site_register_blocks' );

/* -------------------------------------------------------------------------
 * Render callbacks
 * ---------------------------------------------------------------------- */

function personal_site_render_block_hero( $attr ) {
	$portfolio_url = get_post_type_archive_link( 'portfolio' );
	$blog_url      = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );
	$primary_url   = ! empty( $attr['primaryUrl'] ) ? $attr['primaryUrl'] : $portfolio_url;
	$secondary_url = ! empty( $attr['secondaryUrl'] ) ? $attr['secondaryUrl'] : $blog_url;

	ob_start();
	?>
	<section class="container hero">
		<h1 class="hero__title"><?php echo wp_kses_post( $attr['heading'] ); ?></h1>
		<?php if ( ! empty( $attr['text'] ) ) : ?>
			<p class="hero__text"><?php echo wp_kses_post( $attr['text'] ); ?></p>
		<?php endif; ?>
		<div class="hero__actions">
			<?php if ( ! empty( $attr['primaryLabel'] ) && $primary_url ) : ?>
				<a class="button" href="<?php echo esc_url( $primary_url ); ?>"><?php echo esc_html( wp_strip_all_tags( $attr['primaryLabel'] ) ); ?> <span class="button__arrow" aria-hidden="true">&rarr;</span></a>
			<?php endif; ?>
			<?php if ( ! empty( $attr['secondaryLabel'] ) && $secondary_url ) : ?>
				<a class="button button--ghost" href="<?php echo esc_url( $secondary_url ); ?>"><?php echo esc_html( wp_strip_all_tags( $attr['secondaryLabel'] ) ); ?> <span class="button__arrow" aria-hidden="true">&rarr;</span></a>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

function personal_site_render_block_focus( $attr, $content = '' ) {
	if ( '' === trim( (string) $content ) ) {
		return '';
	}
	ob_start();
	?>
	<section class="container section">
		<div class="focus-head">
			<?php if ( ! empty( $attr['eyebrow'] ) ) : ?>
				<p class="eyebrow"><?php echo esc_html( wp_strip_all_tags( $attr['eyebrow'] ) ); ?></p>
			<?php endif; ?>
			<?php if ( ! empty( $attr['heading'] ) ) : ?>
				<h2 class="focus-head__title"><?php echo wp_kses_post( $attr['heading'] ); ?></h2>
			<?php endif; ?>
		</div>
		<div class="focus-grid"><?php echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered inner blocks. ?></div>
	</section>
	<?php
	return ob_get_clean();
}

function personal_site_render_block_focus_item( $attr ) {
	if ( empty( $attr['title'] ) && empty( $attr['text'] ) ) {
		return '';
	}
	ob_start();
	?>
	<div class="focus-item">
		<?php if ( ! empty( $attr['title'] ) ) : ?>
			<h3 class="focus-item__title"><?php echo wp_kses_post( $attr['title'] ); ?></h3>
		<?php endif; ?>
		<?php if ( ! empty( $attr['text'] ) ) : ?>
			<p class="focus-item__text"><?php echo wp_kses_post( $attr['text'] ); ?></p>
		<?php endif; ?>
	</div>
	<?php
	return ob_get_clean();
}

function personal_site_render_block_selected_work( $attr ) {
	$count = isset( $attr['count'] ) ? max( 1, min( 12, (int) $attr['count'] ) ) : 3;
	$query = new WP_Query(
		array(
			'post_type'      => 'portfolio',
			'posts_per_page' => $count,
			'no_found_rows'  => true,
		)
	);
	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return '';
	}
	$archive = get_post_type_archive_link( 'portfolio' );

	ob_start();
	?>
	<section class="container section">
		<div class="section__head">
			<div class="section__head-titles">
				<?php if ( ! empty( $attr['eyebrow'] ) ) : ?><p class="eyebrow"><?php echo esc_html( wp_strip_all_tags( $attr['eyebrow'] ) ); ?></p><?php endif; ?>
				<?php if ( ! empty( $attr['heading'] ) ) : ?><h2><?php echo wp_kses_post( $attr['heading'] ); ?></h2><?php endif; ?>
			</div>
			<?php if ( $archive ) : ?>
				<a class="pill" href="<?php echo esc_url( $archive ); ?>"><?php esc_html_e( 'All projects', 'personal-site' ); ?> <span aria-hidden="true">&rarr;</span></a>
			<?php endif; ?>
		</div>
		<div class="work-grid">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/portfolio-card' );
			endwhile;
			?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}

function personal_site_render_block_recent_posts( $attr ) {
	$count = isset( $attr['count'] ) ? max( 1, min( 12, (int) $attr['count'] ) ) : 3;
	$query = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => $count,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	);
	if ( ! $query->have_posts() ) {
		wp_reset_postdata();
		return '';
	}
	$blog_url = get_option( 'page_for_posts' ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/' );

	ob_start();
	?>
	<section class="container section">
		<div class="section__head">
			<div class="section__head-titles">
				<?php if ( ! empty( $attr['eyebrow'] ) ) : ?><p class="eyebrow"><?php echo esc_html( wp_strip_all_tags( $attr['eyebrow'] ) ); ?></p><?php endif; ?>
				<?php if ( ! empty( $attr['heading'] ) ) : ?><h2><?php echo wp_kses_post( $attr['heading'] ); ?></h2><?php endif; ?>
			</div>
			<a class="pill" href="<?php echo esc_url( $blog_url ); ?>"><?php esc_html_e( 'All posts', 'personal-site' ); ?> <span aria-hidden="true">&rarr;</span></a>
		</div>
		<div class="post-list">
			<?php
			while ( $query->have_posts() ) :
				$query->the_post();
				get_template_part( 'template-parts/post-row' );
			endwhile;
			?>
		</div>
	</section>
	<?php
	wp_reset_postdata();
	return ob_get_clean();
}

function personal_site_render_block_cta( $attr ) {
	$email = personal_site_opt( 'general', 'social_email', '' );
	$url   = ! empty( $attr['url'] ) ? $attr['url'] : ( $email ? 'mailto:' . $email : '' );
	if ( ! $url ) {
		$contact = get_page_by_path( 'contact' );
		if ( $contact ) {
			$url = get_permalink( $contact );
		}
	}

	ob_start();
	?>
	<section class="container section">
		<div class="section__head">
			<?php if ( ! empty( $attr['heading'] ) ) : ?><h2><?php echo wp_kses_post( $attr['heading'] ); ?></h2><?php endif; ?>
			<?php if ( $url && ! empty( $attr['label'] ) ) : ?>
				<a class="button" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( wp_strip_all_tags( $attr['label'] ) ); ?> <span class="button__arrow" aria-hidden="true">&rarr;</span></a>
			<?php endif; ?>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
