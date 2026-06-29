<?php
/**
 * 404 (not found).
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="container page-body">
	<header class="section section--flush">
		<p class="eyebrow"><?php esc_html_e( 'Error 404', 'personal-site' ); ?></p>
		<h1><?php esc_html_e( 'Page not found', 'personal-site' ); ?></h1>
		<p class="lede" style="margin-top: var(--space-3);">
			<?php esc_html_e( 'The page you are looking for has moved or never existed. Try a search instead.', 'personal-site' ); ?>
		</p>
		<div class="stack" style="margin-top: var(--space-5); max-width: 30rem;">
			<?php get_search_form(); ?>
			<p><a class="button button--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back home', 'personal-site' ); ?></a></p>
		</div>
	</header>
</div>
<?php
get_footer();
