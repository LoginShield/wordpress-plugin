<?php

/**
 * Fired during plugin activation
 *
 * @link       https://loginshield.com/
 * @since      1.0.0
 *
 * @package    LoginShield
 * @subpackage LoginShield/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    LoginShield
 * @subpackage LoginShield/includes
 * @author     Luka Modric <lukamodric.world@gmail.com>
 */
class LoginShield_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
        /**
         * Create options if they don't already exist. Existing options will not be updated.
         */
        add_option( 'loginshield_realm_id' );
        add_option( 'loginshield_access_token' );
        add_option( 'loginshield_access_token_not_after' );
        add_option( 'loginshield_refresh_token' );
        add_option( 'loginshield_refresh_token_not_after' );
        add_option( 'loginshield_webauthz_discovery_uri' );
        add_option( 'loginshield_webauthz_register_uri' );
        add_option( 'loginshield_webauthz_request_uri' );
        add_option( 'loginshield_webauthz_exchange_uri' );
        add_option( 'loginshield_client_id' );
        add_option( 'loginshield_client_token' );
        add_option( 'loginshield_realm' );
        add_option( 'loginshield_scope' );
        add_option( 'loginshield_path' );
        add_option( 'loginshield_client_state' );
        add_option( 'loginshield_login_page' );
        
        /**
         * Create LoginShield Login Page.
         *
         * @since    1.0.3
         */
        $loginPage = get_page_by_title('LoginShield', 'OBJECT', 'page');
        $page_id = '';
        if(empty($loginPage)) {
            $page_id = wp_insert_post(
                array(
                    'comment_status' => 'close',
                    'ping_status'    => 'close',
                    'post_author'    => 1,
                    'post_title'     => ucwords('LoginShield'),
                    'post_name'      => sanitize_title('LoginShield'),
                    'post_status'    => 'publish',
                    'post_content'   => '[loginshield_login_page]',
                    'post_type'      => 'page',
                    'post_parent'    => '',
                    'page_template'  => 'loginshield-empty.php'
                )
            );
            update_option( 'loginshield_login_page', $page_id );
        } else {
            update_option( 'loginshield_login_page', $loginPage->ID );
            $page_id = $loginPage->ID;
        }
        update_post_meta( $page_id, '_wp_page_template', 'templates/loginshield-empty.php' );
	}
}
