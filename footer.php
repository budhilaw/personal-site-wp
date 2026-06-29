<?php
/**
 * Close the main region and render the site footer.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
</main><!-- #main -->

<footer class="site-footer">
	<div class="container site-footer__inner">
		<?php
		$ps_tagline   = personal_site_opt( 'footer', 'tagline', '' );
		$ps_tagline   = $ps_tagline ? $ps_tagline : get_bloginfo( 'description' );
		$ps_copyright = personal_site_opt( 'footer', 'copyright', '' );
		if ( $ps_copyright ) {
			$ps_copyright = str_replace(
				array( '{year}', '{name}' ),
				array( date_i18n( 'Y' ), get_bloginfo( 'name' ) ),
				$ps_copyright
			);
		} else {
			$ps_copyright = sprintf(
				/* translators: 1: current year, 2: site name. */
				__( '&copy; %1$s %2$s', 'personal-site' ),
				date_i18n( 'Y' ),
				get_bloginfo( 'name' )
			);
		}
		?>
		<div class="site-footer__about">
			<p class="site-footer__brand">
				<?php bloginfo( 'name' ); ?>
				<?php if ( $ps_tagline ) : ?>
					<span class="text-dim">&mdash; <?php echo esc_html( $ps_tagline ); ?></span>
				<?php endif; ?>
			</p>
			<p class="site-footer__meta"><?php echo esc_html( $ps_copyright ); ?></p>
		</div>

		<?php if ( has_nav_menu( 'footer' ) || has_nav_menu( 'primary' ) ) : ?>
			<nav class="site-footer__nav" aria-label="<?php esc_attr_e( 'Footer', 'personal-site' ); ?>">
				<?php
				wp_nav_menu(
					array(
						'theme_location' => has_nav_menu( 'footer' ) ? 'footer' : 'primary',
						'container'      => false,
						'menu_class'     => 'nav__list',
						'depth'          => 1,
						'fallback_cb'    => false,
					)
				);
				?>
			</nav>
		<?php endif; ?>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
