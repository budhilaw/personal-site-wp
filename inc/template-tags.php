<?php
/**
 * Template helpers used across the theme.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Echo the published date as a machine-readable <time>.
 */
function personal_site_posted_on() {
	printf(
		'<time class="meta__date" datetime="%1$s">%2$s</time>',
		esc_attr( get_the_date( DATE_W3C ) ),
		esc_html( get_the_date() )
	);
}

/**
 * Estimate reading time from the post body.
 *
 * @param int|null $post_id Optional post ID.
 * @return string
 */
function personal_site_reading_time( $post_id = null ) {
	$content = get_post_field( 'post_content', $post_id ?: get_the_ID() );
	$words   = str_word_count( wp_strip_all_tags( $content ) );
	$minutes = max( 1, (int) round( $words / 200 ) );

	/* translators: %d: estimated reading time in minutes. */
	return sprintf( _n( '%d min read', '%d min read', $minutes, 'personal-site' ), $minutes );
}

/**
 * Output a list of social links saved in the Customizer.
 *
 * @param string $class Wrapper class.
 */
function personal_site_social_links( $class = 'social' ) {
	$networks = personal_site_social_networks();
	$links    = array();

	foreach ( $networks as $key => $network ) {
		$value = personal_site_opt( 'general', "social_{$key}", '' );
		if ( ! $value ) {
			continue;
		}

		if ( 'email' === $key ) {
			$href     = esc_url( 'mailto:' . $value );
			$external = '';
		} else {
			$href     = esc_url( $value );
			$external = ' target="_blank" rel="noopener noreferrer me"';
		}

		$links[] = sprintf(
			'<li><a class="social__link" href="%1$s"%2$s aria-label="%3$s">%4$s</a></li>',
			$href,
			$external,
			esc_attr( $network['label'] ),
			personal_site_icon( $network['icon'] )
		);
	}

	if ( empty( $links ) ) {
		return;
	}

	printf( '<ul class="%1$s" role="list">%2$s</ul>', esc_attr( $class ), implode( '', $links ) );
}

/**
 * The networks the theme knows how to render, in display order.
 *
 * @return array
 */
function personal_site_social_networks() {
	return array(
		'github'    => array( 'label' => 'GitHub', 'icon' => 'github' ),
		'x'         => array( 'label' => 'X', 'icon' => 'x' ),
		'linkedin'  => array( 'label' => 'LinkedIn', 'icon' => 'linkedin' ),
		'instagram' => array( 'label' => 'Instagram', 'icon' => 'instagram' ),
		'facebook'  => array( 'label' => 'Facebook', 'icon' => 'facebook' ),
		'email'     => array( 'label' => 'Email', 'icon' => 'email' ),
	);
}

/**
 * Return an inline SVG icon by name (decorative; labels live on the link).
 *
 * @param string $name Icon key.
 * @return string
 */
