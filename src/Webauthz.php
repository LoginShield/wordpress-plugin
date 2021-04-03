<?php


class Webauthz
{

    /**
     * Create a new Webauthz Instance
     */
    public function __construct()
    {
        //
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

            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $client_token,
                    'Content-Type'  => 'application/json',
                ),
                'method'    => 'POST',
                'body'      => '{"grant_token": "' . $grantToken . '"}',
                'sslverify' => false,
            );

            $webauthz_exchange_uri = get_option( 'loginshield_webauthz_exchange_uri' );
            $response = wp_remote_post( $webauthz_exchange_uri , $args );
            $responseObj = wp_remote_retrieve_body($response);

            $responseObj = json_decode($responseObj);

            $access_token = $responseObj->access_token;
            $refresh_token = $responseObj->refresh_token;

            update_option( 'loginshield_access_token', $access_token );
            update_option( 'loginshield_refresh_token', $refresh_token );

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
     * @return false|string
     */
    private function fetchWebAuth()
    {
        $url = 'https://loginshield.com/service/account/realm?uri=' . get_site_url();
        $response = wp_remote_get($url);
        $wwwAuthenticate = wp_remote_retrieve_header( $response, 'WWW-Authenticate' );

        $wwwAuthenticate = substr($wwwAuthenticate, 7);
        $realmInfo = array();

        $wwwAuthenticateInfo = explode(', ', $wwwAuthenticate);
        foreach($wwwAuthenticateInfo as $info) {
            $line = explode('=', $info);
            $key = $line[0];
            $value = urldecode($line[1]);
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
        $client_name = "#admin";
        $client_version = "LoginShield for WordPress v1.0.0";
        $grant_redirect_uri = admin_url( '/options-general.php?page=loginshield' );

        // Register client
        $args = array(
            'headers' => array(
                'Content-Type'  => 'application/json',
            ),
            'method'    => 'POST',
            'body'      => '{"client_name": "' . $client_name . '", "client_version": "' . $client_version . '", "grant_redirect_uri": "' . $grant_redirect_uri . '"}',
            'sslverify' => false,
        );

        $webauthz_register_uri = get_option( 'loginshield_webauthz_register_uri' );

        $response = wp_remote_post( $webauthz_register_uri , $args );
        $client = wp_remote_retrieve_body($response);

        $client = json_decode($client);

        $client_id = $client->client_id;
        $client_token = $client->client_token;

        update_option( 'loginshield_client_id', $client_id );
        update_option( 'loginshield_client_token', $client_token );

        return $client;
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

        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $client_token,
                'Content-Type'  => 'application/json',
            ),
            'method'    => 'POST',
            'body'      => '{"realm":"' . $realm . '", "scope":"' . $scope . '", "client_state": "' . $client_state . '"}',
            'sslverify' => false,
        );
        $response = wp_remote_post( $webauthz_request_uri , $args );
        $redirectObj = wp_remote_retrieve_body($response);
        $redirectObj = json_decode($redirectObj);

        return $redirectObj;
    }

    public function exchangeToken($type, $token)
    {
        try {
            $webauthz_exchange_uri = get_option( 'loginshield_webauthz_exchange_uri' );
            $client_token = get_option( 'loginshield_client_token' );

            if ($type === 'grant') {
                $body = '{"grant_token":"' . $token . '"}';
            } else if ($type === 'refresh') {
                $body = '{"refresh_token":"' . $token . '"}';
            } else {
                return null;
            }

            $args = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $client_token,
                    'Content-Type'  => 'application/json',
                ),
                'method'    => 'POST',
                'body'      => $body,
                'sslverify' => false,
            );
            $response = wp_remote_post( $webauthz_exchange_uri , $args );
            $tokenObj = wp_remote_retrieve_body($response);
            $tokenObj = json_decode($tokenObj);

            return (object) array(
                'status'    => 'success',
                'payload'   => $tokenObj,
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
}