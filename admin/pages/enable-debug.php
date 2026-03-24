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

	iar_wp_config_set_debug( ! empty( $value['display_errors'] ), ! empty( $value['log_to_file'] ) );
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

	iar_wp_config_set_debug( ! empty( $value['display_errors'] ), ! empty( $value['log_to_file'] ) );
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

	<div class="wrap iar-wrap">

		<?php if ( ! $config_writable ) : ?>
			<div class="iar-notice iar-notice--warning">
				<span class="material-symbols-outlined">warning</span>
				<div>
					<strong>wp-config.php is not writable.</strong>
					Debug constants cannot be updated automatically. Please check file permissions.
				</div>
			</div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_enable_debug_group' ); ?>

			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">bug_report</span>
					</div>
					<span class="iar-section-heading__label">Error Reporting</span>
				</div>
				<div class="iar-module-list">
					<div class="iar-module-row">
						<div class="iar-module-row__left">
							<span class="material-symbols-outlined iar-module-icon iar-module-icon--rose">monitor</span>
							<div class="iar-module-info">
								<h4>Display Errors</h4>
								<p>Show PHP errors directly in the browser. Use only in development.</p>
							</div>
						</div>
						<label class="iar-toggle">
							<input
								type="checkbox"
								name="iar_enable_debug_options[display_errors]"
								value="1"
								<?php checked( ! empty( $options['display_errors'] ) ); ?>
							>
							<div class="iar-toggle-track"><div class="iar-toggle-dot"></div></div>
						</label>
					</div>
					<div class="iar-module-row">
						<div class="iar-module-row__left">
							<span class="material-symbols-outlined iar-module-icon iar-module-icon--amber">description</span>
							<div class="iar-module-info">
								<h4>Log to File</h4>
								<p>Write PHP errors to <code>wp-content/debug.log</code>. Recommended for all environments.</p>
							</div>
						</div>
						<label class="iar-toggle">
							<input
								type="checkbox"
								name="iar_enable_debug_options[log_to_file]"
								value="1"
								<?php checked( ! empty( $options['log_to_file'] ) ); ?>
							>
							<div class="iar-toggle-track"><div class="iar-toggle-dot"></div></div>
						</label>
					</div>
				</div>
			</div>

			<div class="iar-save-bar">
				<div class="iar-save-bar__status">
					<span class="iar-save-bar__dot"></span>
					<span>All changes saved.</span>
				</div>
				<div class="iar-save-bar__actions">
					<button type="reset" class="iar-btn-discard">Discard</button>
					<?php submit_button( 'Save Changes', 'primary', 'submit', false, [ 'class' => 'iar-btn-save' ] ); ?>
				</div>
			</div>

		</form>
	</div>
	<?php
}
