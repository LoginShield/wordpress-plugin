<?php


class Webauthz
{

    /**
     * Endpoint URL
     */
    private $endpointURL;


    /**
     * RealmId
     */
    private $realmId;


    /**
     * Authorization Token
     */
    private $authorizationToken;


    /**
     * Create a new RealmClient Instance
     */
    public function __construct()
    {
//        $this->endpointURL = $endpointURL;
//        $this->realmId = $realmId;
//        $this->authorizationToken = $authorizationToken;
    }


    public function verifyRealmInfo()
    {
        try {
            $url = 'https://loginshield.com/service/account/realm?uri=' . get_site_url();
            $response = wp_remote_get($url);
            $wwwAuthenticate = wp_remote_retrieve_header( $response, 'WWW-Authenticate' );

            // $wwwAuthenticate returned value
            $wwwAuthenticate = 'Bearer realm=https%3A%2F%2Floginshield.com, scope=enterprise-realm, path=%2Fservice, webauthz_discovery_uri=https%3A%2F%2Floginshield.com%2Fservice%2Fwebauthz%2Fdiscovery.json';

            // Todo : Add a module to parse $wwwAuthenticate to get realm, scope, path, webauthz_discovery_uri
            $realm = "https://loginshield.com";
            $scope = "enterprise-realm";
            $path = "/service";
            $webauthz_discovery_uri = "https://loginshield.com/service/webauthz/discovery.json";

            // Store $realm, $scope, $path, $webauthz_discovery_uri into the database
            add_option( 'loginshield_realm', $realm );
            add_option( 'loginshield_scope', $scope );
            add_option( 'loginshield_path', $path );
            add_option( 'loginshield_webauthz_discovery_uri', $webauthz_discovery_uri );

            // Fetch Webauthz configuration
            $response = wp_remote_get($webauthz_discovery_uri);
            $webauthzConfig = wp_remote_retrieve_body($response);

            // $webauthzConfig returned value
            $webauthzConfig = '{"webauthz_register_uri":"https://loginshield.com/service/webauthz/register","webauthz_request_uri":"https://loginshield.com/service/webauthz/request","webauthz_exchange_uri":"https://loginshield.com/service/webauthz/exchange"}';

            // Todo : Add a module to parse $webauthzConfig to get $webauthz_register_uri, $webauthz_request_uri, $webauthz_exchange_uri
            $webauthz_register_uri = "https://loginshield.com/service/webauthz/register";
            $webauthz_request_uri = "https://loginshield.com/service/webauthz/request";
            $webauthz_exchange_uri = "https://loginshield.com/service/webauthz/exchange";

            // Store $webauthz_register_uri, $webauthz_request_uri, $webauthz_exchange_uri into the database
            add_option( 'loginshield_webauthz_register_uri', $webauthz_register_uri );
            add_option( 'loginshield_webauthz_request_uri', $webauthz_request_uri );
            add_option( 'loginshield_webauthz_exchange_uri', $webauthz_exchange_uri );

            // Define register parameters
            $client_name = "";
            $client_version = "LoginShield for WordPress v1.0.0";
            $grant_redirect_uri = admin_url( '/options-general.php?page=loginshield' );

            // Register client
            $args = array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                ),
                'method'    => 'POST',
                'body'      => '{"client_name":"test", "client_version": "test2", "grant_redirect_uri": "http://localhost"}',
                'sslverify' => false,
            );

            $response = wp_remote_post( $webauthz_register_uri , $args );
            $client = wp_remote_retrieve_body($response);
            // Client result
            $client = '{"client_id":"5WMfdUI33DdwevKTS2FUdWCN156navt6","client_token":"client:5WMfdUI33DdwevKTS2FUdWCN156navt6:MKWRAJT6WnBfL_GdM3-4L0_6_x1JWbdkeLM98Xk30rnxfl7SYL5-BZG6xB2tLB-C0H6MqluU_L6kHyHbQpvk6d4sa4Jv0Ic8e-K21q-VtBsLlPIYho8IN97YWale5Pgb"}';

            // Todo : fetch client_id, client_token from returned value
            $client_id = "5WMfdUI33DdwevKTS2FUdWCN156navt6";
            $client_token = "client:5WMfdUI33DdwevKTS2FUdWCN156navt6:MKWRAJT6WnBfL_GdM3-4L0_6_x1JWbdkeLM98Xk30rnxfl7SYL5-BZG6xB2tLB-C0H6MqluU_L6kHyHbQpvk6d4sa4Jv0Ic8e-K21q-VtBsLlPIYho8IN97YWale5Pgb";

