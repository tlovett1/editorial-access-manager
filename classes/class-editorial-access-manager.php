<?php

class Editorial_Access_Manager {

	/**
	 * Placeholder constructor
	 *
	 * @since 0.1.0
	 */
	public function __construct() { }

	/**
	 * Register actions and filters
	 *
	 * @since 0.1.0
	 */
	public function setup() {
		add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'action_save_post' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_filter( 'map_meta_cap', array( $this, 'filter_map_meta_cap' ), 100, 4 );
	}

	/**
	 * Map the edit_post meta cap based on whether the users role is whitelisted by the post
	 *
	 * @param array $caps
	 * @param string $cap
	 * @param int $user_id
	 * @param array $args
	 * @since 0.1.0
	 * @return array
	 */
	public function filter_map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( 'edit_post' == $cap || 'publish_posts' == $cap || 'edit_others_posts' == $cap || 'edit_page' == $cap || 'publish_pages' == $cap || 'edit_others_pages' == $cap ) {

			$post_id = ( isset( $args[0] ) ) ? (int) $args[0] : null;
			if ( ! $post_id && ! empty( $_GET['post'] ) ) {
				$post_id = (int) $_GET['post'];
			}

			if ( ! $post_id && ! empty( $_POST['post_ID'] ) ) {
				$post_id = (int) $_POST['post_ID'];
			}

			if ( ! $post_id ) {
				return $caps;
			}

			$enable_custom_access = get_post_meta( $post_id, 'eam_enable_custom_access', true );

			if ( ! empty( $enable_custom_access ) ) {
				$user = new WP_User( $user_id );

				// If user is admin, we do nothing
				if ( ! in_array( 'administrator', $user->roles ) ) {

					if ( 'roles' == $enable_custom_access ) {
						// Limit access to whitelisted roles

						$allowed_roles = (array) get_post_meta( $post_id, 'eam_allowed_roles', true );

						if ( count( array_diff( $user->roles, $allowed_roles ) ) >= 1 ) {
							$caps[] = 'do_not_allow';
						} else {
							$caps = array();
						}
					} elseif ( 'users' == $enable_custom_access ) {
						// Limit access to whitelisted users

						$allowed_users = (array) get_post_meta( $post_id, 'eam_allowed_users', true );

						if ( ! in_array( $user_id, $allowed_users ) ) {
							$caps[] = 'do_not_allow';
						} else {
							$caps = array();
						}
					}
				}
			}
		}

