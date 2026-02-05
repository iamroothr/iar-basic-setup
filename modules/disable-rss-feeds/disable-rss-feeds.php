<?php
/**
 * Module: Disable RSS Feeds
 * Description: Disables all RSS and Atom feed endpoints.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Remove feed links from document head.
 */
function iar_disable_rss_feeds_remove_links(): void {
	remove_action( 'wp_head', 'feed_links', 2 );
	remove_action( 'wp_head', 'feed_links_extra', 3 );
}
add_action( 'init', 'iar_disable_rss_feeds_remove_links' );

/**
 * Intercept feed requests and return 403.
 */
function iar_disable_rss_feeds_intercept(): void {
	wp_die(
		__( 'RSS feeds are disabled on this site.', 'iar-basic-setup' ),
		__( 'Feeds Disabled', 'iar-basic-setup' ),
		[ 'response' => 403 ]
	);
}
add_action( 'do_feed', 'iar_disable_rss_feeds_intercept', 1 );
add_action( 'do_feed_rdf', 'iar_disable_rss_feeds_intercept', 1 );
add_action( 'do_feed_rss', 'iar_disable_rss_feeds_intercept', 1 );
add_action( 'do_feed_rss2', 'iar_disable_rss_feeds_intercept', 1 );
add_action( 'do_feed_atom', 'iar_disable_rss_feeds_intercept', 1 );
