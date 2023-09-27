<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           custom wp new user notification
 *
 * @wordpress-plugin
 * Plugin Name:       WP new user notification
 * Plugin URI:        https://github.com/jmucak/wp-new-user-notification
 * Description:       custom wp new user notification
 * Version:           1.0.0
 * Author:            Josip Mucak
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BF_NEW_USER_NOTIFICATION_PATH', plugin_dir_path( __FILE__ ) );
define( 'BF_NEW_USER_NOTIFICATION_URL', plugin_dir_url( __FILE__ ) );
define( 'BF_NEW_USER_NOTIFICATION_BASENAME', plugin_basename( __FILE__ ) );
define( 'BF_NEW_USER_NOTIFICATION_SLUG', 'bf-new-user-notification' );

require_once BF_NEW_USER_NOTIFICATION_PATH . 'app/BFDashboardSetup.php';
$bf_dashboard_setup = new BFDashboardSetup();
$bf_dashboard_setup->init();

require_once BF_NEW_USER_NOTIFICATION_PATH . 'app/function.php';