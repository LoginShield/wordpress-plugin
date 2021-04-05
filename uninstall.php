<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       https://loginshield.com/
 * @since      1.0.0
 *
 * @package    LoginShield
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option('loginshield_activation_redirect');
delete_option('loginshield_realm_id');
delete_option('loginshield_authorization_token');
delete_option('loginshield_login_page');
delete_option('loginshield_access_token');
delete_option('loginshield_access_token_max_seconds');
delete_option('loginshield_refresh_token');
delete_option('loginshield_refresh_token_max_seconds');
delete_option('loginshield_authorization_token');
delete_option('map_loginshielduserid_to_id');
delete_option('loginshield_realm');
delete_option('loginshield_scope');
delete_option('loginshield_path');
delete_option('loginshield_webauthz_discovery_uri');
delete_option('loginshield_webauthz_register_uri');
delete_option('loginshield_webauthz_request_uri');
delete_option('loginshield_webauthz_exchange_uri');
delete_option('loginshield_client_id');
delete_option('loginshield_client_token');
delete_option('loginshield_client_state');
