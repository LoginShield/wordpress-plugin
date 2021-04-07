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
