<?php

/**
 * Register all Rest APIs for the plugin
 *
 * @link       https://loginshield.com/
 * @since      1.0.0
 *
 * @package    LoginShield
 * @subpackage LoginShield/includes
 */

/**
 * Register all Rest APIs for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    LoginShield
 * @subpackage LoginShield/includes
 * @author     Luka Modric <lukamodric.world@gmail.com>
 */
class LoginShield_RestAPI
{

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The endpoint url.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $endpoint_url    The endpoint url for each customer (temporal)
     */
    private $endpoint_url;

    /**
     * The Loginshield Endpoint URL.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $loginshield_endpoint_url    The endpoint url for each customer (temporal)
     */
    private $loginshield_endpoint_url;

    /**
     * The Loginshield Realm ID.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $loginshield_realm_id    The endpoint url for each customer (temporal)
     */
    private $loginshield_realm_id;

    /**
     * The Loginshield Authorization Token.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $loginshield_authorization_token    The endpoint url for each customer (temporal)
     */
    private $loginshield_authorization_token;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $this->endpoint_url = get_home_url();
        $this->loginshield_endpoint_url = 'https://loginshield.com';
        $this->loginshield_realm_id = get_option('loginshield_realm_id');
        $this->loginshield_authorization_token = get_option('loginshield_authorization_token');

        if ( in_array($_SERVER['SERVER_ADDR'], ['127.0.0.1', '::1', 'localhost']) ) {
            $this->endpoint_url = 'https://demo.earlyhq.com';
        }

