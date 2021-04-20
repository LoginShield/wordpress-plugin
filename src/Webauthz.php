<?php

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
 * Implementation of a Webauthz client for integration into applications to support
 * the Webauthz protocol for obtaining access to network resources controlled by the
 * user.
 *
 */
class Webauthz
{

	/**
	 * The client name to report to the authorization server
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $client_name    The client name
	 */
	public $client_name;

	/**
	 * The client version to report to the authorization server
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $client_version    The client version
	 */
	public $client_version;

    /**
     * Create a new Webauthz Instance
     */
    public function __construct($client_name, $client_version)
    {
		$this->client_name = $client_name;
		$this->client_version = $client_version;
    }
    
    /**
     * Remove characters that cannot be part of a client id
     */
    public function sanitizeClientId($input)
    {
        $sanitized = preg_replace( '/[^a-zA-Z0-9!@#$%^&*()+\/=?_{|}~\.:,;-]/', '', $input );
        return $sanitized;
    }
    
    /**
     * Remove characters that cannot be part of an access token, client token, grant token, or refresh token
     */
    public function sanitizeToken($input)
    {
        $sanitized = preg_replace( '/[^a-zA-Z0-9!@#$%^&*()+\/=?_{|}~\.:,;-]/', '', $input );
        return $sanitized;
    }

    /**
     * Fetch WebAuthz Config
     *
     * @return mixed
     */
    public function fetchWebAuthzConfig($webauthz_discovery_uri)
    {
        try {
            $args = $this->prepare_json_get();
            $response = wp_remote_get($webauthz_discovery_uri, $args);
            
            return $this->get_json_from_response($response);
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => $exception->getMessage(),
                'fault'     => $exception
            );
        }
    }

    /**
     * Register Client.
     *
     * @return mixed
     */
    public function registerClient($webauthz_register_uri, $client_name, $client_version, $grant_redirect_uri, $client_token = null)
    {
        try {
            $requestInfo = array(
                'client_name' => $client_name,
                'client_version' => $client_version,
                'grant_redirect_uri' => $grant_redirect_uri
            );

            $args = $this->prepare_json_post($requestInfo, $client_token);
            $response = wp_remote_post( $webauthz_register_uri , $args );
            return $this->get_json_from_response($response);
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => $exception->getMessage(),
                'fault'     => $exception
            );
        }
    }

    /**
     * Request Web Authorization
     *
     * $request_info is array('realm' => $realm, 'scope' => $scope, 'client_state' => $client_state)
     *
     * @return mixed
     */
    public function requestAccess($webauthz_request_uri, $request_info, $client_token)
    {
        try {
            $args = $this->prepare_json_post($request_info, $client_token);
            $response = wp_remote_post( $webauthz_request_uri, $args );
            return $this->get_json_from_response($response);
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => $exception->getMessage(),
                'fault'     => $exception
            );
        }
    }

    public function exchangeToken($webauthz_exchange_uri, $type, $exchange_token, $client_token)
    {
        try {
            if ($type === 'grant') {
                $requestInfo = array(
                    'grant_token' => $exchange_token
                );
            } else if ($type === 'refresh') {
                $requestInfo = array(
                    'refresh_token' => $exchange_token
                );
            } else {
                return null;
            }

            $args = $this->prepare_json_post($requestInfo, $client_token);
            $response = wp_remote_post( $webauthz_exchange_uri , $args );
            return $this->get_json_from_response($response);
        } catch (\Exception $exception) {
            return (object) array(
                'error'     => $exception->getMessage(),
                'fault'     => $exception
            );
        }
    }
    
    private function prepare_json_get($client_token = '') {
        $headers = array();
        $headers['Accept'] = 'application/json';
        if ($client_token) {
            $headers['Authorization'] = 'Bearer ' . $client_token;
        }
        
        $args = array(
            'headers' => $headers,
            'method'    => 'GET',
            'sslverify' => true,
        );
        return $args;
    }
    
    private function prepare_json_post($message, $client_token = '') {
        $headers = array();
        $headers['Accept'] = 'application/json';
        $headers['Content-Type'] = 'application/json';
        if ($client_token) {
            $headers['Authorization'] = 'Bearer ' . $client_token;
        }
        
        $args = array(
            'headers' => $headers,
            'method'    => 'POST',
            'body'      => json_encode($message),
            'sslverify' => true,
        );
        return $args;
    }
    
    private function get_json_from_response($response) {
        $status = wp_remote_retrieve_response_code($response);
        $contentType = wp_remote_retrieve_header($response, 'content-type');
        $payload = (object) array();
        
        if ($contentType === 'application/json' || str_starts_with($contentType, 'application/json;')) {
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
     * Check if an http response includes a Webauthz challenge.
     *
     * @param object $httpResponse      The result from wp_remote_get, wp_remote_post, etc.
     * @return array('isWebauthz' => boolean)
     
     */
    public function checkResponseForWebauthz($httpResponse) {
        $wwwAuthenticate = wp_remote_retrieve_header( $httpResponse, 'WWW-Authenticate' );
        
        if (!isset($wwwAuthenticate) || $wwwAuthenticate == '') {
            return (object) array('isWebauthz' => false);
        }
        
        $csv = '';
        if (str_starts_with(strtolower($wwwAuthenticate), 'webauthz ')) {
            $csv = substr($wwwAuthenticate, strlen('webauthz '));
        } elseif (str_starts_with(strtolower($wwwAuthenticate), 'bearer ')) {
            $csv = substr($wwwAuthenticate, strlen('bearer '));
        } else {
            return (object) array('isWebauthz' => false, 'WWW-Authenticate' => $wwwAuthenticate);
        }
        
        $webauthz = array('WWW-Authenticate' => $wwwAuthenticate);

        $realmInfo = array();
        $wwwAuthenticateInfo = explode(', ', $csv);
        foreach($wwwAuthenticateInfo as $info) {
            $kvpair = explode('=', $info);
            $key = $kvpair[0];
            $rawvalue = $kvpair[1];
            if (str_starts_with($rawvalue, '"') && str_ends_with($rawvalue, '"')) {
                $rawvalue = substr($rawvalue, 1, strlen($rawvalue) - 1);
            }
            $value = urldecode($rawvalue);
            $realmInfo[$key] = $value;
        }

        $realm = $realmInfo['realm'];
        $scope = $realmInfo['scope'];
        $path = $realmInfo['path'];
        $webauthz_discovery_uri = $realmInfo['webauthz_discovery_uri'];

        if (!isset($webauthz_discovery_uri) || $webauthz_discovery_uri == '') {
            return (object) array('isWebauthz' => false, 'WWW-Authenticate' => $wwwAuthenticate);
        }
        
        return (object) array(
            'isWebauthz' => true,
            'WWW-Authenticate' => $wwwAuthenticate,
            'realm' => $realm,
            'scope' => $scope,
            'path' => $path,
            'webauthz_discovery_uri' => $realmInfo['webauthz_discovery_uri']
        );
    }

    /**
     * Get random string (Code generation)
     *
     */
    public function generateRandomString($length = 16) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}