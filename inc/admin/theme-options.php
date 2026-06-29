<?php
/**
 * Theme Options admin screen (Appearance -> Theme Options).
 *
 * One tabbed form (General / Home / About / Contact) that saves the whole
 * options array through admin-post.php with a nonce and capability check.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the options page under Appearance.
 */
function personal_site_options_menu() {
	add_theme_page(
		__( 'Theme Options', 'personal-site' ),
		__( 'Theme Options', 'personal-site' ),
		'manage_options',
		'personal-site-options',
		'personal_site_render_options_page'
	);
}
add_action( 'admin_menu', 'personal_site_options_menu' );

/**
 * Enqueue the options-screen assets (custom UI + media uploader).
 *
 * @param string $hook Current admin page hook.
 */
function personal_site_options_assets( $hook ) {
	if ( 'appearance_page_personal-site-options' !== $hook ) {
		return;
	}
	wp_enqueue_media();

	$css = '/assets/css/admin-options.css';
	wp_enqueue_style( 'personal-site-admin', PERSONAL_SITE_URI . $css, array(), filemtime( PERSONAL_SITE_DIR . $css ) );

	$js = '/assets/js/admin-options.js';
	wp_enqueue_script( 'personal-site-admin', PERSONAL_SITE_URI . $js, array( 'jquery' ), filemtime( PERSONAL_SITE_DIR . $js ), true );
	wp_localize_script(
		'personal-site-admin',
		'personalSiteAdmin',
		array(
			'choose' => __( 'Choose image', 'personal-site' ),
			'use'    => __( 'Use this image', 'personal-site' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'personal_site_options_assets' );

/**
 * Persist the submitted options.
 */
function personal_site_save_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'personal-site' ) );
	}
	check_admin_referer( 'personal_site_save_options' );

	$raw   = isset( $_POST['personal_site_options'] ) ? wp_unslash( $_POST['personal_site_options'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitized below.
	$clean = personal_site_sanitize_options( $raw );
	update_option( PERSONAL_SITE_OPTION, $clean );

	$tab = isset( $_POST['ps_active_tab'] ) ? sanitize_key( wp_unslash( $_POST['ps_active_tab'] ) ) : 'general';
	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => 'personal-site-options',
				'updated' => 'true',
				'tab'     => $tab,
			),
			admin_url( 'themes.php' )
		)
	);
	exit;
}
add_action( 'admin_post_personal_site_save_options', 'personal_site_save_options' );

/* -------------------------------------------------------------------------
 * Field helpers
 * ---------------------------------------------------------------------- */

function personal_site_field_name( $section, $key ) {
	return sprintf( 'personal_site_options[%s][%s]', esc_attr( $section ), esc_attr( $key ) );
}

function personal_site_field_text( $section, $key, $label, $desc = '', $type = 'text' ) {
	$value = personal_site_opt( $section, $key, '' );
	?>
	<div class="ps-field">
		<label for="<?php echo esc_attr( "{$section}_{$key}" ); ?>"><?php echo esc_html( $label ); ?></label>
		<input type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( "{$section}_{$key}" ); ?>"
			name="<?php echo esc_attr( personal_site_field_name( $section, $key ) ); ?>"
			value="<?php echo esc_attr( $value ); ?>" class="ps-input" />
		<?php if ( $desc ) : ?><p class="ps-help"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
	</div>
	<?php
}

function personal_site_field_textarea( $section, $key, $label, $desc = '' ) {
	$value = personal_site_opt( $section, $key, '' );
	?>
	<div class="ps-field">
		<label for="<?php echo esc_attr( "{$section}_{$key}" ); ?>"><?php echo esc_html( $label ); ?></label>
		<textarea id="<?php echo esc_attr( "{$section}_{$key}" ); ?>" rows="3"
			name="<?php echo esc_attr( personal_site_field_name( $section, $key ) ); ?>" class="ps-input"><?php echo esc_textarea( $value ); ?></textarea>
		<?php if ( $desc ) : ?><p class="ps-help"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
	</div>
	<?php
}

