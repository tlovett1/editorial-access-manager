<?php

class EAMTestCore extends WP_UnitTestCase {

	/**
	 * Configure editorial access for a given post
	 *
	 * @param int $post_id
	 * @param int $type
	 * @param array $allowed_roles
	 * @param array $allowed_users
	 * @since 0.1.0
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
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
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
	 *
	 * @since 0.1.0
	 */
	public function testEditByNonWhitelistedContributorUser() {

		$post_id = $this->factory->post->create();

		$user = $this->_createAndSignInUser( 'contributor' );

		$this->_configureAccess( $post_id, 'users', array(), array( (int) $user->ID + 1, (int) $user->ID + 2 ) );

		$_POST['post_ID'] = $post_id;
		$_GET['post'] = $post_id;

		$this->assertTrue( ! ( current_user_can( 'edit_post', $post_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) ) );
	}

	/**
	 * Test an edit by a whitelisted editor where access is role restricted
	 *
	 * @since 0.1.0
	 */
	public function testPageEditByWhitelistedEditorRole() {

		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );

		$user = $this->_createAndSignInUser( 'editor' );

		$this->_configureAccess( $page_id, 'roles', array( 'editor' ) );

		$_POST['post_ID'] = $page_id;
		$_GET['post'] = $page_id;

		$this->assertTrue( current_user_can( 'edit_page', $page_id ) && current_user_can( 'publish_pages' ) && current_user_can( 'edit_others_pages' ) );
	}

	/**
	 * Test an edit by a non whitelisted editor where access is role restricted
	 *
	 * @since 0.1.0
	 */
	public function testPageEditByNonWhitelistedEditorRole() {

		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );

		$user = $this->_createAndSignInUser( 'editor' );

		$this->_configureAccess( $page_id, 'roles', array() );

		$_POST['post_ID'] = $page_id;
		$_GET['post'] = $page_id;

		$this->assertTrue( ! ( current_user_can( 'edit_page', $page_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) ) );
	}

	/**
	 * Test an edit by a whitelisted editor where access is user restricted
	 *
	 * @since 0.1.0
	 */
	public function testPageEditByWhitelistedEditorUser() {

		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );

		$user = $this->_createAndSignInUser( 'editor' );

		$this->_configureAccess( $page_id, 'users', array(), array( $user->ID, (int) $user->ID + 1 ) );

		$_POST['post_ID'] = $page_id;
		$_GET['post'] = $page_id;

		$this->assertTrue( current_user_can( 'edit_page', $page_id ) && current_user_can( 'publish_pages' ) && current_user_can( 'edit_others_pages' ) );
	}

	/**
	 * Test an edit by a non whitelisted editor where access is user restricted
	 *
	 * @since 0.1.0
	 */
	public function testPageEditByNonWhitelistedEditorUser() {

		$page_id = $this->factory->post->create( array( 'post_type' => 'page' ) );

		$user = $this->_createAndSignInUser( 'editor' );

		$this->_configureAccess( $page_id, 'users' );

		$_POST['post_ID'] = $page_id;
		$_GET['post'] = $page_id;

		$this->assertTrue( ! ( current_user_can( 'edit_page', $page_id ) && current_user_can( 'publish_posts' ) && current_user_can( 'edit_others_posts' ) ) );
	}
}