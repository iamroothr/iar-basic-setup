<?php
/**
 * Module: Disable Auto Updates
 * Description: Disables automatic updates for core, plugins, and themes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'automatic_updater_disabled', '__return_true' );
add_filter( 'auto_update_core', '__return_false' );
add_filter( 'auto_update_plugin', '__return_false' );
add_filter( 'auto_update_theme', '__return_false' );
add_filter( 'auto_update_translation', '__return_false' );
