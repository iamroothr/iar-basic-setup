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
	if ( ! isset( $_POST['iar_duplicate_menu_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['iar_duplicate_menu_nonce'] ) ), 'iar_duplicate_menu' ) ) {
		wp_die( __( 'Security check failed.', 'iar-basic-setup' ) );
	}

	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( __( 'You do not have permission to duplicate menus.', 'iar-basic-setup' ) );
	}

	$menu_id = isset( $_POST['source_menu_id'] ) ? absint( $_POST['source_menu_id'] ) : 0;

	if ( ! $menu_id ) {
		wp_die( __( 'No menu to duplicate.', 'iar-basic-setup' ) );
	}

	$source_menu = wp_get_nav_menu_object( $menu_id );
	if ( ! $source_menu ) {
		wp_die( __( 'Menu not found.', 'iar-basic-setup' ) );
	}

	$new_menu_name = isset( $_POST['new_menu_name'] ) ? sanitize_text_field( wp_unslash( $_POST['new_menu_name'] ) ) : '';
	if ( empty( $new_menu_name ) ) {
		$new_menu_name = sprintf( '%s (Copy)', $source_menu->name );
	}

	$new_menu_id = wp_create_nav_menu( $new_menu_name );

	if ( is_wp_error( $new_menu_id ) ) {
		wp_die( $new_menu_id->get_error_message() );
	}

	iar_duplicate_menu_clone_items( $menu_id, $new_menu_id );

	$redirect_url = add_query_arg(
		[ 'iar_menu_duplicated' => 1 ],
		admin_url( 'admin.php?page=iar-duplicate-menu' )
	);

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

		$new_item_id   = $id_map[ $item->ID ];
		$new_parent_id = $id_map[ $item->menu_item_parent ];

		update_post_meta( $new_item_id, '_menu_item_menu_item_parent', $new_parent_id );
	}
}
