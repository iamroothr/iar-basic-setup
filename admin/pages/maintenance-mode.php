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
 * Enqueue media uploader on the maintenance mode settings page.
 */
add_action( 'admin_enqueue_scripts', function ( string $hook ) {
	if ( 'iar-basic-setup_page_iar-maintenance-mode' !== $hook ) {
		return;
	}

	wp_enqueue_media();
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

	$sanitized['background_image'] = ! empty( $input['background_image'] )
		? absint( $input['background_image'] )
		: 0;

	return $sanitized;
}

/**
 * Render settings page
 */
function iar_maintenance_mode_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options          = iar_maintenance_mode_get_options();
	$enabled          = $options['enabled'];
	$page_title       = $options['page_title'];
	$message          = $options['message'];
	$bg_image_id      = ! empty( $options['background_image'] ) ? absint( $options['background_image'] ) : 0;
	$bg_image_url     = $bg_image_id ? wp_get_attachment_image_url( $bg_image_id, 'large' ) : '';
	?>

	<div class="wrap iar-wrap">

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_maintenance_mode_group' ); ?>

			<!-- Page Header -->
			<div class="iar-page-header">
				<div class="iar-page-header__text">
					<h2 class="iar-page-title">Maintenance Mode</h2>
					<p class="iar-page-subtitle">Displays a maintenance page for non-admin visitors.</p>
				</div>
				<span class="iar-system-badge">System Active</span>
			</div>

			<!-- Status -->
			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">construction</span>
					</div>
					<span class="iar-section-heading__label">Status</span>
				</div>
				<div class="iar-section--card">
				<div class="iar-module-list">
					<div class="iar-module-row">
						<div class="iar-module-row__left">
							<span class="material-symbols-outlined iar-module-icon iar-module-icon--amber">construction</span>
							<div class="iar-module-info">
								<h4>Enable Maintenance Mode</h4>
								<p>Non-admin visitors will see the maintenance page.</p>
							</div>
						</div>
						<label class="iar-toggle">
							<input
								type="checkbox"
								name="iar_maintenance_mode_options[enabled]"
								value="1"
								<?php checked( $enabled ); ?>
							>
							<div class="iar-toggle-track"><div class="iar-toggle-dot"></div></div>
						</label>
					</div>
				</div>
				</div>
			</div>

			<!-- Page Content -->
			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">edit_note</span>
					</div>
					<span class="iar-section-heading__label">Page Content</span>
				</div>
				<div class="iar-section--card">
				<div class="iar-field-list">
					<div class="iar-field-row">
						<div class="iar-field-row__left">
							<div>
								<div class="iar-field-row__label">Page Title</div>
								<div class="iar-field-row__desc">Browser tab title on the maintenance page.</div>
							</div>
						</div>
						<div class="iar-field-row__control">
							<input
								type="text"
								name="iar_maintenance_mode_options[page_title]"
								value="<?php echo esc_attr( $page_title ); ?>"
								class="iar-input"
								placeholder="Under Maintenance"
							>
						</div>
					</div>
					<div class="iar-field-row">
						<div class="iar-field-row__left">
							<div>
								<div class="iar-field-row__label">Message</div>
								<div class="iar-field-row__desc">Shown to visitors. Basic HTML allowed.</div>
							</div>
						</div>
						<div class="iar-field-row__control">
							<textarea
								name="iar_maintenance_mode_options[message]"
								rows="4"
								class="iar-textarea"
								placeholder="We&rsquo;ll be back soon."
							><?php echo esc_textarea( $message ); ?></textarea>
						</div>
					</div>
				</div>
				</div>
			</div>

			<!-- Background Image -->
			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">image</span>
					</div>
					<span class="iar-section-heading__label">Background Image</span>
				</div>
				<div class="iar-section--card">
				<div class="iar-logo-panel__body">
					<div id="iar-maintenance-bg-preview" class="iar-logo-preview iar-logo-preview--bg">
						<?php if ( $bg_image_url ) : ?>
							<img src="<?php echo esc_url( $bg_image_url ); ?>" alt="Background image preview">
						<?php else : ?>
							<div class="iar-logo-preview__empty">
								<span class="material-symbols-outlined">image</span>
								<span>No image selected &mdash; background will be plain.</span>
							</div>
						<?php endif; ?>
					</div>

					<input
						type="hidden"
						id="iar-maintenance-bg-id"
						name="iar_maintenance_mode_options[background_image]"
						value="<?php echo esc_attr( $bg_image_id ); ?>"
					>

					<div class="iar-logo-actions">
						<button type="button" class="iar-btn-media iar-btn-media--primary" id="iar-maintenance-bg-select">
							<span class="material-symbols-outlined">upload</span>
							Select Image
						</button>
						<button
							type="button"
							class="iar-btn-media iar-btn-media--ghost"
							id="iar-maintenance-bg-remove"
							<?php echo $bg_image_id ? '' : 'style="display:none;"'; ?>
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
		var $preview = $('#iar-maintenance-bg-preview');
		var $input   = $('#iar-maintenance-bg-id');
		var $remove  = $('#iar-maintenance-bg-remove');

		$('#iar-maintenance-bg-select').on('click', function(e) {
			e.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: 'Select Background Image',
				button: { text: 'Use as Background' },
				multiple: false,
				library: { type: 'image' }
			});

			frame.on('select', function() {
				var attachment = frame.state().get('selection').first().toJSON();
				var url = attachment.sizes && attachment.sizes.large
					? attachment.sizes.large.url
					: attachment.url;

				$input.val(attachment.id);
				$preview.html('<img src="' + url + '" alt="Background image preview">');
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
					'<span>No image selected.</span>' +
				'</div>'
			);
			$(this).hide();
		});
	})(jQuery);
	</script>
	<?php
}
