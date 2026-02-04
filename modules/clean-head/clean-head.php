<?php
/**
 * Module: Clean Head
 * Description: Removes unnecessary meta tags from the <head>.
 */

if (!defined('ABSPATH')) exit;

add_action('init', function() {
    remove_action('wp_head', 'rsd_link'); // XML-RPC Client
    remove_action('wp_head', 'wlwmanifest_link'); // Windows Live Writer
    remove_action('wp_head', 'wp_generator'); // WordPress Version
    remove_action('wp_head', 'start_post_rel_link');
    remove_action('wp_head', 'index_rel_link');
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head');
    remove_action('wp_head', 'wp_shortlink_wp_head');
});
