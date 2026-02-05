<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin menu — top-level page + "Setup" submenu rename
 */
add_action( 'admin_menu', function () {
    add_menu_page(
            'IAR Basic Setup',
            'IAR Basic Setup',
            'manage_options',
            'iar-basic-setup-settings',
            'iar_basic_setup_render_page',
            IAR_PLUGIN_URL . 'assets/images/settings.png',
            80
    );

    // Rename first submenu item
    add_submenu_page(
            'iar-basic-setup-settings',
            'IAR Basic Setup',
            'Setup',
            'manage_options',
            'iar-basic-setup-settings',
            'iar_basic_setup_render_page'
    );
} );

/**
 * Enqueue admin assets on plugin pages
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    if ( strpos( $hook, 'iar-basic-setup' ) === false ) {
        return;
    }

    wp_enqueue_style( 'iar-basic-setup-admin', IAR_PLUGIN_URL . 'assets/style/admin/admin.css', [], IAR_PLUGIN_VERSION );
} );

/**
 * Menu icon styling
 */
add_action( 'admin_head', function () {
    ?>
    <style>
        #adminmenu .toplevel_page_iar-basic-setup-settings .wp-menu-image img {
            padding-top: 7px;
        }
    </style>
    <?php
} );

/**
 * Auto-load all page files
 */
foreach ( glob( __DIR__ . '/pages/*.php' ) as $page_file ) {
    require_once $page_file;
}
