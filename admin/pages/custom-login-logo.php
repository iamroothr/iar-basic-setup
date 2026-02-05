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
	<div class="wrap iar-admin-wrap">
		<h1>Custom Login Logo <small style="font-size: .7rem;">Replace the login page logo:</small></h1>

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_custom_login_logo_group' ); ?>

			<div class="iar-modules-grid">
				<div class="iar-card">
					<div class="iar-card-header">
						<h3>Logo Image</h3>
					</div>

					<div id="iar-logo-preview" style="margin-bottom: 12px;">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo esc_url( $image_url ); ?>" alt="Login logo preview" style="max-width: 320px; max-height: 120px;">
						<?php else : ?>
							<p class="description">No custom logo selected. The default WordPress logo will be shown.</p>
						<?php endif; ?>
					</div>

					<input
						type="hidden"
						id="iar-logo-attachment-id"
						name="iar_custom_login_logo_options[attachment_id]"
						value="<?php echo esc_attr( $attachment_id ); ?>"
					>

					<p>
						<button type="button" class="button" id="iar-logo-select">Select Image</button>
						<button type="button" class="button" id="iar-logo-remove" <?php echo $attachment_id ? '' : 'style="display:none;"'; ?>>Remove Image</button>
					</p>
				</div>
			</div>

			<?php submit_button( 'Save Settings' ); ?>
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
				$preview.html('<img src="' + url + '" alt="Login logo preview" style="max-width: 320px; max-height: 120px;">');
				$remove.show();
			});

			frame.open();
		});

		$remove.on('click', function(e) {
			e.preventDefault();
			$input.val('');
			$preview.html('<p class="description">No custom logo selected. The default WordPress logo will be shown.</p>');
			$(this).hide();
		});
	})(jQuery);
	</script>
	<?php
}
