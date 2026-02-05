<?php
/**
 * Module: Limit Login Attempts
 * Description: Blocks an IP address after failed login attempts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get module options with defaults.
 *
 * @return array Options array.
 */
function iar_limit_login_get_options(): array {
	$defaults = [
		'max_attempts'     => 5,
		'lockout_duration' => 15,
		'lockout_message'  => 'Too many failed login attempts. Please try again in %d minutes.',
	];

	$options = get_option( 'iar_limit_login_attempts_options', [] );

	return wp_parse_args( $options, $defaults );
}

/**
 * Get transient key for an IP address.
 *
 * @param string $ip IP address.
 * @return string Transient key.
 */
function iar_limit_login_get_transient_key( string $ip ): string {
	return 'iar_login_attempts_' . md5( $ip );
}

/**
 * Get current attempt data for an IP.
 *
 * @param string $ip IP address.
 * @return array Attempt data with count and locked_until.
 */
function iar_limit_login_get_attempts( string $ip ): array {
	$key  = iar_limit_login_get_transient_key( $ip );
	$data = get_transient( $key );

	if ( false === $data || ! is_array( $data ) ) {
		return [
			'count'        => 0,
			'locked_until' => 0,
		];
	}

	return wp_parse_args( $data, [
		'count'        => 0,
		'locked_until' => 0,
	] );
}

/**
 * Check if IP is locked out before authentication.
 *
 * @param WP_User|WP_Error|null $user     User object or error.
 * @param string                $username Username.
 * @param string                $password Password.
 * @return WP_User|WP_Error|null Modified result.
 */
function iar_limit_login_check_lockout( $user, string $username, string $password ) {
	if ( empty( $username ) ) {
		return $user;
	}

	$ip       = iar_limit_login_get_client_ip();
	$attempts = iar_limit_login_get_attempts( $ip );

	if ( $attempts['locked_until'] > time() ) {
		$options          = iar_limit_login_get_options();
		$minutes_left     = ceil( ( $attempts['locked_until'] - time() ) / 60 );
		$lockout_message  = $options['lockout_message'];

		return new WP_Error(
			'iar_too_many_attempts',
			sprintf( $lockout_message, $minutes_left )
		);
	}

	return $user;
}
add_filter( 'authenticate', 'iar_limit_login_check_lockout', 30, 3 );

/**
 * Record a failed login attempt.
 *
 * @param string $username Username that failed.
 */
function iar_limit_login_record_failure( string $username ): void {
	$ip       = iar_limit_login_get_client_ip();
	$options  = iar_limit_login_get_options();
	$attempts = iar_limit_login_get_attempts( $ip );

	$attempts['count']++;

	if ( $attempts['count'] >= $options['max_attempts'] ) {
		$attempts['locked_until'] = time() + ( $options['lockout_duration'] * 60 );
	}

	$key = iar_limit_login_get_transient_key( $ip );
	set_transient( $key, $attempts, $options['lockout_duration'] * 60 );
}
add_action( 'wp_login_failed', 'iar_limit_login_record_failure' );

/**
 * Clear login attempts on successful login.
 *
 * @param string $user_login Username.
 */
function iar_limit_login_clear_attempts( string $user_login ): void {
	$ip  = iar_limit_login_get_client_ip();
	$key = iar_limit_login_get_transient_key( $ip );
	delete_transient( $key );
}
add_action( 'wp_login', 'iar_limit_login_clear_attempts' );

/**
 * Get client IP address.
 *
 * @return string IP address.
 */
function iar_limit_login_get_client_ip(): string {
	$ip = '';

	if ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	return $ip;
}