		return $caps;
	}

	/**
	 * Enqueue backend JS and CSS for post edit screen
	 *
	 * @param string $hook
	 * @since 0.1.0
	 */
	public function action_admin_enqueue_scripts( $hook ) {

		if ( 'post.php' == $hook || 'post-new.php' == $hook ) {
			/**
			 * Setup JS stuff
			 */
			if ( true /*defined( SCRIPT_DEBUG ) && SCRIPT_DEBUG*/ ) {
				$js_path = '/js/post-admin.js';
				$css_path = '/build/css/post-admin.css';
			} else {
				$js_path = '/build/js/post-admin.min.js';
				$css_path = '/build/css/post-admin.min.css';
			}

			wp_register_script( 'jquery-chosen', plugins_url( '/bower_components/chosen_v1.1.0/chosen.jquery.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'eam-post-admin', plugins_url( $js_path, dirname( __FILE__ ) ), array( 'jquery-chosen' ), '1.0', true );

			/**
			 * Setup CSS stuff
			 */
			wp_enqueue_style( 'jquery-chosen', plugins_url( '/bower_components/chosen_v1.1.0/chosen.min.css', dirname( __FILE__ ) ) );
			wp_enqueue_style( 'eam-post-admin', plugins_url( $css_path, dirname( __FILE__ ) ) );
		}
	}

	/**
	 * Register meta boxes
	 *
	 * @since 0.1.0
	 */
	public function action_add_meta_boxes() {
		$post_types = get_post_types();

		foreach( $post_types as $post_type ) {
			add_meta_box( 'eam_access_manager', __( 'Editorial Access Manager', 'editorial-access-manager' ), array( $this, 'meta_box_access_manager' ), $post_type, 'side', 'core' );
		}
	}

	/**
	 * Save access control information
	 *
	 * @param int $post_id
	 * @since 0.1.0
	 */
	public function action_save_post( $post_id ) {
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) || 'revision' == get_post_type( $post_id ) ) {
			return;
		}

		if ( ! empty( $_POST['eam_access_manager'] ) && wp_verify_nonce( $_POST['eam_access_manager'], 'eam_access_manager_action' ) ) {

			if ( ! empty( $_POST['eam_enable_custom_access'] ) ) {
				update_post_meta( $post_id, 'eam_enable_custom_access', sanitize_text_field( $_POST['eam_enable_custom_access'] ) );

				if ( 'roles' == $_POST['eam_enable_custom_access'] ) {
					if ( ! empty( $_POST['eam_allowed_roles'] ) ) {

						foreach( $_POST['eam_allowed_roles'] as $role ) {
							$allowed_roles[] = sanitize_text_field( $role );
						}

						update_post_meta( $post_id, 'eam_allowed_roles', $allowed_roles );

					} else {
						update_post_meta( $post_id, 'eam_allowed_roles', array() );
					}
				} elseif ( 'users' == $_POST['eam_enable_custom_access'] ) {
					if ( ! empty( $_POST['eam_allowed_users'] ) ) {
						update_post_meta( $post_id, 'eam_allowed_users', array_map( 'absint', $_POST['eam_allowed_users'] ) );

					} else {
						update_post_meta( $post_id, 'eam_allowed_users', array() );
					}
				}
			} else {
				delete_post_meta( $post_id, 'eam_enable_custom_access' );
			}

		}
	}

	/**
	 * Output access manager meta box
	 *
	 * @param object $post
	 * @since 0.1.0
	 */
	public function meta_box_access_manager( $post ) {
		$post_type_object = get_post_type_object( get_post_type( $post->ID ) );

		// By default every user and every role with the edit_others_posts cap can edit this post
		$edit_others_posts_cap = $post_type_object->cap->edit_others_posts;

		$roles = get_editable_roles();

		// We only want to allow roles to be whitelisted that already have edit_posts
		foreach ( $roles as $role_name => $role_array ) {
			$role = get_role( $role_name );

			if ( ! $role->has_cap( $post_type_object->cap->edit_posts ) ) {
				unset( $roles[$role_name] );
			}
		}

		$users = get_users();

		// We only want to allow users to be whitelisted that already have edit_posts
		foreach ( $users as $key => $user_object) {
			if ( ! user_can( $user_object->ID, $post_type_object->cap->edit_posts ) ) {
				unset( $users[$key] );
			}
		}

		$allowed_roles = get_post_meta( $post->ID, 'eam_allowed_roles', true );
		if ( $allowed_roles === '' ) {
			// get default allowed roles since we have never saved allowed roles for this post

			foreach ( $roles as $role_name => $role_array ) {
				$role = get_role( $role_name );

				if ( $role->has_cap( $edit_others_posts_cap ) ) {
					$allowed_roles[] = $role_name;
				}
			}
		}
		$allowed_roles = (array) $allowed_roles;

		$allowed_users = get_post_meta( $post->ID, 'eam_allowed_users', true );
		if ( $allowed_users === '' ) {
			// get default allowed users since we have never saved allowed users for this post

			foreach ( $users as $user_object ) {

				if ( user_can( $user_object->ID, $edit_others_posts_cap ) ) {
					$allowed_users = $user_object->ID;
				}
			}

		}
		$allowed_users = (array) $allowed_users;
		?>

		<?php wp_nonce_field( 'eam_access_manager_action', 'eam_access_manager' ); ?>

		<div>
		 	<?php esc_html_e( 'Enable custom access management by', 'editorial-access-manager' ); ?>
			<select name="eam_enable_custom_access" id="eam_enable_custom_access">
				<option value="0"><?php esc_html_e( 'Off' ); ?></option>
				<option <?php selected( 'roles', get_post_meta( $post->ID, 'eam_enable_custom_access', true ) ); ?> value="roles"><?php esc_html_e( 'Roles', 'editorial-access-manager' ); ?></option>
				<option <?php selected( 'users', get_post_meta( $post->ID, 'eam_enable_custom_access', true ) ); ?> value="users"><?php esc_html_e( 'Users', 'editorial-access-manager' ); ?></option>
			</select>
		</div>

		<div id="eam_control_roles">
			<label for="eam_allowed_roles"><?php esc_html_e( 'Manage access for roles:', 'editorial-access-manager' ); ?></label>
			<select multiple name="eam_allowed_roles[]" id="eam_allowed_roles">
				<?php foreach ( $roles as $role_name => $role_array ) : ?>
					<option
						value="<?php echo esc_attr( $role_name ); ?>"
						<?php if ( 'administrator' == $role_name ) : ?>selected disabled
						<?php elseif ( in_array( $role_name, $allowed_roles ) ) : ?>selected<?php endif;?>
						>
						<?php echo esc_attr( ucwords( $role_name ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div id="eam_control_users">
			<label for="eam_allowed_users"><?php esc_html_e( 'Manage access for users:', 'editorial-access-manager' ); ?></label>
			<select multiple name="eam_allowed_users[]" id="eam_allowed_users">
				<?php foreach ( $users as $user_object ) : $user = new WP_User( $user_object->ID ); ?>
					<option
						value="<?php echo absint( $user_object->ID ); ?>"
						<?php if ( in_array( 'administrator', $user->roles ) ) : ?>selected disabled
						<?php elseif ( in_array( $user_object->ID, $allowed_users ) ) : ?>selected<?php endif;?>
						>
						<?php echo esc_attr( $user->user_login ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<?php
	}

	/**
	 * Return singleton instance of class
	 *
	 * @since 0.1.0
	 * @return object
	 */
	public static function factory() {
		static $instance;

		if ( ! $instance ) {
			$instance = new self();
			$instance->setup();
		}

		return $instance;
	}
}