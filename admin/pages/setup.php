<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once IAR_PLUGIN_PATH . 'includes/wp-config-editor.php';

/**
 * Revert wp-config.php debug constants when the enable-debug module is turned off.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $value     New option value.
 */
function iar_maybe_revert_wp_config_debug( $old_value, $value ): void {
    $was_enabled = is_array( $old_value ) && ! empty( $old_value['enable-debug'] );
    $is_enabled  = is_array( $value ) && ! empty( $value['enable-debug'] );

    if ( $was_enabled && ! $is_enabled ) {
        iar_wp_config_remove_debug();
    }
}
add_action( 'update_option_iar_basic_setup_options', 'iar_maybe_revert_wp_config_debug', 10, 2 );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
    register_setting( 'iar_basic_setup_group', 'iar_basic_setup_options' );
} );

/**
 * Render page
 */
function iar_basic_setup_render_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options = get_option( 'iar_basic_setup_options', [] );
    $modules = iar_get_modules();
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
