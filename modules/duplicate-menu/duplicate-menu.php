<?php
/**
 * Module: Duplicate Menu
 * Description: Adds a Duplicate action to clone nav menus.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle menu duplication request.
 */
function iar_duplicate_menu_handler(): void {
	$menu_id = isset( $_GET['menu_id'] ) ? absint( $_GET['menu_id'] ) : 0;

	if ( ! $menu_id ) {
		wp_die( __( 'No menu to duplicate.', 'iar-basic-setup' ) );
	}

	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'iar_duplicate_menu_' . $menu_id ) ) {
		wp_die( __( 'Security check failed.', 'iar-basic-setup' ) );
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( __( 'You do not have permission to duplicate menus.', 'iar-basic-setup' ) );
	}

	$source_menu = wp_get_nav_menu_object( $menu_id );
	if ( ! $source_menu ) {
		wp_die( __( 'Menu not found.', 'iar-basic-setup' ) );
	}

	$new_menu_name = sprintf( '%s (Copy)', $source_menu->name );
	$new_menu_id   = wp_create_nav_menu( $new_menu_name );

	if ( is_wp_error( $new_menu_id ) ) {
		wp_die( $new_menu_id->get_error_message() );
	}

	iar_duplicate_menu_clone_items( $menu_id, $new_menu_id );

	$redirect_url = add_query_arg( [
		'menu'                   => $new_menu_id,
		'iar_menu_duplicated'    => 1,
	], admin_url( 'nav-menus.php' ) );

	wp_redirect( $redirect_url );
	exit;
}
add_action( 'admin_post_iar_duplicate_menu', 'iar_duplicate_menu_handler' );

/**
 * Clone menu items from source menu to new menu.
 *
 * @param int $source_menu_id Source menu ID.
 * @param int $new_menu_id    New menu ID.
 */
function iar_duplicate_menu_clone_items( int $source_menu_id, int $new_menu_id ): void {
	$menu_items = wp_get_nav_menu_items( $source_menu_id, [ 'post_status' => 'any' ] );

	if ( empty( $menu_items ) ) {
		return;
	}

	$id_map = [];

	foreach ( $menu_items as $item ) {
		$new_item_data = [
			'menu-item-object-id'   => $item->object_id,
			'menu-item-object'      => $item->object,
			'menu-item-parent-id'   => 0,
			'menu-item-position'    => $item->menu_order,
			'menu-item-type'        => $item->type,
			'menu-item-title'       => $item->title,
			'menu-item-url'         => $item->url,
			'menu-item-description' => $item->description,
			'menu-item-attr-title'  => $item->attr_title,
			'menu-item-target'      => $item->target,
			'menu-item-classes'     => implode( ' ', (array) $item->classes ),
			'menu-item-xfn'         => $item->xfn,
			'menu-item-status'      => 'publish',
		];

		$new_item_id = wp_update_nav_menu_item( $new_menu_id, 0, $new_item_data );

		if ( ! is_wp_error( $new_item_id ) ) {
			$id_map[ $item->ID ] = $new_item_id;
		}
	}

	foreach ( $menu_items as $item ) {
		if ( empty( $item->menu_item_parent ) ) {
			continue;
		}

		if ( ! isset( $id_map[ $item->ID ] ) || ! isset( $id_map[ $item->menu_item_parent ] ) ) {
			continue;
		}

		$new_item_id     = $id_map[ $item->ID ];
		$new_parent_id   = $id_map[ $item->menu_item_parent ];

		update_post_meta( $new_item_id, '_menu_item_menu_item_parent', $new_parent_id );
	}
}

/**
 * Add duplicate button via JavaScript.
 */
function iar_duplicate_menu_add_button(): void {
	$menu_id = isset( $_GET['menu'] ) ? absint( $_GET['menu'] ) : 0;

	if ( ! $menu_id ) {
		return;
	}

	$duplicate_url = wp_nonce_url(
		admin_url( 'admin-post.php?action=iar_duplicate_menu&menu_id=' . $menu_id ),
		'iar_duplicate_menu_' . $menu_id
	);
	?>
	<script>
	(function() {
		var menuSelector = document.getElementById('menu-name-label');
		if (!menuSelector) return;

		var parentDiv = menuSelector.closest('.menu-name-label');
		if (!parentDiv) return;

		var duplicateBtn = document.createElement('a');
		duplicateBtn.href = '<?php echo esc_js( $duplicate_url ); ?>';
		duplicateBtn.className = 'button button-secondary';
		duplicateBtn.style.marginLeft = '10px';
		duplicateBtn.textContent = '<?php echo esc_js( __( 'Duplicate This Menu', 'iar-basic-setup' ) ); ?>';

		var inputField = parentDiv.querySelector('input[name="menu-name"]');
		if (inputField) {
			inputField.parentNode.insertBefore(duplicateBtn, inputField.nextSibling);
		}
	})();
	</script>
	<?php
}
add_action( 'admin_footer-nav-menus.php', 'iar_duplicate_menu_add_button' );

/**
 * Show admin notice after successful duplication.
 */
function iar_duplicate_menu_admin_notice(): void {
	$screen = get_current_screen();
	if ( ! $screen || 'nav-menus' !== $screen->id ) {
		return;
	}

	if ( empty( $_GET['iar_menu_duplicated'] ) ) {
		return;
	}

	?>
	<div class="notice notice-success is-dismissible">
		<p><?php esc_html_e( 'Menu duplicated successfully.', 'iar-basic-setup' ); ?></p>
	</div>
	<?php
}
add_action( 'admin_notices', 'iar_duplicate_menu_admin_notice' );
