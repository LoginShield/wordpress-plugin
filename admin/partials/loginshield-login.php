<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://loginshield.com/
 * @since      1.0.3
 *
 * @package    LoginShield
 * @subpackage LoginShield/admin/partials
 */
?>

<?php
    // NOTE: see https://developer.wordpress.org/reference/functions/login_header/ for reference on some of the setup that happens here
    global $wp_version;
    
    $login_header_url = apply_filters( 'login_headerurl', get_bloginfo('url') );

    if (version_compare($wp_version, '5.2', '>=')) {
        $login_header_text = apply_filters( 'login_headertext', get_bloginfo('name') );
    } else {
        $login_header_text = apply_filters( 'login_headertitle', get_bloginfo('name') );
    }

    $redirect_to = isset($_REQUEST['redirect_to']) && wp_validate_redirect($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : get_home_url();
    $mode = isset($_GET['mode']) ? sanitize_key($_GET['mode']) : '';
    $loginshield = isset($_GET['loginshield']) && wp_http_validate_url($_GET['loginshield']) ? $_GET['loginshield'] : '';
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div id="LoginShieldLogin">
    <div style="max-width: 300px;"><?php echo get_custom_logo(); ?></div>
    <h1 class="sitename"><a href="<?php echo esc_attr( $login_header_url ); ?>"><?php echo esc_html($login_header_text); ?></a></h1>
    <div id="LoginShieldLoginForm" data-redirect-to="<?php echo esc_attr($redirect_to); ?>" data-mode="<?php echo esc_attr($mode); ?>" data-loginshield="<?php echo esc_attr($loginshield); ?>">
        <form>
        <div class="form-group form-group-login">
            <label for="user_login">Username or Email Address</label>
            <input type="text" name="log" id="user_login" autocomplete="username" class="input" value="" size="20" autocapitalize="off">
            <p class="error-msg"></p>
        </div>
        <div class="form-group form-group-password" style="display: none">
            <label for="user_pass">Password</label>
            <input type="password" name="pwd" id="user_pass" autocomplete="current-password" class="input password-input" value="" size="20" />
            <p class="error-msg"></p>
        </div>
        <div class="form-group form-group-loginshield" style="display: none">
            <div id="loginshield-content" style="width: 100%;"></div>
        </div>
        <div class="form-group form-group-action">
            <p class="forgetmenot"><input name="rememberme" type="checkbox" id="rememberme" value="forever"> <label for="rememberme" class="remember-me">Remember Me</label></p>
            <button type="button" class="button button-primary" id="btnNext">Next</button>
            <button type="button" class="button button-primary" id="btnLogin" style="display:none;">Log In</button>
        </div>
        </form>
    </div>
</div>