<?php
/**
 * Centralized module configuration.
 *
 * @package IAR_Basic_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all available modules.
 *
 * @return array<string, array{file: string, title: string, desc: string}>
 */
function iar_get_modules(): array {
	return [
		'disable-gutenberg' => [
			'file'  => 'disable-gutenberg/disable-gutenberg.php',
			'title' => 'Disable Gutenberg',
			'desc'  => 'Replaces the block editor with the classic editor.',
		],
		'disable-comments'  => [
			'file'  => 'disable-comments/disable-comments.php',
			'title' => 'Disable Comments',
			'desc'  => 'Completely removes comments functionality.',
		],
		'hide-admin-bar'    => [
			'file'  => 'hide-admin-bar/hide-admin-bar.php',
			'title' => 'Hide Admin Bar',
			'desc'  => 'Hides the admin bar for non-administrator users.',
		],
		'clean-head'        => [
			'file'  => 'clean-head/clean-head.php',
			'title' => 'Clean Head',
			'desc'  => 'Removes unnecessary meta tags from the document head.',
		],
		'disable-emojis'    => [
			'file'  => 'disable-emojis/disable-emojis.php',
			'title' => 'Disable Emojis',
			'desc'  => 'Removes emoji scripts and styles.',
		],
		'svg-support'       => [
			'file'  => 'svg-support/svg-support.php',
			'title' => 'Enable SVG Support',
			'desc'  => 'Allows uploading SVG files to the media library.',
		],
		'disable-xmlrpc'    => [
			'file'  => 'disable-xmlrpc/disable-xmlrpc.php',
			'title' => 'Disable XML-RPC',
			'desc'  => 'Disables XML-RPC for better security.',
		],
		'disable-auto-updates' => [
			'file'  => 'disable-auto-updates/disable-auto-updates.php',
			'title' => 'Disable Auto Updates',
			'desc'  => 'Disables automatic updates for core, plugins, and themes.',
		],
		'enable-debug'         => [
			'file'  => 'enable-debug/enable-debug.php',
			'title' => 'Enable Debug Mode',
			'desc'  => 'Enables error reporting, display, and logging for troubleshooting.',
		],
		'post-cloner'       => [
			'file'  => 'post-cloner/post-cloner.php',
			'title' => 'Post Cloner',
			'desc'  => 'Adds a Clone action to duplicate posts, pages, and CPTs.',
		],
		'custom-login-url'  => [
			'file'  => 'custom-login-url/custom-login-url.php',
			'title' => 'Custom Login URL',
			'desc'  => 'Replaces /wp-login.php with a custom path to reduce brute-force attacks.',
		],
		'custom-login-logo' => [
			'file'  => 'custom-login-logo/custom-login-logo.php',
			'title' => 'Custom Login Logo',
			'desc'  => 'Replaces the WordPress logo on the login page with a custom image.',
		],
		'disable-rss-feeds' => [
			'file'  => 'disable-rss-feeds/disable-rss-feeds.php',
			'title' => 'Disable RSS Feeds',
			'desc'  => 'Disables all RSS and Atom feed endpoints.',
		],
		'disable-author-archives' => [
			'file'  => 'disable-author-archives/disable-author-archives.php',
			'title' => 'Disable Author Archives',
			'desc'  => 'Prevents user enumeration through author archive pages.',
		],
		'disable-rest-api-guests' => [
			'file'  => 'disable-rest-api-guests/disable-rest-api-guests.php',
			'title' => 'Disable REST API for Guests',
			'desc'  => 'Restricts REST API access to authenticated users only.',
		],
		'limit-login-attempts' => [
			'file'  => 'limit-login-attempts/limit-login-attempts.php',
			'title' => 'Limit Login Attempts',
			'desc'  => 'Blocks an IP address after failed login attempts.',
		],
		'maintenance-mode' => [
			'file'  => 'maintenance-mode/maintenance-mode.php',
			'title' => 'Maintenance Mode',
			'desc'  => 'Displays a maintenance page for non-admin visitors.',
		],
		'duplicate-menu' => [
			'file'  => 'duplicate-menu/duplicate-menu.php',
			'title' => 'Duplicate Menu',
			'desc'  => 'Adds a Duplicate action to clone nav menus with all items.',
		],
		'smtp-mail' => [
			'file'  => 'smtp-mail/smtp-mail.php',
			'title' => 'SMTP Mail',
			'desc'  => 'Configures WordPress to send emails through SMTP.',
		],
	];
}