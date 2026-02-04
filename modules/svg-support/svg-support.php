<?php
/**
 * Module: SVG Support
 * Description: Enables SVG uploads with sanitization.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Allow SVG uploads for users who can upload files.
 */
add_filter( 'upload_mimes', function ( $mimes ) {
    if ( current_user_can( 'upload_files' ) ) {
        $mimes['svg'] = 'image/svg+xml';
    }
    return $mimes;
} );

/**
 * Sanitize SVG uploads by removing potentially dangerous elements and attributes.
 */
add_filter( 'wp_handle_upload_prefilter', function ( $file ) {
    if ( 'image/svg+xml' !== $file['type'] ) {
        return $file;
    }

    $svg_content = file_get_contents( $file['tmp_name'] );

    if ( false === $svg_content ) {
        $file['error'] = __( 'Could not read SVG file.', 'iar-basic-setup' );
        return $file;
    }

    // Check for potentially dangerous elements
    $dangerous_patterns = [
        '/<script[\s\S]*?>/i',
        '/on\w+\s*=/i',
        '/<foreignObject[\s\S]*?>/i',
        '/javascript:/i',
        '/data:/i',
        '/<embed[\s\S]*?>/i',
        '/<object[\s\S]*?>/i',
        '/<iframe[\s\S]*?>/i',
    ];

    foreach ( $dangerous_patterns as $pattern ) {
        if ( preg_match( $pattern, $svg_content ) ) {
            $file['error'] = __( 'SVG file contains potentially unsafe content.', 'iar-basic-setup' );
            return $file;
        }
    }

    return $file;
} );
