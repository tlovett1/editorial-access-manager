<?php
/**
 * Plugin Name: Editorial Access Manager
 * Plugin URI: http://www.taylorlovett.com
 * Description: Allow for granular editorial access control for all post types
 * Author: Taylor Lovett
 * Version: 0.1.0
 * Author URI: http://www.taylorlovett.com
 */

/**
 * Define some plugin constants
 */
define( 'EAM_OPTION_NAME', 'eam_editorial_access_manager' );

/**
 * Include plugin reqs
 */
require_once( dirname( __FILE__ ) . '/classes/class-editorial-access-manager.php' );

Editorial_Access_Manager::factory();