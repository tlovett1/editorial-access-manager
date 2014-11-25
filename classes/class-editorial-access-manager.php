<?php

class Editorial_Access_Manager {

	/**
	 * Constructor
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'admin_init', array( $this, 'setup' ) );
		add_filter( 'map_meta_cap', array( $this, 'filter_map_meta_cap' ), 100, 4 );
	}
	
	/**
	 * Activation
	 *
	 * @since 0.3.0
	 */
	public static function activation() {
		$roles = get_editable_roles();
		foreach ( $roles as $key => $role ) {
			$role_object = get_role( $key );
			$role_object->add_cap( EAM_CAPABILITY, $role_object->has_cap( 'manage_options' ) );
		}
	}

	/**
	 * Deactivation
	 *
	 * @since 0.3.0
	 */
	public static function deactivation() {
		$roles = get_editable_roles();
		foreach ( $roles as $key => $role ) {
			get_role( $key )->remove_cap( EAM_CAPABILITY );
		}
	}

	/**
	 * Register actions and filters
	 *
	 * @since 0.1.0
	 */
	public function setup() {
		
		// If current user has not the capability to manage access do nothing
		if ( ! current_user_can( EAM_CAPABILITY ) ) {
			return;
		}
		
		// Register meta boxes and other hooks
		add_action( 'add_meta_boxes', array( $this, 'action_add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'action_save_post' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'action_admin_enqueue_scripts' ) );
		add_action( 'bulk_edit_custom_box', array( $this, 'action_bulk_edit_custom_box' ), 10, 2 );
		
		// Setup admin columns
		$post_types = $this->get_post_types();
		foreach( $post_types as $post_type ) {
			add_filter( "manage_${post_type}_posts_columns", array( $this, 'manage_columns' ) );
			add_action( "manage_${post_type}_posts_custom_column", array( $this, 'manage_custom_column' ), 10, 2 );
		}
	}

	/**
	 * Return list of post types managed by the plugin
	 *
	 * @since 0.3.0
	 * @return array
	 */
	public function get_post_types() {
		return apply_filters( 'eam_post_types', get_post_types( array( 'public' => true ) ) );
	}

	/**
	 * Load translation
	 *
	 * @since 0.2.0
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'editorial-access-manager', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
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
		$eam_caps = array(
			'edit_page',
			'edit_post',
			'edit_others_pages',
			'edit_others_posts',
			'publish_posts',
			'publish_pages',
			'delete_page',
			'delete_post',
		);

		if ( in_array( $cap, $eam_caps ) ) {

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

					if ( 'roles' === $enable_custom_access ) {
						// Limit access to whitelisted roles

						$allowed_roles = (array) get_post_meta( $post_id, 'eam_allowed_roles', true );

						if ( empty( $user->roles ) || count( array_diff( $user->roles, $allowed_roles ) ) >= 1 ) {
							$caps[] = 'do_not_allow';
						} else {
							$caps = array();
						}
					} elseif ( 'users' === $enable_custom_access ) {
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

		/**
		 * Setup CSS stuff
		 */
		if ( 'post.php' == $hook || 'post-new.php' == $hook || 'edit.php' == $hook ) {
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$css_path = '/build/css/post-admin.css';
			} else {
				$css_path = '/build/css/post-admin.min.css';
			}
			wp_enqueue_style( 'eam-post-admin', plugins_url( $css_path, dirname( __FILE__ ) ) );
		}

		/**
		 * Setup JS stuff
		 */
		if ( 'post.php' == $hook || 'post-new.php' == $hook || 'edit.php' == $hook ) { // edit.php needed for bulk edit
			if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
				$js_path = '/js/post-admin.js';
			} else {
				$js_path = '/build/js/post-admin.min.js';
			}
			wp_enqueue_style( 'jquery-chosen', plugins_url( '/bower_components/chosen_v1.1.0/chosen.min.css', dirname( __FILE__ ) ) );
			wp_register_script( 'jquery-chosen', plugins_url( '/bower_components/chosen_v1.1.0/chosen.jquery.js', dirname( __FILE__ ) ), array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'eam-post-admin', plugins_url( $js_path, dirname( __FILE__ ) ), array( 'jquery-chosen' ), '1.0', true );
		}

	}

	/**
	 * Register meta boxes
	 *
	 * @since 0.1.0
	 */
	public function action_add_meta_boxes() {
		$post_types = $this->get_post_types();

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

		if ( isset( $_REQUEST['bulk_edit'] ) || ( ! empty( $_REQUEST['eam_access_manager'] ) && wp_verify_nonce( $_REQUEST['eam_access_manager'], 'eam_access_manager_action' ) ) ) {

			if ( ! empty( $_REQUEST['eam_enable_custom_access'] ) && in_array( $_REQUEST['eam_enable_custom_access'], array( 'roles', 'users' ) ) ) {
				update_post_meta( $post_id, 'eam_enable_custom_access', sanitize_text_field( $_REQUEST['eam_enable_custom_access'] ) );

				if ( 'roles' == $_REQUEST['eam_enable_custom_access'] ) {
					if ( ! empty( $_REQUEST['eam_allowed_roles'] ) ) {

						foreach( $_REQUEST['eam_allowed_roles'] as $role ) {
							$allowed_roles[] = sanitize_text_field( $role );
						}

						update_post_meta( $post_id, 'eam_allowed_roles', $allowed_roles );

					} else {
						update_post_meta( $post_id, 'eam_allowed_roles', array() );
					}
				} elseif ( 'users' == $_REQUEST['eam_enable_custom_access'] ) {
					if ( ! empty( $_REQUEST['eam_allowed_users'] ) ) {
						update_post_meta( $post_id, 'eam_allowed_users', array_map( 'absint', $_REQUEST['eam_allowed_users'] ) );

					} else {
						update_post_meta( $post_id, 'eam_allowed_users', array() );
					}
				}
			} elseif ( '0' == $_REQUEST['eam_enable_custom_access'] ) {
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
		$this->access_form( get_post_type( $post->ID ), $post->ID, false );
	}
	
	/**
	 * Output form for bulk editing
	 *
	 * @since 0.3.0
	 * @param string $column_name
	 * @param string $post_type
	 * @return object
	 */
	public function action_bulk_edit_custom_box( $column_name, $post_type ) {
		if ( $column_name == 'editorial-access-manager' ) {
			$this->access_form( $post_type );
		}
	}

	/**
	 * Output access manager form
	 *
	 * @param string $post_type
	 * @param int $post_id
	 * @param bool $bulk
	 * @since 0.3.0
	 */
	public function access_form( $post_type, $post_id = null, $bulk = true ) {
		global $wp_roles;
		$post_type_object = get_post_type_object( $post_type );

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

		$allowed_roles = $post_id ? get_post_meta( $post_id, 'eam_allowed_roles', true ) : '';
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

		$allowed_users = $post_id ? get_post_meta( $post_id, 'eam_allowed_users', true ) : '';
		if ( $allowed_users === '' ) {
			// get default allowed users since we have never saved allowed users for this post

			foreach ( $users as $user_object ) {

				if ( user_can( $user_object->ID, $edit_others_posts_cap ) ) {
					$allowed_users = $user_object->ID;
				}
			}

		}
		$allowed_users = (array) $allowed_users;

		if ( ! $bulk ) { 
			wp_nonce_field( 'eam_access_manager_action', 'eam_access_manager' );
		}
		?>

		<fieldset class="inline-edit-eam">
		
		<div id="eam_control_access" class="inline-edit-group">
		 	<label for="eam_enable_custom_access"><span class="title"><?php esc_html_e( 'Enable custom access management by', 'editorial-access-manager' ); ?></span></label>
			<select name="eam_enable_custom_access" id="eam_enable_custom_access">
				<?php if ( $bulk ) { ?><option value="-1"><?php _e( '&mdash; No Change &mdash;' ); ?></option><?php } ?>
				<option value="0"><?php esc_html_e( 'Off', 'editorial-access-manager' ); ?></option>
				<option <?php selected( 'roles', $post_id ? get_post_meta( $post_id, 'eam_enable_custom_access', true ) : '' ); ?> value="roles"><?php esc_html_e( 'Roles', 'editorial-access-manager' ); ?></option>
				<option <?php selected( 'users', $post_id ? get_post_meta( $post_id, 'eam_enable_custom_access', true ) : '' ); ?> value="users"><?php esc_html_e( 'Users', 'editorial-access-manager' ); ?></option>
			</select>
		</div>

		<div id="eam_control_roles" class="inline-edit-group">
			<label for="eam_allowed_roles"><span class="title"><?php esc_html_e( 'Manage access for roles:', 'editorial-access-manager' ); ?></span></label>
			<select multiple name="eam_allowed_roles[]" id="eam_allowed_roles">
				<?php foreach ( $roles as $role_name => $role_array ) : ?>
					<option
						value="<?php echo esc_attr( $role_name ); ?>"
						<?php if ( 'administrator' == $role_name ) : ?>selected disabled
						<?php elseif ( in_array( $role_name, $allowed_roles ) ) : ?>selected<?php endif;?>
						>
						<?php echo esc_attr( translate_user_role( $wp_roles->roles[ $role_name ]['name'] ) ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div id="eam_control_users" class="inline-edit-group">
			<label for="eam_allowed_users"><span class="title"><?php esc_html_e( 'Manage access for users:', 'editorial-access-manager' ); ?></span></label>
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

		</fieldset>

		<?php
	}

	/**
	 * Add access manager column
	 *
	 * @param array $columns
	 * @since 0.2.0
	 * @return array
	 */
	public function manage_columns( $columns ) {
		$columns['editorial-access-manager'] = __( 'Editorial access', 'editorial-access-manager' );
		return $columns;
	}

	/**
	 * Populate access manager column cells
	 *
	 * @param string $column_name
	 * @param int $post_id
	 * @since 0.2.0
	 */
	public function manage_custom_column( $column_name, $post_id ) {
		if ( $column_name == 'editorial-access-manager' ) {
			$eam = get_post_meta( $post_id, 'eam_enable_custom_access', true );
			if ( ! empty( $eam ) ) {
				if ( 'roles' == $eam ) {
					$roles = get_post_meta( $post_id, 'eam_allowed_roles', true );
					array_unshift( $roles, 'administrator' );
					global $wp_roles;
					$role_names = array();
					echo '<strong>' . __( 'Roles', 'editorial-access-manager' ) . ':</strong><br />';
					foreach ( $roles as $role ) {
						if ( ! empty( $wp_roles->roles[ $role ]['name'] ) ) {
							$role_names[] = translate_user_role( $wp_roles->roles[ $role ]['name'] );
						}
					}
					sort( $role_names );
					echo implode( ', ', $role_names );
				} elseif ( 'users' === $eam ) {
					$users = get_post_meta( $post_id, 'eam_allowed_users', true );
					$admins = get_users( array( 'role' => 'administrator', 'fields' => 'ID' ) );
					$users = array_merge( $users, $admins );
					$user_names = array();
					echo '<strong>' . __( 'Users', 'editorial-access-manager' ) . ':</strong><br />';
					foreach ( $users as $user ) {
						$user_object = get_userdata( $user );
						if ( ! empty( $user_object ) ) {
							$user_names[] = $user_object->user_login;
						}
					}
					sort( $user_names );
					echo implode( ', ', $user_names );
				}
			}
			else {
				esc_html_e( 'Off', 'editorial-access-manager' );
			}
		}
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
		}

		return $instance;
	}
}