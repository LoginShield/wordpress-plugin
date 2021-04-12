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
	 * The display name of this plugin.
	 *
	 * @since    1.0.8
	 * @access   private
	 * @var      string    $plugin_display_name    The display name of this plugin.
	 */
	private $plugin_display_name;

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
     * Webauthz client instance
     *
     * @since    1.0.8
     * @access   private
     * @var      string    $webauthz    Webauthz client instance
     */
    private $webauthz;

	/**
	 * The webauthz discovery URI
	 *
	 * @since    1.0.8
	 * @access   private
	 * @var      string    $webauthz_discovery_uri    webauthz discovery URI
	 */
	private $webauthz_discovery_uri;

	/**
	 * The webauthz registration URI
	 *
	 * @since    1.0.8
	 * @access   private
	 * @var      string    $webauthz_register_uri    webauthz registration URI
	 */
	private $webauthz_register_uri;

	/**
	 * The webauthz request URI
	 *
	 * @since    1.0.8
	 * @access   private
	 * @var      string    $webauthz_request_uri    webauthz request URI
	 */
	private $webauthz_request_uri;

	/**
	 * The webauthz exchange URI
	 *
	 * @since    1.0.8
	 * @access   private
	 * @var      string    $webauthz_exchange_uri    webauthz exchange URI
	 */
	private $webauthz_exchange_uri;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version, $plugin_display_name )
    {
        $this->plugin_name = $plugin_name;
        $this->plugin_display_name = $plugin_display_name;
        $this->version = $version;

        $this->endpoint_url = get_home_url();
        $this->loginshield_endpoint_url = 'https://loginshield.com';
        $this->loginshield_realm_id = get_option('loginshield_realm_id');
        $this->loginshield_authorization_token = get_option('loginshield_access_token');
        
        $this->webauthz = new Webauthz($plugin_display_name, $version);

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

        register_rest_route( $this->plugin_name, '/realm/status', array(
            'methods'  => 'POST',
            'callback' => array($this, 'checkRealmStatus')
        ));

        register_rest_route( $this->plugin_name, '/webauthz/start', array(
            'methods'  => 'POST',
            'callback' => array($this, 'webauthzStartAccessRequest')
        ));

        register_rest_route( $this->plugin_name, '/webauthz/exchange', array(
            'methods'  => 'POST',
            'callback' => array($this, 'webauthzExchangeToken')
        ));
    }
    
    
	/**
	 * The webauthz discovery URI
	 *
	 * @since    1.0.8
	 * @access   private
	 */
	private function get_webauthz_discovery_uri() {
        if (!isset($this->webauthz_discovery_uri) || $this->webauthz_discovery_uri === '') {
            $this->webauthz_discovery_uri = get_option('loginshield_webauthz_discovery_uri');
        }
        return $this->webauthz_discovery_uri;
    }

	private function set_webauthz_discovery_uri($webauthz_discovery_uri) {
        $this->webauthz_discovery_uri = $webauthz_discovery_uri;
        update_option('loginshield_webauthz_discovery_uri', $webauthz_discovery_uri);
    }

	/**
	 * The webauthz registration URI
	 *
	 * @since    1.0.8
	 * @access   private
	 */
	private function get_webauthz_register_uri() {
        if (!isset($this->webauthz_register_uri) || $this->webauthz_register_uri === '') {
            $this->webauthz_register_uri = get_option('loginshield_webauthz_register_uri');
        }
        return $this->webauthz_register_uri;
    }
    
	private function set_webauthz_register_uri($webauthz_register_uri) {
        $this->webauthz_register_uri = $webauthz_register_uri;
        update_option('loginshield_webauthz_register_uri', $webauthz_register_uri);
    }
    
	/**
	 * The webauthz request URI
	 *
	 * @since    1.0.8
	 * @access   private
	 */
	private function get_webauthz_request_uri() {
        if (!isset($this->webauthz_request_uri) || $this->webauthz_request_uri === '') {
            $this->webauthz_request_uri = get_option('loginshield_webauthz_request_uri');
        }
        return $this->webauthz_request_uri;
    }
    
	private function set_webauthz_request_uri($webauthz_request_uri) {
        $this->webauthz_request_uri = $webauthz_request_uri;
        update_option('loginshield_webauthz_request_uri', $webauthz_request_uri);
    }

	/**
	 * The webauthz exchange URI
	 *
	 * @since    1.0.8
	 * @access   private
	 * @var      string    $webauthz_exchange_uri    webauthz exchange URI
	 */
	private function get_webauthz_exchange_uri() {
        if (!isset($this->webauthz_exchange_uri) || $this->webauthz_exchange_uri === '') {
            $this->webauthz_exchange_uri = get_option('loginshield_webauthz_exchange_uri');
        }
        return $this->webauthz_exchange_uri;
    }
    
	private function set_webauthz_exchange_uri($webauthz_exchange_uri) {
        $this->webauthz_exchange_uri = $webauthz_exchange_uri;
        update_option('loginshield_webauthz_exchange_uri', $webauthz_exchange_uri);
    }

    /**
     * Check if user has LoginShield enabled by Login information.
     *
     * This API is used by the login activity, so the user does NOT have
     * to be authenticated to get this information. The function returns
     * 'isActivated' value of `true` if the username exists and
     * has activated LoginShield, and `false` otherwise.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function checkUserWithLogin(WP_REST_Request $request) {
        try {            
            $login = $request->get_param('login');
            if (!isset($login) || $login === '') {
                return new WP_REST_Response([
                    'error'     => 'Bad request',
                    'message'   => $exception->getMessage(),
                ], 400);
            }

            $user = get_user_by('login', sanitize_user($login));

            if (!$user) {
                return new WP_REST_Response([
                    'isActivated'  => false,
                ], 200);
            }

            $userId = $user->get_ID() ? $user->get_ID() : $user->data->ID;
            $isActivated = $this->get_boolean_user_meta($userId, 'loginshield_is_activated');
            return new WP_REST_Response([
                'isActivated'      => $isActivated,
            ], 200);
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage(),
            ], 500);
        }
    }


    /**
     * Check if the site is configured with a LoginShield authentication
     * realm and is able to use it. This API requires the administrator
     * capability 'manage_options'.
     *
     * Possible result states:
     *
     * 1. site is not configured with realm id or credentials; administrator
     *    can request access to an authentication realm using webauthz
     *    (see start webauthz access request)
     * 2. site is configured with realm id and credentials, but does not yet
     *    have permission to manage the authentication realm because domain
     *    verification is required; administrator can verify control of the
     *    domain using the automated or manual procedure
     * 3. site is configured with realm id and credentials, but cannot manage
     *    the authentication realm because payment is required (either the free
     *    trial expired, or the subscription was suspended due to non-payment,
     *    or the subscription was cancelled); the administrator can visit
     *    loginshield.com to update payment information and restore the subscription
     * 4. site is configured with realm id and credentials, and is able to
     *    manage the authentication realm; users can now activate LoginShield
     *    on their accounts and use it to log in
     *
     */
    public function checkRealmStatus(WP_REST_Request $request) {
        try {
            if (!is_user_logged_in()) {
                return new WP_REST_Response([
                    'error'     => 'Unauthorized'
                ], 401);
            }
            
            $user = wp_get_current_user();
            if (!user_can($user, 'manage_options')) {
                return new WP_REST_Response([
                    'error'     => 'Forbidden',
                    'isEdited' => false
                ], 403);
            }
            
            $accessToken = get_option('loginshield_access_token');
            
            if (empty($accessToken)) {
                return new WP_REST_Response([
                    'error'      => 'no-access-token',
                    'message'    => 'Set up your free trial or manage your subscription.',
                ], 200);
            }
            
            $response = null;
            
            // after obtaining access via webauthz, we'll have a client_id and client_token but
            // no realm info yet; so if we don't have a realm id, try to find it by url and then
            // store the realm id
            $realmId = get_option('loginshield_realm_id');
            if (isset($realmId) && $realmId) {
                $response = $this->fetchRealmInfoById($realmId, $accessToken);
            } else {
                $response = $this->fetchRealmInfoByURL(get_site_url(), $accessToken);
            }

            if ($response->error || !isset($response->payload)) {
                return new WP_REST_Response([
                    'error'      => $response->error,
                    'message'    => 'Set up your free trial or manage your subscription.',
                ], 200);
            }

            if ($response->payload->id) {
                if ($realmId !== $response->payload->id) {
                    $realmId = $response->payload->id;
                    update_option('loginshield_realm_id', $realmId);
                }

                return new WP_REST_Response([
                    'status'    => 'success',
                    'message'   => 'You are ready to use LoginShield.',
                    'realmId'   => $realmId, // to update the UI after initial setup without reloading the page
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
     * Start a Webauthz access request so we can manage an authentication realm
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function webauthzStartAccessRequest(WP_REST_Request $request) {
        try {
            $response = $this->fetchRealmInfoByURL(get_site_url());
            
            if ($response->error && $response->isWebauthz) {
                $webauthz = $this->webauthz;
                
                $webauthzConfigResponse = $webauthz->fetchWebAuthzConfig($this->get_webauthz_discovery_uri());
                
                if ($webauthzConfigResponse->error) {
                    error_log('LoginShield webauthz fetchWebAuthzConfig failed');
                    return new WP_REST_Response([
                        'error'      => $response->error,
                        'message'    => 'Service not available',
                    ], 200);
                }                
                
                $webauthz_config = $webauthzConfigResponse->payload;
                
                // Get specific uris
                $webauthz_register_uri = $webauthz_config->webauthz_register_uri;
                $webauthz_request_uri = $webauthz_config->webauthz_request_uri;
                $webauthz_exchange_uri = $webauthz_config->webauthz_exchange_uri;

                // Store $webauthz_register_uri, $webauthz_request_uri, $webauthz_exchange_uri
                $this->set_webauthz_register_uri($webauthz_register_uri);
                $this->set_webauthz_request_uri($webauthz_request_uri);
                $this->set_webauthz_exchange_uri($webauthz_exchange_uri);
                
                // prepare registration info
                $client_name = get_bloginfo('name');
                $client_version = $this->plugin_display_name . " v" . $this->version;
                $grant_redirect_uri = add_query_arg(
                    'page',
                    $this->plugin_name,
                    admin_url('/options-general.php')
                );
                
                // check if we already registered a client
                $client_id = get_option( 'loginshield_client_id' );
                $client_token = get_option( 'loginshield_client_token' );
                
                $clientInfoResponse = null;
                if (!isset($client_id) || $client_id === '' || !isset($client_token) || $client_token === '') {
                    // new registration
                    $clientInfoResponse = $webauthz->registerClient($webauthz_register_uri, $client_name, $client_version, $grant_redirect_uri);
                } else {
                    // update existing registration
                    $clientInfoResponse = $webauthz->registerClient($webauthz_register_uri, $client_name, $client_version, $grant_redirect_uri, $client_token);
                }
                
                if ($clientInfoResponse->error) {
                    error_log('LoginShield webauthz registerClient failed');
                    return new WP_REST_Response([
                        'error'      => $response->error,
                        'message'    => 'Service not available',
                    ], 200);
                }
                
                $clientInfo = $clientInfoResponse->payload;
                
                if (isset($clientInfo)) {
                    $client_id = $webauthz->sanitizeClientId( $clientInfo->client_id );
                    $client_token = $webauthz->sanitizeToken( $clientInfo->client_token );

                    if ($client_id) {
                        update_option( 'loginshield_client_id', $client_id );
                    }
                    if ($client_token) {
                        update_option( 'loginshield_client_token', $client_token );
                    }
                }                
                
                // start the access request with the realm and scope we obtained from the initial failed access in fetchRealmInfoByURL
                $realm = get_option( 'loginshield_realm' );
                $scope = get_option( 'loginshield_scope' );
                $client_state = $webauthz->generateRandomString();
                update_option( 'loginshield_client_state', $client_state );
                
                $request_info = array(
                    'realm' => $realm,
                    'scope' => $scope,
                    'client_state' => $client_state,
                );
                
                $requestAccessResponse = $webauthz->requestAccess($webauthz_request_uri, $request_info, $client_token);
                
                if ($requestAccessResponse->error) {
                    error_log('LoginShield webauthz requestAccess failed');
                    return new WP_REST_Response([
                        'error'      => $response->error,
                        'message'    => 'Service not available',
                    ], 200);
                }
                
                $requestAccessResult = $requestAccessResponse->payload;
                
                if ($requestAccessResult->redirect) {
                    return (object) array(
                        'status'=> 'success',
                        'payload'=> $requestAccessResult
                    );
                }
                
                return new WP_REST_Response([
                    'error'      => 'unknown-error',
                    'response'   => $requestAccessResponse->payload,
                ], 200);
                
            }
            
            return (object) array(
                'status'=> 'success',
                'payload'=> $response->payload
            );
            
        } catch (\Exception $exception) {
            return new WP_REST_Response([
                'error'     => 'initialization-failed',
                'message'   => $exception->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch realm info or Webauthz challenge when we already have a realm id
     * and access token.
     */
    private function fetchRealmInfoById($realmId, $accessToken)
    {
        $url = 'https://loginshield.com/service/realm';
        $url = add_query_arg( 'id', $realmId, $url );
        
        $args = $this->prepare_json_get($accessToken);        
        
        $response = wp_remote_get($url, $args);
        
        $responseInfo = $this->get_json_from_response($response);
        if ($responseInfo->error) {
            $webauthz = $this->webauthz;
            $webauthzInfo = $webauthz->checkResponseForWebauthz( $responseInfo->response );
            
            if ($webauthzInfo->isWebauthz === true) {
                update_option( 'loginshield_realm', $webauthzInfo->realm );
                update_option( 'loginshield_scope', $webauthzInfo->scope );
                update_option( 'loginshield_path', $webauthzInfo->path );
                update_option( 'loginshield_webauthz_discovery_uri', $webauthzInfo->webauthz_discovery_uri );
            }

            return (object) array(
                'error' => 'fetch-failed',
                'isWebauthz' => $webauthzInfo->isWebauthz,
                'response' => $responseInfo->response,
            );
        }
        
        return $responseInfo;
    }
    
    /**
     * Fetch realm info or Webauthz challenge when we already have a realm id
     * and access token.
     */
    private function fetchRealmInfoByURL($realmURL, $accessToken = '')
    {
        $url = 'https://loginshield.com/service/realm';
        $url = add_query_arg( 'uri', $realmURL, $url );
        
        $args = $this->prepare_json_get($accessToken);               
        
        $response = wp_remote_get($url, $args);
        
        $responseInfo = $this->get_json_from_response($response);
        if ($responseInfo->error) {
            $webauthz = $this->webauthz;
            $webauthzInfo = $webauthz->checkResponseForWebauthz( $responseInfo->response );
            
            if ($webauthzInfo->isWebauthz === true) {
                update_option( 'loginshield_realm', $webauthzInfo->realm );
                update_option( 'loginshield_scope', $webauthzInfo->scope );
                update_option( 'loginshield_path', $webauthzInfo->path );
                update_option( 'loginshield_webauthz_discovery_uri', $webauthzInfo->webauthz_discovery_uri );
            }

            return (object) array(
                'error' => 'fetch-failed',
                'isWebauthz' => $webauthzInfo->isWebauthz,
                'response' => $responseInfo->response,
            );
        }
        
        return $responseInfo;
    }

    /**
     * Exchange Token
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response
     */
    public function webauthzExchangeToken(WP_REST_Request $request) {
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

            $webauthz_exchange_uri = $this->get_webauthz_exchange_uri();
            $client_token = get_option( 'loginshield_client_token' );
            $webauthz = $this->webauthz;
            if ($grantToken) {
                $response = $webauthz->exchangeToken($webauthz_exchange_uri, 'grant', $grantToken, $client_token);
            } else if ($refresh && $refreshToken) {
                $response = $webauthz->exchangeToken($webauthz_exchange_uri, 'refresh', $refreshToken, $client_token);
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
            update_option('loginshield_access_token_not_after', time() + $accessTokenMaxSeconds);
            update_option('loginshield_refresh_token', $refreshToken);
            update_option('loginshield_refresh_token_not_after', time() + $refreshTokenMaxSeconds);

            return new WP_REST_Response([
                'status'         => 'success',
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
                
                if (!is_user_logged_in()) {
                    return new WP_REST_Response([
                        'error'     => 'Unauthorized'
                    ], 401);
                }
                
                $current_user = wp_get_current_user();
                $user_id = $current_user->ID;
                $isActivated = $this->get_boolean_user_meta($user_id, 'loginshield_is_activated');
                $loginshieldUserId = $this->get_string_user_meta($user_id, 'loginshield_user_id');

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
                        $isActivated = $this->get_boolean_user_meta($user_id, 'loginshield_is_activated');
                        if (!$isActivated) {
                            $this->set_boolean_user_meta($user_id, 'loginshield_is_activated', true);
                            $this->set_boolean_user_meta($user_id, 'loginshield_is_registered', true);
                            $this->set_boolean_user_meta($user_id, 'loginshield_is_confirmed', true);
                            $this->set_string_user_meta($user_id, 'loginshield_user_id', $verifyLoginResponse->realmScopedUserId);
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
                $isActivated = $this->get_boolean_user_meta($userId, 'loginshield_is_activated');
                $loginshieldUserId = $this->get_string_user_meta($userId, 'loginshield_user_id');
                
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
                return $this->activateLoginShieldForCurrentUser($request);
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
            $loginshield_user_id = $this->get_string_user_meta($user_id, 'loginshield_user_id');
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
     * Enable LoginShield for current user.
     *
     * All users are allowed to activate/deactivate LoginShield in their profile settings, so
     * we use current user id and do not check for permissions here.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function activateLoginShieldForCurrentUser(WP_REST_Request $request)
    {
        try {
            if (!is_user_logged_in()) {
                return new WP_REST_Response([
                    'error'     => 'Unauthorized',
                    'isEdited' => false
                ], 401);
            }
            
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $user_name = $current_user->user_login;
            $user_email = $current_user->user_email;

            $realmScopedUserId = $this->get_string_user_meta($user_id, 'loginshield_user_id');
            if ($realmScopedUserId) {
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
                $this->set_boolean_user_meta($user_id, 'loginshield_is_registered', true);
                $this->set_boolean_user_meta($user_id, 'loginshield_is_activated', false);
                $this->set_boolean_user_meta($user_id, 'loginshield_is_confirmed', false);
                $this->set_string_user_meta($user_id, 'loginshield_user_id', $realmScopedUserId);

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
            
            if (!is_user_logged_in()) {
                return new WP_REST_Response([
                    'error'     => 'Unauthorized'
                ], 401);
            }
            
            $isActive = $request->get_param('isActive');
            if (!isset($isActive)) {
                return new WP_REST_Response([
                    'error'     => 'update failed',
                    'message'   => 'missing parameter'
                ], 400);
            }
            
            $isActive = $isActive === true || $isActive === 'true' || $isActive === 'checked' ? true : false;

            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            
            $isRegistered = $this->get_boolean_user_meta($user_id, 'loginshield_is_registered');
            $isConfirmed = $this->get_boolean_user_meta($user_id, 'loginshield_is_confirmed');
            
            if ($isRegistered && $isConfirmed) {
                $this->set_boolean_user_meta($user_id, 'loginshield_is_activated', $isActive);
                return new WP_REST_Response([
                    'isActive'     => $isActive
                ], 200);
            } else {
                $this->set_boolean_user_meta($user_id, 'loginshield_is_activated', false);
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
    
    private function prepare_json_get($access_token = '') {
        $headers = array();
        $headers['Accept'] = 'application/json';
        if ($access_token) {
            $headers['Authorization'] = 'Bearer ' . $access_token;
        }
        
        $args = array(
            'headers' => $headers,
            'method'    => 'GET',
            'sslverify' => true,
        );
        return $args;
    }
    
    private function get_json_from_response($response) {
        $status = wp_remote_retrieve_response_code($response);
        $contentType = wp_remote_retrieve_header($response, 'content-type');
        $payload = (object) array();
        
        if ($contentType === 'application/json' || $this->startsWith($contentType, 'application/json;')) {
            $bodyJson = wp_remote_retrieve_body($response);
            $payload = json_decode($bodyJson);
        }
        
        if ($status === 200) {
            return (object) array('response' => $response, 'payload' => $payload);
        } else {
            return (object) array('response' => $response, 'payload' => $payload, 'error' => wp_remote_retrieve_response_message($response), 'http_status' => $status);
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
    
    /**
     * Retrieves the user meta key as a boolean; if it has a string value such as
     * 'true' or 'false', it is converted to a boolean value for the result.
     */
    private function get_boolean_user_meta($user_id, $key) {
        $value = get_user_meta($user_id, $key, true);
        return isset($value) && is_string($value) && filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
    
    /**
     * Updates the user meta key with a string value of either 'true' or 'false'.
     * If the input is a non-empty string with values OTHER THAN '0', 'false', 'off',
     * it will be stored as 'true'.
     */
    private function set_boolean_user_meta($user_id, $key, $value) {
        $sanitized = isset($value) ? $value : '';
        if (is_string($sanitized)) {
            $sanitized = filter_var($sanitized, FILTER_VALIDATE_BOOLEAN);
        }
        update_user_meta($user_id, $key, $sanitized ? 'true' : 'false');
    }
    
    /**
     * Retrieves the user meta key as a string
     */
    private function get_string_user_meta($user_id, $key) {
        $value = get_user_meta($user_id, $key, true);
        return isset($value) && is_string($value) ? $value : '';
    }
    
    /**
     * Updates the user meta key with a string value
     */
    private function set_string_user_meta($user_id, $key, $value) {
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
    
}