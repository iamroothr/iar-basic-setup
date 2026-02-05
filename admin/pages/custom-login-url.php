<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Custom Login URL submenu (only when module is enabled)
 */
add_action( 'admin_menu', function () {
	$options = get_option( 'iar_basic_setup_options', [] );

	if ( ! empty( $options['custom-login-url'] ) ) {
		add_submenu_page(
			'iar-basic-setup-settings',
			'Custom Login URL',
			'Login URL',
			'manage_options',
			'iar-custom-login-url',
			'iar_custom_login_url_render_page'
		);
	}
} );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
	register_setting( 'iar_custom_login_url_group', 'iar_custom_login_url_options', [
		'sanitize_callback' => 'iar_custom_login_url_sanitize',
	] );
} );

/**
 * Sanitize and validate Custom Login URL options.
 *
 * @param mixed $input Raw form input.
 * @return array Sanitized options.
 */
function iar_custom_login_url_sanitize( $input ): array {
	$current = get_option( 'iar_custom_login_url_options', [] );

	$login_path = isset( $input['login_path'] ) ? sanitize_title( $input['login_path'] ) : '';
	$login_path = trim( $login_path, '/' );

	$reserved = [ 'wp-admin', 'wp-login', 'admin', 'login', 'dashboard' ];

	if ( '' !== $login_path && in_array( $login_path, $reserved, true ) ) {
		add_settings_error(
			'iar_custom_login_url_options',
			'reserved_slug',
			sprintf( '"%s" is a reserved slug and cannot be used as a login path.', esc_html( $login_path ) ),
			'error'
		);

		return $current;
	}

	$redirect_behavior = isset( $input['redirect_behavior'] ) ? $input['redirect_behavior'] : '404';

	if ( ! in_array( $redirect_behavior, [ '404', 'home' ], true ) ) {
		$redirect_behavior = '404';
	}

	return [
		'login_path'        => $login_path,
		'redirect_behavior' => $redirect_behavior,
	];
}

/**
 * Render Custom Login URL settings page
 */
function iar_custom_login_url_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options           = get_option( 'iar_custom_login_url_options', [] );
	$login_path        = isset( $options['login_path'] ) ? $options['login_path'] : '';
	$redirect_behavior = isset( $options['redirect_behavior'] ) ? $options['redirect_behavior'] : '404';
	?>
	<div class="wrap iar-admin-wrap">
		<h1>Custom Login URL <small style="font-size: .7rem;">Configure your login path:</small></h1>

		<?php settings_errors( 'iar_custom_login_url_options' ); ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_custom_login_url_group' ); ?>

			<div class="iar-modules-grid">
				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Custom Login Path</h3>
					</div>

					<p class="iar-card-desc">
						<label for="iar-login-path">
							<?php echo esc_html( home_url( '/' ) ); ?>
						</label>
					</p>

					<p>
						<input
							type="text"
							id="iar-login-path"
							name="iar_custom_login_url_options[login_path]"
							value="<?php echo esc_attr( $login_path ); ?>"
							class="regular-text"
							placeholder="my-login"
						>
					</p>

					<p class="description">
						Use only lowercase letters, numbers, and hyphens. Leave empty to use the default <code>/wp-login.php</code>.
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Redirect Behavior</h3>
					</div>

					<p class="iar-card-desc">
						What happens when someone visits <code>/wp-login.php</code> directly.
					</p>

					<fieldset>
						<p>
							<label>
								<input
									type="radio"
									name="iar_custom_login_url_options[redirect_behavior]"
									value="404"
									<?php checked( $redirect_behavior, '404' ); ?>
								>
								Show a 404 (Not Found) page
							</label>
						</p>
						<p>
							<label>
								<input
									type="radio"
									name="iar_custom_login_url_options[redirect_behavior]"
									value="home"
									<?php checked( $redirect_behavior, 'home' ); ?>
								>
								Redirect to homepage
							</label>
						</p>
					</fieldset>
				</div>
			</div>

			<?php submit_button( 'Save Settings' ); ?>
		</form>
	</div>
	<?php
}
