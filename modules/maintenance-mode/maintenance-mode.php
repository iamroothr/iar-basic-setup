<?php
/**
 * Module: Maintenance Mode
 * Description: Displays a maintenance page for non-admin visitors.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get module options with defaults.
 *
 * @return array Options array.
 */
function iar_maintenance_mode_get_options(): array {
	$defaults = [
		'enabled'    => 0,
		'page_title' => 'Under Maintenance',
		'message'    => 'We are currently performing scheduled maintenance. Please check back soon.',
	];

	$options = get_option( 'iar_maintenance_mode_options', [] );

	return wp_parse_args( $options, $defaults );
}

/**
 * Check if current request should be blocked.
 */
function iar_maintenance_mode_check(): void {
	$options = iar_maintenance_mode_get_options();

	if ( empty( $options['enabled'] ) ) {
		return;
	}

	if ( current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( iar_maintenance_mode_is_exempt_request() ) {
		return;
	}

	$title   = esc_html( $options['page_title'] );
	$message = wp_kses_post( $options['message'] );

	header( 'HTTP/1.1 503 Service Unavailable' );
	header( 'Retry-After: 3600' );

	?>
	<!DOCTYPE html>
	<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo $title; ?></title>
		<style>
			* { box-sizing: border-box; }
			body {
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
				background: #f1f1f1;
				color: #444;
				margin: 0;
				padding: 20px;
				display: flex;
				justify-content: center;
				align-items: center;
				min-height: 100vh;
			}
			.maintenance-container {
				background: #fff;
				padding: 40px;
				border-radius: 8px;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
				max-width: 600px;
				text-align: center;
			}
			h1 {
				margin: 0 0 20px;
				font-size: 28px;
				font-weight: 600;
			}
			p {
				margin: 0;
				font-size: 16px;
				line-height: 1.6;
			}
		</style>
	</head>
	<body>
		<div class="maintenance-container">
			<h1><?php echo $title; ?></h1>
			<p><?php echo $message; ?></p>
		</div>
	</body>
	</html>
	<?php
	exit;
}
add_action( 'template_redirect', 'iar_maintenance_mode_check', 1 );

/**
 * Check if current request is exempt from maintenance mode.
 *
 * @return bool True if exempt.
 */
function iar_maintenance_mode_is_exempt_request(): bool {
	if ( is_admin() ) {
		return true;
	}

	$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

	$exempt_paths = [
		'/wp-login.php',
		'/wp-admin',
		'/wp-cron.php',
		'/wp-json',
		'/admin-ajax.php',
		'/admin-post.php',
	];

	$custom_login_options = get_option( 'iar_custom_login_url_options', [] );
	if ( ! empty( $custom_login_options['login_path'] ) ) {
		$exempt_paths[] = '/' . $custom_login_options['login_path'];
	}

	foreach ( $exempt_paths as $path ) {
		if ( false !== strpos( $request_uri, $path ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Add admin bar notice when maintenance mode is active.
 *
 * @param WP_Admin_Bar $wp_admin_bar Admin bar instance.
 */
function iar_maintenance_mode_admin_bar_notice( WP_Admin_Bar $wp_admin_bar ): void {
	$options = iar_maintenance_mode_get_options();

	if ( empty( $options['enabled'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$wp_admin_bar->add_node( [
		'id'    => 'iar-maintenance-mode',
		'title' => 'Maintenance Mode Active',
		'href'  => admin_url( 'admin.php?page=iar-maintenance-mode' ),
		'meta'  => [
			'class' => 'iar-maintenance-mode-notice',
		],
	] );
}
add_action( 'admin_bar_menu', 'iar_maintenance_mode_admin_bar_notice', 999 );

/**
 * Add CSS for admin bar notice.
 */
function iar_maintenance_mode_admin_bar_css(): void {
	$options = iar_maintenance_mode_get_options();

	if ( empty( $options['enabled'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	if ( ! is_admin_bar_showing() ) {
		return;
	}

	?>
	<style>
		#wpadminbar .iar-maintenance-mode-notice > .ab-item {
			background: #d63638 !important;
			color: #fff !important;
		}
		#wpadminbar .iar-maintenance-mode-notice:hover > .ab-item {
			background: #b32d2e !important;
			color: #fff !important;
		}
	</style>
	<?php
}
add_action( 'admin_head', 'iar_maintenance_mode_admin_bar_css' );
add_action( 'wp_head', 'iar_maintenance_mode_admin_bar_css' );
