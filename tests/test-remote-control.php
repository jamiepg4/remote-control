<?php

class Test_Ext_Admin_Menu extends WP_UnitTestCase {

	// @codingStandardsIgnoreStart
	public function setUp() {
		parent::setUp();

		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );

		$this->namespace     = '/clicker/v1';
		$this->administrator = $this->factory->user->create( array( 'role' => 'administrator' ) );
	}

	public function tearDown() {
		parent::tearDown();

		global $wp_rest_server;
		$wp_rest_server = null;
	}
	// @codingStandardsIgnoreEnd

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

		$request = new WP_REST_Request( 'GET', '/clicker/v1/lite' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( '', $data['html'] );
	}

	/**
	 * Test Full Menu Authenticated
	 */
	public function test_full_auth() {

		global $wp_admin_bar;
		$wp_admin_bar = null;
		do_action( 'rest_api_init' );

		wp_set_current_user( $this->administrator );

		$request = new WP_REST_Request( 'GET', '/clicker/v1/full' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertContains( 'Log Out', $data['html'] );

	}

	/**
	 * Test Lite Menu Authenticated
	 */
	public function test_lite_auth() {

		global $wp_admin_bar;
		$wp_admin_bar = null;
		do_action( 'rest_api_init' );

		wp_set_current_user( $this->administrator );

		$request = new WP_REST_Request( 'GET', $this->namespace . '/lite' );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertContains( 'Log Out', $data['html'] );

	}

	/**
	 * Test Edit Post Authenticated
	 */
	public function test_edit_post_auth() {

		global $wp_admin_bar;
		$wp_admin_bar = null;
		do_action( 'rest_api_init' );

		wp_set_current_user( $this->administrator );
		$post_id = $this->factory->post->create();

		$request = new WP_REST_Request( 'GET', $this->namespace . '/edit/post/' . $post_id );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertContains( 'Edit Post', $data['html'] );

	}

	/**
	 * Test Edit Page Authenticated
	 */
	public function test_edit_page_auth() {

		global $wp_admin_bar;
		$wp_admin_bar = null;
		do_action( 'rest_api_init' );

		wp_set_current_user( $this->administrator );
		$post_id = $this->factory->post->create( array( 'post_type' => 'page' ) );

		$request = new WP_REST_Request( 'GET', $this->namespace . '/edit/page/' . $post_id );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertContains( 'Edit Page', $data['html'] );

	}

	/**
	 * Test Edit Tag Authenticated
	 */


}

