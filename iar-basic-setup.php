<?php
/**
 * Plugin Name:       IAR Basic Setup
 * Plugin URI:        https://iamroot.agency
 * Description:       A modular WordPress cleanup and optimization plugin. Enable only the features you need.
 * Version:           1.0.0
 * Author:            I am root
 * Author URI:        https://iamroot.agency
 * Text Domain:       iar-basic-setup
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'IAR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'IAR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Load Admin Dashboard
require_once IAR_PLUGIN_PATH . 'admin/settings.php';

/**
 * Load active modules
 */
add_action( 'plugins_loaded', function () {
	$options = get_option( 'iar_basic_setup_options', [] );

	$modules = [
		'disable-gutenberg' => 'disable-gutenberg/disable-gutenberg.php',
		'disable-comments'  => 'disable-comments/disable-comments.php',
		'hide-admin-bar'    => 'hide-admin-bar/hide-admin-bar.php',
		'clean-head'        => 'clean-head/clean-head.php',
		'disable-emojis'    => 'disable-emojis/disable-emojis.php',
		'svg-support'       => 'svg-support/svg-support.php',
		'disable-xmlrpc'    => 'disable-xmlrpc/disable-xmlrpc.php',
		'post-cloner'       => 'post-cloner/post-cloner.php',
	];

	foreach ( $modules as $key => $file ) {
		if ( ! empty( $options[ $key ] ) ) {
			require_once IAR_PLUGIN_PATH . 'modules/' . $file;
		}
	}
} );
