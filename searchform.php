<?php
/**
 * Search form.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$field_id = 'search-field-' . wp_unique_id();
?>
<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>"><?php esc_html_e( 'Search for:', 'personal-site' ); ?></label>
	<input id="<?php echo esc_attr( $field_id ); ?>" type="search" name="s" placeholder="<?php esc_attr_e( 'Search&hellip;', 'personal-site' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" />
	<button class="button" type="submit"><?php esc_html_e( 'Search', 'personal-site' ); ?></button>
</form>
