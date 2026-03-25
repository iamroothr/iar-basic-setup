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

	$errors = get_settings_errors( 'iar_custom_login_url_options' );
	?>

	<div class="wrap iar-wrap">

		<?php foreach ( $errors as $error ) : ?>
			<div class="iar-notice iar-notice--<?php echo 'error' === $error['type'] ? 'error' : 'warning'; ?>">
				<span class="material-symbols-outlined"><?php echo 'error' === $error['type'] ? 'error' : 'warning'; ?></span>
				<div><?php echo wp_kses_post( $error['message'] ); ?></div>
			</div>
		<?php endforeach; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_custom_login_url_group' ); ?>

			<!-- Page Header -->
			<div class="iar-page-header">
				<div class="iar-page-header__text">
					<h2 class="iar-page-title">Custom Login URL</h2>
					<p class="iar-page-subtitle">Replaces /wp-login.php with a custom path to reduce brute-force attacks.</p>
				</div>
				<span class="iar-system-badge">System Active</span>
			</div>

			<!-- Login Path -->
			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">link</span>
					</div>
					<span class="iar-section-heading__label">Login Path</span>
				</div>
				<div class="iar-section--card">
				<div class="iar-field-list">
					<div class="iar-field-row">
						<div class="iar-field-row__left">
							<span class="material-symbols-outlined iar-module-icon iar-module-icon--sky">edit</span>
							<div>
								<div class="iar-field-row__label">Custom Path</div>
								<div class="iar-field-row__desc">Lowercase letters, numbers, hyphens only. Leave empty to keep <code>/wp-login.php</code>.</div>
							</div>
						</div>
						<div class="iar-field-row__control">
							<div class="iar-input-with-prefix">
								<span class="iar-input-prefix"><?php echo esc_html( trailingslashit( home_url() ) ); ?></span>
								<input
									type="text"
									id="iar-login-path"
									name="iar_custom_login_url_options[login_path]"
									value="<?php echo esc_attr( $login_path ); ?>"
									class="iar-input"
									placeholder="my-login"
								>
							</div>
						</div>
					</div>
				</div>
				</div>
			</div>

			<!-- Redirect Behavior -->
			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">arrow_forward</span>
					</div>
					<span class="iar-section-heading__label">Redirect Behavior</span>
				</div>
				<div class="iar-section--card">
				<div class="iar-field-list">
					<div class="iar-field-row">
						<div class="iar-field-row__left">
							<span class="material-symbols-outlined iar-module-icon iar-module-icon--amber">manage_accounts</span>
							<div>
								<div class="iar-field-row__label">When <code>/wp-login.php</code> is accessed directly</div>
								<div class="iar-field-row__desc">What happens when someone visits the old login URL.</div>
							</div>
						</div>
						<div class="iar-field-row__control">
							<div class="iar-radio-group">
								<label class="iar-radio-label">
									<input
										type="radio"
										name="iar_custom_login_url_options[redirect_behavior]"
										value="404"
										<?php checked( $redirect_behavior, '404' ); ?>
									>
									Show a 404 (Not Found) page
								</label>
								<label class="iar-radio-label">
									<input
										type="radio"
										name="iar_custom_login_url_options[redirect_behavior]"
										value="home"
										<?php checked( $redirect_behavior, 'home' ); ?>
									>
									Redirect to homepage
								</label>
							</div>
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