function personal_site_field_number( $section, $key, $label, $desc = '', $min = 1, $max = 12 ) {
	$value = personal_site_opt( $section, $key, $min );
	?>
	<div class="ps-field ps-field--narrow">
		<label for="<?php echo esc_attr( "{$section}_{$key}" ); ?>"><?php echo esc_html( $label ); ?></label>
		<input type="number" id="<?php echo esc_attr( "{$section}_{$key}" ); ?>" min="<?php echo esc_attr( $min ); ?>" max="<?php echo esc_attr( $max ); ?>"
			name="<?php echo esc_attr( personal_site_field_name( $section, $key ) ); ?>" value="<?php echo esc_attr( $value ); ?>" class="ps-input" />
		<?php if ( $desc ) : ?><p class="ps-help"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
	</div>
	<?php
}

function personal_site_field_checkbox( $section, $key, $label, $desc = '' ) {
	$value = personal_site_opt( $section, $key, 0 );
	?>
	<div class="ps-field ps-field--toggle">
		<label class="ps-switch">
			<input type="checkbox" name="<?php echo esc_attr( personal_site_field_name( $section, $key ) ); ?>" value="1" <?php checked( $value, 1 ); ?> />
			<span class="ps-switch__track" aria-hidden="true"></span>
			<span class="ps-switch__label"><?php echo esc_html( $label ); ?></span>
		</label>
		<?php if ( $desc ) : ?><p class="ps-help"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
	</div>
	<?php
}

function personal_site_field_color( $section, $key, $label, $desc = '' ) {
	$value = personal_site_opt( $section, $key, '#345b7d' );
	?>
	<div class="ps-field ps-field--narrow">
		<label for="<?php echo esc_attr( "{$section}_{$key}" ); ?>"><?php echo esc_html( $label ); ?></label>
		<input type="color" id="<?php echo esc_attr( "{$section}_{$key}" ); ?>"
			name="<?php echo esc_attr( personal_site_field_name( $section, $key ) ); ?>" value="<?php echo esc_attr( $value ); ?>" class="ps-color" />
		<?php if ( $desc ) : ?><p class="ps-help"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
	</div>
	<?php
}

function personal_site_field_media( $section, $key, $label, $desc = '' ) {
	$id    = (int) personal_site_opt( $section, $key, 0 );
	$src   = $id ? wp_get_attachment_image_url( $id, 'medium' ) : '';
	$class = $src ? 'ps-media has-image' : 'ps-media';
	?>
	<div class="ps-field">
		<label><?php echo esc_html( $label ); ?></label>
		<div class="<?php echo esc_attr( $class ); ?>" data-ps-media>
			<div class="ps-media__preview">
				<?php if ( $src ) : ?><img src="<?php echo esc_url( $src ); ?>" alt="" /><?php endif; ?>
			</div>
			<input type="hidden" name="<?php echo esc_attr( personal_site_field_name( $section, $key ) ); ?>" value="<?php echo esc_attr( $id ); ?>" data-ps-media-input />
			<div class="ps-media__actions">
				<button type="button" class="button" data-ps-media-select><?php esc_html_e( 'Select image', 'personal-site' ); ?></button>
				<button type="button" class="button-link ps-media__remove" data-ps-media-remove><?php esc_html_e( 'Remove', 'personal-site' ); ?></button>
			</div>
		</div>
		<?php if ( $desc ) : ?><p class="ps-help"><?php echo esc_html( $desc ); ?></p><?php endif; ?>
	</div>
	<?php
}

/* -------------------------------------------------------------------------
 * Page render
 * ---------------------------------------------------------------------- */

