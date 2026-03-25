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

	<div class="wrap iar-wrap">

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_limit_login_attempts_group' ); ?>

			<!-- Page Header -->
			<div class="iar-page-header">
				<div class="iar-page-header__text">
					<h2 class="iar-page-title">Limit Login Attempts</h2>
					<p class="iar-page-subtitle">Blocks an IP address after failed login attempts.</p>
				</div>
				<span class="iar-system-badge">System Active</span>
			</div>

			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">block</span>
					</div>
					<span class="iar-section-heading__label">Protection Settings</span>
				</div>
				<div class="iar-section--card">
				<div class="iar-field-list">
					<div class="iar-field-row">
						<div class="iar-field-row__left">
							<span class="material-symbols-outlined iar-module-icon iar-module-icon--rose">password</span>
							<div>
								<div class="iar-field-row__label">Max Attempts</div>
								<div class="iar-field-row__desc">Failed logins before lockout (1&ndash;100).</div>
							</div>
						</div>
						<div class="iar-field-row__control">
							<div class="iar-input-suffix">
								<input
									type="number"
									name="iar_limit_login_attempts_options[max_attempts]"
									value="<?php echo esc_attr( $max_attempts ); ?>"
									min="1"
									max="100"
									class="iar-input-number"
								>
								<span>attempts</span>
							</div>
						</div>
					</div>
					<div class="iar-field-row">
						<div class="iar-field-row__left">
							<span class="material-symbols-outlined iar-module-icon iar-module-icon--amber">timer</span>
							<div>
								<div class="iar-field-row__label">Lockout Duration</div>
								<div class="iar-field-row__desc">How long the IP is blocked after exceeding max attempts.</div>
							</div>
						</div>
						<div class="iar-field-row__control">
							<div class="iar-input-suffix">
								<input
									type="number"
									name="iar_limit_login_attempts_options[lockout_duration]"
									value="<?php echo esc_attr( $lockout_duration ); ?>"
									min="1"
									max="1440"
									class="iar-input-number"
								>
								<span>minutes</span>
							</div>
						</div>
					</div>
					<div class="iar-field-row">
						<div class="iar-field-row__left">
							<span class="material-symbols-outlined iar-module-icon iar-module-icon--slate">chat</span>
							<div>
								<div class="iar-field-row__label">Lockout Message</div>
								<div class="iar-field-row__desc">Shown to blocked users. Use <code>%d</code> for remaining minutes.</div>
							</div>
						</div>
						<div class="iar-field-row__control">
							<textarea
								name="iar_limit_login_attempts_options[lockout_message]"
								rows="3"
								class="iar-textarea"
							><?php echo esc_textarea( $lockout_message ); ?></textarea>
						</div>
					</div>
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