            // Step 4
            $args = array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                ),
                'method'    => 'POST',
                'body'      => '{"client_name":"test", "client_version": "test2", "grant_redirect_uri": "http://localhost"}',
                'sslverify' => false,
            );
            $response = wp_remote_post( $webauthz_request_uri , $args );

            return (object) array(
                'error' => 'unexpected-response',
                'response'=> $response,
                'grant_redirect_uri'=> $grant_redirect_uri,
                'client'=> $client,
                'www_authenticate'=> $wwwAuthenticate,
                'webauthz_config'=> $webauthzConfig,
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error' => 'registration-failed',
                'response'=> $exception
            );
        }
    }


    /**
     * Register a new user with the 'immediate' method.
     *
     * The immediate method is preferred for the best user experience.
     * To use the immediate method, provide `realmScopedUserId`, `name`, and `email`,
     * and if the service responds with { isCreated: true } then continue to the
     * first login with LoginShield (specify the new key flag) to complete registration.
     *
     * @param string $realmScopedUserId     how LoginShield should identify the user for this authentication realm
     * @param string $name                  the user's display name
     * @param string $email                 the user's email address
     * @param boolean $replace              optional, if true the service will replace any existing realmScopedUserId record instead of returning a conflict error
     *
     * @return mixed
     */
    public function createRealmUser($realmScopedUserId, $name, $email, $replace)
    {
        try {
            $url = $this->endpointURL . '/service/realm/user/create';

            wp_remote_get('http://thirdparty.com?foo=bar');

            $fields = array(
                'businessId'    => $businessId,
                'url'           => $contentUrl,
                'tokenValue'    => $tokenValue
            );
            $args = array(
                'headers' => array(
                    'Content-Type'  => 'application/json',
                    'User-Agent'    => 'WSKR WPP',
                    'Authorization' => 'Bearer ' . $this->authorizationToken,
                ),
                'method'    => 'POST',
                'sslverify' => false,
            );

            $responseArr = wp_remote_post( $url , $args );
            $body = $responseArr['body'];
            $response = $responseArr['response'];



            $fields = array (
                'realmId' => $this->realmId,
                'realmScopedUserId' => $realmScopedUserId,
                'name' => $name,
                'email' => $email,
                'replace' => $replace
            );
            $data_string = json_encode($fields);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Authorization: Token ' . $this->authorizationToken,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
            );

            $apiResponse = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($apiResponse);

            if ($response && $response->isCreated) {
                return $response;
            }

            return (object) array(
                'error' => 'unexpected-response',
                'response'=> $response
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error' => 'registration-failed',
                'response'=> $exception
            );
        }
    }


    /**
     * Register a new user with the 'redirect' method.
     *
     * The immediate method is preferred for the best user experience.
     *
     * To use the redirect method, provide `realmScopedUserId` and `redirect`,
     * and if the service responds with { isCreated: true, forward: <url> } then
     * redirect the user to that forward URL; and when the service has registered
     * the user, the service will redirect the user back to the specified `redirect`
     * URL.
     *
     * @param string $realmScopedUserId     how LoginShield should identify the user for this authentication realm
     * @param string $redirect              where loginshield will redirect the user after the user authenticates and confirms the link with the realm (the enterprise should complete the registration with the first login with loginshield at this url)
     *
     * @return mixed
     */
    public function createRealmUserWithRedirect($realmScopedUserId, $redirect)
    {
        try {
            $url = $this->endpointURL . '/service/realm/user/create';

            $fields = array (
                'realmId' => $this->realmId,
                'realmScopedUserId' => $realmScopedUserId,
                'redirect' => $redirect
            );
            $data_string = json_encode($fields);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Token ' . $this->authorizationToken,
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );

            $apiResponse = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($apiResponse);

            if ($response && $response->isCreated && $response->forward && $this->startsWith($response->forward, $this->endpointURL)) {
                return $response;
            }

            return json_encode(array(
                'error' => 'unexpected-response',
                'response'=> $response
            ));
        } catch (\Exception $exception) {
            return json_encode(array(
                'error' => 'registration-failed',
                'response'=> $exception
            ));
        }
    }


    /**
     * Start Login.
     *
     * @param string $realmScopedUserId     how LoginShield should identify the user for this authentication realm
     * @param string $redirect              where loginshield will redirect the user after the user authenticates and confirms the link with the realm (the enterprise should complete the registration with the first login with loginshield at this url)
     * @param boolean $isNewKey
     *
     * @return mixed
     */
    public function startLogin($realmScopedUserId, $redirect, $isNewKey = false)
    {
        try {
            $url = $this->endpointURL . '/service/realm/login/start';

            $fields = array (
                'realmId' => $this->realmId,
                'userId' => $realmScopedUserId,
                'isNewKey' => $isNewKey,
                'redirect' => $redirect
            );
            $data_string = json_encode($fields);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Token ' . $this->authorizationToken,
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );

            $apiResponse = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($apiResponse);

            if ($response && $response->forward && $this->startsWith($response->forward, $this->endpointURL)) {
                return $response;
            }

            return (object) array(
                'error' => 'unexpected-response',
                'response'=> $response
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error' => 'login-failed',
                'response'=> $exception
            );
        }
    }


    /**
     * Verify Login.
     *
     * @param string $token
     *
     * @return mixed
     */
    public function verifyLogin($token)
    {
        try {
            $url = $this->endpointURL . '/service/realm/login/verify';

            $fields = array (
                'token' => $token
            );
            $data_string = json_encode($fields);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Authorization: Token ' . $this->authorizationToken,
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );

            $apiResponse = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($apiResponse);

            if ($response) {
                return $response;
            }

            return (object) array(
                'error' => 'unexpected-response',
                'response'=> $response
            );
        } catch (\Exception $exception) {
            return (object) array(
                'error' => 'unexpected-response',
                'response'=> $exception
            );
        }
    }


    /**
     * An utility to check if a string starts with a sub string or not
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