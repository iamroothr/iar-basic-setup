<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Post Cloner submenu (only when module is enabled)
 */
add_action( 'admin_menu', function () {
	$options = get_option( 'iar_basic_setup_options', [] );

	if ( ! empty( $options['post-cloner'] ) ) {
		add_submenu_page(
			'iar-basic-setup-settings',
			'Post Cloner',
			'Post Cloner',
			'manage_options',
			'iar-post-cloner',
			'iar_post_cloner_render_page'
		);
	}
} );

/**
 * Register settings
 */
add_action( 'admin_init', function () {
	register_setting( 'iar_post_cloner_group', 'iar_post_cloner_options' );
} );

/**
 * Render Post Cloner page
 */
function iar_post_cloner_render_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options    = get_option( 'iar_post_cloner_options', [] );
	$post_types = get_post_types( [ 'show_ui' => true ], 'objects' );
	unset( $post_types['attachment'] );

	$always_open = ! empty( $options['always_open'] );
	?>

	<div class="wrap iar-wrap">

		<form method="post" action="options.php">
			<?php settings_fields( 'iar_post_cloner_group' ); ?>

			<!-- Page Header -->
			<div class="iar-page-header">
				<div class="iar-page-header__text">
					<h2 class="iar-page-title">Post Cloner</h2>
					<p class="iar-page-subtitle">Adds a Clone action to duplicate posts, pages, and CPTs.</p>
				</div>
				<span class="iar-system-badge">System Active</span>
			</div>

			<!-- Behaviour -->
			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">tune</span>
					</div>
					<span class="iar-section-heading__label">Behaviour</span>
				</div>
				<div class="iar-module-list">
					<div class="iar-module-row">
						<div class="iar-module-row__left">
							<span class="material-symbols-outlined iar-module-icon">open_in_new</span>
							<div class="iar-module-info">
								<h4>Open Cloned Post Immediately</h4>
								<p>After cloning, open the new draft in the editor instead of returning to the post list.</p>
							</div>
						</div>
						<label class="iar-toggle">
							<input
								type="checkbox"
								name="iar_post_cloner_options[always_open]"
								value="1"
								<?php checked( $always_open ); ?>
							>
							<div class="iar-toggle-track"><div class="iar-toggle-dot"></div></div>
						</label>
					</div>
				</div>
			</div>

			<!-- Enabled Post Types -->
			<div class="iar-section">
				<div class="iar-section-heading">
					<div class="iar-section-heading__icon">
						<span class="material-symbols-outlined">category</span>
					</div>
					<span class="iar-section-heading__label">Enabled Post Types</span>
				</div>
				<div class="iar-module-list">
					<?php foreach ( $post_types as $post_type ) :
						$enabled = ! empty( $options['post_types'][ $post_type->name ] );
						?>
						<div class="iar-module-row">
							<div class="iar-module-row__left">
								<span class="material-symbols-outlined iar-module-icon">article</span>
								<div class="iar-module-info">
									<h4><?php echo esc_html( $post_type->labels->singular_name ); ?></h4>
									<p><?php echo esc_html( $post_type->labels->name ); ?> &mdash; <code><?php echo esc_html( $post_type->name ); ?></code></p>
								</div>
							</div>
							<label class="iar-toggle">
								<input
									type="checkbox"
									name="iar_post_cloner_options[post_types][<?php echo esc_attr( $post_type->name ); ?>]"
									value="1"
									<?php checked( $enabled ); ?>
								>
								<div class="iar-toggle-track"><div class="iar-toggle-dot"></div></div>
							</label>
						</div>
					<?php endforeach; ?>
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
