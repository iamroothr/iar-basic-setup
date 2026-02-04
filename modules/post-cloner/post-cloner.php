<?php
/**
 * Module: Post Cloner
 * Description: Adds a Clone action to duplicate posts, pages, and CPTs.
 */

if (!defined('ABSPATH')) exit;

/**
 * Check if cloning is enabled for a post type
 */
function iar_post_cloner_is_enabled($post_type) {
    $options = get_option('iar_post_cloner_options', []);
    return !empty($options['post_types'][$post_type]);
}

/**
 * Add Clone link to post row actions (posts and non-hierarchical CPT)
 */
add_filter('post_row_actions', 'iar_post_cloner_row_action', 10, 2);

/**
 * Add Clone link to page row actions (pages and hierarchical CPT)
 */
add_filter('page_row_actions', 'iar_post_cloner_row_action', 10, 2);

/**
 * Add Clone link to row actions
 */
function iar_post_cloner_row_action($actions, $post) {
    if (!iar_post_cloner_is_enabled($post->post_type)) {
        return $actions;
    }

    if (!current_user_can('edit_post', $post->ID)) {
        return $actions;
    }

    $post_type_object = get_post_type_object($post->post_type);
    if (!$post_type_object || !current_user_can($post_type_object->cap->create_posts)) {
        return $actions;
    }

    $clone_url = wp_nonce_url(
        admin_url('admin-post.php?action=iar_clone_post&post_id=' . $post->ID),
        'iar_clone_post_' . $post->ID
    );

    $actions['clone'] = sprintf(
        '<a href="%s" title="%s">%s</a>',
        esc_url($clone_url),
        esc_attr__('Clone this item', 'iar-basic-setup'),
        __('Clone', 'iar-basic-setup')
    );

    return $actions;
}

/**
 * Handle clone action
 */
add_action('admin_post_iar_clone_post', 'iar_post_cloner_handle_clone');

function iar_post_cloner_handle_clone() {
    $post_id = isset($_GET['post_id']) ? absint($_GET['post_id']) : 0;

    if (!$post_id) {
        wp_die(__('No post to clone.', 'iar-basic-setup'));
    }

    if (!wp_verify_nonce($_GET['_wpnonce'], 'iar_clone_post_' . $post_id)) {
        wp_die(__('Security check failed.', 'iar-basic-setup'));
    }

    $post = get_post($post_id);
    if (!$post) {
        wp_die(__('Post not found.', 'iar-basic-setup'));
    }

    if (!iar_post_cloner_is_enabled($post->post_type)) {
        wp_die(__('Cloning is not enabled for this post type.', 'iar-basic-setup'));
    }

    if (!current_user_can('edit_post', $post_id)) {
        wp_die(__('You do not have permission to clone this post.', 'iar-basic-setup'));
    }

    $post_type_object = get_post_type_object($post->post_type);
    if (!$post_type_object || !current_user_can($post_type_object->cap->create_posts)) {
        wp_die(__('You do not have permission to create posts of this type.', 'iar-basic-setup'));
    }

    $new_post_id = iar_post_cloner_clone_post($post);

    if (is_wp_error($new_post_id)) {
        wp_die($new_post_id->get_error_message());
    }

    wp_redirect(admin_url('post.php?action=edit&post=' . $new_post_id));
    exit;
}

/**
 * Clone a post
 */
function iar_post_cloner_clone_post($post) {
    $new_post_args = [
        'post_title'     => sprintf(__('Copy of %s', 'iar-basic-setup'), $post->post_title),
        'post_content'   => $post->post_content,
        'post_excerpt'   => $post->post_excerpt,
        'post_status'    => 'draft',
        'post_type'      => $post->post_type,
        'post_author'    => get_current_user_id(),
        'post_parent'    => $post->post_parent,
        'menu_order'     => $post->menu_order,
        'post_password'  => $post->post_password,
        'comment_status' => $post->comment_status,
        'ping_status'    => $post->ping_status,
    ];

    $new_post_id = wp_insert_post($new_post_args, true);

    if (is_wp_error($new_post_id)) {
        return $new_post_id;
    }

    // Clone taxonomies
    iar_post_cloner_clone_taxonomies($post->ID, $new_post_id, $post->post_type);

    // Clone meta data
    iar_post_cloner_clone_meta($post->ID, $new_post_id);

    return $new_post_id;
}

/**
 * Clone taxonomies from original post to new post
 */
function iar_post_cloner_clone_taxonomies($original_id, $new_id, $post_type) {
    $taxonomies = get_object_taxonomies($post_type);

    foreach ($taxonomies as $taxonomy) {
        $terms = wp_get_object_terms($original_id, $taxonomy, ['fields' => 'ids']);
        if (!is_wp_error($terms) && !empty($terms)) {
            wp_set_object_terms($new_id, $terms, $taxonomy);
        }
    }
}

/**
 * Clone meta data from original post to new post
 */
function iar_post_cloner_clone_meta($original_id, $new_id) {
    $meta_data = get_post_meta($original_id);

    if (empty($meta_data)) {
        return;
    }

    foreach ($meta_data as $meta_key => $meta_values) {
        // Skip internal WordPress meta
        if (in_array($meta_key, ['_edit_lock', '_edit_last'], true)) {
            continue;
        }

        foreach ($meta_values as $meta_value) {
            add_post_meta($new_id, $meta_key, maybe_unserialize($meta_value));
        }
    }
}
