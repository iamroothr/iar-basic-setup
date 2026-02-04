<?php
/**
 * Module: SVG Support
 * Description: Enables SVG uploads.
 */

if (!defined('ABSPATH')) exit;

add_filter('upload_mimes', function($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
});
