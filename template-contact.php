<?php
/**
 * Template Name: Contact
 *
 * Header, then contact details on the left and the built-in form on the right.
 * The form, validation, storage, and notifications are handled by the theme
 * (inc/contact.php) with no plugin.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$email    = personal_site_opt( 'general', 'social_email', '' );
	$based_in = personal_site_opt( 'contact', 'based_in', '' );
	$response = personal_site_opt( 'contact', 'response_time', '' );
	$intro    = personal_site_opt( 'contact', 'intro', '' );
	$title    = apply_filters( 'personal_site_contact_title', __( 'Get in touch', 'personal-site' ) );
	?>
	<div class="container">
		<header class="page-header">
			<p class="eyebrow"><?php esc_html_e( 'Contact', 'personal-site' ); ?></p>
			<h1><?php echo esc_html( $title ); ?></h1>
			<?php if ( $intro ) : ?>
				<p class="lede page-header__intro"><?php echo esc_html( $intro ); ?></p>
			<?php endif; ?>
		</header>
	</div>

	<div class="section page-body">
		<div class="container">
			<div class="contact-layout">
				<aside class="contact-info">
					<?php if ( $email ) : ?>
						<div class="contact-info__item">
							<p class="eyebrow"><?php esc_html_e( 'Email', 'personal-site' ); ?></p>
							<p><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></p>
						</div>
					<?php endif; ?>
					<?php if ( $based_in ) : ?>
						<div class="contact-info__item">
							<p class="eyebrow"><?php esc_html_e( 'Based in', 'personal-site' ); ?></p>
							<p><?php echo esc_html( $based_in ); ?></p>
						</div>
					<?php endif; ?>
					<?php if ( $response ) : ?>
						<div class="contact-info__item">
							<p class="eyebrow"><?php esc_html_e( 'Response', 'personal-site' ); ?></p>
							<p><?php echo esc_html( $response ); ?></p>
						</div>
					<?php endif; ?>
					<div class="contact-info__item">
						<p class="eyebrow"><?php esc_html_e( 'Elsewhere', 'personal-site' ); ?></p>
						<?php personal_site_social_links(); ?>
					</div>
				</aside>

				<div class="contact-form-col">
					<?php
					if ( trim( get_the_content() ) ) {
						echo '<div class="prose" style="margin-bottom: var(--space-6);">';
						the_content();
						echo '</div>';
					}
					echo personal_site_contact_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside.
					?>
				</div>
			</div>
		</div>
	</div>
	<?php
endwhile;

get_footer();
