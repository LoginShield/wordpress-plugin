<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://loginshield.com/
 * @since      1.0.0
 *
 * @package    LoginShield
 * @subpackage LoginShield/admin/partials
 */
if(!function_exists('loginshield_option_fields')){
   function loginshield_option_fields(){
   		$args = array('loginshield_realm_id', 'loginshield_authorization_token', 'loginshield_client_id');
   		return $args;
   }
}
?>