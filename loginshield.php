<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://loginshield.com/
 * @since             1.0.0
 * @package           LoginShield
 *
 * @wordpress-plugin
 * Plugin Name:       LoginShield
 * Plugin URI:        https://loginshield.com/
 * Description:       This plugin is created to implement LoginShield functionality in WordPress.
 * Version:           1.0.5
 * Author:            Luka Modric
 * Author URI:        https://loginshield.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       loginshield
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'LOGINSHIELD_VERSION', '1.0.5' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-loginshield-activator.php
 */
function activate_loginshield() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-loginshield-activator.php';
	LoginShield_Activator::activate();

    // Don't do redirects when multiple plugins are bulk activated
    if (
        ( isset( $_REQUEST['action'] ) && 'activate-selected' === $_REQUEST['action'] ) &&
        ( isset( $_POST['checked'] ) && count( $_POST['checked'] ) > 1 ) ) {
        return;
    }
    add_option( 'loginshield_activation_redirect', wp_get_current_user()->ID );
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-loginshield-deactivator.php
 */
function deactivate_loginshield() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-loginshield-deactivator.php';
	LoginShield_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_loginshield' );
register_deactivation_hook( __FILE__, 'deactivate_loginshield' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-loginshield.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_loginshield() {

	$plugin = new LoginShield();
	$plugin->run();

}
run_loginshield();

add_action( 'wp_ajax_loginshield_enterprise_settings', 'loginshield_enterprise_settings' );

function loginshield_enterprise_settings() {
    $form_data = array();
    parse_str($_POST['formdata'], $form_data);
    $form_data = wp_unslash($form_data);

    $realm_id = trim($form_data['$loginshield_realm_id']);
    $authorization_token = trim($form_data['$loginshield_authorization_token']);

    try {
        update_option('loginshield_realm_id', $realm_id);
        update_option('loginshield_authorization_token', $authorization_token);
        echo json_encode(array('status' => 1,'data'=>'success'));
    } catch (\Exception $exception) {
        echo json_encode(array('status' => 0,'data'=>'error'));
    }

    die;
}
