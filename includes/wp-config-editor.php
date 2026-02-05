<?php
/**
 * Programmatic wp-config.php debug constant management.
 *
 * @package IAR_Basic_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Write WP_DEBUG, WP_DEBUG_LOG, and WP_DEBUG_DISPLAY constants to wp-config.php.
 *
 * @param bool $display Whether to display errors in the browser.
 * @param bool $log     Whether to log errors to wp-content/debug.log.
 * @return bool True on success, false on failure.
 */
function iar_wp_config_set_debug( bool $display, bool $log ): bool {
	$config_path = ABSPATH . 'wp-config.php';

	if ( ! file_exists( $config_path ) || ! is_writable( $config_path ) ) {
		return false;
	}

	$contents = file_get_contents( $config_path );
	if ( false === $contents ) {
		return false;
	}

	// Remove any existing IAR debug block.
	$contents = preg_replace(
		'/\s*\/\* IAR_DEBUG_START \*\/.*?\/\* IAR_DEBUG_END \*\//s',
		'',
		$contents
	);

	// Remove standalone WP_DEBUG, WP_DEBUG_LOG, WP_DEBUG_DISPLAY define lines.
	$contents = preg_replace(
		'/^.*define\s*\(\s*[\'"]WP_DEBUG[\'"]\s*,.*\);\s*\R?/m',
		'',
		$contents
	);
	$contents = preg_replace(
		'/^.*define\s*\(\s*[\'"]WP_DEBUG_LOG[\'"]\s*,.*\);\s*\R?/m',
		'',
		$contents
	);
	$contents = preg_replace(
		'/^.*define\s*\(\s*[\'"]WP_DEBUG_DISPLAY[\'"]\s*,.*\);\s*\R?/m',
		'',
		$contents
	);

	// Build our marker block.
	$display_val = $display ? 'true' : 'false';
	$log_val     = $log ? 'true' : 'false';

	$block = "/* IAR_DEBUG_START */\n"
		. "define( 'WP_DEBUG', true );\n"
		. "define( 'WP_DEBUG_LOG', {$log_val} );\n"
		. "define( 'WP_DEBUG_DISPLAY', {$display_val} );\n"
		. "/* IAR_DEBUG_END */\n";

	// Insert before the "stop editing" marker.
	$marker = "/* That's all, stop editing!";
	$pos    = strpos( $contents, $marker );

	if ( false === $pos ) {
		return false;
	}

	$contents = substr( $contents, 0, $pos ) . $block . "\n" . substr( $contents, $pos );

	return iar_wp_config_write( $config_path, $contents );
}

/**
 * Remove IAR debug constants from wp-config.php and restore WP_DEBUG to false.
 *
 * @return bool True on success, false on failure.
 */
function iar_wp_config_remove_debug(): bool {
	$config_path = ABSPATH . 'wp-config.php';

	if ( ! file_exists( $config_path ) || ! is_writable( $config_path ) ) {
		return false;
	}

	$contents = file_get_contents( $config_path );
	if ( false === $contents ) {
		return false;
	}

	// Remove our marker block.
	$contents = preg_replace(
		'/\s*\/\* IAR_DEBUG_START \*\/.*?\/\* IAR_DEBUG_END \*\//s',
		'',
		$contents
	);

	// If no standalone WP_DEBUG define remains, restore it to false.
	if ( ! preg_match( '/define\s*\(\s*[\'"]WP_DEBUG[\'"]/', $contents ) ) {
		$marker = "/* That's all, stop editing!";
		$pos    = strpos( $contents, $marker );

		if ( false === $pos ) {
			return false;
		}

		$restore  = "define( 'WP_DEBUG', false );\n\n";
		$contents = substr( $contents, 0, $pos ) . $restore . substr( $contents, $pos );
	}

	return iar_wp_config_write( $config_path, $contents );
}

/**
 * Atomically write contents to wp-config.php using file locking.
 *
 * @param string $path     Full path to wp-config.php.
 * @param string $contents File contents to write.
 * @return bool True on success, false on failure.
 */
function iar_wp_config_write( string $path, string $contents ): bool {
	$handle = fopen( $path, 'c' );
	if ( ! $handle ) {
		return false;
	}

	if ( ! flock( $handle, LOCK_EX ) ) {
		fclose( $handle );
		return false;
	}

	ftruncate( $handle, 0 );
	rewind( $handle );
	fwrite( $handle, $contents );
	fflush( $handle );
	flock( $handle, LOCK_UN );
	fclose( $handle );

	return true;
}
