<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Admin menu
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

    $options = get_option( 'iar_basic_setup_options', [] );

    if ( ! empty( $options['post-cloner'] ) ) {
        add_submenu_page(
                'iar-basic-setup-settings',
                'Post Cloner',
                'Post Cloner',
                'manage_options',
                'iar-post-cloner',
                'iar_post_cloner_render_page'
        );
    }
} );


/**
 * Register settings
 */
add_action( 'admin_init', function () {
    register_setting( 'iar_basic_setup_group', 'iar_basic_setup_options' );
    register_setting( 'iar_post_cloner_group', 'iar_post_cloner_options' );
} );

/**
 * Enqueue admin assets
 */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
    $allowed_hooks = [
        'toplevel_page_iar-basic-setup-settings',
        'iar-basic-setup_page_iar-post-cloner',
    ];

    if ( ! in_array( $hook, $allowed_hooks, true ) ) {
        return;
    }

    wp_enqueue_style( 'iar-basic-setup-admin', IAR_PLUGIN_URL . 'assets/style/admin/admin.css', [], '1.0.0' );
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
 * Render page
 */
function iar_basic_setup_render_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options = get_option( 'iar_basic_setup_options', [] );

    $modules = [
            'disable-gutenberg' => [
                    'title' => 'Disable Gutenberg',
                    'desc'  => 'Replaces the block editor with the classic editor.',
            ],
            'disable-comments'  => [
                    'title' => 'Disable Comments',
                    'desc'  => 'Completely removes comments functionality.',
            ],
            'hide-admin-bar'    => [
                    'title' => 'Hide Admin Bar',
                    'desc'  => 'Hides the admin bar for non-administrator users.',
            ],
            'clean-head'        => [
                    'title' => 'Clean Head',
                    'desc'  => 'Removes unnecessary meta tags from the document head.',
            ],
            'disable-emojis'    => [
                    'title' => 'Disable Emojis',
                    'desc'  => 'Removes emoji scripts and styles.',
            ],
            'svg-support'       => [
                    'title' => 'Enable SVG Support',
                    'desc'  => 'Allows uploading SVG files to the media library.',
            ],
            'disable-xmlrpc'    => [
                    'title' => 'Disable XML-RPC',
                    'desc'  => 'Disables XML-RPC for better security.',
            ],
            'post-cloner'       => [
                    'title' => 'Post Cloner',
                    'desc'  => 'Adds a Clone action to duplicate posts, pages, and CPTs.',
            ],
    ];
    ?>

    <div class="wrap iar-admin-wrap">
        <h1>IAR Basic Setup</h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'iar_basic_setup_group' ); ?>

            <div class="iar-modules-grid">

                <?php foreach ( $modules as $key => $module ) :
                    $enabled = ! empty( $options[ $key ] );
                    ?>
                    <div class="iar-card">
                        <div class="iar-card-header">
                            <h3><?php echo esc_html( $module['title'] ); ?></h3>

                            <label class="iar-toggle">
                                <input
                                        type="checkbox"
                                        name="iar_basic_setup_options[<?php echo esc_attr( $key ); ?>]"
                                        value="1"
                                        <?php checked( $enabled ); ?>
                                >
                                <span class="iar-slider"></span>
                            </label>
                        </div>

                        <p class="iar-card-desc">
                            <?php echo esc_html( $module['desc'] ); ?>
                        </p>
                    </div>
                <?php endforeach; ?>

            </div>

            <?php submit_button( 'Save Settings' ); ?>
        </form>
    </div>
    <?php
}

/**
 * Render Post Cloner page
 */
function iar_post_cloner_render_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options    = get_option( 'iar_post_cloner_options', [] );
    $post_types = get_post_types( [ 'show_ui' => true ], 'objects' );

    // Remove attachment from the list
    unset( $post_types['attachment'] );
    ?>
    <div class="wrap iar-admin-wrap">
        <h1>Post Cloner <small style="font-size: .7rem;">Select which post types can be cloned:</small></h1>

        <form method="post" action="options.php">
            <?php settings_fields( 'iar_post_cloner_group' ); ?>

            <div class="iar-modules-grid">
                <?php foreach ( $post_types as $post_type ) :
                    $enabled = ! empty( $options['post_types'][ $post_type->name ] );
                    ?>
                    <div class="iar-card">
                        <div class="iar-card-header">
                            <h3><?php echo esc_html( $post_type->labels->singular_name ); ?></h3>

                            <label class="iar-toggle">
                                <input
                                        type="checkbox"
                                        name="iar_post_cloner_options[post_types][<?php echo esc_attr( $post_type->name ); ?>]"
                                        value="1"
                                        <?php checked( $enabled ); ?>
                                >
                                <span class="iar-slider"></span>
                            </label>
                        </div>

                        <p class="iar-card-desc">
                            <?php echo esc_html( $post_type->labels->name ); ?> (<?php echo esc_html( $post_type->name ); ?>)
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php submit_button( 'Save Settings' ); ?>
        </form>
    </div>
    <?php
}
