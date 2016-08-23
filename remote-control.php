<?php

/**
 * Plugin Name:     Remote Control
 * Plugin URI:      https://github.com/ndevrinc/remote-control
 * Description:     Displays the top admin menu on non WordPress generated pages
 * Author:          Fusion Engineering
 * Author URI:      http://fusion.net
 * Text Domain:     remote-control
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Remote_Control
 */
class Remote_Control {

	private static $instance;

	/**
	 * Prevent the creation of a new instance of the "SINGLETON" using the
	 * 'new' operator from outside of this class.
	 **/
	protected function __construct() {
	}

	/**
	 * Prevent cloning the instance of the "SINGLETON" instance.
	 * @return void
	 **/
	private function __clone() {
	}

	/**
	 * Prevent the unserialization of the "SINGLETON" instance.
	 * @return void
	 **/
	private function __wakeup() {
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Remote_Control();
			self::$instance->setup_actions();
		}

		return self::$instance;
	}

	private function setup_actions() {

		add_action( 'rest_api_init', array( $this, 'register_api_hooks' ) );

	}

	public function register_api_hooks() {

		$namespace = 'clicker/v1';

		$uri      = $_SERVER['REQUEST_URI'];
		$pos_lite = strpos( $uri, $namespace . '/lite' );
		$pos_full = strpos( $uri, $namespace . '/full' );

		// Need to allow for simple cookie authentication for this plugin's namespace only
		// see: http://v2.wp-api.org/guide/authentication/
		if ( $pos_lite !== false || $pos_full !== false ) {
			$nonce                = wp_create_nonce( 'wp_rest' );
			$_REQUEST['_wpnonce'] = $nonce;
		}

		global $wp_admin_bar;

		if ( ! class_exists( 'WP_Admin_Bar' ) ) {
			require( ABSPATH . WPINC . '/class-wp-admin-bar.php' );
		}

		if ( $wp_admin_bar === NULL ) {
			show_admin_bar( true );

			/**
			 * Filter the admin bar class to instantiate.
			 *
			 * @since 3.1.0
			 *
			 * @param string $wp_admin_bar_class Admin bar class to use. Default 'WP_Admin_Bar'.
			 */
			$admin_bar_class = apply_filters( 'wp_admin_bar_class', 'WP_Admin_Bar' );
			if ( class_exists( $admin_bar_class ) ) {
				$wp_admin_bar = new $admin_bar_class;
			} else {
				return false;
			}

			$wp_admin_bar->initialize();
			$wp_admin_bar->add_menus();
		}

		register_rest_route( $namespace, '/full', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_full' ),
		) );
		register_rest_route( $namespace, '/lite', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_lite' ),
		) );

		register_rest_route( $namespace, '/edit/(?P<type>[post|page|tag]+)/(?P<id>\d+)', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'get_edit' ),
		) );
	}

	private function get_css() {
		global $wp_version;
		$css = "<link rel='stylesheet' id='dashicons-css' href='" . get_site_url() .
		       "/wp-includes/css/dashicons.min.css?ver=" . $wp_version . "' type='text/css' media='all' />";
		$css .= "<link rel='stylesheet' id='admin-bar-css' href='" . get_site_url() .
		        "/wp-includes/css/admin-bar.min.css?ver=" . $wp_version . "' type='text/css' media='all' />";

		return $css;
	}

	/**
	 * For displaying the full admin menu with all additional menu items based on user's role
	 */
	public function get_full() {

		// Return empty html if user is not logged in
		if ( ! is_user_logged_in() ) {
			return array( 'html' => '' );
		}

		$nonce = $_REQUEST['_wpnonce'];

		ob_start();
		wp_admin_bar_render();
		$header = $this->get_css();
		$header .= "<div id=\"wpadminwrapper\">";
		$header .= ob_get_clean();
		$header .= "</div>";

		$return = array(
			'html'  => $header,
			'nonce' => $nonce
		);

		return $return;

	}

	/**
	 * Displays a reduced set of menu items
	 *
	 * @return string
	 */
	public function get_lite() {

		// Return empty html if user is not logged in
		if ( ! is_user_logged_in() ) {
			return array( 'html' => '' );
		}

		global $wp_admin_bar;
		$nonce = $_REQUEST['_wpnonce'];

		// Loads a lite version of the menu manually
		wp_admin_bar_wp_menu( $wp_admin_bar );
		wp_admin_bar_my_account_menu( $wp_admin_bar );
		wp_admin_bar_site_menu( $wp_admin_bar );
		wp_admin_bar_my_account_item( $wp_admin_bar );
		wp_admin_bar_new_content_menu( $wp_admin_bar );
		wp_admin_bar_edit_menu( $wp_admin_bar );
		wp_admin_bar_add_secondary_groups( $wp_admin_bar );

		ob_start();
		$wp_admin_bar->render();
		$header = $this->get_css();
		$header .= "<div id=\"wpadminwrapper\">";
		$header .= ob_get_clean();
		$header .= "</div>";

		$return = array(
			'html'  => $header,
			'nonce' => $nonce
		);

		return $return;

	}

	/**
	 * Displays the lite version of the menu along with overridden edit links based on what is passed
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_edit( $request ) {

		// Return empty html if user is not logged in
		if ( ! is_user_logged_in() ) {
			return array( 'html' => '' );
		}

		global $wp_admin_bar, $wp_the_query;
		$params = $request->get_params();
		$type   = $params['type'];
		$id     = $params['id'];

		if ( ! is_numeric( $id ) ) {
			// Just show the default lite version if $id is not valid
			$this->get_lite(); // exits with output
		} elseif ( $type == 'post' && is_numeric( $id ) ) {
			$wp_the_query = new WP_Query( array(
				'p'           => $id,
				'post_status' => 'publish',
			) );
		} elseif ( $type == 'tag' ) {
			$term = get_term( $id );

			if ( $term !== false ) {
				$args         = array(
					'taxonomy'       => $term->taxonomy,
					'term'           => $term->slug,
					'posts_per_page' => 1
				);
				$wp_the_query = new WP_Query( $args );
			}

		} elseif ( $type == 'page' ) {
			$wp_the_query = new WP_Query( array(
				'page_id' => $id,
			) );
		}

		// Loads a lighter version of the menu manually
		wp_admin_bar_wp_menu( $wp_admin_bar );
		wp_admin_bar_my_account_menu( $wp_admin_bar );
		wp_admin_bar_site_menu( $wp_admin_bar );
		wp_admin_bar_my_account_item( $wp_admin_bar );
		wp_admin_bar_new_content_menu( $wp_admin_bar );
		wp_admin_bar_edit_menu( $wp_admin_bar );
		wp_admin_bar_add_secondary_groups( $wp_admin_bar );

		ob_start();
		$wp_admin_bar->render();
		$header = ob_get_clean();

		return array( 'html' => $header );

	}

}

// Registers the class on loading file
Remote_Control::get_instance();
