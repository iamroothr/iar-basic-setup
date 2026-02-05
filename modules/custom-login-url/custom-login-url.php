<?php
/**
 * Custom Login URL module.
 *
 * Replaces /wp-login.php with a custom path.
 *
 * @package IAR_Basic_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get the custom login path from options.
 *
 * Caches the value in a static variable for the duration of the request.
 *
 * @return string Custom login path or empty string if not set.
 */
function iar_custom_login_get_path(): string {
	static $path = null;

	if ( null === $path ) {
		$options = get_option( 'iar_custom_login_url_options', [] );
		$path    = isset( $options['login_path'] ) ? trim( $options['login_path'], '/' ) : '';
	}

	return $path;
}

/**
 * Intercept requests to the custom login path and serve wp-login.php.
 */
function iar_custom_login_handle_request(): void {
	$custom_path = iar_custom_login_get_path();

	if ( '' === $custom_path ) {
		return;
	}

	$request_path = trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

	if ( $request_path === $custom_path ) {
		define( 'IAR_CUSTOM_LOGIN_ACCESS', true );

		// Initialize globals expected by wp-login.php.
		global $user_login, $error;
		$user_login = '';
		$error      = '';

		require_once ABSPATH . 'wp-login.php';
		exit;
	}
}
add_action( 'init', 'iar_custom_login_handle_request', 1 );

/**
 * Send the configured block response (404 or redirect home) and exit.
 */
function iar_custom_login_send_block_response(): void {
	$options  = get_option( 'iar_custom_login_url_options', [] );
	$behavior = isset( $options['redirect_behavior'] ) ? $options['redirect_behavior'] : '404';

	if ( 'home' === $behavior ) {
		wp_safe_redirect( home_url(), 302 );
		exit;
	}

	status_header( 404 );
	nocache_headers();
	wp_die( 'Not Found', '404 Not Found', [ 'response' => 404 ] );
}

/**
 * Block direct access to wp-login.php when a custom path is configured.
 */
function iar_custom_login_block_default(): void {
	$custom_path = iar_custom_login_get_path();

	if ( '' === $custom_path ) {
		return;
	}

	if ( defined( 'IAR_CUSTOM_LOGIN_ACCESS' ) && IAR_CUSTOM_LOGIN_ACCESS ) {
		return;
	}

	// Allow password-protected post form submissions.
	if ( isset( $_REQUEST['action'] ) && 'postpass' === $_REQUEST['action'] ) {
		return;
	}

	iar_custom_login_send_block_response();
}
add_action( 'login_init', 'iar_custom_login_block_default', 10 );

/**
 * Block wp-admin access for unauthenticated users when a custom path is configured.
 */
function iar_custom_login_block_wp_admin(): void {
	$custom_path = iar_custom_login_get_path();

	if ( '' === $custom_path ) {
		return;
	}

	if ( is_user_logged_in() ) {
		return;
	}

	$request_path = trim( wp_parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );

	if ( 0 !== strpos( $request_path, 'wp-admin' ) ) {
		return;
	}

	// Allow AJAX and admin-post requests from the front-end.
	if ( false !== strpos( $request_path, 'admin-ajax.php' ) || false !== strpos( $request_path, 'admin-post.php' ) ) {
		return;
	}

	iar_custom_login_send_block_response();
}
add_action( 'init', 'iar_custom_login_block_wp_admin', 1 );

/**
 * Filter site_url() to rewrite wp-login.php to the custom path.
 *
 * @param string $url    The complete site URL.
 * @param string $path   Path relative to the site URL.
 * @param string $scheme URL scheme.
 * @return string Filtered URL.
 */
function iar_custom_login_filter_url( string $url, string $path, ?string $scheme ): string {
	$custom_path = iar_custom_login_get_path();

	if ( '' === $custom_path ) {
		return $url;
	}

	if ( false !== strpos( $path, 'wp-login.php' ) ) {
		$url = str_replace( 'wp-login.php', $custom_path, $url );
	}

	return $url;
}
add_filter( 'site_url', 'iar_custom_login_filter_url', 10, 3 );

/**
 * Filter wp_redirect() to rewrite wp-login.php redirects to the custom path.
 *
 * @param string $location Redirect URL.
 * @return string Filtered redirect URL.
 */
function iar_custom_login_filter_redirect( string $location ): string {
	$custom_path = iar_custom_login_get_path();

	if ( '' === $custom_path ) {
		return $location;
	}

	if ( false !== strpos( $location, 'wp-login.php' ) ) {
		$location = str_replace( 'wp-login.php', $custom_path, $location );
	}

	return $location;
}
add_filter( 'wp_redirect', 'iar_custom_login_filter_redirect', 10 );

/**
 * Filter login_url() output.
 *
 * @param string $login_url The login URL.
 * @param string $redirect  Redirect URL after login.
 * @param bool   $force_reauth Whether to force reauth.
 * @return string Filtered login URL.
 */
function iar_custom_login_url( string $login_url, string $redirect, bool $force_reauth ): string {
	$custom_path = iar_custom_login_get_path();

	if ( '' === $custom_path ) {
		return $login_url;
	}

	return str_replace( 'wp-login.php', $custom_path, $login_url );
}
add_filter( 'login_url', 'iar_custom_login_url', 10, 3 );

/**
 * Filter logout_url() to use the custom path.
 *
 * @param string $logout_url The logout URL.
 * @param string $redirect   Redirect URL after logout.
 * @return string Filtered logout URL.
 */
function iar_custom_login_logout_url( string $logout_url, string $redirect ): string {
	$custom_path = iar_custom_login_get_path();

	if ( '' === $custom_path ) {
		return $logout_url;
	}

	return str_replace( 'wp-login.php', $custom_path, $logout_url );
}
add_filter( 'logout_url', 'iar_custom_login_logout_url', 10, 2 );

/**
 * Filter lostpassword_url() to use the custom path.
 *
 * @param string $lostpassword_url The lost password URL.
 * @param string $redirect         Redirect URL after password reset.
 * @return string Filtered lost password URL.
 */
function iar_custom_login_lostpassword_url( string $lostpassword_url, string $redirect ): string {
	$custom_path = iar_custom_login_get_path();

	if ( '' === $custom_path ) {
		return $lostpassword_url;
	}

	return str_replace( 'wp-login.php', $custom_path, $lostpassword_url );
}
add_filter( 'lostpassword_url', 'iar_custom_login_lostpassword_url', 10, 2 );
