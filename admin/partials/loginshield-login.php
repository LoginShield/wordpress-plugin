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

    $login_header_url = get_bloginfo('url');

    /**
     * Filters link URL of the header logo above login form.
     *
     * @since 2.1.0
     *
     * @param string $login_header_url Login header logo URL.
     */
    $login_header_url = apply_filters( 'login_headerurl', $login_header_url );

    $login_header_title = '';

    /**
     * Filters the title attribute of the header logo above login form.
     *
     * @since 2.1.0
     * @deprecated 5.2.0 Use {@see 'login_headertext'} instead.
     *
     * @param string $login_header_title Login header logo title attribute.
     */
    $login_header_title = apply_filters_deprecated(
        'login_headertitle',
        array( $login_header_title ),
        '5.2.0',
        'login_headertext',
        __( 'Usage of the title attribute on the login logo is not recommended for accessibility reasons. Use the link text instead.' )
    );

    $login_header_text = empty( $login_header_title ) ? get_bloginfo('name') : $login_header_title;

    $redirect_to = isset($_REQUEST['redirect_to']) ? $_REQUEST['redirect_to'] : get_home_url();
    $mode = $variable = $_GET['mode'];
    $loginshield = $variable = $_GET['loginshield'];

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div id="LoginShieldLogin">
    <h1><a href="<?php echo esc_attr( $login_header_url ); ?>"><?php echo esc_html($login_header_text); ?></a></h1>
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