<?php
/**
 * Document head, site header, and the opening of the main content region.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<link rel="profile" href="https://gmpg.org/xfn/11" />
	<script>
		/* Set the color scheme before paint to avoid a flash. */
		( function () {
			try {
				var stored = localStorage.getItem( 'personal-site-theme' );
				var theme = stored || ( window.matchMedia( '(prefers-color-scheme: dark)' ).matches ? 'dark' : 'light' );
				document.documentElement.setAttribute( 'data-theme', theme );
			} catch ( e ) {}
		} )();
	</script>
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main"><?php esc_html_e( 'Skip to content', 'personal-site' ); ?></a>

<header class="site-header">
	<div class="container site-header__inner">
		<?php if ( has_custom_logo() ) : ?>
			<?php the_custom_logo(); ?>
		<?php else : ?>
			<a class="brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
				<span class="brand__text"><?php bloginfo( 'name' ); ?></span>
			</a>
		<?php endif; ?>

		<nav id="primary-nav" class="nav" aria-label="<?php esc_attr_e( 'Primary', 'personal-site' ); ?>" data-open="false">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'nav__list',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
		</nav>

		<div class="header-actions">
			<button class="theme-toggle" type="button" data-theme-toggle aria-label="<?php esc_attr_e( 'Toggle dark mode', 'personal-site' ); ?>">
				<svg class="theme-toggle__moon icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
				<svg class="theme-toggle__sun icon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4 1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
			</button>

			<button class="menu-toggle" type="button" data-menu-toggle aria-controls="primary-nav" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle menu', 'personal-site' ); ?>">
				<svg class="icon" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
			</button>
		</div>
	</div>
</header>

<main id="main" class="site-main">
