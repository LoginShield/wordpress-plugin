<?php


class RealmClient
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
    public function __construct($endpointURL, $realmId, $authorizationToken)
    {
        $this->endpointURL = $endpointURL;
        $this->realmId = $realmId;
        $this->authorizationToken = $authorizationToken;
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
     * Delete an existing user.
     *
     * @param string $realmScopedUserId     the user's LoginShield user id
conflict error
     *
     * @return mixed
     */
    public function deleteRealmUser($realmScopedUserId)
    {
        try {
            $url = $this->endpointURL . '/service/realm/user/delete';

            $fields = array (
                'realmId' => $this->realmId,
                'realmScopedUserId' => $realmScopedUserId
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

            if ($response && $response->isDeleted) {
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