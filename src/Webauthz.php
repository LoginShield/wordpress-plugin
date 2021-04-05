<?php


class Webauthz
{

	/**
	 * The client name to report to the authorization server
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $client_name    The client name
	 */
	private $client_name;

	/**
	 * The client version to report to the authorization server
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $client_version    The client version
	 */
	private $client_version;

    /**
     * Create a new Webauthz Instance
     */
    public function __construct()
    {
		$this->client_name = "LoginShield for WordPress";
		$this->client_version = LOGINSHIELD_VERSION;
    }

    /**
     * Verify RealmInfo
     *
     */
    public function verifyRealmInfo()
    {
        try {
            $this->fetchWebAuth();

            $this->fetchWebAuthzConfig();

            $this->registerClient();

            $data = $this->requestAccess();

            return (object) array(
                'status'=> 'success',
                'payload'=> $data
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage()
            );
        }
    }

    /**
     * Exchange Grant Token to Access Token
     *
     * @param $grantToken
     * @return object
     */
    public function getAccessToken($grantToken)
    {
        try {
            $client_token = get_option( 'loginshield_client_token' );

            $requestInfo = array(
                'grant_token' => $grantToken
            );

            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $client_token,
                    'Content-Type'  => 'application/json',
                ),
                'method'    => 'POST',
                'body'      => json_encode($requestInfo),
                'sslverify' => true,
            );

            $webauthz_exchange_uri = get_option( 'loginshield_webauthz_exchange_uri' );
            $response = wp_remote_post( $webauthz_exchange_uri , $args );
            $responseObj = wp_remote_retrieve_body($response);

            $responseObj = json_decode($responseObj);

            $access_token = $responseObj->access_token;
            $refresh_token = $responseObj->refresh_token;

            update_option( 'loginshield_access_token', $access_token );
            update_option( 'loginshield_refresh_token', $refresh_token );

            update_option( 'loginshield_authorization_token', $access_token );

