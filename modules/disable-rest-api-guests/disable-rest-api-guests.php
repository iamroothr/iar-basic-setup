<?php
/**
 * Module: Disable REST API for Guests
 * Description: Restricts REST API access to authenticated users only.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if user is authenticated before allowing REST API access.
 *
 * @param WP_Error|null|true $result Authentication result.
 * @return WP_Error|null|true Modified result.
 */
function iar_disable_rest_api_guests_check( $result ) {
	if ( true === $result || is_wp_error( $result ) ) {
		return $result;
	}

	if ( ! is_user_logged_in() ) {
		return new WP_Error(
			'rest_not_logged_in',
			__( 'REST API access restricted to authenticated users.', 'iar-basic-setup' ),
			[ 'status' => 401 ]
		);
	}

	return $result;
}
add_filter( 'rest_authentication_errors', 'iar_disable_rest_api_guests_check', 99 );
