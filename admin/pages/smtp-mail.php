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
        add_submenu_page( 'iar-basic-setup-settings', 'SMTP Mail', 'SMTP Mail', 'manage_options', 'iar-smtp-mail', 'iar_smtp_mail_render_page' );
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
 *
 * @return array Sanitized options.
 */
function iar_smtp_mail_sanitize( $input ): array {
    $sanitized = [];

    $sanitized['host'] = isset( $input['host'] ) ? sanitize_text_field( $input['host'] ) : '';

    $sanitized['port'] = isset( $input['port'] ) ? absint( $input['port'] ) : 587;

    $encryption = isset( $input['encryption'] ) ? $input['encryption'] : 'tls';
    if ( ! in_array( $encryption, [ 'none', 'ssl', 'tls' ], true ) ) {
        $encryption = 'tls';
    }
    $sanitized['encryption'] = $encryption;

    $sanitized['auth'] = ! empty( $input['auth'] ) ? 1 : 0;

    $sanitized['username'] = isset( $input['username'] ) ? sanitize_text_field( $input['username'] ) : '';

    $sanitized['password'] = isset( $input['password'] ) ? $input['password'] : '';

    $sanitized['from_email'] = isset( $input['from_email'] ) ? sanitize_email( $input['from_email'] ) : '';

    $sanitized['from_name'] = isset( $input['from_name'] ) ? sanitize_text_field( $input['from_name'] ) : '';

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

    $test_nonce_url = wp_nonce_url( admin_url( 'admin-post.php?action=iar_smtp_test_email' ), 'iar_smtp_test_email' );
    ?>

    <div class="wrap iar-wrap">

        <?php if ( isset( $_GET['iar_smtp_test'] ) ) : ?>
            <?php if ( 'success' === $_GET['iar_smtp_test'] ) : ?>
                <div class="iar-notice iar-notice--success">
                    <span class="material-symbols-outlined">check_circle</span>
                    <div><?php esc_html_e( 'Test email sent successfully! Check your inbox.', 'iar-basic-setup' ); ?></div>
                </div>
            <?php else : ?>
                <div class="iar-notice iar-notice--error">
                    <span class="material-symbols-outlined">error</span>
                    <div><?php esc_html_e( 'Failed to send test email. Please check your SMTP settings.', 'iar-basic-setup' ); ?></div>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php settings_fields( 'iar_smtp_mail_group' ); ?>

            <!-- Page Header -->
            <div class="iar-page-header">
                <div class="iar-page-header__text">
                    <h2 class="iar-page-title">SMTP Mail</h2>
                    <p class="iar-page-subtitle">Configures WordPress to send emails through SMTP.</p>
                </div>
                <span class="iar-system-badge">System Active</span>
            </div>

            <!-- Server -->
            <div class="iar-section">
                <div class="iar-section-heading">
                    <div class="iar-section-heading__icon">
                        <span class="material-symbols-outlined">dns</span>
                    </div>
                    <span class="iar-section-heading__label">Server</span>
                </div>
                <div class="iar-section--card">
                    <div class="iar-field-list">
                        <div class="iar-field-row">
                            <div class="iar-field-row__left">
                                <div>
                                    <div class="iar-field-row__label">SMTP Host</div>
                                    <div class="iar-field-row__desc">Server address (e.g. smtp.gmail.com).</div>
                                </div>
                            </div>
                            <div class="iar-field-row__control">
                                <input
                                        type="text"
                                        name="iar_smtp_mail_options[host]"
                                        value="<?php echo esc_attr( $host ); ?>"
                                        class="iar-input"
                                        placeholder="smtp.example.com"
                                >
                            </div>
                        </div>
                        <div class="iar-field-row">
                            <div class="iar-field-row__left">
                                <div>
                                    <div class="iar-field-row__label">Port</div>
                                    <div class="iar-field-row__desc">587 for TLS &bull; 465 for SSL &bull; 25 for
                                        none.
                                    </div>
                                </div>
                            </div>
                            <div class="iar-field-row__control">
                                <input
                                        type="number"
                                        name="iar_smtp_mail_options[port]"
                                        value="<?php echo esc_attr( $port ); ?>"
                                        min="1"
                                        max="65535"
                                        class="iar-input-number"
                                >
                            </div>
                        </div>
                        <div class="iar-field-row">
                            <div class="iar-field-row__left">
                                <div>
                                    <div class="iar-field-row__label">Encryption</div>
                                    <div class="iar-field-row__desc">Connection security method.</div>
                                </div>
                            </div>
                            <div class="iar-field-row__control">
                                <div class="iar-radio-group">
                                    <label class="iar-radio-label">
                                        <input type="radio" name="iar_smtp_mail_options[encryption]"
                                               value="tls" <?php checked( $encryption, 'tls' ); ?>>
                                        TLS (recommended)
                                    </label>
                                    <label class="iar-radio-label">
                                        <input type="radio" name="iar_smtp_mail_options[encryption]"
                                               value="ssl" <?php checked( $encryption, 'ssl' ); ?>>
                                        SSL
                                    </label>
                                    <label class="iar-radio-label">
                                        <input type="radio" name="iar_smtp_mail_options[encryption]"
                                               value="none" <?php checked( $encryption, 'none' ); ?>>
                                        None
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Authentication -->
            <div class="iar-section">
                <div class="iar-section-heading">
                    <div class="iar-section-heading__icon">
                        <span class="material-symbols-outlined">key</span>
                    </div>
                    <span class="iar-section-heading__label">Authentication</span>
                </div>
                <div class="iar-section--card">
                    <div class="iar-module-list">
                        <div class="iar-module-row">
                            <div class="iar-module-row__left">
                                <div class="iar-module-info">
                                    <h4>Enable Authentication</h4>
                                    <p>Required if your SMTP server needs a username and password.</p>
                                </div>
                            </div>
                            <label class="iar-toggle">
                                <input
                                        type="checkbox"
                                        name="iar_smtp_mail_options[auth]"
                                        value="1"
                                        <?php checked( $auth ); ?>
                                >
                                <div class="iar-toggle-track">
                                    <div class="iar-toggle-dot"></div>
                                </div>
                            </label>
                        </div>
                    </div>
                    <div class="iar-field-list">
                        <div class="iar-field-row">
                            <div class="iar-field-row__left">
                                <div>
                                    <div class="iar-field-row__label">Username</div>
                                    <div class="iar-field-row__desc">SMTP authentication username.</div>
                                </div>
                            </div>
                            <div class="iar-field-row__control">
                                <input
                                        type="text"
                                        name="iar_smtp_mail_options[username]"
                                        value="<?php echo esc_attr( $username ); ?>"
                                        class="iar-input"
                                        autocomplete="off"
                                >
                            </div>
                        </div>
                        <div class="iar-field-row">
                            <div class="iar-field-row__left">
                                <div>
                                    <div class="iar-field-row__label">Password</div>
                                    <div class="iar-field-row__desc">SMTP authentication password.</div>
                                </div>
                            </div>
                            <div class="iar-field-row__control">
                                <input
                                        type="password"
                                        name="iar_smtp_mail_options[password]"
                                        value="<?php echo esc_attr( $password ); ?>"
                                        class="iar-input"
                                        autocomplete="new-password"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sender -->
            <div class="iar-section">
                <div class="iar-section-heading">
                    <div class="iar-section-heading__icon">
                        <span class="material-symbols-outlined">person</span>
                    </div>
                    <span class="iar-section-heading__label">Sender</span>
                </div>
                <div class="iar-section--card">
                    <div class="iar-field-list">
                        <div class="iar-field-row">
                            <div class="iar-field-row__left">
                                <div>
                                    <div class="iar-field-row__label">From Email</div>
                                    <div class="iar-field-row__desc">Leave empty to use WordPress default.</div>
                                </div>
                            </div>
                            <div class="iar-field-row__control">
                                <input
                                        type="email"
                                        name="iar_smtp_mail_options[from_email]"
                                        value="<?php echo esc_attr( $from_email ); ?>"
                                        class="iar-input"
                                        placeholder="noreply@example.com"
                                >
                            </div>
                        </div>
                        <div class="iar-field-row">
                            <div class="iar-field-row__left">
                                <div>
                                    <div class="iar-field-row__label">From Name</div>
                                    <div class="iar-field-row__desc">Leave empty to use WordPress default.</div>
                                </div>
                            </div>
                            <div class="iar-field-row__control">
                                <input
                                        type="text"
                                        name="iar_smtp_mail_options[from_name]"
                                        value="<?php echo esc_attr( $from_name ); ?>"
                                        class="iar-input"
                                        placeholder="My Website"
                                >
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

        <!-- Test Email (outside form) -->
        <div class="iar-section">
            <div class="iar-section-heading">
                <div class="iar-section-heading__icon">
                    <span class="material-symbols-outlined">send</span>
                </div>
                <span class="iar-section-heading__label">Test Email</span>
            </div>
            <div class="iar-section--card">
                <div class="iar-action-panel__body">
                    <div class="iar-action-panel__info">
                        <h4>Send Test Email</h4>
                        <p>Sends a test message to your admin email address to verify SMTP settings.</p>
                    </div>
                    <a href="<?php echo esc_url( $test_nonce_url ); ?>" class="iar-btn-action">
                        <span class="material-symbols-outlined">send</span>
                        Send Test
                    </a>
                </div>
            </div>
        </div>

    </div>
    <?php
}
