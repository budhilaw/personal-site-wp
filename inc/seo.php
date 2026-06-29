<?php
/**
 * Lightweight SEO: meta description, Open Graph, Twitter cards, and JSON-LD.
 *
 * Skips output when a dedicated SEO plugin (Yoast, Rank Math, SEOPress) is
 * active so tags are never duplicated.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether another SEO plugin already manages meta tags.
 *
 * @return bool
 */
function personal_site_seo_handled_elsewhere() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * A clean description for the current view.
 *
 * @return string
 */
function personal_site_meta_description() {
	if ( is_singular() ) {
		$excerpt = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content() ), 30 );
		return trim( $excerpt );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		return trim( wp_strip_all_tags( term_description() ) ) ?: get_bloginfo( 'description' );
	}

	return get_bloginfo( 'description' );
}

/**
 * Print social and description meta in the document head.
 */
function personal_site_head_meta() {
	if ( personal_site_seo_handled_elsewhere() ) {
		return;
	}

	$description = personal_site_meta_description();
	$title       = wp_get_document_title();
	$url         = ( is_singular() ) ? get_permalink() : home_url( add_query_arg( null, null ) );
	$type        = ( is_singular() && ! is_front_page() ) ? 'article' : 'website';
	$image       = '';

	if ( is_singular() && has_post_thumbnail() ) {
		$image = get_the_post_thumbnail_url( get_the_ID(), 'personal-site-cover' );
	}

	echo "\n";
	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $type ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta name="twitter:card" content="%s" />' . "\n", $image ? 'summary_large_image' : 'summary' );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $image ) );
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $image ) );
	}
}
add_action( 'wp_head', 'personal_site_head_meta', 5 );

/**
 * Emit JSON-LD structured data: WebSite + Person on the front page,
 * Article + BreadcrumbList on single posts.
 */
function personal_site_json_ld() {
	if ( personal_site_seo_handled_elsewhere() ) {
		return;
	}

	$graph = array();

	if ( is_front_page() ) {
		$graph[] = array(
			'@type' => 'WebSite',
			'name'  => get_bloginfo( 'name' ),
			'url'   => home_url( '/' ),
		);

		$same_as = array();
		foreach ( personal_site_social_networks() as $key => $network ) {
			if ( 'email' === $key ) {
				continue;
			}
			$value = personal_site_opt( 'general', "social_{$key}", '' );
			if ( $value ) {
				$same_as[] = $value;
			}
		}

		$graph[] = array_filter(
			array(
				'@type'    => 'Person',
				'name'     => get_bloginfo( 'name' ),
				'url'      => home_url( '/' ),
				'jobTitle' => get_bloginfo( 'description' ),
				'sameAs'   => $same_as ?: null,
			)
		);
	}

	if ( is_singular( 'post' ) ) {
		$graph[] = array(
			'@type'         => 'Article',
			'headline'      => get_the_title(),
			'datePublished' => get_the_date( DATE_W3C ),
			'dateModified'  => get_the_modified_date( DATE_W3C ),
			'author'        => array(
				'@type' => 'Person',
				'name'  => get_the_author(),
			),
			'mainEntityOfPage' => get_permalink(),
		);

		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => array(
				array( '@type' => 'ListItem', 'position' => 1, 'name' => __( 'Home', 'personal-site' ), 'item' => home_url( '/' ) ),
				array( '@type' => 'ListItem', 'position' => 2, 'name' => get_the_title(), 'item' => get_permalink() ),
			),
		);
	}

	if ( empty( $graph ) ) {
		return;
	}

	$payload = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE )
	);
}
add_action( 'wp_head', 'personal_site_json_ld', 6 );
