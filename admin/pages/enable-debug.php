<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once IAR_PLUGIN_PATH . 'includes/wp-config-editor.php';

/**
 * Add Debug Mode submenu (only when module is enabled)
 */
add_action( 'admin_menu', function () {
    $options = get_option( 'iar_basic_setup_options', [] );

    if ( ! empty( $options['enable-debug'] ) ) {
        add_submenu_page(
                'iar-basic-setup-settings',
                'Debug Mode',
                'Debug Mode',
                'manage_options',
                'iar-enable-debug',
                'iar_enable_debug_render_page'
        );
    }
} );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
    register_setting( 'iar_enable_debug_group', 'iar_enable_debug_options' );
} );

/**
 * Sync wp-config.php debug constants when debug options are saved.
 *
 * @param mixed $old_value Previous option value.
 * @param mixed $value     New option value.
 */
function iar_sync_wp_config_debug( $old_value, $value ): void {
    if ( ! is_array( $value ) ) {
        $value = [];
    }

    $display = ! empty( $value['display_errors'] );
    $log     = ! empty( $value['log_to_file'] );

    iar_wp_config_set_debug( $display, $log );
}
add_action( 'update_option_iar_enable_debug_options', 'iar_sync_wp_config_debug', 10, 2 );

/**
 * Handle first-time save (add_option).
 *
 * @param string $option Option name.
 * @param mixed  $value  Option value.
 */
function iar_sync_wp_config_debug_on_add( $option, $value ): void {
    if ( ! is_array( $value ) ) {
        $value = [];
    }

    $display = ! empty( $value['display_errors'] );
    $log     = ! empty( $value['log_to_file'] );

    iar_wp_config_set_debug( $display, $log );
}
add_action( 'add_option_iar_enable_debug_options', 'iar_sync_wp_config_debug_on_add', 10, 2 );

/**
 * Render Debug Mode page
 */
function iar_enable_debug_render_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $options         = get_option( 'iar_enable_debug_options', [] );
    $config_path     = ABSPATH . 'wp-config.php';
    $config_writable = file_exists( $config_path ) && is_writable( $config_path );
    ?>
    <div class="wrap iar-admin-wrap">
        <h1>Debug Mode <small style="font-size: .7rem;">Configure error handling:</small></h1>

        <?php if ( ! $config_writable ) : ?>
            <div class="notice notice-warning">
                <p>
                    <strong>wp-config.php is not writable.</strong>
                    This plugin needs write access to <code>wp-config.php</code> to manage debug constants automatically.
                    Please check file permissions.
                </p>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'iar_enable_debug_group' ); ?>

            <div class="iar-modules-grid">
                <div class="iar-card">
                    <div class="iar-card-header">
                        <h3>Display Errors</h3>

                        <label class="iar-toggle">
                            <input
                                    type="checkbox"
                                    name="iar_enable_debug_options[display_errors]"
                                    value="1"
                                    <?php checked( ! empty( $options['display_errors'] ) ); ?>
                            >
                            <span class="iar-slider"></span>
                        </label>
                    </div>

                    <p class="iar-card-desc">
                        Show PHP errors directly in the browser. Use only in development.
                    </p>
                </div>

                <div class="iar-card">
                    <div class="iar-card-header">
                        <h3>Log to File</h3>

                        <label class="iar-toggle">
                            <input
                                    type="checkbox"
                                    name="iar_enable_debug_options[log_to_file]"
                                    value="1"
                                    <?php checked( ! empty( $options['log_to_file'] ) ); ?>
                            >
                            <span class="iar-slider"></span>
                        </label>
                    </div>

                    <p class="iar-card-desc">
                        Log PHP errors to wp-content/debug.log. Recommended for all environments.
                    </p>
                </div>
            </div>

            <?php submit_button( 'Save Settings' ); ?>
        </form>
    </div>
    <?php
}
