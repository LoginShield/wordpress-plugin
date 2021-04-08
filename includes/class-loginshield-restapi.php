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

        register_rest_route( $this->plugin_name, '/account/reset', array(
            'methods'  => 'POST',
            'callback' => array($this, 'resetAccount')
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

        register_rest_route( $this->plugin_name, '/token/verify', array(
            'methods'  => 'POST',
            'callback' => array($this, 'verifyToken')
        ));

        register_rest_route( $this->plugin_name, '/token/request', array(
            'methods'  => 'POST',
            'callback' => array($this, 'requestToken')
        ));

        register_rest_route( $this->plugin_name, '/token/exchange', array(
            'methods'  => 'POST',
            'callback' => array($this, 'exchangeToken')
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
            $isLoginShieldEnabled = get_user_meta($userId, 'loginshield_is_activated', true);

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


    public function verifyToken(WP_REST_Request $request) {
        try {
            $realmId = get_option('loginshield_realm_id');
            $accessToken = get_option('loginshield_access_token');

            if (!isset($accessToken) || $accessToken === "") {
                return new WP_REST_Response([
                    'error'      => 'no-access-token',
                    'message'    => 'Set up your free trial or manage your subscription.',
                ], 200);
            }

            if (!isset($realmId) || $realmId === "") {
                return new WP_REST_Response([
                    'error'      => 'no-realm-id',
                    'message'    => 'Set up your free trial or manage your subscription.',
                ], 200);
            }

            $webauthz = new Webauthz();
            $response = $webauthz->fetchRealmId($accessToken);

            if ($response->error || !isset($response->payload)) {
                return new WP_REST_Response([
                    'error'      => 'no-access-token',
                    'message'    => 'Set up your free trial or manage your subscription.',
                ], 200);
            }

            if ($response->payload->id) {
                $realmId = $response->payload->id;
                update_option('loginshield_realm_id', $realmId);

                return new WP_REST_Response([
                    'status'    => 'success',
                    'message'   => 'You are ready to use LoginShield.',
                ], 200);
            }

            return new WP_REST_Response([
                'error'      => 'unknown-issue',
                'message'    => 'Set up your free trial or manage your subscription.',
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'      => 'failed-check-token',
                'message'    => 'Service is unavailable. Please contact admin.',
            ], 500);
        }
    }


    /**
     * Initialize Admin Setting
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function requestToken(WP_REST_Request $request) {
        try {
            $client_id = isset($_REQUEST['client_id']) ? $_REQUEST['client_id'] : '';
            $client_token = isset($_REQUEST['redirect_to']) ? $_REQUEST['client_token'] : '';
            $grant_token = isset($_REQUEST['grant_token']) ? $_REQUEST['grant_token'] : '';

            if ($client_id !== '' && $client_token !== '' && $grant_token !== '') {
                $accessToken = $this->getAccessToken($grant_token);
                if ($accessToken) {
                    return new WP_REST_Response([
                        'status'     => 'success',
                    ], 200);
                } else {
                    return new WP_REST_Response([
                        'error'      => 'invalid-credentials',
                        'message'    => 'Invalid Credentials',
                    ], 200);
                }
            } else {
                $response = $this->verifyRealmInfo();
                if ($response->error) {
                    return new WP_REST_Response([
                        'error'      => $response->error,
                        'response'   => $response->payload,
                    ], 200);
                }
                return new WP_REST_Response([
                    'status'    => 'success',
                    'payload'   => $response->payload,
                ], 200);
            }
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'initialization-failed',
                'message'   => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Get Access Token
     *
     * @param $grantToken
     * @return WP_REST_Response
     */
    private function getAccessToken($grantToken)
    {
        try {
            $webauthz = new Webauthz();

            $response = $webauthz->getAccessToken();

            if ($response->error) {
                return new WP_REST_Response([
                    'error'     => $response->error,
                    'message'   => $response->message,
                ], 200);
            }

            return new WP_REST_Response([
                'status'    => 'success',
                'payload'   => $response->payload,
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage(),
            ], 500);
        }
    }


    /**
     * Verify Realm Info
     *
     * @return object
     */
    private function verifyRealmInfo()
    {
        try {
            $webauthz = new Webauthz();

            $response = $webauthz->verifyRealmInfo();

            if ($response->error) {
                return (object) array(
                    'error'      => $response->error,
                    'message'    => $response->message,
                );
            }

            return (object) array(
                'status'    => 'success',
                'payload'   => $response->payload,
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage(),
            );
        }
    }


    /**
     * Exchange Token
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function exchangeToken(WP_REST_Request $request) {
        try {
            $clientId = $request->get_param('client_id');
            $clientState = $request->get_param('client_state');
            $grantToken = $request->get_param('grant_token');

            $refresh = $request->get_param('refresh');
            $refreshToken = $request->get_param('refresh_token');

            $storedClientId = get_option( 'loginshield_client_id' );
            if ($clientId != $storedClientId) {
                return new WP_REST_Response([
                    'error'    => 'not-found',
                    'message'  => 'Exchange: client id does not match stored client id',
                ], 400);
            }

            $storedClientState = get_option( 'loginshield_client_state' );
            if ($clientState != $storedClientState) {
                return new WP_REST_Response([
                    'error'    => 'not-found',
                    'message'  => 'Exchange: client state does not match stored client state',
                ], 400);
            }

            $webauthz = new Webauthz();
            if ($grantToken) {
                $response = $webauthz->exchangeToken('grant', $grantToken);
            } else if ($refresh && $refreshToken) {
                $response = $webauthz->exchangeToken('refresh', $refreshToken);
            } else {
                return new WP_REST_Response([
                    'error'    => 'invalid-request',
                    'message'  => 'Exchange: input grant_token or stored refresh_token is required',
                ], 400);
            }

            if ($response->error) {
                return new WP_REST_Response([
                    'error'      => $response->error,
                    'message'    => $response->message,
                ], 400);
            }

            $payload = $response->payload;
            if ($payload->fault) {
                return new WP_REST_Response([
                    'error'      => 'access-denied',
                    'message'    => $payload->fault->type,
                ], 400);
            }

            $accessToken = $payload->access_token;
            $accessTokenMaxSeconds = $payload->access_token_max_seconds;
            $refreshToken = $payload->refresh_token;
            $refreshTokenMaxSeconds = $payload->refresh_token_max_seconds;

            if (!isset($accessToken) || $accessToken === "") {
                return new WP_REST_Response([
                    'error'      => 'access-denied',
                    'message'    => 'Exchange: no access token in response',
                ], 400);
            }

            update_option('loginshield_access_token', $accessToken);
            update_option('loginshield_access_token_max_seconds', $accessTokenMaxSeconds);
            update_option('loginshield_refresh_token', $refreshToken);
            update_option('loginshield_refresh_token_max_seconds', $refreshTokenMaxSeconds);

            update_option('loginshield_authorization_token', $accessToken);

            $webauthz = new Webauthz();
            $response = $webauthz->fetchRealmId($accessToken);

            if ($response->error || !isset($response->payload)) {
                return new WP_REST_Response([
                    'error'      => 'no-access-token',
                    'message'    => 'Set up your free trial or manage your subscription.',
                ], 200);
            }

            if ($response->payload->id) {
                $realmId = $response->payload->id;
                update_option('loginshield_realm_id', $realmId);

                return new WP_REST_Response([
                    'status'        => 'granted',
                    'access_token'  => $accessToken,
                    'realm_id'      => $realmId,
                ], 200);
            }

            return new WP_REST_Response([
                'error'         => 'unknown-issue',
                'access_token'  => 'Set up your free trial or manage your subscription.',
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'login-failed',
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
            $redirectTo = $request->get_param('redirectTo'); // optional, for normal login only (not for activation)

            if ($mode === 'activate-loginshield') {
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;
                $isActivated = get_user_meta($user_id, 'loginshield_is_activated', true);
                $loginshieldUserId = get_user_meta($user_id, 'loginshield_user_id', true);

                if ($isActivated && $loginshieldUserId) {
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
                        'isAuthenticated'    => false,
                    ], 200);
                }
                if ($verifyLoginResponse->realmId == $this->loginshield_realm_id) {
                    $user_id = $this->findUserIdByLoginShieldUserId($verifyLoginResponse->realmScopedUserId);
                    if ($user_id) {
                        $isActivated = get_user_meta($user_id, 'loginshield_is_activated', true);
                        if (!$isActivated) {
                            $this->setUserMeta($user_id, 'loginshield_is_activated', true, true);
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
                $isActivated = get_user_meta($userId, 'loginshield_is_activated', true);
                $loginshieldUserId = get_user_meta($userId, 'loginshield_user_id', true);
                
                $login_page_id = get_option( 'loginshield_login_page' );
                $login_url = get_permalink( $login_page_id );
                $login_url = add_query_arg( 'mode', 'resume-loginshield', $login_url );
                $login_url = add_query_arg( 't', time(), $login_url );
                if ($redirectTo) {
                    $login_url = add_query_arg( 'redirect_to', $redirectTo, $login_url );
                }

                if ($isActivated && $loginshieldUserId) {
                    $loginshield = new RealmClient($this->loginshield_endpoint_url, $this->loginshield_realm_id, $this->loginshield_authorization_token);
                    $startLoginResponse = $loginshield->startLogin($loginshieldUserId, $login_url);
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

            return new WP_REST_Response([
                'error'    => 'edit-account-failed',
                'message'    => 'Bad Request'
            ], 400);

        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'edit-account-failed',
                'message'   => $exception->getMessage()
            ], 500);
        }
    }

    /**
     * Reset Account (admin feature)
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function resetAccount(WP_REST_Request $request)
    {
        try {
            $user_id = $request->get_param('user_id');
            
            if (!isset($user_id) || $user_id == '') {
                return new WP_REST_Response([
                    'error'    => 'reset-account-failed',
                    'message'    => 'Bad Request'
                ], 400);
            }

            if (!current_user_can( 'edit_user', $user_id )) {
                return new WP_REST_Response([
                    'error'    => 'reset-account-failed',
                    'message'    => 'Forbidden'
                ], 403);
            }
            
            // delete the user registration via LoginShield API
            $loginshield_user_id = get_user_meta($user_id, 'loginshield_user_id', true);
            $isDeletedFromAuthenticationServer = false;
            if ($loginshield_user_id) {
                $loginshield = new RealmClient($this->loginshield_endpoint_url, $this->loginshield_realm_id, $this->loginshield_authorization_token);
                $deleteUserResponse = $loginshield->deleteRealmUser($loginshieldUserId);
                $isDeletedFromAuthenticationServer = $deleteUserResponse->isDeleted;
            }
            
            delete_user_meta($user_id, 'loginshield_is_registered');
            delete_user_meta($user_id, 'loginshield_is_confirmed');
            delete_user_meta($user_id, 'loginshield_is_activated');
            delete_user_meta($user_id, 'loginshield_user_id');

            return new WP_REST_Response([
                'isEdited' => true,
                'isDeleted' => $isDeletedFromAuthenticationServer
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
            $realmScopedUserId = $this->generateRandomId(16);
            
            // make sure no other user is already assigned the same realm scoped user id
            while ( !is_null( $this->findUserIdByLoginShieldUserId($realmScopedUserId) ) ) {
                $realmScopedUserId = $this->generateRandomId(16);
            }

            $response = $loginshield->createRealmUser($realmScopedUserId, $user_name, $user_email, true);
            if ($response->error) {
                return new WP_REST_Response([
                    'error'     => 'registration failed',
                    'isEdited' => false
                ], 500);
            }

            if ($response->isCreated) {
                $this->setUserMeta($user_id, 'loginshield_is_activated', false, true);
                $this->setUserMeta($user_id, 'loginshield_is_registered', true, true);
                $this->setUserMeta($user_id, 'loginshield_is_confirmed', false, true);
                $this->setUserMeta($user_id, 'loginshield_user_id', $realmScopedUserId, true);

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
            $isActive = $request->get_param('isActive');
            if (!isset($isActive)) {
                return new WP_REST_Response([
                    'error'     => 'update failed',
                    'message'   => 'missing parameter'
                ], 400);
            }

            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            
            $isRegistered = get_user_meta($user_id, 'loginshield_is_registered', true);
            $isConfirmed = get_user_meta($user_id, 'loginshield_is_confirmed', true);
            
            if ($isRegistered && $isConfirmed) {
                $this->setUserMeta($user_id, 'loginshield_is_activated', $isActive, true);
                return new WP_REST_Response([
                    'isActive'     => $isActive
                ], 200);
            } else {
                $this->setUserMeta($user_id, 'loginshield_is_activated', false, true);
                return new WP_REST_Response([
                    'isActive'     => false,
                    'error'        => 'Must complete registration to activate'
                ], 200);
            }

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
        $user = wp_signon( $creds );

        if (is_wp_error($user))
            return false;

        wp_set_current_user ( $user->ID );
        wp_set_auth_cookie  ( $user->ID );

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
     * Find a WordPress user id for the specified LoginShield realm-scoped user id
     *
     * @param string $realmScopedUserId
     *
     * @return string
     */
    private function findUserIdByLoginShieldUserId($realmScopedUserId)
    {
        
        $args  = array(
            'meta_key' => 'loginshield_user_id',
            'meta_value' => $realmScopedUserId,
            'meta_compare' => '=' // exact match only
        );
        
        $query = new WP_User_Query( $args );
        
        $users = $query->get_results();
        
        if (isset($users) && count($users) == 1) {
            return $users[0]->ID;
        }

        return null;
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
    private function generateRandomId($length = 16) {
        $characters = '0123456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
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