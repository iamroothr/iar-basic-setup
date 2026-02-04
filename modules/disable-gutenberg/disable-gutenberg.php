<?php
/**
 * Module: Disable Gutenberg
 * Description: Disables the block editor and restores the classic editor.
 */

if (!defined('ABSPATH')) exit;

// Disable Gutenberg for posts
add_filter('use_block_editor_for_post', '__return_false', 10);

// Disable Gutenberg for post types
add_filter('use_block_editor_for_post_type', '__return_false', 10);
