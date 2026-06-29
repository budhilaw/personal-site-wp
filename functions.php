<?php
/**
 * Personal Site theme bootstrap.
 *
 * Loads the small, focused includes that make up the theme. Each include
 * guards against direct access and prefixes its functions with `personal_site_`.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PERSONAL_SITE_VERSION', '1.0.0' );
define( 'PERSONAL_SITE_DIR', get_template_directory() );
define( 'PERSONAL_SITE_URI', get_template_directory_uri() );

require_once PERSONAL_SITE_DIR . '/inc/options.php';
require_once PERSONAL_SITE_DIR . '/inc/setup.php';
require_once PERSONAL_SITE_DIR . '/inc/enqueue.php';
require_once PERSONAL_SITE_DIR . '/inc/post-types.php';
require_once PERSONAL_SITE_DIR . '/inc/template-tags.php';
require_once PERSONAL_SITE_DIR . '/inc/seo.php';
require_once PERSONAL_SITE_DIR . '/inc/contact.php';
require_once PERSONAL_SITE_DIR . '/inc/blocks.php';
require_once PERSONAL_SITE_DIR . '/inc/updater.php';

if ( is_admin() ) {
	require_once PERSONAL_SITE_DIR . '/inc/admin/theme-options.php';
}
