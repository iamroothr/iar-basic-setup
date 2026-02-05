<?php
/**
 * Module: Disable Author Archives
 * Description: Prevents user enumeration through author archive pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirect author archive requests to homepage.
 */
function iar_disable_author_archives_redirect(): void {
	if ( is_author() ) {
		wp_redirect( home_url(), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'iar_disable_author_archives_redirect' );

/**
 * Remove author rewrite rules.
 *
 * @param array $rules Author rewrite rules.
 * @return array Empty array to disable author archives.
 */
function iar_disable_author_archives_rewrite_rules( array $rules ): array {
	return [];
}
add_filter( 'author_rewrite_rules', 'iar_disable_author_archives_rewrite_rules' );
