<?php
/**
 * Self-contained, plugin-free contact form.
 *
 * Security layers: nonce, signed time-trap (rejects instant + stale posts),
 * honeypot, same-origin referer check, per-IP rate limiting, strict
 * sanitization, length caps, a link-spam heuristic, and absolutely no file
 * uploads. Submissions are stored as a private `ps_submission` post type listed
 * under a top-level "Contact" admin menu.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PERSONAL_SITE_RL_MAX    = 5;            // Max submissions per window, per IP.
const PERSONAL_SITE_RL_WINDOW = HOUR_IN_SECONDS;
const PERSONAL_SITE_MIN_TIME  = 3;            // Seconds a human needs to fill the form.

/* -------------------------------------------------------------------------
 * Submissions post type + admin screen
 * ---------------------------------------------------------------------- */

/**
 * Register the private submissions type with its own top-level menu.
 */
function personal_site_register_submissions() {
	register_post_type(
		'ps_submission',
		array(
			'labels'              => array(
				'name'          => __( 'Contact', 'personal-site' ),
				'singular_name' => __( 'Submission', 'personal-site' ),
				'menu_name'     => __( 'Contact', 'personal-site' ),
				'all_items'     => __( 'Submissions', 'personal-site' ),
				'edit_item'     => __( 'View Submission', 'personal-site' ),
				'search_items'  => __( 'Search Submissions', 'personal-site' ),
				'not_found'     => __( 'No submissions yet.', 'personal-site' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'menu_position'       => 59,
			'menu_icon'           => 'dashicons-email-alt',
			'supports'            => array( 'title' ),
			'capability_type'     => 'post',
			'map_meta_cap'        => true,
			'capabilities'        => array( 'create_posts' => 'do_not_allow' ),
			'show_in_rest'        => false,
			'rewrite'             => false,
			'query_var'           => false,
			'exclude_from_search' => true,
		)
	);
}
add_action( 'init', 'personal_site_register_submissions' );

/**
 * Drop the "Add New" affordances; submissions are created by the form only.
 */
function personal_site_submissions_menu_cleanup() {
	remove_submenu_page( 'edit.php?post_type=ps_submission', 'post-new.php?post_type=ps_submission' );
}
add_action( 'admin_menu', 'personal_site_submissions_menu_cleanup', 999 );

/**
 * Block direct access to the "add new submission" screen.
 */
function personal_site_block_new_submission() {
	$screen = get_current_screen();
	if ( $screen && 'ps_submission' === $screen->post_type && 'add' === $screen->action ) {
		wp_safe_redirect( admin_url( 'edit.php?post_type=ps_submission' ) );
		exit;
	}
}
add_action( 'current_screen', 'personal_site_block_new_submission' );

/**
 * Custom columns for the submissions list.
 *
 * @param array $columns Columns.
 * @return array
 */
function personal_site_submission_columns( $columns ) {
	return array(
		'cb'         => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'      => __( 'Name', 'personal-site' ),
		'ps_email'   => __( 'Email', 'personal-site' ),
		'ps_subject' => __( 'Subject', 'personal-site' ),
		'date'       => __( 'Received', 'personal-site' ),
	);
}
add_filter( 'manage_ps_submission_posts_columns', 'personal_site_submission_columns' );

/**
 * Render the custom column values.
 *
 * @param string $column  Column key.
 * @param int    $post_id Submission ID.
 */
function personal_site_submission_column( $column, $post_id ) {
	if ( 'ps_email' === $column ) {
		$email = get_post_meta( $post_id, '_ps_email', true );
		if ( $email ) {
			printf( '<a href="mailto:%1$s">%1$s</a>', esc_attr( $email ) );
		}
	} elseif ( 'ps_subject' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_ps_subject', true ) );
	}
}
add_action( 'manage_ps_submission_posts_custom_column', 'personal_site_submission_column', 10, 2 );

/**
 * Read-only detail box on the submission edit screen.
 */
function personal_site_submission_meta_box() {
	add_meta_box( 'ps_submission_detail', __( 'Submission', 'personal-site' ), 'personal_site_render_submission_detail', 'ps_submission', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'personal_site_submission_meta_box' );

/**
 * Render the submission detail.
 *
 * @param WP_Post $post Submission.
 */
function personal_site_render_submission_detail( $post ) {
	$email   = get_post_meta( $post->ID, '_ps_email', true );
	$subject = get_post_meta( $post->ID, '_ps_subject', true );
	$ip      = get_post_meta( $post->ID, '_ps_ip', true );
	$ua      = get_post_meta( $post->ID, '_ps_ua', true );
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th><?php esc_html_e( 'Email', 'personal-site' ); ?></th>
			<td><?php echo $email ? '<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>' : '&mdash;'; ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Subject', 'personal-site' ); ?></th>
			<td><?php echo $subject ? esc_html( $subject ) : '&mdash;'; ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Message', 'personal-site' ); ?></th>
			<td><?php echo nl2br( esc_html( $post->post_content ) ); ?></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'IP address', 'personal-site' ); ?></th>
			<td><code><?php echo esc_html( $ip ); ?></code></td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'User agent', 'personal-site' ); ?></th>
			<td><small><?php echo esc_html( $ua ); ?></small></td>
		</tr>
	</table>
	<?php
}

/* -------------------------------------------------------------------------
 * Front-end form
 * ---------------------------------------------------------------------- */

/**
 * The signed token used to detect bots that submit too fast or too late.
 *
 * @return string "timestamp|signature"
 */
function personal_site_time_token() {
	$ts = time();
	return $ts . '|' . hash_hmac( 'sha256', (string) $ts, wp_salt( 'nonce' ) );
}

/**
 * Best-effort client IP (REMOTE_ADDR only; forwarded headers are not trusted
 * so the rate limit cannot be bypassed by spoofing them).
 *
 * @return string
 */
function personal_site_client_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? wp_unslash( $_SERVER['REMOTE_ADDR'] ) : '';
	return filter_var( $ip, FILTER_VALIDATE_IP ) ? $ip : '0.0.0.0';
}

