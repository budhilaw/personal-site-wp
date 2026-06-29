<?php
/**
 * Default page template.
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
	<article <?php post_class( 'container page-body' ); ?>>
		<header class="entry-header">
			<h1 class="entry-header__title"><?php the_title(); ?></h1>
		</header>

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

		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</article>
	<?php
endwhile;

get_footer();
