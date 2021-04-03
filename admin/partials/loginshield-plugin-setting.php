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
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<section class="login-shield">
    <div class="LOGINSHIELDFormInside clear p40">
        <h2>LoginShield Settings</h2>
        <form id="login-shield-form" method="post" action="action.php">
            <?php
            settings_fields('loginshield-settings');
            do_settings_sections('loginshield-settings');
            ?>
            <div class="form-group w-50 float-left">
                <label for=""><?php echo esc_html__('EndPoint URL', 'loginshield') ?></label>
                <a href="https://loginshield.com" target="_blank">https://loginshield.com</a>
                <p class="wp-lead m-0">Manage your enterprise account settings at <a href="https://loginshield.com" title="LoginShield">https://loginshield.com</a></p>
            </div>
            <div class="form-group w-50 float-left">
                <label for=""><?php echo esc_html__('*LoginShield Realm ID', 'loginshield') ?></label>
                <input type="text" name="$loginshield_realm_id" value="<?php echo esc_attr($loginshield_realm_id); ?>" id="" class="form-control input_fields" placeholder="Realm ID" required>
            </div>
            <div class="form-group w-50 float-left">
                <label for=""><?php echo esc_html__('*Authorization Token', 'loginshield') ?> </label>
                <input type="text" name="$loginshield_authorization_token" value="<?php echo esc_attr($loginshield_authorization_token); ?>" id="" class="form-control input_fields" placeholder="Authorization Token" required>
            </div>
            <div class="error_note w-50 float-left">
                <p class="error_msg"><strong>Note:</strong> *All fields are required.</p>
            </div>

            <div class="SubmitBtn w-50 clear float-left">
                <button type="button" class="btn" style="position: relative;" onclick="saveLoginShieldSetting(this);"><?php echo esc_html__('Save', 'loginshield') ?><img src="<?php echo plugin_dir_url( __DIR__ ).'assets/icon/loder.gif'; ?>" class="Loderimg" style="display: none;"></button>
                <button type="submit" style="display: none;" id="loginshield_from_submit_btn">Submit</button>
            </div>
            <div class="response_msg w-50 clear float-left" style="visibility: hidden;">
                Response Message
            </div>
        </form>
    </div>
</section>