            return (object) array(
                'status'    => 'success',
                'payload'   => $access_token,
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage()
            );
        }
    }

    /**
     * Fetch WebAuth Header
     *
     * @return string value of WWW-Authenticate header (may be empty if header was not found)
     */
    private function fetchWebAuth()
    {
        $url = 'https://loginshield.com/service/account/realm';
        $url = add_query_arg( 'uri', get_site_url(), $url );
        
        $response = wp_remote_get($url);
        $wwwAuthenticate = wp_remote_retrieve_header( $response, 'WWW-Authenticate' );
        
        $csv = '';
        if ($this->startsWith(strtolower($wwwAuthenticate), 'webauthz ')) {
            $csv = substr($wwwAuthenticate, strlen('webauthz '));
        } elseif ($this->startsWith(strtolower($wwwAuthenticate), 'bearer ')) {
            $csv = substr($wwwAuthenticate, strlen('bearer '));
        } else {
            return $wwwAuthenticate;
        }

        $realmInfo = array();

        $wwwAuthenticateInfo = explode(', ', $csv);
        foreach($wwwAuthenticateInfo as $info) {
            $kvpair = explode('=', $info);
            $key = $kvpair[0];
            $value = urldecode($kvpair[1]);
            $realmInfo[$key] = $value;
        }

        $realm = $realmInfo['realm'];
        $scope = $realmInfo['scope'];
        $path = $realmInfo['path'];
        $webauthz_discovery_uri = $realmInfo['webauthz_discovery_uri'];

        update_option( 'loginshield_realm', $realm );
        update_option( 'loginshield_scope', $scope );
        update_option( 'loginshield_path', $path );
        update_option( 'loginshield_webauthz_discovery_uri', $webauthz_discovery_uri );

        return $wwwAuthenticate;
    }

    /**
     * Fetch WebAuthz Config
     *
     * @return mixed
     */
    private function fetchWebAuthzConfig()
    {
        $webauthz_discovery_uri = get_option( 'loginshield_webauthz_discovery_uri' );

        $response = wp_remote_get($webauthz_discovery_uri);
        $webauthzConfig = wp_remote_retrieve_body($response);

        $webauthzConfig = json_decode($webauthzConfig);

        // Get specific uris
        $webauthz_register_uri = $webauthzConfig->webauthz_register_uri;
        $webauthz_request_uri = $webauthzConfig->webauthz_request_uri;
        $webauthz_exchange_uri = $webauthzConfig->webauthz_exchange_uri;

        // Store $webauthz_register_uri, $webauthz_request_uri, $webauthz_exchange_uri into the database
        update_option( 'loginshield_webauthz_register_uri', $webauthz_register_uri );
        update_option( 'loginshield_webauthz_request_uri', $webauthz_request_uri );
        update_option( 'loginshield_webauthz_exchange_uri', $webauthz_exchange_uri );

        return $webauthzConfig;
    }

    /**
     * Register Client
     *
     * @return mixed
     */
    private function registerClient()
    {
        $client_name = get_bloginfo('name');
        $client_version = $this->client_name . " v" . $this->client_version;
        $grant_redirect_uri = admin_url( '/options-general.php?page=loginshield' );

        $requestInfo = array(
            'client_name' => $client_name,
            'client_version' => $client_version,
            'grant_redirect_uri' => $grant_redirect_uri
        );

        // Register client
        $args = array(
            'headers' => array(
                'Content-Type'  => 'application/json',
            ),
            'method'    => 'POST',
            'body'      => json_encode($requestInfo),
            'sslverify' => true,
        );

        $webauthz_register_uri = get_option( 'loginshield_webauthz_register_uri' );

        $response = wp_remote_post( $webauthz_register_uri , $args );
        $clientInfoJson = wp_remote_retrieve_body($response);

        $clientInfo = json_decode($clientInfoJson);

        $client_id = $clientInfo->client_id;
        $client_token = $clientInfo->client_token;

        update_option( 'loginshield_client_id', $client_id );
        update_option( 'loginshield_client_token', $client_token );

        return $clientInfo;
    }

    /**
     * Request Web Authorization
     *
     * @return mixed
     */
    private function requestAccess()
    {
        $realm = get_option( 'loginshield_realm' );
        $scope = get_option( 'loginshield_scope' );
        $client_state = $this->generateRandomString();
        $client_token = get_option( 'loginshield_client_token' );
        $webauthz_request_uri = get_option( 'loginshield_webauthz_request_uri' );

        // Store client state
        update_option( 'loginshield_client_state', $client_state );

        $requestInfo = array(
            'realm' => $realm,
            'scope' => $scope,
            'client_state' => $client_state
        );

        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $client_token,
                'Content-Type'  => 'application/json',
            ),
            'method'    => 'POST',
            'body'      => json_encode($requestInfo),
            'sslverify' => true,
        );
        $response = wp_remote_post( $webauthz_request_uri , $args );
        $redirectInfoJson = wp_remote_retrieve_body($response);
        $redirectInfo = json_decode($redirectInfoJson);

        return $redirectInfo;
    }

    public function exchangeToken($type, $token)
    {
        try {
            $webauthz_exchange_uri = get_option( 'loginshield_webauthz_exchange_uri' );
            $client_token = get_option( 'loginshield_client_token' );

            if ($type === 'grant') {
                $requestInfo = array(
                    'grant_token' => $token
                );
            } else if ($type === 'refresh') {
                $requestInfo = array(
                    'refresh_token' => $token
                );
            } else {
                return null;
            }

            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $client_token,
                    'Content-Type'  => 'application/json',
                ),
                'method'    => 'POST',
                'body'      => json_encode($requestInfo),
                'sslverify' => true,
            );
            $response = wp_remote_post( $webauthz_exchange_uri , $args );
            $tokenInfoJson = wp_remote_retrieve_body($response);
            $tokenInfo = json_decode($tokenInfoJson);

            return (object) array(
                'status'    => 'success',
                'payload'   => $tokenInfo,
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage()
            );
        }
    }

    /**
     * Fetch Realm Id
     *
     * @param $accessToken
     * @return object
     */
    public function fetchRealmId($accessToken) {
        try {
            $uri = "https://loginshield.com/service/realm?uri=" . get_site_url();
            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $accessToken,
                    'Content-Type'  => 'application/json',
                ),
                'method'    => 'GET',
                'sslverify' => true,
            );
            $response = wp_remote_get( $uri , $args );

            $realmInfoJson = wp_remote_retrieve_body($response);
            $realmInfo = json_decode($realmInfoJson);

            if ($realmInfo->id) {
                update_option( 'loginshield_realm_id', $realmInfo->id );
            }

            return (object) array(
                'status'    => 'success',
                'payload'   => $realmInfo,
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => 'fetch-failed',
                'message'   => $exception->getMessage()
            );
        }
    }

    /**
     * Get random string (Code generation)
     *
     */
    private function generateRandomString($length = 16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    
    /**
     * A utility to check if a string starts with a sub string or not
     *
     * @param string $haystack     Resource String
     * @param string $needle       Target Sub String
     *
     * @return mixed
     */
    private function startsWith( $haystack, $needle ) {
        $length = strlen( $needle );
        return substr( $haystack, 0, $length ) === $needle;
    }    
}