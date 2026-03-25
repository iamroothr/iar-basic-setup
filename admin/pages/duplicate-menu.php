<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add Duplicate Menu submenu (only when module is enabled)
 */
add_action( 'admin_menu', function () {
	$options = get_option( 'iar_basic_setup_options', [] );

	if ( ! empty( $options['duplicate-menu'] ) ) {
		add_submenu_page(
			'iar-basic-setup-settings',
			'Duplicate Menu',
			'Duplicate Menu',
			'edit_theme_options',
			'iar-duplicate-menu',
			'iar_duplicate_menu_render_page'
		);
	}
} );

/**
 * Render Duplicate Menu page
 */
function iar_duplicate_menu_render_page(): void {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		return;
	}

	$menus = wp_get_nav_menus();
	?>

	<div class="wrap iar-wrap">

		<!-- Page Header -->
		<div class="iar-page-header">
			<div class="iar-page-header__text">
				<h2 class="iar-page-title">Duplicate Menu</h2>
				<p class="iar-page-subtitle">Adds a Duplicate action to clone nav menus with all items.</p>
			</div>
			<span class="iar-system-badge">System Active</span>
		</div>

		<?php if ( isset( $_GET['iar_menu_duplicated'] ) && 1 === absint( $_GET['iar_menu_duplicated'] ) ) : ?>
			<div class="iar-notice iar-notice--success">
				<span class="material-symbols-outlined">check_circle</span>
				<div><?php esc_html_e( 'Menu duplicated successfully.', 'iar-basic-setup' ); ?></div>
			</div>
		<?php endif; ?>

		<?php if ( empty( $menus ) ) : ?>
			<div class="iar-notice iar-notice--warning">
				<span class="material-symbols-outlined">warning</span>
				<div><?php esc_html_e( 'No menus found. Create a menu first.', 'iar-basic-setup' ); ?></div>
			</div>
		<?php else : ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="iar_duplicate_menu">
				<?php wp_nonce_field( 'iar_duplicate_menu', 'iar_duplicate_menu_nonce' ); ?>

				<div class="iar-section">
					<div class="iar-section-heading">
						<div class="iar-section-heading__icon">
							<span class="material-symbols-outlined">menu_book</span>
						</div>
						<span class="iar-section-heading__label">Clone Menu</span>
					</div>
					<div class="iar-section--card">
					<div class="iar-field-list">
						<div class="iar-field-row">
							<div class="iar-field-row__left">
								<span class="material-symbols-outlined iar-module-icon iar-module-icon--indigo">list</span>
								<div>
									<div class="iar-field-row__label">Source Menu</div>
									<div class="iar-field-row__desc">Select the menu you want to duplicate.</div>
								</div>
							</div>
							<div class="iar-field-row__control">
								<select name="source_menu_id" class="iar-select" required>
									<?php foreach ( $menus as $menu ) : ?>
										<option value="<?php echo esc_attr( $menu->term_id ); ?>">
											<?php echo esc_html( $menu->name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
						<div class="iar-field-row">
							<div class="iar-field-row__left">
								<span class="material-symbols-outlined iar-module-icon iar-module-icon--sky">title</span>
								<div>
									<div class="iar-field-row__label">New Menu Name</div>
									<div class="iar-field-row__desc">Leave blank to use &ldquo;Original Name (Copy)&rdquo;.</div>
								</div>
							</div>
							<div class="iar-field-row__control">
								<input
									type="text"
									name="new_menu_name"
									placeholder="My Menu (Copy)"
									class="iar-input"
								>
							</div>
						</div>
					</div>
					</div>
				</div>

				<div class="iar-save-bar">
					<div></div>
					<div class="iar-save-bar__actions">
						<input type="submit" class="iar-btn-save" value="<?php esc_attr_e( 'Duplicate Menu', 'iar-basic-setup' ); ?>">
					</div>
				</div>

			</form>

		<?php endif; ?>
	</div>
	<?php
}
