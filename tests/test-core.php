<?php

class EAMTestCore extends WP_UnitTestCase {

	/**
	 * Configure editorial access for a given post
	 *
	 * @param int $post_id
	 * @param int $type
	 * @param array $allowed_roles
	 * @param array $allowed_users
	 */
	public function _configureAccess( $post_id, $type = 0, $allowed_roles = array(), $allowed_users = array() ) {
		update_post_meta( $post_id, 'eam_enable_custom_access', sanitize_text_field( $type ) );
		update_post_meta( $post_id, 'eam_allowed_roles', array_map( 'sanitize_text_field', $allowed_roles ) );
		update_post_meta( $post_id, 'eam_allowed_users', array_map( 'absint', $allowed_users ) );
	}

	/**
	 * Create and sign in a test user
	 *
	 * @param string $role
	 * @param string $username
	 * @return WP_Error|WP_User
	 */
	public function _createAndSignInUser( $role, $username = 'testuser' ) {
		$this->factory->user->create( array( 'user_login' => $username, 'role' => $role, 'user_pass' => '12345' ) );

		$user = @wp_signon(
			array(
				'user_login' => $username,
				'user_password' => '12345',
			)
		);

		wp_set_current_user( $user->ID );

		return $user;
	}

	/**
	 * Test edit a post whitelisted for contributors by a contributor
	 */
	public function testEditByWhitelistedContributorPost() {

		$post_id = $this->factory->post->create();

		$this->_configureAccess( $post_id, 'roles', array( 'contributor' ) );

		$this->_createAndSignInUser( 'contributor' );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) );
	}

	/**
	 * Test edit a post not whitelisted for contributors by a contributor
	 */
	public function testEditByNonWhitelistedContributorPost() {

		$post_id = $this->factory->post->create();

		$this->_configureAccess( $post_id, 'roles', array( 'editor' ) );

		$this->_createAndSignInUser( 'contributor' );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( ! ( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) ) );
	}

	/**
	 * Test edit a post whitelisted for editors by an editor
	 */
	public function testEditByWhitelistedEditorPost() {

		$post_id = $this->factory->post->create();

		$this->_configureAccess( $post_id, 'roles', array( 'editor' ) );

		$this->_createAndSignInUser( 'editor' );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) );
	}

	/**
	 * Test edit a post not whitelisted for editors by an editor
	 */
	public function testEditByNonWhitelistedEditorPost() {

		$post_id = $this->factory->post->create();

		$this->_configureAccess( $post_id, 'roles', array( 'contributor' ) );

		$this->_createAndSignInUser( 'editor' );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( ! ( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) ) );
	}

	/**
	 * Test edit a post whitelisted for editor by admin
	 */
	public function testEditByNonWhitelistedAdminPost() {

		$post_id = $this->factory->post->create();

		$this->_configureAccess( $post_id, 'roles', array( 'editor' ) );

		$this->_createAndSignInUser( 'administrator' );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) );
	}

	/**
	 * Test edit a post whitelisted for authors by an author
	 */
	public function testEditByWhitelistedAuthorPost() {

		$post_id = $this->factory->post->create();

		$this->_configureAccess( $post_id, 'roles', array( 'author', 'contributor', 'editor' ) );

		$this->_createAndSignInUser( 'author' );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) );
	}

	/**
	 * Test edit a post not whitelisted for authors by an author
	 */
	public function testEditByNonWhitelistedAuthorPost() {

		$post_id = $this->factory->post->create();

		$this->_configureAccess( $post_id, 'roles', array( 'contributor', 'editor' ) );

		$this->_createAndSignInUser( 'author' );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( ! ( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) ) );
	}

	/**
	 * Test an edit by a whitelisted author user where access is user restricted
	 */
	public function testEditByWhitelistedAuthorUser() {

		$post_id = $this->factory->post->create();

		$user = $this->_createAndSignInUser( 'author' );

		$this->_configureAccess( $post_id, 'users', array(), array( $user->ID ) );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) );
	}

	/**
	 * Test an edit by a non whitelisted author user where access is user restricted
	 */
	public function testEditByNonWhitelistedAuthorUser() {

		$post_id = $this->factory->post->create();

		$user = $this->_createAndSignInUser( 'author' );

		$this->_configureAccess( $post_id, 'users', array(), array( (int) $user->ID + 1 ) );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( ! ( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) ) );
	}

	/**
	 * Test an edit by a whitelisted editor user where access is user restricted
	 */
	public function testEditByWhitelistedEditorUser() {

		$post_id = $this->factory->post->create();

		$user = $this->_createAndSignInUser( 'editor' );

		$this->_configureAccess( $post_id, 'users', array(), array( $user->ID, (int) $user->ID + 1 ) );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) );
	}

	/**
	 * Test an edit by a non whitelisted editor user where access is user restricted
	 */
	public function testEditByNonWhitelistedEditorUser() {

		$post_id = $this->factory->post->create();

		$user = $this->_createAndSignInUser( 'editor' );

		$this->_configureAccess( $post_id, 'users', array(), array( (int) $user->ID + 1, (int) $user->ID + 2 ) );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( ! ( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) ) );
	}

	/**
	 * Test an edit by a whitelisted contributor user where access is user restricted
	 */
	public function testEditByWhitelistedContributorUser() {

		$post_id = $this->factory->post->create();

		$user = $this->_createAndSignInUser( 'contributor' );

		$this->_configureAccess( $post_id, 'users', array(), array( $user->ID, (int) $user->ID + 1, (int) $user->ID + 2 ) );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) );
	}

	/**
	 * Test an edit by a non whitelisted contributor user where access is user restricted
	 */
	public function testEditByNonWhitelistedContributorUser() {

		$post_id = $this->factory->post->create();

		$user = $this->_createAndSignInUser( 'contributor' );

		$this->_configureAccess( $post_id, 'users', array(), array( (int) $user->ID + 1, (int) $user->ID + 2 ) );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( ! ( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) ) );
	}

}