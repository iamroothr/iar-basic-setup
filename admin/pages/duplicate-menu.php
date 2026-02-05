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
	<div class="wrap iar-admin-wrap">
		<h1>Duplicate Menu <small style="font-size: .7rem;">Clone an existing navigation menu:</small></h1>

		<?php
		if ( isset( $_GET['iar_menu_duplicated'] ) && 1 === absint( $_GET['iar_menu_duplicated'] ) ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php esc_html_e( 'Menu duplicated successfully.', 'iar-basic-setup' ); ?></p>
			</div>
			<?php
		}
		?>

		<?php if ( empty( $menus ) ) : ?>
			<div class="notice notice-warning">
				<p><?php esc_html_e( 'No menus found. Create a menu first.', 'iar-basic-setup' ); ?></p>
			</div>
		<?php else : ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<input type="hidden" name="action" value="iar_duplicate_menu">
				<?php wp_nonce_field( 'iar_duplicate_menu', 'iar_duplicate_menu_nonce' ); ?>

				<div class="iar-modules-grid">
					<div class="iar-card">
						<div class="iar-card-header">
							<h3>Source Menu</h3>
						</div>

						<p class="iar-card-desc">
							Select the menu you want to duplicate.
						</p>

						<p>
							<select name="source_menu_id" class="regular-text" required>
								<?php foreach ( $menus as $menu ) : ?>
									<option value="<?php echo esc_attr( $menu->term_id ); ?>">
										<?php echo esc_html( $menu->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</p>
					</div>

					<div class="iar-card">
						<div class="iar-card-header">
							<h3>New Menu Name</h3>
						</div>

						<p class="iar-card-desc">
							Leave blank to use "Original Name (Copy)".
						</p>

						<p>
							<input
								type="text"
								name="new_menu_name"
								placeholder="My Menu (Copy)"
								class="regular-text"
							>
						</p>
					</div>
				</div>

				<?php submit_button( 'Duplicate Menu' ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}
