<?php
/**
 * Fallback template and blog post index (the "Writing" page).
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$posts_page = get_option( 'page_for_posts' );
$heading    = ( $posts_page && is_home() && ! is_front_page() ) ? get_the_title( $posts_page ) : __( 'Writing', 'personal-site' );
$intro      = $posts_page ? get_post_field( 'post_excerpt', $posts_page ) : '';
$intro      = $intro ? $intro : apply_filters( 'personal_site_writing_intro', '' );
?>
<div class="container">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e( 'Writing', 'personal-site' ); ?></p>
		<h1><?php echo esc_html( $heading ); ?></h1>
		<?php if ( $intro ) : ?>
			<p class="lede page-header__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
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
