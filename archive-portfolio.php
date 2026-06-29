<?php
/**
 * Portfolio archive.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$intro = apply_filters( 'personal_site_portfolio_intro', __( 'Systems and products built for scale, reliability, and longevity.', 'personal-site' ) );
$title = apply_filters( 'personal_site_portfolio_title', __( 'Selected projects', 'personal-site' ) );
?>
<div class="container">
	<header class="page-header">
		<p class="eyebrow"><?php esc_html_e( 'Work', 'personal-site' ); ?></p>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $intro ) : ?>
			<p class="lede page-header__intro"><?php echo esc_html( $intro ); ?></p>
		<?php endif; ?>
	</header>
</div>

<div class="section page-body">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="work-grid">
				<?php
				while ( have_posts() ) :
					the_post();
					get_template_part( 'template-parts/portfolio-card' );
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
