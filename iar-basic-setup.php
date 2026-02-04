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

define( 'IAR_PLUGIN_VERSION', '1.0.0' );
define( 'IAR_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'IAR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once IAR_PLUGIN_PATH . 'includes/modules-config.php';
require_once IAR_PLUGIN_PATH . 'admin/settings.php';

/**
 * Load active modules
 */
add_action( 'plugins_loaded', function () {
	$options = get_option( 'iar_basic_setup_options', [] );
	$modules = iar_get_modules();

	foreach ( $modules as $key => $module ) {
		if ( ! empty( $options[ $key ] ) ) {
			require_once IAR_PLUGIN_PATH . 'modules/' . $module['file'];
		}
	}
} );