function personal_site_icon( $name ) {
	$icons = array(
		'github'    => '<path d="M12 .5C5.7.5.5 5.7.5 12c0 5.1 3.3 9.4 7.9 10.9.6.1.8-.2.8-.5v-2c-3.2.7-3.9-1.4-3.9-1.4-.5-1.3-1.3-1.7-1.3-1.7-1.1-.7.1-.7.1-.7 1.2.1 1.8 1.2 1.8 1.2 1 1.8 2.8 1.3 3.5 1 .1-.8.4-1.3.7-1.6-2.6-.3-5.3-1.3-5.3-5.8 0-1.3.5-2.3 1.2-3.1-.1-.3-.5-1.5.1-3.1 0 0 1-.3 3.3 1.2a11.5 11.5 0 0 1 6 0C17.3 4.6 18.3 5 18.3 5c.6 1.6.2 2.8.1 3.1.8.8 1.2 1.8 1.2 3.1 0 4.5-2.7 5.5-5.3 5.8.4.4.8 1.1.8 2.2v3.3c0 .3.2.6.8.5 4.6-1.5 7.9-5.8 7.9-10.9C23.5 5.7 18.3.5 12 .5z"/>',
		'x'         => '<path d="M18.9 1.5h3.7l-8.1 9.2 9.5 12.6h-7.4l-5.8-7.6-6.7 7.6H.4l8.6-9.9L0 1.5h7.6l5.2 6.9 6.1-6.9zm-1.3 19.6h2L6.5 3.6H4.4l13.2 17.5z"/>',
		'linkedin'  => '<path d="M20.5 2h-17A1.5 1.5 0 0 0 2 3.5v17A1.5 1.5 0 0 0 3.5 22h17a1.5 1.5 0 0 0 1.5-1.5v-17A1.5 1.5 0 0 0 20.5 2zM8 19H5V9h3v10zM6.5 7.7a1.8 1.8 0 1 1 0-3.6 1.8 1.8 0 0 1 0 3.6zM19 19h-3v-5c0-1.2-.4-2-1.5-2-.8 0-1.3.6-1.5 1.1-.1.2-.1.5-.1.8V19h-3V9h3v1.4c.4-.6 1.1-1.5 2.7-1.5 2 0 3.4 1.3 3.4 4V19z"/>',
		'instagram' => '<path d="M12 2.2c3.2 0 3.6 0 4.9.1 1.2.1 1.8.3 2.2.4.6.2 1 .5 1.4.9.4.4.7.8.9 1.4.2.4.4 1 .4 2.2.1 1.3.1 1.7.1 4.9s0 3.6-.1 4.9c-.1 1.2-.3 1.8-.4 2.2-.2.6-.5 1-.9 1.4-.4.4-.8.7-1.4.9-.4.2-1 .4-2.2.4-1.3.1-1.7.1-4.9.1s-3.6 0-4.9-.1c-1.2-.1-1.8-.3-2.2-.4a3.7 3.7 0 0 1-1.4-.9 3.7 3.7 0 0 1-.9-1.4c-.2-.4-.4-1-.4-2.2C2.2 15.6 2.2 15.2 2.2 12s0-3.6.1-4.9c.1-1.2.3-1.8.4-2.2.2-.6.5-1 .9-1.4.4-.4.8-.7 1.4-.9.4-.2 1-.4 2.2-.4C8.4 2.2 8.8 2.2 12 2.2zm0 1.6c-3.1 0-3.5 0-4.7.1-1.1.1-1.7.2-2.1.4-.5.2-.9.4-1.3.8-.4.4-.6.8-.8 1.3-.2.4-.3 1-.4 2.1-.1 1.2-.1 1.6-.1 4.7s0 3.5.1 4.7c.1 1.1.2 1.7.4 2.1.2.5.4.9.8 1.3.4.4.8.6 1.3.8.4.2 1 .3 2.1.4 1.2.1 1.6.1 4.7.1s3.5 0 4.7-.1c1.1-.1 1.7-.2 2.1-.4.5-.2.9-.4 1.3-.8.4-.4.6-.8.8-1.3.2-.4.3-1 .4-2.1.1-1.2.1-1.6.1-4.7s0-3.5-.1-4.7c-.1-1.1-.2-1.7-.4-2.1a3.5 3.5 0 0 0-.8-1.3 3.5 3.5 0 0 0-1.3-.8c-.4-.2-1-.3-2.1-.4-1.2-.1-1.6-.1-4.7-.1zm0 2.7a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11zm0 9.1a3.6 3.6 0 1 0 0-7.2 3.6 3.6 0 0 0 0 7.2zm7-9.3a1.3 1.3 0 1 1-2.6 0 1.3 1.3 0 0 1 2.6 0z"/>',
		'facebook'  => '<path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.3c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12z"/>',
		'email'     => '<path d="M3 4h18a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zm9 7.6L4.3 6H19.7L12 11.6zM4 8v10h16V8l-8 5.8L4 8z"/>',
	);

	$path = isset( $icons[ $name ] ) ? $icons[ $name ] : '';

	return '<svg class="icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false" fill="currentColor">' . $path . '</svg>';
}

/**
 * Render a category pill for the current post, if any.
 */
function personal_site_primary_category() {
	$cats = get_the_category();
	if ( empty( $cats ) ) {
		return;
	}
	$cat = $cats[0];
	printf(
		'<a class="pill" href="%1$s">%2$s</a>',
		esc_url( get_category_link( $cat->term_id ) ),
		esc_html( $cat->name )
	);
}

/**
 * Render a vertical timeline from option rows (period, role, org, summary).
 *
 * @param array $rows Timeline rows.
 */
function personal_site_render_timeline( $rows ) {
	if ( empty( $rows ) ) {
		return;
	}
	echo '<ol class="timeline" role="list">';
	foreach ( $rows as $entry ) {
		$is_now = ! empty( $entry['period'] ) && false !== stripos( $entry['period'], 'present' );
		echo '<li class="timeline__item">';
		if ( ! empty( $entry['period'] ) ) {
			echo '<p class="eyebrow">' . esc_html( $entry['period'] );
			if ( $is_now ) {
				echo ' <span class="timeline__now">' . esc_html__( 'Now', 'personal-site' ) . '</span>';
			}
			echo '</p>';
		}
		echo '<h3 class="timeline__role">' . esc_html( $entry['role'] );
		if ( ! empty( $entry['org'] ) ) {
			echo '<span class="timeline__org"> &middot; ' . esc_html( $entry['org'] ) . '</span>';
		}
		echo '</h3>';
		if ( ! empty( $entry['summary'] ) ) {
			echo '<p class="timeline__summary">' . esc_html( $entry['summary'] ) . '</p>';
		}
		echo '</li>';
	}
	echo '</ol>';
}

/**
 * Numbered, accessible pagination for archive loops.
 */
function personal_site_pagination() {
	$links = paginate_links(
		array(
			'type'      => 'list',
			'mid_size'  => 1,
			'prev_text' => __( 'Previous', 'personal-site' ),
			'next_text' => __( 'Next', 'personal-site' ),
		)
	);

	if ( $links ) {
		printf(
			'<nav class="pagination" aria-label="%1$s">%2$s</nav>',
			esc_attr__( 'Posts', 'personal-site' ),
			wp_kses_post( $links )
		);
	}
}
