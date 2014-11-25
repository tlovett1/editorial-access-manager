<?php
/**
 * Plugin Name: Editorial Access Manager
 * Plugin URI: http://www.taylorlovett.com
 * Description: Allow for granular editorial access control for all post types
 * Author: Taylor Lovett
 * Version: 0.3.1
 * Author URI: http://www.taylorlovett.com
 */

/**
 * Define some plugin constants
 */
define( 'EAM_OPTION_NAME', 'eam_editorial_access_manager' );
define( 'EAM_CAPABILITY', 'manage_editorial_access' );

/**
 * Include plugin reqs
 */
require_once( dirname( __FILE__ ) . '/classes/class-editorial-access-manager.php' );

register_activation_hook( __FILE__, array( 'Editorial_Access_Manager', 'activation' ) );
register_deactivation_hook( __FILE__, array( 'Editorial_Access_Manager', 'deactivation' ) );

Editorial_Access_Manager::factory();