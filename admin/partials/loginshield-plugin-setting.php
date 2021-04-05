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
if(function_exists('loginshield_option_fields')){
    $args = loginshield_option_fields();
    if($args){
        foreach ($args as $args_val) {
            switch($args_val){
                case 'loginshield_client_id':
                    $loginshield_client_id = get_option($args_val) ;
                    break;
                case 'loginshield_realm_id':
                    $loginshield_realm_id = get_option($args_val) ;
                    break;
                case 'loginshield_authorization_token':
                    $loginshield_authorization_token = get_option($args_val) ;
                    break;
            }
        }
    }
}

$client_id = $_GET['client_id'];
$client_state = $_GET['client_state'];
$grant_token = $_GET['grant_token'];

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<section class="login-shield">
    <div class="LOGINSHIELDFormInside clear p40">
        <h1>LoginShield Settings</h1>
        <form id="LoginShieldSettingsForm" method="post" action="action.php"
              data-client-id="<?php echo esc_attr($client_id); ?>"
              data-client-state="<?php echo esc_attr($client_state); ?>"
              data-grant-token="<?php echo esc_attr($grant_token); ?>">
            <?php
            settings_fields('loginshield-settings');
            do_settings_sections('loginshield-settings');
            ?>
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
                <p><?php esc_html_e('Endpoint URL', 'loginshield') ?>: <a href="https://loginshield.com" target="_blank">https://loginshield.com</a></p>
                <p><?php esc_html_e('Client ID', 'loginshield') ?>: <?php if($loginshield_client_id) { echo esc_html($loginshield_client_id); } else { echo 'Not configured'; } ?></p>
                <p><?php esc_html_e('Realm ID', 'loginshield') ?>: <?php if($loginshield_realm_id) { echo esc_html($loginshield_realm_id); } else { echo 'Not configured'; } ?></p>                
            </div>            
        </form>
    </div>
</section>