        add_action('rest_api_init', array($this, 'register_rest_api'));
    }

    /**
     * Register REST API
     *
     * @return void
     */
    public function register_rest_api()
    {
        register_rest_route( $this->plugin_name, '/account/edit', array(
            'methods'  => 'POST',
            'callback' => array($this, 'editAccount')
        ));

        register_rest_route( $this->plugin_name, '/session/login/loginshield', array(
            'methods'  => 'POST',
            'callback' => array($this, 'loginWithLoginShield')
        ));

        register_rest_route( $this->plugin_name, '/loginWithPassword', array(
            'methods'  => 'POST',
            'callback' => array($this, 'loginWithPassword')
        ));

        register_rest_route( $this->plugin_name, '/checkUserWithLogin', array(
            'methods'  => 'POST',
            'callback' => array($this, 'checkUserWithLogin')
        ));

        register_rest_route( $this->plugin_name, '/verifyRealmInfo', array(
            'methods'  => 'POST',
            'callback' => array($this, 'verifyRealmInfo')
        ));
    }

    /**
     * Check if user has LoginShield enabled by Login information
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function checkUserWithLogin(WP_REST_Request $request) {
        try {
            $login = $request->get_param('login');

            $userByLogin = get_userdatabylogin($login);
            $userByEmail = get_user_by('email', $login);

            if ($userByLogin) $user = $userByLogin;
            if ($userByEmail) $user = $userByEmail;

            if (!$user) {
                return new WP_REST_Response([
                    'isLoginShieldEnabled'  => false,
                ], 200);
            }

            $userId = $user->get_ID() ? $user->get_ID() : $user->data->ID;
            $isLoginShieldEnabled = get_user_meta($userId, 'loginshield_is_enabled', true);

            return new WP_REST_Response([
                'isLoginShieldEnabled'      => $isLoginShieldEnabled,
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify Realm Information
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function verifyRealmInfo(WP_REST_Request $request) {
        try {
            $webauthz = new Webauthz();

            $response = $webauthz->verifyRealmInfo();
//            $response = 'test';

//            $login = $request->get_param('login');
//
//            $userByLogin = get_userdatabylogin($login);
//            $userByEmail = get_user_by('email', $login);
//
//            if ($userByLogin) $user = $userByLogin;
//            if ($userByEmail) $user = $userByEmail;
//
//            if (!$user) {
//                return new WP_REST_Response([
//                    'isLoginShieldEnabled'  => false,
//                ], 200);
//            }
//
//            $userId = $user->get_ID() ? $user->get_ID() : $user->data->ID;
//            $isLoginShieldEnabled = get_user_meta($userId, 'loginshield_is_enabled', true);

            return new WP_REST_Response([
                'response'      => $response,
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Login with Password
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function loginWithPassword(WP_REST_Request $request)
    {
        try {
            $login = $request->get_param('login');
            $password = $request->get_param('password');
            $remember = $request->get_param('remember');

            $loggedIn = $this->autoLogin($login, $password, $remember);

            return new WP_REST_Response([
                'isLoggedIn'    => $loggedIn
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'login-failed',
                'message'   => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Login with LoginShield
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function loginWithLoginShield(WP_REST_Request $request)
    {
        try {
            $login = $request->get_param('login');
            $mode = $request->get_param('mode');
            $verifyToken = $request->get_param('verifyToken');

            if ($mode === 'activate-loginshield') {
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;
                $isEnabled = get_user_meta($user_id, 'loginshield_is_enabled', true);
                $loginshieldUserId = get_user_meta($user_id, 'loginshield_user_id', true);

                if ($isEnabled && $loginshieldUserId) {
                    $loginshield = new RealmClient($this->loginshield_endpoint_url, $this->loginshield_realm_id, $this->loginshield_authorization_token);
                    $startLoginResponse = $loginshield->startLogin($loginshieldUserId, $this->endpoint_url . '/wp-admin/profile.php?mode=resume-loginshield');
                    return new WP_REST_Response([
                        'isAuthenticated'   => false,
                        'forward'           => $startLoginResponse->forward,
                        'startLoginResponse'           => $startLoginResponse,
                    ], 200);
                }

                if (!$loginshieldUserId) {
                    return new WP_REST_Response([
                        'isAuthenticated'   => false,
                        'error'             => 'registration-required'
                    ], 200);
                }

                $loginshield = new RealmClient($this->loginshield_endpoint_url, $this->loginshield_realm_id, $this->loginshield_authorization_token);
                $startLoginResponse = $loginshield->startLogin($loginshieldUserId, $this->endpoint_url . '/wp-admin/profile.php?mode=resume-loginshield', true);
                return new WP_REST_Response([
                    'isAuthenticated'   => true,
                    'forward'           => $startLoginResponse->forward
                ], 200);
            }

            if ($verifyToken) {
                $loginshield = new RealmClient($this->loginshield_endpoint_url, $this->loginshield_realm_id, $this->loginshield_authorization_token);
                $verifyLoginResponse = $loginshield->verifyLogin($verifyToken);
                if ($verifyLoginResponse->error || $verifyLoginResponse->fault) {
                    return new WP_REST_Response([
                        'isAuthenticated'    => false
                    ], 200);
                }
                if ($verifyLoginResponse->realmId == $this->loginshield_realm_id) {
                    $user_id = $this->getLoginShieldUserId($verifyLoginResponse->realmScopedUserId);
                    if ($user_id) {
                        $isEnabled = get_user_meta($user_id, 'loginshield_is_enabled', true);
                        if (!$isEnabled) {
                            $this->setUserMeta($user_id, 'loginshield_is_enabled', true, true);
                            $this->setUserMeta($user_id, 'loginshield_is_registered', true, true);
                            $this->setUserMeta($user_id, 'loginshield_is_confirmed', true, true);
                            $this->setUserMeta($user_id, 'loginshield_user_id', $verifyLoginResponse->realmScopedUserId, true);
                        }
                        $this->autoLoginWithCookie($user_id);
                        return new WP_REST_Response([
                            'isAuthenticated'   => true,
                            'isConfirmed'       => true
                        ], 200);
                    }
                }
                return new WP_REST_Response([
                    'isAuthenticated'    => false
                ], 200);
            }

            if ($login) {
                $userByLogin = get_userdatabylogin($login);
                $userByEmail = get_user_by('email', $login);

                if ($userByLogin) $user = $userByLogin;
                if ($userByEmail) $user = $userByEmail;

                if (!$user) {
                    return new WP_REST_Response([
                        'error'             => 'login-required',
                        'isAuthenticated'   => false
                    ], 400);
                }

                $userId = $user->get_ID() ? $user->get_ID() : $user->data->ID;
                $isLoginShieldEnabled = get_user_meta($userId, 'loginshield_is_enabled', true);
                $loginshieldUserId = get_user_meta($userId, 'loginshield_user_id', true);

                if ($isLoginShieldEnabled && $loginshieldUserId) {
                    $loginshield = new RealmClient($this->loginshield_endpoint_url, $this->loginshield_realm_id, $this->loginshield_authorization_token);
                    $startLoginResponse = $loginshield->startLogin($loginshieldUserId, $this->endpoint_url . '/loginshield-login?mode=resume-loginshield');
                    return new WP_REST_Response([
                        'isAuthenticated'   => false,
                        'forward'           => $startLoginResponse->forward
                    ], 200);
                }
            }

            return new WP_REST_Response([
                'error'             => 'password-required',
                'isAuthenticated'   => false
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'login-failed',
                'message'   => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Edit Account
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function editAccount(WP_REST_Request $request)
    {
        try {
            $action = $request->get_param('action');
            if ($action && $action == 'register-loginshield-user') {
                return $this->enableLoginShieldForAccount($request);
            }

            if ($action && $action == 'update-security') {
                return $this->updateSecurity($request);
            }

            $loginshield = $request->get_param('loginshield');
            if (!($loginshield && isset($loginshield->isEnabled))) {
                return new WP_REST_Response([
                    'error'    => 'edit-account-failed',
                    'message'    => 'Bad Request'
                ], 400);
            }

            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;

            $isRegistered = get_user_meta($user_id, 'loginshield_is_registered', true);
            $isConfirmed = get_user_meta($user_id, 'loginshield_is_confirmed', true);
            $isEnabled = get_user_meta($user_id, 'loginshield_is_enabled', true);

            if ($isRegistered && $isConfirmed) {
                if (isset($isEnabled)) {
                    update_user_meta($user_id, 'loginshield_is_enabled', $loginshield->isEnabled);
                } else {
                    add_user_meta($user_id, 'loginshield_is_enabled', $loginshield->isEnabled, true);
                }
            }

            return new WP_REST_Response([
                'isEdited' => true
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'edit-account-failed',
                'message'   => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Enable LoginShield for Account
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function enableLoginShieldForAccount(WP_REST_Request $request)
    {
        try {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $user_name = $current_user->user_login;
            $user_email = $current_user->user_email;

            $loginshieldUserId = get_user_meta($user_id, 'loginshield_user_id', true);
            if (isset($loginshieldUserId) && $loginshieldUserId) {
                return new WP_REST_Response([
                    'forward'     => $this->endpoint_url . '/account/loginshield/continue-registration'
                ], 200);
            }

            $loginshield = new RealmClient($this->loginshield_endpoint_url, $this->loginshield_realm_id, $this->loginshield_authorization_token);
            $realmScopedUserId = $this->getRandomHex(16);

            $response = $loginshield->createRealmUser($realmScopedUserId, $user_name, $user_email, true);
            if ($response->error) {
                return new WP_REST_Response([
                    'error'     => 'registration failed',
                    'isEdited' => false
                ], 500);
            }

            if ($response->isCreated) {
                $this->setUserMeta($user_id, 'loginshield_is_enabled', false, true);
                $this->setUserMeta($user_id, 'loginshield_is_registered', true, true);
                $this->setUserMeta($user_id, 'loginshield_is_enabled', false, true);
                $this->setUserMeta($user_id, 'loginshield_user_id', $realmScopedUserId, true);

                $this->addNewLoginShieldUser($realmScopedUserId, $user_id);

                if ($response->forward) {
                    return new WP_REST_Response([
                        'forward'   => $response->forward
                    ], 200);
                }

                return new WP_REST_Response([
                    'isEdited'   => true
                ], 200);
            }

            return new WP_REST_Response([
                'error'   => 'unexpected reply from registration',
                'response'   => $response
            ], 500);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'registration failed',
                'message'   => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Update Security of LoginShield Account
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function updateSecurity(WP_REST_Request $request)
    {
        try {
            $isSecured = $request->get_param('isSecured');
            if (!isset($isSecured)) {
                return new WP_REST_Response([
                    'error'     => 'update failed',
                    'message'   => 'missing parameter'
                ], 400);
            }

            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;

            $this->setUserMeta($user_id, 'loginshield_is_enabled', $isSecured, true);

            return new WP_REST_Response([
                'isSecured'     => $isSecured
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'update failed',
                'message'   => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Auto Login
     *
     * @param string $login
     * @param string $password
     * @param bool $remember
     *
     * @return boolean
     */
    private function autoLogin($login = '', $password = '', $remember = false)
    {
        $creds = array(
            'user_login'    => $login,
            'user_password' => $password,
            'remember'      => $remember
        );
        $user = wp_signon( $creds, false );

        if (is_wp_error($user))
            return false;

        return true;
    }

    /**
     * Auto Login with Cookie
     *
     * @since 1.0.3
     */
    private function autoLoginWithCookie($user_id)
    {
        wp_clear_auth_cookie();
        wp_set_current_user ( $user_id );
        wp_set_auth_cookie  ( $user_id );
    }

    /**
     * Add new LoginShield User
     *
     * @param string $realmScopedUserId
     * @param string $userId
     *
     * @return void
     */
    private function addNewLoginShieldUser($realmScopedUserId, $userId)
    {
        $userList = get_option('map_loginshielduserid_to_id');
        $userList = json_decode($userList, true);
        if (!isset($userList) || !$userList) {
            $userList = [];
        }

        $userList[$realmScopedUserId] = $userId;
        update_option('map_loginshielduserid_to_id', json_encode($userList));
    }

    /**
     * Get LoginShield UserId (WordPress User Id)
     *
     * @param string $realmScopedUserId
     *
     * @return string
     */
    private function getLoginShieldUserId($realmScopedUserId)
    {
        $userList = get_option('map_loginshielduserid_to_id');
        $userList = json_decode($userList, true);
        if (!isset($userList) || !$userList) {
            $userList = [];
        }

        if (!$userList[$realmScopedUserId]) {
            return null;
        }

        return $userList[$realmScopedUserId];
    }

    /**
     * Get LoginShield UserId (WordPress User Id)
     *
     * @return array
     */
    private function getLoginShieldUsers()
    {
        $userList = get_option('map_loginshielduserid_to_id');
        $userList = json_decode($userList, true);
        if (!isset($userList) || !$userList) {
            $userList = [];
        }

        return $userList;
    }

    /**
     * Login WordPress User Meta
     *
     * @param string $user_id
     * @param string $meta_key
     * @param string $meta_value
     * @param boolean $unique
     *
     * @return void
     */
    private function setUserMeta($user_id, $meta_key, $meta_value, $unique = false)
    {
        $hasMeta = get_user_meta($user_id, $meta_key, true);
        if (isset($hasMeta)) {
            update_user_meta($user_id, $meta_key, $meta_value);
        } else {
            add_user_meta($user_id, $meta_value, $meta_value, $unique);
        }
    }

    /**
     * Get Random Hex
     *
     * @param int $length
     *
     * @return string
     */
    private function getRandomHex($length)
    {
        $result = '';
        $moduleLength = 40;   // we use sha1, so module is 40 chars
        $steps = round(($length/$moduleLength) + 0.5);

        for( $i=0; $i<$steps; $i++ ) {
            $result .= sha1( uniqid() . md5( rand() . uniqid() ) );
        }

        return substr( $result, 0, $length );
    }

    /**
     * Check if an option exists in WP_Options table
     *
     * @param string $name
     * @param boolean $site_wide
     *
     * @return object
     */
    private function option_exists($name, $site_wide = false) {
        global $wpdb;
        return $wpdb->query("SELECT * FROM ". ($site_wide ? $wpdb->base_prefix : $wpdb->prefix). "options WHERE option_name ='$name' LIMIT 1");
    }
}