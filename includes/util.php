<?php

/**
 * Utility functions
 *
 * These functions are loaded by the main plugin file and can be used from
 * any of the classes and hooks.
 *
 * @since      1.0.10
 *
 * @package    LoginShield
 * @subpackage LoginShield/includes
 */

function loginshield_version() {
    if ( defined( 'LOGINSHIELD_VERSION' ) ) {
        return LOGINSHIELD_VERSION;
    } else {
        return '1.0.0';
    }
}

function loginshield_plugin_name() {
    return 'loginshield';
}

function loginshield_plugin_display_name() {
    return 'LoginShield for WordPress';
}

function loginshield_endpoint_url() {
    if ( defined( 'LOGINSHIELD_ENDPOINT_URL' ) ) {
        return LOGINSHIELD_ENDPOINT_URL;
    } else {
        return 'https://loginshield.com';
    }
}


// str_starts_with available since php 8
if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }
}

// str_ends_with available since php 8
if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        $length = strlen( $needle );
        if ($length == 0) {
            return true;
        }
        return substr( $haystack, -$length ) === $needle;
    }
}

/**
 * Retrieves the option as a string; if the option is not defined,
 * an empty string is returned
 */
function get_string_option($key) {
    $value = get_option($key);
    return isset($value) && is_string($value) ? $value : '';
}

/**
 * Retrieves the user meta key as a boolean; if it has a string value such as
 * 'true' or 'false', it is converted to a boolean value for the result.
 */
function get_boolean_user_meta($user_id, $key) {
    $value = get_user_meta($user_id, $key, true);
    return isset($value) && is_string($value) && filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

/**
 * Updates the user meta key with a string value of either 'true' or 'false'.
 * If the input is a non-empty string with values OTHER THAN '0', 'false', 'off',
 * it will be stored as 'true'.
 */
function set_boolean_user_meta($user_id, $key, $value) {
    $sanitized = isset($value) ? $value : '';
    if (is_string($sanitized)) {
        $sanitized = filter_var($sanitized, FILTER_VALIDATE_BOOLEAN);
    }
    update_user_meta($user_id, $key, $sanitized ? 'true' : 'false');
}

/**
 * Retrieves the user meta key as a string; if the key is not defined,
 * an empty string is returned
 */
function get_string_user_meta($user_id, $key) {
    $value = get_user_meta($user_id, $key, true);
    return isset($value) && is_string($value) ? $value : '';
}

/**
 * Updates the user meta key with a string value
 */
function set_string_user_meta($user_id, $key, $value) {
    $sanitized = isset($value) ? $value : '';
    if (!is_string($sanitized)) {
        try {
            $sanitized = strval($sanitized);
        } catch (\Exception $exception) {
            $sanitized = '';
        }
    }
    update_user_meta($user_id, $key, $sanitized);
}
