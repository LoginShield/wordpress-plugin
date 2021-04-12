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
/**
 * Get loginshield option fields value
 */

$loginshield_client_id = get_option('loginshield_client_id');
$loginshield_realm_id = get_option('loginshield_realm_id');

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<section class="login-shield">
    <div class="LOGINSHIELDFormInside clear p40">
        <h1>LoginShield Settings</h1>
        <form id="LoginShieldSettingsForm" method="post" action="action.php">
            <div class="form-group w-50 float-left">
                <p class="wp-lead">Manage your enterprise account settings at <a href="https://loginshield.com" title="LoginShield" target="_blank">https://loginshield.com</a></p>
            </div>
            <div id="ActionForm" class="form-group w-50 float-left loading">
                <div class="loading-wrapper">
                    <p class="lg-loader">Loading...</p>
                </div>
                <div class="normal-form">
                    <p>You are ready to use LoginShield.</p>
                    <p>In profile settings, users can activate or deactivate LoginShield protection for their own account.</p>
                </div>
                <div class="request-form">
                    <p>Set up your free trial or manage your subscription.</p>
                    <a href="javascript:void(0)" id="btnAccessRequest" class="button btn-access-request">Continue</a>
                </div>
            </div>
            <div class="form-group w-50 float-left">
                <h4>Advanced</h4>
                <p><?php esc_html_e('Endpoint URL', 'loginshield') ?>: <span id="loginshield_endpoint_url"><a href="https://loginshield.com" target="_blank">https://loginshield.com</a></span></p>
                <p><?php esc_html_e('Client ID', 'loginshield') ?>: <span id="loginshield_client_id"><?php if($loginshield_client_id) { echo esc_html($loginshield_client_id); } else { echo 'Not configured'; } ?></span></p>
                <p><?php esc_html_e('Realm ID', 'loginshield') ?>: <span id="loginshield_realm_id"><?php if($loginshield_realm_id) { echo esc_html($loginshield_realm_id); } else { echo 'Not configured'; } ?></span></p>
            </div>            
        </form>
    </div>
</section>