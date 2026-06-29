<?php
/**
 * Search results.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="container">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e( 'Search', 'personal-site' ); ?></p>
		<h1>
			<?php
			printf(
				/* translators: %s: search query. */
				esc_html__( 'Results for &ldquo;%s&rdquo;', 'personal-site' ),
				esc_html( get_search_query() )
			);
			?>
		</h1>
	</header>
</div>

<div class="section page-body">
	<div class="container">
		<div class="writing-search"><?php get_search_form(); ?></div>

		<?php if ( have_posts() ) : ?>
			<div class="post-list">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/post-row' );
				endwhile;
				?>
			</div>
			<?php personal_site_pagination(); ?>
		<?php else : ?>
			<?php get_template_part( 'template-parts/content-none' ); ?>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
