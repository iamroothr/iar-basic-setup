<?php
/**
 * Custom Login Logo module.
 *
 * Replaces the WordPress logo on the login page with a custom image.
 *
 * @package IAR_Basic_Setup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inject inline CSS to replace the login page logo.
 */
function iar_custom_login_logo_css(): void {
	$options       = get_option( 'iar_custom_login_logo_options', [] );
	$attachment_id = ! empty( $options['attachment_id'] ) ? (int) $options['attachment_id'] : 0;

	if ( ! $attachment_id ) {
		return;
	}

	$image_url = wp_get_attachment_image_url( $attachment_id, 'medium' );

	if ( ! $image_url ) {
		return;
	}

	$metadata = wp_get_attachment_metadata( $attachment_id );
	$height   = 80;

	if ( ! empty( $metadata['width'] ) && ! empty( $metadata['height'] ) ) {
		$ratio  = $metadata['height'] / $metadata['width'];
		$height = min( (int) round( 320 * $ratio ), 120 );
	}

	?>
	<style>
		.login h1 a {
			background-image: url('<?php echo esc_url( $image_url ); ?>');
			background-size: contain;
			background-position: center;
			background-repeat: no-repeat;
			width: 320px;
			height: <?php echo esc_attr( $height ); ?>px;
		}
	</style>
	<?php
}
add_action( 'login_head', 'iar_custom_login_logo_css' );

/**
 * Change the login logo URL to the site homepage.
 *
 * @return string Site home URL.
 */
function iar_custom_login_logo_url(): string {
	return home_url();
}
add_filter( 'login_headerurl', 'iar_custom_login_logo_url' );

/**
 * Change the login logo hover text to the site name.
 *
 * @return string Site name.
 */
function iar_custom_login_logo_text(): string {
	return get_bloginfo( 'name' );
}
add_filter( 'login_headertext', 'iar_custom_login_logo_text' );
