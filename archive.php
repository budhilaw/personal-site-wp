<?php
/**
 * Archive for categories, tags, dates, and authors.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

if ( is_category() ) {
	$eyebrow = __( 'Category', 'personal-site' );
} elseif ( is_tag() ) {
	$eyebrow = __( 'Tag', 'personal-site' );
} elseif ( is_author() ) {
	$eyebrow = __( 'Author', 'personal-site' );
} else {
	$eyebrow = __( 'Archive', 'personal-site' );
}
$description = get_the_archive_description();
?>
<div class="container">
	<header class="page-header">
		<p class="eyebrow"><?php echo esc_html( $eyebrow ); ?></p>
		<h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
		<?php if ( $description ) : ?>
			<div class="lede page-header__intro"><?php echo wp_kses_post( $description ); ?></div>
		<?php endif; ?>
	</header>
</div>

<div class="section page-body">
	<div class="container">
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
