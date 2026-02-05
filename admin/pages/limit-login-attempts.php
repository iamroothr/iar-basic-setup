<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Limit Login Attempts submenu (only when module is enabled)
 */
add_action( 'admin_menu', function () {
	$options = get_option( 'iar_basic_setup_options', [] );

	if ( ! empty( $options['limit-login-attempts'] ) ) {
		add_submenu_page(
			'iar-basic-setup-settings',
			'Login Attempts',
			'Login Attempts',
			'manage_options',
			'iar-limit-login-attempts',
			'iar_limit_login_attempts_render_page'
		);
	}
} );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
	register_setting( 'iar_limit_login_attempts_group', 'iar_limit_login_attempts_options', [
		'sanitize_callback' => 'iar_limit_login_attempts_sanitize',
	] );
} );

/**
 * Sanitize options.
 *
 * @param mixed $input Raw form input.
 * @return array Sanitized options.
 */
function iar_limit_login_attempts_sanitize( $input ): array {
	$sanitized = [];

	$sanitized['max_attempts'] = isset( $input['max_attempts'] )
		? absint( $input['max_attempts'] )
		: 5;

	if ( $sanitized['max_attempts'] < 1 ) {
		$sanitized['max_attempts'] = 1;
	}

	$sanitized['lockout_duration'] = isset( $input['lockout_duration'] )
		? absint( $input['lockout_duration'] )
		: 15;

	if ( $sanitized['lockout_duration'] < 1 ) {
		$sanitized['lockout_duration'] = 1;
	}

	$sanitized['lockout_message'] = isset( $input['lockout_message'] )
		? sanitize_textarea_field( $input['lockout_message'] )
		: 'Too many failed login attempts. Please try again in %d minutes.';

	return $sanitized;
}

/**
 * Render settings page
 */
function iar_limit_login_attempts_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options          = iar_limit_login_get_options();
	$max_attempts     = $options['max_attempts'];
	$lockout_duration = $options['lockout_duration'];
	$lockout_message  = $options['lockout_message'];
	?>
	<div class="wrap iar-admin-wrap">
		<h1>Login Attempts <small style="font-size: .7rem;">Configure brute-force protection:</small></h1>

		<?php settings_errors( 'iar_limit_login_attempts_options' ); ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_limit_login_attempts_group' ); ?>

			<div class="iar-modules-grid">
				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Max Attempts</h3>
					</div>

					<p class="iar-card-desc">
						Number of failed login attempts before lockout.
					</p>

					<p>
						<input
							type="number"
							name="iar_limit_login_attempts_options[max_attempts]"
							value="<?php echo esc_attr( $max_attempts ); ?>"
							min="1"
							max="100"
							class="small-text"
						>
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Lockout Duration</h3>
					</div>

					<p class="iar-card-desc">
						How long (in minutes) an IP is blocked after exceeding max attempts.
					</p>

					<p>
						<input
							type="number"
							name="iar_limit_login_attempts_options[lockout_duration]"
							value="<?php echo esc_attr( $lockout_duration ); ?>"
							min="1"
							max="1440"
							class="small-text"
						> minutes
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Lockout Message</h3>
					</div>

					<p class="iar-card-desc">
						Message shown to locked out users. Use <code>%d</code> for remaining minutes.
					</p>

					<p>
						<textarea
							name="iar_limit_login_attempts_options[lockout_message]"
							rows="3"
							class="large-text"
						><?php echo esc_textarea( $lockout_message ); ?></textarea>
					</p>
				</div>
			</div>

			<?php submit_button( 'Save Settings' ); ?>
		</form>
	</div>
	<?php
}
