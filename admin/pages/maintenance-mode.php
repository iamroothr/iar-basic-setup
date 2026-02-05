<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Maintenance Mode submenu (only when module is enabled)
 */
add_action( 'admin_menu', function () {
	$options = get_option( 'iar_basic_setup_options', [] );

	if ( ! empty( $options['maintenance-mode'] ) ) {
		add_submenu_page(
			'iar-basic-setup-settings',
			'Maintenance Mode',
			'Maintenance',
			'manage_options',
			'iar-maintenance-mode',
			'iar_maintenance_mode_render_page'
		);
	}
} );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
	register_setting( 'iar_maintenance_mode_group', 'iar_maintenance_mode_options', [
		'sanitize_callback' => 'iar_maintenance_mode_sanitize',
	] );
} );

/**
 * Sanitize options.
 *
 * @param mixed $input Raw form input.
 * @return array Sanitized options.
 */
function iar_maintenance_mode_sanitize( $input ): array {
	$sanitized = [];

	$sanitized['enabled'] = ! empty( $input['enabled'] ) ? 1 : 0;

	$sanitized['page_title'] = isset( $input['page_title'] )
		? sanitize_text_field( $input['page_title'] )
		: 'Under Maintenance';

	$sanitized['message'] = isset( $input['message'] )
		? wp_kses_post( $input['message'] )
		: '';

	return $sanitized;
}

/**
 * Render settings page
 */
function iar_maintenance_mode_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options    = iar_maintenance_mode_get_options();
	$enabled    = $options['enabled'];
	$page_title = $options['page_title'];
	$message    = $options['message'];
	?>
	<div class="wrap iar-admin-wrap">
		<h1>Maintenance Mode <small style="font-size: .7rem;">Configure maintenance page:</small></h1>

		<?php settings_errors( 'iar_maintenance_mode_options' ); ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_maintenance_mode_group' ); ?>

			<div class="iar-modules-grid">
				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Enable Maintenance Mode</h3>

						<label class="iar-toggle">
							<input
								type="checkbox"
								name="iar_maintenance_mode_options[enabled]"
								value="1"
								<?php checked( $enabled ); ?>
							>
							<span class="iar-slider"></span>
						</label>
					</div>

					<p class="iar-card-desc">
						When enabled, non-admin visitors see the maintenance page.
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Page Title</h3>
					</div>

					<p class="iar-card-desc">
						Title displayed on the maintenance page.
					</p>

					<p>
						<input
							type="text"
							name="iar_maintenance_mode_options[page_title]"
							value="<?php echo esc_attr( $page_title ); ?>"
							class="regular-text"
						>
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Message</h3>
					</div>

					<p class="iar-card-desc">
						Message displayed to visitors. Basic HTML is allowed.
					</p>

					<p>
						<textarea
							name="iar_maintenance_mode_options[message]"
							rows="4"
							class="large-text"
						><?php echo esc_textarea( $message ); ?></textarea>
					</p>
				</div>
			</div>

			<?php submit_button( 'Save Settings' ); ?>
		</form>
	</div>
	<?php
}
