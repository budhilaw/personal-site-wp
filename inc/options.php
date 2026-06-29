<?php
/**
 * Theme Options storage: defaults, getters, accent output, and sanitization.
 *
 * All options live in a single `personal_site_options` array so reads are one
 * query and the admin form maps cleanly to a schema.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PERSONAL_SITE_OPTION = 'personal_site_options';

/**
 * The full default schema. Also documents every option the theme reads.
 *
 * @return array
 */
function personal_site_default_options() {
	return array(
		'general' => array(
			'social_github'    => '',
			'social_x'         => '',
			'social_linkedin'  => '',
			'social_instagram' => '',
			'social_facebook'  => '',
			'social_email'     => '',
			'footer_text'      => '',
			'accent'           => '#345b7d',
		),
		'home'    => array(
			'hero_enabled'         => 1,
			'hero_heading'         => __( 'I build things for the web.', 'personal-site' ),
			'hero_text'            => __( 'Engineer and writer. I care about systems that stay fast, correct, and easy to live with.', 'personal-site' ),
			'hero_primary_label'   => __( 'View work', 'personal-site' ),
			'hero_primary_url'     => '',
			'hero_secondary_label' => __( 'Read writing', 'personal-site' ),
			'hero_secondary_url'   => '',

			'focus_enabled'        => 1,
			'focus_heading'        => __( 'What I focus on', 'personal-site' ),
			'focus_items'          => array(
				array(
					'title' => __( 'Backend systems', 'personal-site' ),
					'text'  => __( 'APIs and services built to stay correct under load.', 'personal-site' ),
				),
				array(
					'title' => __( 'Developer tools', 'personal-site' ),
					'text'  => __( 'Tooling that makes the right thing the easy thing.', 'personal-site' ),
				),
				array(
					'title' => __( 'Writing', 'personal-site' ),
					'text'  => __( 'Notes on what I learn while shipping.', 'personal-site' ),
				),
			),

			'work_enabled'         => 1,
			'work_heading'         => __( 'Selected work', 'personal-site' ),
			'work_count'           => 3,

			'posts_enabled'        => 1,
			'posts_heading'        => __( 'Recent writing', 'personal-site' ),
			'posts_count'          => 3,

			'cta_enabled'          => 1,
			'cta_heading'          => __( 'Want to work together?', 'personal-site' ),
			'cta_label'            => __( 'Get in touch', 'personal-site' ),
			'cta_url'              => '',
		),
		'about'   => array(
			'portrait_id'       => 0,
			'role'              => __( 'Senior Software Engineer', 'personal-site' ),
			'location'          => __( 'Jakarta, Indonesia', 'personal-site' ),
			'focus'             => __( 'Reliability & distributed systems', 'personal-site' ),
			'experience'        => __( '8+ years building for scale', 'personal-site' ),
			'timeline_heading'  => __( 'Career history', 'personal-site' ),
			'timeline'          => array(),
			'freelance_heading' => __( 'Alongside — freelance', 'personal-site' ),
			'freelance'         => array(),
			'education'         => '',
		),
		'contact' => array(
			'intro'                => '',
			'based_in'             => '',
			'response_time'        => '',
			'success_message'      => __( 'Thanks. Your message is on its way.', 'personal-site' ),
			'notify_enabled'       => 0,
			'notify_email'         => '',
			'turnstile_enabled'    => 0,
			'turnstile_site_key'   => '',
			'turnstile_secret_key' => '',
		),
		'footer'  => array(
			'tagline'   => '',
			'copyright' => '',
		),
	);
}

/**
 * Read the merged options (stored over defaults), cached per request.
 *
 * @return array
 */
function personal_site_options() {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$defaults = personal_site_default_options();
	$stored   = get_option( PERSONAL_SITE_OPTION, array() );
	$merged   = $defaults;

	if ( is_array( $stored ) ) {
		foreach ( $stored as $section => $values ) {
			if ( ! isset( $merged[ $section ] ) || ! is_array( $values ) ) {
				continue;
			}
			foreach ( $values as $key => $value ) {
				$merged[ $section ][ $key ] = $value;
			}
		}
	}

	$cache = $merged;
	return $cache;
}

