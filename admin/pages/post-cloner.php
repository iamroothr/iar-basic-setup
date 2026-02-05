<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add Post Cloner submenu (only when module is enabled)
 */
add_action( 'admin_menu', function () {
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
    register_setting( 'iar_post_cloner_group', 'iar_post_cloner_options' );
} );

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
