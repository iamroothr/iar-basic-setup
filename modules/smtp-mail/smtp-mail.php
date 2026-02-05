<?php
/**
 * Module: SMTP Mail
 * Description: Configures WordPress to send emails through SMTP.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get module options with defaults.
 *
 * @return array Options array.
 */
function iar_smtp_mail_get_options(): array {
	$defaults = [
		'host'       => '',
		'port'       => 587,
		'encryption' => 'tls',
		'auth'       => 1,
		'username'   => '',
		'password'   => '',
		'from_email' => '',
		'from_name'  => '',
	];

	$options = get_option( 'iar_smtp_mail_options', [] );

	return wp_parse_args( $options, $defaults );
}

/**
 * Configure PHPMailer for SMTP.
 *
 * @param PHPMailer\PHPMailer\PHPMailer $phpmailer PHPMailer instance.
 */
function iar_smtp_mail_configure( $phpmailer ): void {
	$options = iar_smtp_mail_get_options();

	if ( empty( $options['host'] ) ) {
		return;
	}

	$phpmailer->isSMTP();
	$phpmailer->Host = $options['host'];
	$phpmailer->Port = intval( $options['port'] );

	if ( 'none' === $options['encryption'] ) {
		$phpmailer->SMTPSecure  = '';
		$phpmailer->SMTPAutoTLS = false;
	} else {
		$phpmailer->SMTPSecure = $options['encryption'];
	}

	if ( ! empty( $options['auth'] ) ) {
		$phpmailer->SMTPAuth = true;
		$phpmailer->Username = $options['username'];
		$phpmailer->Password = $options['password'];
	} else {
		$phpmailer->SMTPAuth = false;
	}
}
add_action( 'phpmailer_init', 'iar_smtp_mail_configure' );

/**
 * Override From email address.
 *
 * @param string $from_email Default from email.
 * @return string Modified from email.
 */
function iar_smtp_mail_from( string $from_email ): string {
	$options = iar_smtp_mail_get_options();

	if ( ! empty( $options['from_email'] ) ) {
		return $options['from_email'];
	}

	return $from_email;
}
add_filter( 'wp_mail_from', 'iar_smtp_mail_from' );

/**
 * Override From name.
 *
 * @param string $from_name Default from name.
 * @return string Modified from name.
 */
function iar_smtp_mail_from_name( string $from_name ): string {
	$options = iar_smtp_mail_get_options();

	if ( ! empty( $options['from_name'] ) ) {
		return $options['from_name'];
	}

	return $from_name;
}
add_filter( 'wp_mail_from_name', 'iar_smtp_mail_from_name' );

/**
 * Handle test email request.
 */
function iar_smtp_mail_send_test(): void {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'iar_smtp_test_email' ) ) {
		wp_die( __( 'Security check failed.', 'iar-basic-setup' ) );
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have permission to send test emails.', 'iar-basic-setup' ) );
	}

	$current_user = wp_get_current_user();
	$to           = $current_user->user_email;
	$subject      = __( 'IAR Basic Setup - SMTP Test Email', 'iar-basic-setup' );
	$message      = __( 'This is a test email sent from your WordPress site to verify SMTP settings are working correctly.', 'iar-basic-setup' );

	$result = wp_mail( $to, $subject, $message );

	$redirect_url = add_query_arg( [
		'iar_smtp_test' => $result ? 'success' : 'error',
	], admin_url( 'admin.php?page=iar-smtp-mail' ) );

	wp_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_iar_smtp_test_email', 'iar_smtp_mail_send_test' );
