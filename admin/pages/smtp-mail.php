<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add SMTP Mail submenu (only when module is enabled)
 */
add_action( 'admin_menu', function () {
	$options = get_option( 'iar_basic_setup_options', [] );

	if ( ! empty( $options['smtp-mail'] ) ) {
		add_submenu_page(
			'iar-basic-setup-settings',
			'SMTP Mail',
			'SMTP Mail',
			'manage_options',
			'iar-smtp-mail',
			'iar_smtp_mail_render_page'
		);
	}
} );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
	register_setting( 'iar_smtp_mail_group', 'iar_smtp_mail_options', [
		'sanitize_callback' => 'iar_smtp_mail_sanitize',
	] );
} );

/**
 * Sanitize options.
 *
 * @param mixed $input Raw form input.
 * @return array Sanitized options.
 */
function iar_smtp_mail_sanitize( $input ): array {
	$sanitized = [];

	$sanitized['host'] = isset( $input['host'] )
		? sanitize_text_field( $input['host'] )
		: '';

	$sanitized['port'] = isset( $input['port'] )
		? absint( $input['port'] )
		: 587;

	$encryption = isset( $input['encryption'] ) ? $input['encryption'] : 'tls';
	if ( ! in_array( $encryption, [ 'none', 'ssl', 'tls' ], true ) ) {
		$encryption = 'tls';
	}
	$sanitized['encryption'] = $encryption;

	$sanitized['auth'] = ! empty( $input['auth'] ) ? 1 : 0;

	$sanitized['username'] = isset( $input['username'] )
		? sanitize_text_field( $input['username'] )
		: '';

	$sanitized['password'] = isset( $input['password'] )
		? $input['password']
		: '';

	$sanitized['from_email'] = isset( $input['from_email'] )
		? sanitize_email( $input['from_email'] )
		: '';

	$sanitized['from_name'] = isset( $input['from_name'] )
		? sanitize_text_field( $input['from_name'] )
		: '';

	return $sanitized;
}

/**
 * Render settings page
 */
function iar_smtp_mail_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options    = iar_smtp_mail_get_options();
	$host       = $options['host'];
	$port       = $options['port'];
	$encryption = $options['encryption'];
	$auth       = $options['auth'];
	$username   = $options['username'];
	$password   = $options['password'];
	$from_email = $options['from_email'];
	$from_name  = $options['from_name'];

	$test_nonce_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=iar_smtp_test_email' ),
		'iar_smtp_test_email'
	);
	?>
	<div class="wrap iar-admin-wrap">
		<h1>SMTP Mail <small style="font-size: .7rem;">Configure email delivery:</small></h1>

		<?php settings_errors( 'iar_smtp_mail_options' ); ?>

		<?php if ( isset( $_GET['iar_smtp_test'] ) ) : ?>
			<?php if ( 'success' === $_GET['iar_smtp_test'] ) : ?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Test email sent successfully! Check your inbox.', 'iar-basic-setup' ); ?></p>
				</div>
			<?php else : ?>
				<div class="notice notice-error is-dismissible">
					<p><?php esc_html_e( 'Failed to send test email. Please check your SMTP settings.', 'iar-basic-setup' ); ?></p>
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_smtp_mail_group' ); ?>

			<div class="iar-modules-grid">
				<div class="iar-card">
					<div class="iar-card-header">
						<h3>SMTP Host</h3>
					</div>

					<p class="iar-card-desc">
						SMTP server address (e.g., smtp.gmail.com).
					</p>

					<p>
						<input
							type="text"
							name="iar_smtp_mail_options[host]"
							value="<?php echo esc_attr( $host ); ?>"
							class="regular-text"
							placeholder="smtp.example.com"
						>
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>SMTP Port</h3>
					</div>

					<p class="iar-card-desc">
						Port number (typically 587 for TLS, 465 for SSL, 25 for none).
					</p>

					<p>
						<input
							type="number"
							name="iar_smtp_mail_options[port]"
							value="<?php echo esc_attr( $port ); ?>"
							class="small-text"
							min="1"
							max="65535"
						>
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Encryption</h3>
					</div>

					<p class="iar-card-desc">
						Connection security method.
					</p>

					<fieldset>
						<p>
							<label>
								<input
									type="radio"
									name="iar_smtp_mail_options[encryption]"
									value="none"
									<?php checked( $encryption, 'none' ); ?>
								>
								None
							</label>
						</p>
						<p>
							<label>
								<input
									type="radio"
									name="iar_smtp_mail_options[encryption]"
									value="ssl"
									<?php checked( $encryption, 'ssl' ); ?>
								>
								SSL
							</label>
						</p>
						<p>
							<label>
								<input
									type="radio"
									name="iar_smtp_mail_options[encryption]"
									value="tls"
									<?php checked( $encryption, 'tls' ); ?>
								>
								TLS
							</label>
						</p>
					</fieldset>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Authentication</h3>

						<label class="iar-toggle">
							<input
								type="checkbox"
								name="iar_smtp_mail_options[auth]"
								value="1"
								<?php checked( $auth ); ?>
							>
							<span class="iar-slider"></span>
						</label>
					</div>

					<p class="iar-card-desc">
						Enable if your SMTP server requires authentication.
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Username</h3>
					</div>

					<p class="iar-card-desc">
						SMTP authentication username.
					</p>

					<p>
						<input
							type="text"
							name="iar_smtp_mail_options[username]"
							value="<?php echo esc_attr( $username ); ?>"
							class="regular-text"
							autocomplete="off"
						>
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Password</h3>
					</div>

					<p class="iar-card-desc">
						SMTP authentication password.
					</p>

					<p>
						<input
							type="password"
							name="iar_smtp_mail_options[password]"
							value="<?php echo esc_attr( $password ); ?>"
							class="regular-text"
							autocomplete="new-password"
						>
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>From Email</h3>
					</div>

					<p class="iar-card-desc">
						Override the default From email address. Leave empty to use WordPress default.
					</p>

					<p>
						<input
							type="email"
							name="iar_smtp_mail_options[from_email]"
							value="<?php echo esc_attr( $from_email ); ?>"
							class="regular-text"
							placeholder="noreply@example.com"
						>
					</p>
				</div>

				<div class="iar-card">
					<div class="iar-card-header">
						<h3>From Name</h3>
					</div>

					<p class="iar-card-desc">
						Override the default From name. Leave empty to use WordPress default.
					</p>

					<p>
						<input
							type="text"
							name="iar_smtp_mail_options[from_name]"
							value="<?php echo esc_attr( $from_name ); ?>"
							class="regular-text"
							placeholder="My Website"
						>
					</p>
				</div>
			</div>

			<?php submit_button( 'Save Settings' ); ?>
		</form>

		<hr>

		<h2><?php esc_html_e( 'Test Email', 'iar-basic-setup' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Send a test email to your admin email address to verify SMTP settings.', 'iar-basic-setup' ); ?>
		</p>
		<p>
			<a href="<?php echo esc_url( $test_nonce_url ); ?>" class="button button-secondary">
				<?php esc_html_e( 'Send Test Email', 'iar-basic-setup' ); ?>
			</a>
		</p>
	</div>
	<?php
}
