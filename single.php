<?php
/**
 * Single blog post.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article <?php post_class(); ?>>
		<header class="container entry-header">
			<?php personal_site_primary_category(); ?>
			<h1 class="entry-header__title"><?php the_title(); ?></h1>
			<div class="entry-header__meta meta">
				<span><?php the_author(); ?></span>
				<span class="meta__sep" aria-hidden="true">&middot;</span>
				<?php personal_site_posted_on(); ?>
				<span class="meta__sep" aria-hidden="true">&middot;</span>
				<span><?php echo esc_html( personal_site_reading_time() ); ?></span>
			</div>
			<?php if ( has_excerpt() ) : ?>
				<p class="entry-lede"><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="entry-cover">
				<div class="container">
					<?php the_post_thumbnail( 'personal-site-cover' ); ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="container page-body">
			<div class="prose">
				<?php
				the_content();
				wp_link_pages(
					array(
						'before' => '<nav class="pagination" aria-label="' . esc_attr__( 'Page', 'personal-site' ) . '"><span>',
						'after'  => '</span></nav>',
					)
				);
				?>
			</div>

			<?php if ( has_tag() ) : ?>
				<div class="entry-tags prose-width">
					<?php foreach ( get_the_tags() as $tag ) : ?>
						<a class="pill" href="<?php echo esc_url( get_tag_link( $tag ) ); ?>">#<?php echo esc_html( $tag->name ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			if ( comments_open() || get_comments_number() ) {
				comments_template();
			}
			?>
		</div>
	</article>
	<?php
endwhile;

get_footer();
