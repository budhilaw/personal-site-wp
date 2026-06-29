<?php
/**
 * Styles, scripts, and resource hints.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the Google Fonts request used for display, body, and label faces.
 *
 * @return string
 */
function personal_site_fonts_url() {
	$families = array(
		'Hanken+Grotesk:wght@400;500;600;700',
		'Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;1,6..72,400',
		'JetBrains+Mono:wght@400;500',
	);

	return 'https://fonts.googleapis.com/css2?family=' . implode( '&family=', $families ) . '&display=swap';
}

/**
 * Enqueue front-end styles and scripts with file-modification cache busting.
 */
function personal_site_assets() {
	wp_enqueue_style( 'personal-site-fonts', personal_site_fonts_url(), array(), null );

	$main_rel = '/assets/css/main.css';
	wp_enqueue_style(
		'personal-site-main',
		PERSONAL_SITE_URI . $main_rel,
		array( 'personal-site-fonts' ),
		filemtime( PERSONAL_SITE_DIR . $main_rel )
	);

	$js_rel = '/assets/js/theme.js';
	wp_enqueue_script(
		'personal-site-theme',
		PERSONAL_SITE_URI . $js_rel,
		array(),
		filemtime( PERSONAL_SITE_DIR . $js_rel ),
		array( 'strategy' => 'defer' )
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'personal_site_assets' );

/**
 * Preconnect to the font host so the first paint is not blocked.
 *
 * @param array  $urls     Resource URLs.
 * @param string $relation Relation type.
 * @return array
 */
function personal_site_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'personal_site_resource_hints', 10, 2 );

/**
 * Enqueue the editor font so the block editor matches the front end.
 */
function personal_site_editor_assets() {
	wp_enqueue_style( 'personal-site-editor-fonts', personal_site_fonts_url(), array(), null );
}
add_action( 'enqueue_block_editor_assets', 'personal_site_editor_assets' );