/**
 * Human-readable error for a failure code.
 *
 * @param string $code Failure code.
 * @return string
 */
function personal_site_contact_error( $code ) {
	$messages = array(
		'nonce'   => __( 'Your session expired. Please reload the page and try again.', 'personal-site' ),
		'expired' => __( 'The form expired. Please reload the page and try again.', 'personal-site' ),
		'toofast' => __( 'That was a little too fast. Please try again.', 'personal-site' ),
		'toomany' => __( 'Too many messages from your network. Please try again later.', 'personal-site' ),
		'email'   => __( 'Please enter a valid email address.', 'personal-site' ),
		'message' => __( 'Please write a message between 10 and 5000 characters.', 'personal-site' ),
		'invalid' => __( 'Please check the form and try again.', 'personal-site' ),
		'spam'      => __( 'Your message looked like spam and was not sent.', 'personal-site' ),
		'turnstile' => __( 'Please complete the verification and try again.', 'personal-site' ),
		'server'    => __( 'Something went wrong. Please try again.', 'personal-site' ),
	);
	return isset( $messages[ $code ] ) ? $messages[ $code ] : $messages['invalid'];
}

/**
 * Whether the Turnstile challenge is active (enabled + a site key is set).
 *
 * @return bool
 */
function personal_site_turnstile_active() {
	return (bool) personal_site_opt( 'contact', 'turnstile_enabled', 0 ) && personal_site_opt( 'contact', 'turnstile_site_key', '' );
}

/**
 * Load the Turnstile script on the contact page when the challenge is active.
 */
