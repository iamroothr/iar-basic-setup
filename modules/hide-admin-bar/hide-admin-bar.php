<?php
/**
 * Module: Hide Admin Bar
 * Description: Hides the admin bar for non-administrator users.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'after_setup_theme', function () {
	if ( ! current_user_can( 'administrator' ) && ! is_admin() ) {
		show_admin_bar( false );
	}
} );