function personal_site_render_options_page() {
	$tabs = array(
		'general' => __( 'General', 'personal-site' ),
		'about'   => __( 'About', 'personal-site' ),
		'contact' => __( 'Contact', 'personal-site' ),
		'footer'  => __( 'Footer', 'personal-site' ),
	);
	$active = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $tabs[ $active ] ) ) {
		$active = 'general';
	}
	?>
	<div class="wrap ps-options">
		<h1 class="ps-options__title"><?php esc_html_e( 'Personal Site — Theme Options', 'personal-site' ); ?></h1>

		<?php if ( isset( $_GET['updated'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="ps-notice"><?php esc_html_e( 'Options saved.', 'personal-site' ); ?></div>
		<?php endif; ?>

		<nav class="ps-tabs" role="tablist">
			<?php foreach ( $tabs as $slug => $label ) : ?>
				<button type="button" class="ps-tab<?php echo $active === $slug ? ' is-active' : ''; ?>" role="tab" data-ps-tab="<?php echo esc_attr( $slug ); ?>">
					<?php echo esc_html( $label ); ?>
				</button>
			<?php endforeach; ?>
		</nav>

		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ps-form">
			<input type="hidden" name="action" value="personal_site_save_options" />
			<input type="hidden" name="ps_active_tab" value="<?php echo esc_attr( $active ); ?>" data-ps-active-tab />
			<?php wp_nonce_field( 'personal_site_save_options' ); ?>

			<?php
			personal_site_render_tab_general( $active );
			personal_site_render_tab_about( $active );
			personal_site_render_tab_contact( $active );
			personal_site_render_tab_footer( $active );
			?>

			<div class="ps-save">
				<button type="submit" class="button button-primary button-hero"><?php esc_html_e( 'Save Changes', 'personal-site' ); ?></button>
			</div>
		</form>
	</div>
	<?php
}

function personal_site_panel_open( $slug, $active ) {
	printf(
		'<section class="ps-panel%s" data-ps-panel="%s" role="tabpanel"%s>',
		$active === $slug ? ' is-active' : '',
		esc_attr( $slug ),
		$active === $slug ? '' : ' hidden'
	);
}

function personal_site_render_tab_general( $active ) {
	personal_site_panel_open( 'general', $active );
	?>
	<div class="ps-callout" style="margin-bottom: 18px;">
		<?php esc_html_e( 'Homepage sections (Hero, Focus, Selected Work, Recent Posts, Call to action) are blocks. Edit your front page and add them from the "Personal Site" category in the block inserter.', 'personal-site' ); ?>
	</div>
	<div class="ps-card">
		<h2><?php esc_html_e( 'Branding', 'personal-site' ); ?></h2>
		<?php
		personal_site_field_color( 'general', 'accent', __( 'Accent color', 'personal-site' ), __( 'Links, buttons, and highlights.', 'personal-site' ) );
		?>
	</div>
	<div class="ps-card">
		<h2><?php esc_html_e( 'Social links', 'personal-site' ); ?></h2>
		<div class="ps-grid-2">
			<?php
			personal_site_field_text( 'general', 'social_github', __( 'GitHub URL', 'personal-site' ), '', 'url' );
			personal_site_field_text( 'general', 'social_x', __( 'X URL', 'personal-site' ), '', 'url' );
			personal_site_field_text( 'general', 'social_linkedin', __( 'LinkedIn URL', 'personal-site' ), '', 'url' );
			personal_site_field_text( 'general', 'social_instagram', __( 'Instagram URL', 'personal-site' ), '', 'url' );
			personal_site_field_text( 'general', 'social_facebook', __( 'Facebook URL', 'personal-site' ), '', 'url' );
			personal_site_field_text( 'general', 'social_email', __( 'Email address', 'personal-site' ), '', 'email' );
			?>
		</div>
	</div>
	</section>
	<?php
}

function personal_site_render_tab_home( $active ) {
	personal_site_panel_open( 'home', $active );
	?>
	<div class="ps-card">
		<div class="ps-card__head">
			<h2><?php esc_html_e( 'Hero section', 'personal-site' ); ?></h2>
			<?php personal_site_field_checkbox( 'home', 'hero_enabled', __( 'Show', 'personal-site' ) ); ?>
		</div>
		<?php
		personal_site_field_text( 'home', 'hero_heading', __( 'Heading', 'personal-site' ) );
		personal_site_field_textarea( 'home', 'hero_text', __( 'Intro text', 'personal-site' ) );
		?>
		<div class="ps-grid-2">
			<?php
			personal_site_field_text( 'home', 'hero_primary_label', __( 'Primary button label', 'personal-site' ) );
			personal_site_field_text( 'home', 'hero_primary_url', __( 'Primary button URL', 'personal-site' ), __( 'Defaults to the portfolio archive.', 'personal-site' ), 'url' );
			personal_site_field_text( 'home', 'hero_secondary_label', __( 'Secondary button label', 'personal-site' ) );
			personal_site_field_text( 'home', 'hero_secondary_url', __( 'Secondary button URL', 'personal-site' ), __( 'Defaults to the blog.', 'personal-site' ), 'url' );
			?>
		</div>
	</div>

	<div class="ps-card">
		<div class="ps-card__head">
			<h2><?php esc_html_e( 'Focus section', 'personal-site' ); ?></h2>
			<?php personal_site_field_checkbox( 'home', 'focus_enabled', __( 'Show', 'personal-site' ) ); ?>
		</div>
		<?php personal_site_field_text( 'home', 'focus_heading', __( 'Heading', 'personal-site' ) ); ?>
		<?php personal_site_render_focus_repeater(); ?>
	</div>

	<div class="ps-card">
		<div class="ps-card__head">
			<h2><?php esc_html_e( 'Selected work section', 'personal-site' ); ?></h2>
			<?php personal_site_field_checkbox( 'home', 'work_enabled', __( 'Show', 'personal-site' ) ); ?>
		</div>
		<div class="ps-grid-2">
			<?php
			personal_site_field_text( 'home', 'work_heading', __( 'Heading', 'personal-site' ) );
			personal_site_field_number( 'home', 'work_count', __( 'Projects to show', 'personal-site' ) );
			?>
		</div>
	</div>

	<div class="ps-card">
		<div class="ps-card__head">
			<h2><?php esc_html_e( 'Recent posts section', 'personal-site' ); ?></h2>
			<?php personal_site_field_checkbox( 'home', 'posts_enabled', __( 'Show', 'personal-site' ) ); ?>
		</div>
		<div class="ps-grid-2">
			<?php
			personal_site_field_text( 'home', 'posts_heading', __( 'Heading', 'personal-site' ) );
			personal_site_field_number( 'home', 'posts_count', __( 'Posts to show', 'personal-site' ) );
			?>
		</div>
	</div>

	<div class="ps-card">
		<div class="ps-card__head">
			<h2><?php esc_html_e( 'Call to action section', 'personal-site' ); ?></h2>
			<?php personal_site_field_checkbox( 'home', 'cta_enabled', __( 'Show', 'personal-site' ) ); ?>
		</div>
		<?php personal_site_field_text( 'home', 'cta_heading', __( 'Heading', 'personal-site' ) ); ?>
		<div class="ps-grid-2">
			<?php
			personal_site_field_text( 'home', 'cta_label', __( 'Button label', 'personal-site' ) );
			personal_site_field_text( 'home', 'cta_url', __( 'Button URL', 'personal-site' ), __( 'Defaults to your email or Contact page.', 'personal-site' ), 'url' );
			?>
		</div>
	</div>
	</section>
	<?php
}

function personal_site_render_tab_about( $active ) {
	personal_site_panel_open( 'about', $active );
	?>
	<div class="ps-card">
		<h2><?php esc_html_e( 'Portrait', 'personal-site' ); ?></h2>
		<?php personal_site_field_media( 'about', 'portrait_id', __( 'Photo', 'personal-site' ), __( 'Shown beside the intro on the About page. Falls back to the page featured image.', 'personal-site' ) ); ?>
	</div>

	<div class="ps-card">
		<h2><?php esc_html_e( 'Quick facts', 'personal-site' ); ?></h2>
		<div class="ps-grid-2">
			<?php
			personal_site_field_text( 'about', 'role', __( 'Role', 'personal-site' ) );
			personal_site_field_text( 'about', 'location', __( 'Based in', 'personal-site' ) );
			personal_site_field_text( 'about', 'focus', __( 'Focus', 'personal-site' ) );
			personal_site_field_text( 'about', 'experience', __( 'Experience', 'personal-site' ) );
			?>
		</div>
	</div>

	<div class="ps-card">
		<h2><?php esc_html_e( 'Career history', 'personal-site' ); ?></h2>
		<?php personal_site_field_text( 'about', 'timeline_heading', __( 'Section heading', 'personal-site' ) ); ?>
		<?php personal_site_render_rows_repeater( 'timeline', __( 'Add timeline entry', 'personal-site' ) ); ?>
	</div>

	<div class="ps-card">
		<h2><?php esc_html_e( 'Freelance', 'personal-site' ); ?></h2>
		<?php personal_site_field_text( 'about', 'freelance_heading', __( 'Section heading', 'personal-site' ) ); ?>
		<?php personal_site_render_rows_repeater( 'freelance', __( 'Add freelance entry', 'personal-site' ) ); ?>
	</div>

	<div class="ps-card">
		<h2><?php esc_html_e( 'Education', 'personal-site' ); ?></h2>
		<?php personal_site_field_text( 'about', 'education', __( 'Education line', 'personal-site' ), __( 'e.g. B.Sc. Computer Science · University Name', 'personal-site' ) ); ?>
	</div>
	</section>
	<?php
}

function personal_site_render_tab_contact( $active ) {
	personal_site_panel_open( 'contact', $active );
	?>
	<div class="ps-card">
		<h2><?php esc_html_e( 'Contact form', 'personal-site' ); ?></h2>
		<?php
		personal_site_field_textarea( 'contact', 'intro', __( 'Intro text', 'personal-site' ), __( 'Shown under the heading, above the form.', 'personal-site' ) );
		personal_site_field_text( 'contact', 'success_message', __( 'Success message', 'personal-site' ) );
		?>
		<div class="ps-grid-2">
			<?php
			personal_site_field_text( 'contact', 'based_in', __( 'Based in', 'personal-site' ), __( 'e.g. Jakarta, Indonesia · GMT+7', 'personal-site' ) );
			personal_site_field_text( 'contact', 'response_time', __( 'Response time', 'personal-site' ), __( 'e.g. Usually within a day or two.', 'personal-site' ) );
			?>
		</div>
		<p class="ps-help"><?php esc_html_e( 'Submissions appear under the Contact menu near Appearance. File uploads are never accepted.', 'personal-site' ); ?></p>
	</div>

	<div class="ps-card">
		<h2><?php esc_html_e( 'Email notifications', 'personal-site' ); ?></h2>
		<?php
		personal_site_field_checkbox( 'contact', 'notify_enabled', __( 'Email me when someone submits the form', 'personal-site' ) );
		personal_site_field_text( 'contact', 'notify_email', __( 'Notification email', 'personal-site' ), __( 'Defaults to the site admin email.', 'personal-site' ), 'email' );
		?>
		<div class="ps-callout">
			<?php esc_html_e( 'WordPress sends email through your server by default, which often fails or lands in spam. For reliable delivery, install and configure an SMTP plugin (for example WP Mail SMTP).', 'personal-site' ); ?>
		</div>
	</div>

	<div class="ps-card">
		<h2><?php esc_html_e( 'Spam protection (Cloudflare Turnstile)', 'personal-site' ); ?></h2>
		<?php personal_site_field_checkbox( 'contact', 'turnstile_enabled', __( 'Require a Turnstile challenge on the contact form', 'personal-site' ) ); ?>
		<div class="ps-grid-2" style="margin-top: 16px;">
			<?php
			personal_site_field_text( 'contact', 'turnstile_site_key', __( 'Site key', 'personal-site' ) );
			personal_site_field_text( 'contact', 'turnstile_secret_key', __( 'Secret key', 'personal-site' ), '', 'password' );
			?>
		</div>
		<div class="ps-callout">
			<?php
			printf(
				/* translators: %s: Cloudflare Turnstile dashboard URL. */
				wp_kses( __( 'Create a free widget at <a href="%s" target="_blank" rel="noopener">Cloudflare Turnstile</a> and paste the Site and Secret keys here. The secret key never leaves your server.', 'personal-site' ), array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ),
				esc_url( 'https://dash.cloudflare.com/?to=/:account/turnstile' )
			);
			?>
		</div>
	</div>
	</section>
	<?php
}

function personal_site_render_tab_footer( $active ) {
	personal_site_panel_open( 'footer', $active );
	?>
	<div class="ps-card">
		<h2><?php esc_html_e( 'Footer', 'personal-site' ); ?></h2>
		<?php
		personal_site_field_text( 'footer', 'tagline', __( 'Tagline', 'personal-site' ), __( 'Shown after the site name. Defaults to the site tagline (Settings → General).', 'personal-site' ) );
		personal_site_field_text( 'footer', 'copyright', __( 'Copyright line', 'personal-site' ), __( 'Use {year} and {name} as placeholders. Leave blank for the default copyright.', 'personal-site' ) );
		?>
		<div class="ps-callout">
			<?php esc_html_e( 'The footer navigation uses the menu assigned to the Footer location (or the Primary menu). Manage it under Appearance → Menus.', 'personal-site' ); ?>
		</div>
	</div>
	</section>
	<?php
}

/**
 * Repeater: focus items (title + text).
 */
function personal_site_render_focus_repeater() {
	$items = personal_site_opt( 'home', 'focus_items', array() );
	?>
	<div class="ps-repeater" data-ps-repeater data-ps-prefix="personal_site_options[home][focus_items]">
		<div class="ps-repeater__items">
			<?php foreach ( $items as $i => $item ) : ?>
				<?php personal_site_focus_row( $i, $item ); ?>
			<?php endforeach; ?>
		</div>
		<template data-ps-template><?php personal_site_focus_row( '__index__', array() ); ?></template>
		<button type="button" class="button ps-repeater__add" data-ps-add><?php esc_html_e( 'Add focus item', 'personal-site' ); ?></button>
	</div>
	<?php
}

function personal_site_focus_row( $index, $item ) {
	$base  = "personal_site_options[home][focus_items][{$index}]";
	$title = isset( $item['title'] ) ? $item['title'] : '';
	$text  = isset( $item['text'] ) ? $item['text'] : '';
	?>
	<div class="ps-repeater__row" data-ps-row>
		<div class="ps-repeater__fields">
			<input type="text" class="ps-input" placeholder="<?php esc_attr_e( 'Title', 'personal-site' ); ?>" name="<?php echo esc_attr( "{$base}[title]" ); ?>" value="<?php echo esc_attr( $title ); ?>" />
			<input type="text" class="ps-input" placeholder="<?php esc_attr_e( 'Short description', 'personal-site' ); ?>" name="<?php echo esc_attr( "{$base}[text]" ); ?>" value="<?php echo esc_attr( $text ); ?>" />
		</div>
		<button type="button" class="ps-repeater__remove" data-ps-remove aria-label="<?php esc_attr_e( 'Remove', 'personal-site' ); ?>">&times;</button>
	</div>
	<?php
}

/**
 * Repeater: timeline-style rows (career history, freelance).
 *
 * @param string $field     Option key under "about" (timeline or freelance).
 * @param string $add_label Button text.
 */
function personal_site_render_rows_repeater( $field, $add_label ) {
	$rows = personal_site_opt( 'about', $field, array() );
	?>
	<div class="ps-repeater" data-ps-repeater data-ps-prefix="personal_site_options[about][<?php echo esc_attr( $field ); ?>]">
		<div class="ps-repeater__items">
			<?php foreach ( $rows as $i => $row ) : ?>
				<?php personal_site_rows_row( $field, $i, $row ); ?>
			<?php endforeach; ?>
		</div>
		<template data-ps-template><?php personal_site_rows_row( $field, '__index__', array() ); ?></template>
		<button type="button" class="button ps-repeater__add" data-ps-add><?php echo esc_html( $add_label ); ?></button>
	</div>
	<?php
}

function personal_site_rows_row( $field, $index, $row ) {
	$base    = "personal_site_options[about][{$field}][{$index}]";
	$period  = isset( $row['period'] ) ? $row['period'] : '';
	$role    = isset( $row['role'] ) ? $row['role'] : '';
	$org     = isset( $row['org'] ) ? $row['org'] : '';
	$summary = isset( $row['summary'] ) ? $row['summary'] : '';
	?>
	<div class="ps-repeater__row ps-repeater__row--stack" data-ps-row>
		<div class="ps-repeater__fields ps-grid-3">
			<input type="text" class="ps-input" placeholder="<?php esc_attr_e( 'Period (e.g. 2024 — Present)', 'personal-site' ); ?>" name="<?php echo esc_attr( "{$base}[period]" ); ?>" value="<?php echo esc_attr( $period ); ?>" />
			<input type="text" class="ps-input" placeholder="<?php esc_attr_e( 'Role', 'personal-site' ); ?>" name="<?php echo esc_attr( "{$base}[role]" ); ?>" value="<?php echo esc_attr( $role ); ?>" />
			<input type="text" class="ps-input" placeholder="<?php esc_attr_e( 'Organization (optional)', 'personal-site' ); ?>" name="<?php echo esc_attr( "{$base}[org]" ); ?>" value="<?php echo esc_attr( $org ); ?>" />
		</div>
		<textarea class="ps-input" rows="2" placeholder="<?php esc_attr_e( 'Summary', 'personal-site' ); ?>" name="<?php echo esc_attr( "{$base}[summary]" ); ?>"><?php echo esc_textarea( $summary ); ?></textarea>
		<button type="button" class="ps-repeater__remove" data-ps-remove aria-label="<?php esc_attr_e( 'Remove', 'personal-site' ); ?>">&times;</button>
	</div>
	<?php
}