/**
 * Get a single option value.
 *
 * @param string $section Section key.
 * @param string $key     Field key.
 * @param mixed  $default Fallback.
 * @return mixed
 */
function personal_site_opt( $section, $key, $default = '' ) {
	$options = personal_site_options();
	return isset( $options[ $section ][ $key ] ) ? $options[ $section ][ $key ] : $default;
}

/**
 * Print an accent-color override when a non-default color is set.
 */
function personal_site_accent_css() {
	$accent = personal_site_opt( 'general', 'accent', '' );
	if ( $accent && strtolower( $accent ) !== '#345b7d' ) {
		printf(
			'<style id="ps-accent">:root{--color-accent:%1$s;--color-accent-hover:%1$s;}</style>' . "\n",
			esc_attr( $accent )
		);
	}
}
add_action( 'wp_head', 'personal_site_accent_css', 20 );

/**
 * Sanitize the whole options array against the schema before saving.
 *
 * @param array $input Raw posted values.
 * @return array
 */
function personal_site_sanitize_options( $input ) {
	$input = is_array( $input ) ? $input : array();
	$out   = personal_site_default_options();

	// --- General -------------------------------------------------------------
	$g = isset( $input['general'] ) ? (array) $input['general'] : array();
	foreach ( array( 'github', 'x', 'linkedin', 'instagram', 'facebook' ) as $net ) {
		$out['general'][ "social_{$net}" ] = isset( $g[ "social_{$net}" ] ) ? esc_url_raw( trim( $g[ "social_{$net}" ] ) ) : '';
	}
	$out['general']['social_email'] = isset( $g['social_email'] ) ? sanitize_email( $g['social_email'] ) : '';
	$out['general']['footer_text']  = isset( $g['footer_text'] ) ? sanitize_text_field( $g['footer_text'] ) : '';
	$accent                         = isset( $g['accent'] ) ? sanitize_hex_color( $g['accent'] ) : '';
	$out['general']['accent']       = $accent ? $accent : '#345b7d';

	// --- Home ----------------------------------------------------------------
	$h = isset( $input['home'] ) ? (array) $input['home'] : array();
	foreach ( array( 'hero_enabled', 'focus_enabled', 'work_enabled', 'posts_enabled', 'cta_enabled' ) as $toggle ) {
		$out['home'][ $toggle ] = empty( $h[ $toggle ] ) ? 0 : 1;
	}
	foreach ( array( 'hero_heading', 'hero_primary_label', 'hero_secondary_label', 'focus_heading', 'work_heading', 'posts_heading', 'cta_heading', 'cta_label' ) as $text ) {
		$out['home'][ $text ] = isset( $h[ $text ] ) ? sanitize_text_field( $h[ $text ] ) : '';
	}
	$out['home']['hero_text'] = isset( $h['hero_text'] ) ? sanitize_textarea_field( $h['hero_text'] ) : '';
	foreach ( array( 'hero_primary_url', 'hero_secondary_url', 'cta_url' ) as $url ) {
		$out['home'][ $url ] = isset( $h[ $url ] ) ? esc_url_raw( trim( $h[ $url ] ) ) : '';
	}
	$out['home']['work_count']  = isset( $h['work_count'] ) ? max( 1, min( 12, (int) $h['work_count'] ) ) : 3;
	$out['home']['posts_count'] = isset( $h['posts_count'] ) ? max( 1, min( 12, (int) $h['posts_count'] ) ) : 3;

	$out['home']['focus_items'] = array();
	if ( ! empty( $h['focus_items'] ) && is_array( $h['focus_items'] ) ) {
		foreach ( $h['focus_items'] as $item ) {
			$title = isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '';
			$text  = isset( $item['text'] ) ? sanitize_textarea_field( $item['text'] ) : '';
			if ( '' === $title && '' === $text ) {
				continue;
			}
			$out['home']['focus_items'][] = array(
				'title' => $title,
				'text'  => $text,
			);
		}
	}

	// --- About ---------------------------------------------------------------
	$a                                 = isset( $input['about'] ) ? (array) $input['about'] : array();
	$out['about']['portrait_id']       = isset( $a['portrait_id'] ) ? absint( $a['portrait_id'] ) : 0;
	$out['about']['role']              = isset( $a['role'] ) ? sanitize_text_field( $a['role'] ) : '';
	$out['about']['location']          = isset( $a['location'] ) ? sanitize_text_field( $a['location'] ) : '';
	$out['about']['focus']             = isset( $a['focus'] ) ? sanitize_text_field( $a['focus'] ) : '';
	$out['about']['experience']        = isset( $a['experience'] ) ? sanitize_text_field( $a['experience'] ) : '';
	$out['about']['timeline_heading']  = isset( $a['timeline_heading'] ) ? sanitize_text_field( $a['timeline_heading'] ) : '';
	$out['about']['timeline']          = personal_site_sanitize_rows( isset( $a['timeline'] ) ? $a['timeline'] : array() );
	$out['about']['freelance_heading'] = isset( $a['freelance_heading'] ) ? sanitize_text_field( $a['freelance_heading'] ) : '';
	$out['about']['freelance']         = personal_site_sanitize_rows( isset( $a['freelance'] ) ? $a['freelance'] : array() );
	$out['about']['education']         = isset( $a['education'] ) ? sanitize_text_field( $a['education'] ) : '';

	// --- Contact -------------------------------------------------------------
	$c                                 = isset( $input['contact'] ) ? (array) $input['contact'] : array();
	$out['contact']['intro']           = isset( $c['intro'] ) ? sanitize_textarea_field( $c['intro'] ) : '';
	$out['contact']['based_in']        = isset( $c['based_in'] ) ? sanitize_text_field( $c['based_in'] ) : '';
	$out['contact']['response_time']   = isset( $c['response_time'] ) ? sanitize_text_field( $c['response_time'] ) : '';
	$out['contact']['success_message'] = isset( $c['success_message'] ) ? sanitize_text_field( $c['success_message'] ) : '';
	$out['contact']['notify_enabled']  = empty( $c['notify_enabled'] ) ? 0 : 1;
	$out['contact']['notify_email']    = isset( $c['notify_email'] ) ? sanitize_email( $c['notify_email'] ) : '';

	$out['contact']['turnstile_enabled']    = empty( $c['turnstile_enabled'] ) ? 0 : 1;
	$out['contact']['turnstile_site_key']   = isset( $c['turnstile_site_key'] ) ? sanitize_text_field( $c['turnstile_site_key'] ) : '';
	$out['contact']['turnstile_secret_key'] = isset( $c['turnstile_secret_key'] ) ? sanitize_text_field( $c['turnstile_secret_key'] ) : '';

	// --- Footer --------------------------------------------------------------
	$f                            = isset( $input['footer'] ) ? (array) $input['footer'] : array();
	$out['footer']['tagline']     = isset( $f['tagline'] ) ? sanitize_text_field( $f['tagline'] ) : '';
	$out['footer']['copyright']   = isset( $f['copyright'] ) ? sanitize_text_field( $f['copyright'] ) : '';

	return $out;
}

/**
 * Sanitize a list of timeline-style rows (period, role, org, summary).
 *
 * @param mixed $rows Raw rows.
 * @return array
 */
function personal_site_sanitize_rows( $rows ) {
	$out = array();
	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return $out;
	}
	foreach ( $rows as $row ) {
		$period  = isset( $row['period'] ) ? sanitize_text_field( $row['period'] ) : '';
		$role    = isset( $row['role'] ) ? sanitize_text_field( $row['role'] ) : '';
		$org     = isset( $row['org'] ) ? sanitize_text_field( $row['org'] ) : '';
		$summary = isset( $row['summary'] ) ? sanitize_textarea_field( $row['summary'] ) : '';
		if ( '' === $period && '' === $role && '' === $org && '' === $summary ) {
			continue;
		}
		$out[] = compact( 'period', 'role', 'org', 'summary' );
	}
	return $out;
}
