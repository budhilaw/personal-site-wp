<?php
/**
 * Theme setup: supports, menus, image sizes, content width.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme features.
 */
function personal_site_setup() {
	load_theme_textdomain( 'personal-site', PERSONAL_SITE_DIR . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 64,
			'width'       => 64,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	add_editor_style( 'assets/css/editor.css' );

	add_image_size( 'personal-site-card', 800, 500, true );
	add_image_size( 'personal-site-cover', 1600, 900, true );

	register_nav_menus(
		array(
			'primary' => __( 'Primary', 'personal-site' ),
			'footer'  => __( 'Footer', 'personal-site' ),
		)
	);
}
add_action( 'after_setup_theme', 'personal_site_setup' );

/**
 * Constrain the default content width used by oEmbeds and wide images.
 */
function personal_site_content_width() {
	$GLOBALS['content_width'] = 720;
}
add_action( 'after_setup_theme', 'personal_site_content_width', 0 );

/**
 * Friendly labels for the custom image sizes inside the editor.
 *
 * @param array $sizes Named image sizes.
 * @return array
 */
function personal_site_image_size_names( $sizes ) {
	return array_merge(
		$sizes,
		array(
			'personal-site-card'  => __( 'Card', 'personal-site' ),
			'personal-site-cover' => __( 'Cover', 'personal-site' ),
		)
	);
}
add_filter( 'image_size_names_choose', 'personal_site_image_size_names' );

/**
 * Register the single sidebar used on the blog.
 */
function personal_site_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Blog Sidebar', 'personal-site' ),
			'id'            => 'sidebar-blog',
			'description'   => __( 'Shown beside the blog listing and single posts.', 'personal-site' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget__title eyebrow">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'personal_site_widgets_init' );