function personal_site_turnstile_assets() {
	if ( is_page_template( 'template-contact.php' ) && personal_site_turnstile_active() ) {
		wp_enqueue_script( 'cf-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true );
	}
}
add_action( 'wp_enqueue_scripts', 'personal_site_turnstile_assets' );

/**
 * Render the contact form markup.
 *
 * @return string
 */
function personal_site_contact_form() {
	$status = isset( $_GET['ps_contact'] ) ? sanitize_key( wp_unslash( $_GET['ps_contact'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	ob_start();
	?>
	<div class="ps-contact" id="contact">
		<?php if ( 'sent' === $status ) : ?>
			<p class="ps-form-notice ps-form-notice--ok" role="status">
				<?php echo esc_html( personal_site_opt( 'contact', 'success_message', __( 'Thanks. Your message is on its way.', 'personal-site' ) ) ); ?>
			</p>
		<?php elseif ( $status ) : ?>
			<p class="ps-form-notice ps-form-notice--err" role="alert">
				<?php echo esc_html( personal_site_contact_error( $status ) ); ?>
			</p>
		<?php endif; ?>

		<form class="ps-contact-form stack" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
			<input type="hidden" name="action" value="ps_contact_submit" />
			<input type="hidden" name="ps_ts" value="<?php echo esc_attr( personal_site_time_token() ); ?>" />
			<input type="hidden" name="ps_redirect" value="<?php echo esc_url( get_permalink() ); ?>" />
			<?php wp_nonce_field( 'ps_contact_submit', 'ps_contact_nonce' ); ?>

			<div class="ps-hp" aria-hidden="true">
				<label><?php esc_html_e( 'Leave this field empty', 'personal-site' ); ?>
					<input type="text" name="ps_website" tabindex="-1" autocomplete="off" />
				</label>
			</div>

			<div class="field-row">
				<div class="field">
					<label for="ps-name"><?php esc_html_e( 'Name', 'personal-site' ); ?></label>
					<input id="ps-name" type="text" name="ps_name" maxlength="100" placeholder="<?php esc_attr_e( 'Jane Doe', 'personal-site' ); ?>" required />
				</div>
				<div class="field">
					<label for="ps-email"><?php esc_html_e( 'Email', 'personal-site' ); ?></label>
					<input id="ps-email" type="email" name="ps_email" maxlength="150" placeholder="jane@company.com" required />
				</div>
			</div>
			<div class="field">
				<label for="ps-subject"><?php esc_html_e( 'Subject', 'personal-site' ); ?> <span class="text-dim">(<?php esc_html_e( 'optional', 'personal-site' ); ?>)</span></label>
				<input id="ps-subject" type="text" name="ps_subject" maxlength="150" />
			</div>
			<div class="field">
				<label for="ps-message"><?php esc_html_e( 'Message', 'personal-site' ); ?></label>
				<textarea id="ps-message" name="ps_message" rows="6" maxlength="5000" required></textarea>
			</div>

			<?php if ( personal_site_turnstile_active() ) : ?>
				<div class="ps-turnstile cf-turnstile" data-sitekey="<?php echo esc_attr( personal_site_opt( 'contact', 'turnstile_site_key', '' ) ); ?>" data-theme="auto"></div>
			<?php endif; ?>

			<div>
				<button class="button" type="submit"><?php esc_html_e( 'Send message', 'personal-site' ); ?></button>
			</div>
		</form>
	</div>
	<?php
	return ob_get_clean();
}

/* -------------------------------------------------------------------------
 * Submission handler
 * ---------------------------------------------------------------------- */

/**
 * Validate, store, and (optionally) notify on a contact submission.
 */
function personal_site_handle_contact_submit() {
	$redirect = isset( $_POST['ps_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['ps_redirect'] ) ) : home_url( '/' );
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );

	$bounce = static function ( $code ) use ( $redirect ) {
		wp_safe_redirect( add_query_arg( 'ps_contact', $code, $redirect ) . '#contact' );
		exit;
	};

	// 1. Nonce.
	if ( ! isset( $_POST['ps_contact_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ps_contact_nonce'] ) ), 'ps_contact_submit' ) ) {
		$bounce( 'nonce' );
	}

	// 2. Honeypot — pretend success so bots do not learn anything.
	if ( ! empty( $_POST['ps_website'] ) ) {
		$bounce( 'sent' );
	}

	// 3. Same-origin referer. wp_get_referer() returns a path for same-site
	//    posts (no host), so only reject when an explicit foreign host appears.
	$referer = wp_get_referer();
	if ( $referer ) {
		$referer_host = wp_parse_url( $referer, PHP_URL_HOST );
		if ( $referer_host && $referer_host !== wp_parse_url( home_url(), PHP_URL_HOST ) ) {
			$bounce( 'invalid' );
		}
	}

	// 4. Signed time-trap.
	$token = isset( $_POST['ps_ts'] ) ? sanitize_text_field( wp_unslash( $_POST['ps_ts'] ) ) : '';
	$parts = explode( '|', $token );
	if ( 2 !== count( $parts ) || ! hash_equals( hash_hmac( 'sha256', $parts[0], wp_salt( 'nonce' ) ), $parts[1] ) ) {
		$bounce( 'expired' );
	}
	$elapsed = time() - (int) $parts[0];
	if ( $elapsed < PERSONAL_SITE_MIN_TIME ) {
		$bounce( 'toofast' );
	}
	if ( $elapsed > HOUR_IN_SECONDS ) {
		$bounce( 'expired' );
	}

	// 4b. Cloudflare Turnstile (if enabled).
	if ( personal_site_opt( 'contact', 'turnstile_enabled', 0 ) ) {
		$secret = personal_site_opt( 'contact', 'turnstile_secret_key', '' );
		$token  = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
		if ( ! $secret || ! $token ) {
			$bounce( 'turnstile' );
		}
		$verify = wp_remote_post(
			'https://challenges.cloudflare.com/turnstile/v0/siteverify',
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => personal_site_client_ip(),
				),
			)
		);
		if ( is_wp_error( $verify ) ) {
			$bounce( 'turnstile' );
		}
		$result = json_decode( wp_remote_retrieve_body( $verify ), true );
		if ( empty( $result['success'] ) ) {
			$bounce( 'turnstile' );
		}
	}

	// 5. Per-IP rate limit.
	$ip     = personal_site_client_ip();
	$rl_key = 'ps_rl_' . md5( $ip );
	$count  = (int) get_transient( $rl_key );
	if ( $count >= PERSONAL_SITE_RL_MAX ) {
		$bounce( 'toomany' );
	}

	// 6. Validate + sanitize.
	$name    = isset( $_POST['ps_name'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['ps_name'] ) ) ) : '';
	$email   = isset( $_POST['ps_email'] ) ? sanitize_email( wp_unslash( $_POST['ps_email'] ) ) : '';
	$subject = isset( $_POST['ps_subject'] ) ? trim( sanitize_text_field( wp_unslash( $_POST['ps_subject'] ) ) ) : '';
	$message = isset( $_POST['ps_message'] ) ? trim( sanitize_textarea_field( wp_unslash( $_POST['ps_message'] ) ) ) : '';

	if ( '' === $name || mb_strlen( $name ) > 100 ) {
		$bounce( 'invalid' );
	}
	if ( ! is_email( $email ) ) {
		$bounce( 'email' );
	}
	if ( mb_strlen( $message ) < 10 || mb_strlen( $message ) > 5000 ) {
		$bounce( 'message' );
	}
	if ( mb_strlen( $subject ) > 150 ) {
		$subject = mb_substr( $subject, 0, 150 );
	}
	// Link-spam heuristic.
	if ( preg_match_all( '#https?://#i', $message ) > 4 ) {
		$bounce( 'spam' );
	}

	// 7. Store.
	$post_id = wp_insert_post(
		array(
			'post_type'    => 'ps_submission',
			'post_status'  => 'private',
			'post_title'   => $name,
			'post_content' => $message,
		),
		true
	);
	if ( is_wp_error( $post_id ) ) {
		$bounce( 'server' );
	}

	update_post_meta( $post_id, '_ps_email', $email );
	if ( $subject ) {
		update_post_meta( $post_id, '_ps_subject', $subject );
	}
	update_post_meta( $post_id, '_ps_ip', $ip );
	$ua = isset( $_SERVER['HTTP_USER_AGENT'] ) ? substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 255 ) : '';
	update_post_meta( $post_id, '_ps_ua', $ua );

	// 8. Bump the rate-limit counter.
	set_transient( $rl_key, $count + 1, PERSONAL_SITE_RL_WINDOW );

	// 9. Optional notification.
	if ( personal_site_opt( 'contact', 'notify_enabled', 0 ) ) {
		$to = personal_site_opt( 'contact', 'notify_email', '' );
		$to = $to ? $to : get_option( 'admin_email' );
		if ( is_email( $to ) ) {
			/* translators: %s: sender name. */
			$mail_subject = sprintf( __( 'New contact message from %s', 'personal-site' ), $name );
			$body         = sprintf(
				"%s: %s\n%s: %s\n%s: %s\n\n%s\n",
				__( 'Name', 'personal-site' ),
				$name,
				__( 'Email', 'personal-site' ),
				$email,
				__( 'Subject', 'personal-site' ),
				$subject ? $subject : '-',
				$message
			);
			wp_mail( $to, $mail_subject, $body, array( 'Reply-To: ' . $email ) );
		}
	}

	$bounce( 'sent' );
}
add_action( 'admin_post_nopriv_ps_contact_submit', 'personal_site_handle_contact_submit' );
add_action( 'admin_post_ps_contact_submit', 'personal_site_handle_contact_submit' );
