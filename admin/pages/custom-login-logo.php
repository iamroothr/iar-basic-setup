<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Custom Login Logo submenu (only when module is enabled)
 */
add_action( 'admin_menu', function () {
	$options = get_option( 'iar_basic_setup_options', [] );

	if ( ! empty( $options['custom-login-logo'] ) ) {
		add_submenu_page(
			'iar-basic-setup-settings',
			'Login Logo',
			'Login Logo',
			'manage_options',
			'iar-custom-login-logo',
			'iar_custom_login_logo_render_page'
		);
	}
} );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
	register_setting( 'iar_custom_login_logo_group', 'iar_custom_login_logo_options' );
} );

/**
 * Enqueue media uploader on our settings page.
 *
 * @param string $hook Current admin page hook suffix.
 */
function iar_custom_login_logo_enqueue_media( string $hook ): void {
	if ( 'iar-basic-setup_page_iar-custom-login-logo' !== $hook ) {
		return;
	}

	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'iar_custom_login_logo_enqueue_media' );

/**
 * Render Custom Login Logo settings page
 */
function iar_custom_login_logo_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options       = get_option( 'iar_custom_login_logo_options', [] );
	$attachment_id = ! empty( $options['attachment_id'] ) ? (int) $options['attachment_id'] : 0;
	$image_url     = $attachment_id ? wp_get_attachment_image_url( $attachment_id, 'medium' ) : '';
	?>

	<div class="wrap iar-wrap">

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_custom_login_logo_group' ); ?>

			<!-- Page Header -->
			<div class="iar-page-header">
				<div class="iar-page-header__text">
					<h2 class="iar-page-title">Custom Login Logo</h2>
					<p class="iar-page-subtitle">Replaces the WordPress logo on the login page with a custom image.</p>
				</div>
				<span class="iar-system-badge">System Active</span>
			</div>

			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">photo_camera</span>
					</div>
					<span class="iar-section-heading__label">Logo Image</span>
				</div>
				<div class="iar-section--card">
				<div class="iar-logo-panel__body">
					<div id="iar-logo-preview" class="iar-logo-preview">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="Login logo preview">
						<?php else : ?>
							<div class="iar-logo-preview__empty">
								<span class="material-symbols-outlined">image</span>
								<span>No logo selected &mdash; WordPress default will be shown.</span>
							</div>
						<?php endif; ?>
					</div>

					<input
						type="hidden"
						id="iar-logo-attachment-id"
						name="iar_custom_login_logo_options[attachment_id]"
						value="<?php echo esc_attr( $attachment_id ); ?>"
					>

					<div class="iar-logo-actions">
						<button type="button" class="iar-btn-media iar-btn-media--primary" id="iar-logo-select">
							<span class="material-symbols-outlined">upload</span>
							Select Image
						</button>
						<button
							type="button"
							class="iar-btn-media iar-btn-media--ghost"
							id="iar-logo-remove"
							<?php echo $attachment_id ? '' : 'style="display:none;"'; ?>
						>
							<span class="material-symbols-outlined">delete</span>
							Remove
						</button>
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

	<script>
	(function($) {
		var frame;
		var $preview = $('#iar-logo-preview');
		var $input   = $('#iar-logo-attachment-id');
		var $remove  = $('#iar-logo-remove');

		$('#iar-logo-select').on('click', function(e) {
			e.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: 'Select Login Logo',
				button: { text: 'Use as Logo' },
				multiple: false,
				library: { type: 'image' }
			});

			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				var url = attachment.sizes && attachment.sizes.medium
					? attachment.sizes.medium.url
					: attachment.url;

				$input.val(attachment.id);
				$preview.html('<img src="' + url + '" alt="Login logo preview">');
				$remove.show();
			});

			frame.open();
		});

		$remove.on('click', function(e) {
			e.preventDefault();
			$input.val('');
			$preview.html(
				'<div class="iar-logo-preview__empty">' +
					'<span class="material-symbols-outlined">image</span>' +
					'<span>No logo selected \u2014 WordPress default will be shown.</span>' +
				'</div>'
			);
			$(this).hide();
		});
	})(jQuery);
	</script>
	<?php
}
