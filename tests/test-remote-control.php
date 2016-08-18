<?php

class Test_Ext_Admin_Menu extends WP_UnitTestCase {

	// @codingStandardsIgnoreStart
	public function setUp() {
		parent::setUp();

		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;

		$this->namespace     = '/clicker/v1';
		$this->administrator = $this->factory->user->create( array( 'role' => 'administrator' ) );
	}

	public function tearDown() {
		parent::tearDown();

		global $wp_rest_server;
		$wp_rest_server = NULL;
	}
	// @codingStandardsIgnoreEnd

	/**
	 * Resets the admin menu for each test request
	 *
	 * @param $route_url (string)
	 */
	private function reset_admin( $route_url ) {
		$_SERVER['REQUEST_URI'] = '/wp-cli' . $route_url;

		global $wp_admin_bar;
		$wp_admin_bar = NULL;
		do_action( 'rest_api_init' );

	}

	/**
	 * The plugin should be installed and activated.
	 */
	public function test_plugin_activated() {
		$this->assertTrue( class_exists( 'Remote_Control' ) );
	}

	/**
	 * Test Non-Authenticated User
	 */
	public function test_non_auth() {

		$route_url = $this->namespace . '/lite';
		$this->reset_admin( $route_url );

		$request = new WP_REST_Request( 'GET', $route_url );
		$request->set_query_params( array( '_wpnonce', 'anythinggoes' ) );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( '', $data['html'] );

	}

	/**
	 * Test Full Menu Authenticated
	 */
	public function test_full_auth() {

		wp_set_current_user( $this->administrator );
		$route_url = $this->namespace . '/full';
		$this->reset_admin( $route_url );

		$request  = new WP_REST_Request( 'GET', $route_url );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertContains( 'Log Out', $data['html'] );
		$this->assertNotEmpty( $data['nonce'] );

	}

	/**
	 * Test Lite Menu Authenticated
	 */
	public function test_lite_auth() {

		wp_set_current_user( $this->administrator );
		$route_url = $this->namespace . '/lite';
		$this->reset_admin( $route_url );

		$request  = new WP_REST_Request( 'GET', $route_url );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertContains( 'Log Out', $data['html'] );
		$this->assertNotEmpty( $data['nonce'] );

	}

	/**
	 * Test Edit Post Authenticated
	 */
	public function test_edit_post_auth() {

		wp_set_current_user( $this->administrator );
		$post_id   = $this->factory->post->create();
		$route_url = $this->namespace . '/edit/post/' . $post_id;
		$this->reset_admin( $route_url );

		$request = new WP_REST_Request( 'GET', $route_url );
		$request->set_query_params( array( '_wpnonce', 'anythinggoes' ) );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertContains( 'Edit Post', $data['html'] );

	}

	/**
	 * Test Edit Page Authenticated
	 */
	public function test_edit_page_auth() {

		wp_set_current_user( $this->administrator );
		$post_id   = $this->factory->post->create( array( 'post_type' => 'page' ) );
		$route_url = $this->namespace . '/edit/page/' . $post_id;
		$this->reset_admin( $route_url );


		$request  = new WP_REST_Request( 'GET', $this->namespace . '/edit/page/' . $post_id );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertContains( 'Edit Page', $data['html'] );

	}

	/**
	 * Test Edit Tag Authenticated
	 */


}

