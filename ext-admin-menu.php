<?php
/**
 * Plugin Name:     External Admin Menu
 * Plugin URI:      https://github.com/ndevrinc/ext-admin-menu
 * Description:     Displays the top admin menu on non WordPress generated pages
 * Author:          Fusion Engineering
 * Author URI:      http://fusion.net
 * Text Domain:     ext-admin-menu
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Ext_Admin_Menu
 */


function ext_admin_menu_endpoint() {

	add_rewrite_tag( '%ext_admin_menu%', '([^&]+)' );
	add_rewrite_rule( 'ext_admin_menu/html/?', "index.php?ext_admin_menu=true", 'top' );

}
add_action( 'init', 'ext_admin_menu_endpoint' );

function ext_admin_menu_data() {

	global $wp_query;

	$show = $wp_query->get( 'ext_admin_menu' );

	if ( ! $show ) {
		return;
	}

	ob_start();
	wp_admin_bar_render();
	$header = ob_get_clean();

	$content_type = 'application/json';
	$json_response = json_encode( array( 'html' => $header ) );

	$callback = 'jsonmenu';

// JSONP callback support
	if ( null !== $callback ) {
		// JSONP requires content type of application/javascript
		$content_type = 'application/javascript';

		// JSONP uses callback for response
		$json_response = esc_js( $callback ) . '(' . $json_response . ')';
	}

	@header( 'Content-Type: ' . $content_type . '; charset=' . get_option( 'blog_charset' ) );
	echo $json_response;

	exit();
}
add_action( 'template_redirect', 'ext_admin_menu_data' );
