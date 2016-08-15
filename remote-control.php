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
		add_action( 'init', array( $this, 'action_init_register_rewrites' ) );
		add_action( 'template_redirect', array( $this, 'action_template_redirect' ) );

	}

	/**
	 * Register the custom rewrite rules
	 */
	public function action_init_register_rewrites() {
		add_rewrite_tag( '%rc_show%', '([^&]+)' );
		add_rewrite_tag( '%rc_type%', '([^&]+)' );
		add_rewrite_tag( '%rc_id%', '([^&]+)' );
		add_rewrite_rule( 'clicker/(full|lite)/?', 'index.php?rc_show=$matches[1]', 'top' );
		add_rewrite_rule( 'clicker/edit/(post|page|tag)/([0-9]+)/?', 'index.php?rc_show=edit&rc_type=$matches[1]&rc_id=$matches[2]', 'top' );

	}

	/**
	 * Sends the requests to a specific function (controller)
	 *
	 * @return void
	 */
	public function action_template_redirect() {

		global $wp_query;

		// Do not return a menu if user is not logged in
		if ( ! is_user_logged_in() ) {
			$this->get_empty(); // exits without output
		}

		switch ( $wp_query->get( 'rc_show' ) ) {
			case 'empty':
				$this->get_empty();
				break;
			case 'full':
				$this->get_full();
				break;
			case 'lite':
				$this->get_lite();
				break;
			case 'edit':
				$this->get_edit( $wp_query->get( 'rc_type' ), $wp_query->get( 'rc_id' ) );
				break;
		}

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
	 * Returns an empty html value for the admin menu
	 */
	public function get_empty() {

		$json_response = json_encode( array( 'html' => '' ) );

		$this->jsonp( $json_response ); // exits with output

	}

	/**
	 * For displaying the full admin menu with all additional menu items based on user's role
	 */
	public function get_full() {

		ob_start();
		wp_admin_bar_render();
		$header = $this->get_css();
		$header .= "<div id=\"wpadminwrapper\">";
		$header .= ob_get_clean();
		$header .= "</div>";

		$json_response = json_encode( array( 'html' => $header ) );

		$this->jsonp( $json_response ); // exits with output

	}

	/**
	 * Displays a reduced set of menu items
	 *
	 * @return void
	 */
	public function get_lite() {

		global $wp_admin_bar;

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

		$json_response = json_encode( array( 'html' => $header ) );

		$this->jsonp( $json_response ); // exits with output

	}

	/**
	 * Displays the lite version of the menu along with overridden edit links based on what is passed
	 *
	 * @param string $type The type of query to be used
	 * @param int $id The ID of the page the user is on
	 *
	 * @return void
	 */
	public function get_edit( $type, $id ) {

		global $wp_admin_bar, $wp_the_query;

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
				'page_id'           => $id,
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

		$json_response = json_encode( array( 'html' => $header ) );

		$this->jsonp( $json_response ); // exits with output

	}

	/**
	 * JSONP callback support
	 *
	 * @param string $json_response the json encoded response string
	 *
	 * @return void
	 */
	private function jsonp( $json_response ) {

		$callback = preg_replace( "/[^a-zA-Z0-9]+/", "", $_GET['callback'] ); // alphanumeric only

		// JSONP requires content type of application/javascript
		$content_type = 'application/javascript';

		// JSONP uses callback for response
		$json_response = esc_js( $callback ) . '(' . $json_response . ')';

		@header( 'Content-Type: ' . $content_type . '; charset=' . get_option( 'blog_charset' ) );
		echo $json_response;

		exit();
	}
}

// Registers the class on loading file
Remote_Control::get_instance();
