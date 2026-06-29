<?php
/**
 * Self-hosted theme updates backed by GitHub Releases.
 *
 * Gives this theme the normal WordPress "update available" experience
 * (Dashboard, Updates and Appearance, Themes) without any plugin or third
 * party library. It reads the latest GitHub release, compares its tag to the
 * installed Version header, and points the update package at the release zip.
 *
 * Everything is cached in a transient so a site checks GitHub at most twice a
 * day, well within the unauthenticated API rate limit. The repository is
 * public, so no token is needed.
 *
 * @package Personal_Site
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const PERSONAL_SITE_GH_USER  = 'budhilaw';
const PERSONAL_SITE_GH_REPO  = 'personal-site-wp';
const PERSONAL_SITE_GH_CACHE = 'personal_site_latest_release';
const PERSONAL_SITE_GH_TTL   = 12 * HOUR_IN_SECONDS;

/**
 * Read the latest GitHub release, cached in a transient.
 *
 * On any error a short negative cache is stored so a flaky network does not
 * trigger a request on every admin page load.
 *
 * @param bool $force Bypass the cache and fetch fresh.
 * @return array|null Release data (version, zip_url, html_url, notes) or null.
 */
function personal_site_latest_release( $force = false ) {
	if ( ! $force ) {
		$cached = get_transient( PERSONAL_SITE_GH_CACHE );
		if ( is_array( $cached ) ) {
			return $cached;
		}
		if ( 'none' === $cached ) {
			return null;
		}
	}

	$url = sprintf(
		'https://api.github.com/repos/%s/%s/releases/latest',
		PERSONAL_SITE_GH_USER,
		PERSONAL_SITE_GH_REPO
	);

	$response = wp_remote_get(
		$url,
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept'     => 'application/vnd.github+json',
				'User-Agent' => 'Personal-Site-Theme-Updater',
			),
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		set_transient( PERSONAL_SITE_GH_CACHE, 'none', HOUR_IN_SECONDS );
		return null;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) || empty( $body['tag_name'] ) ) {
		set_transient( PERSONAL_SITE_GH_CACHE, 'none', HOUR_IN_SECONDS );
		return null;
	}

	// Prefer an uploaded .zip asset (built by CI); fall back to the source zip.
	$zip_url = '';
	if ( ! empty( $body['assets'] ) && is_array( $body['assets'] ) ) {
		foreach ( $body['assets'] as $asset ) {
			if ( empty( $asset['browser_download_url'] ) || empty( $asset['name'] ) ) {
				continue;
			}
			if ( '.zip' === strtolower( substr( $asset['name'], -4 ) ) ) {
				$zip_url = $asset['browser_download_url'];
				break;
			}
		}
	}
	if ( '' === $zip_url && ! empty( $body['zipball_url'] ) ) {
		$zip_url = $body['zipball_url'];
	}

	$release = array(
		'version'  => ltrim( $body['tag_name'], 'vV' ),
		'zip_url'  => $zip_url,
		'html_url' => isset( $body['html_url'] ) ? $body['html_url'] : '',
		'notes'    => isset( $body['body'] ) ? $body['body'] : '',
	);

	set_transient( PERSONAL_SITE_GH_CACHE, $release, PERSONAL_SITE_GH_TTL );
	return $release;
}

/**
 * Inject an available update into the themes update transient.
 *
 * Only does work in the admin or during cron, since the front end never
 * renders the update UI. Versions are compared against the live style.css
 * header so there is a single source of truth.
 *
 * @param mixed $transient The update_themes site transient.
 * @return mixed
 */
function personal_site_check_for_update( $transient ) {
	if ( ! is_admin() && ! wp_doing_cron() ) {
		return $transient;
	}

	$release = personal_site_latest_release();
	if ( ! $release || empty( $release['zip_url'] ) ) {
		return $transient;
	}

	if ( ! is_object( $transient ) ) {
		$transient = new stdClass();
	}

	$slug    = get_template();
	$current = wp_get_theme( $slug )->get( 'Version' );

	if ( $current && version_compare( $release['version'], $current, '>' ) ) {
		if ( ! isset( $transient->response ) || ! is_array( $transient->response ) ) {
			$transient->response = array();
		}
		$transient->response[ $slug ] = array(
			'theme'       => $slug,
			'new_version' => $release['version'],
			'url'         => $release['html_url'],
			'package'     => $release['zip_url'],
		);
	} else {
		if ( ! isset( $transient->no_update ) || ! is_array( $transient->no_update ) ) {
			$transient->no_update = array();
		}
		$transient->no_update[ $slug ] = array(
			'theme'       => $slug,
			'new_version' => $current,
			'url'         => $release['html_url'],
			'package'     => '',
		);
	}

	return $transient;
}
add_filter( 'site_transient_update_themes', 'personal_site_check_for_update' );
add_filter( 'pre_set_site_transient_update_themes', 'personal_site_check_for_update' );

/**
 * Normalize the extracted folder name during an update.
 *
 * GitHub source zips (and any zip whose inner folder is not the theme slug)
 * would otherwise install into the wrong directory. This renames the unpacked
 * folder to the active template slug so the update replaces this theme cleanly.
 *
 * @param string      $source        Path to the unpacked source.
 * @param string      $remote_source Path to the download root.
 * @param WP_Upgrader $upgrader      Upgrader instance.
 * @param array       $args          Extra args (includes 'theme' on theme updates).
 * @return string|WP_Error
 */
function personal_site_fix_update_source( $source, $remote_source, $upgrader, $args = array() ) {
	global $wp_filesystem;

	if ( empty( $args['theme'] ) || get_template() !== $args['theme'] ) {
		return $source;
	}
	if ( ! $wp_filesystem ) {
		return $source;
	}

	$desired = trailingslashit( $remote_source ) . get_template();
	$desired = trailingslashit( $desired );

	if ( trailingslashit( $source ) === $desired ) {
		return $source;
	}

	if ( $wp_filesystem->move( $source, $desired ) ) {
		return $desired;
	}

	return $source;
}
add_filter( 'upgrader_source_selection', 'personal_site_fix_update_source', 10, 4 );

/**
 * Clear the cached release after any theme update completes.
 *
 * @param WP_Upgrader $upgrader Upgrader instance.
 * @param array       $data     Update context.
 */
function personal_site_clear_release_cache( $upgrader, $data ) {
	if ( isset( $data['type'] ) && 'theme' === $data['type'] ) {
		delete_transient( PERSONAL_SITE_GH_CACHE );
	}
}
add_action( 'upgrader_process_complete', 'personal_site_clear_release_cache', 10, 2 );
