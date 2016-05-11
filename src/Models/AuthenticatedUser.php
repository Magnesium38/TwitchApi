<?php namespace MagnesiumOxide\TwitchApi\Model;

use MagnesiumOxide\TwitchApi\Scope;

class AuthenticatedUser extends User {
    public static function getAuthenticatedUser($code) {
        $params = [
                "client_id" => self::$config["ClientId"],
                "client_secret" => self::$config["ClientSecret"],
                "grant_type" => "authorization_code",
                "redirect_uri" => self::$config["RedirectUri"],
                "code" => $code,
                "state" => self::$config["State"],
        ];

        $response = self::post(self::buildUri("/oauth2/token"), $params);

        if ($response->getStatusCode() != 200) {
            // ALL OF THIS NEEDS TESTING.
            throw new \Exception;
            /*if (isset($response->error)) {
                throw new \Exception; // REVISE THIS. Test what errors could be given.
            }*/
        }

        $response = $response->getBody();

        $token = $response["access_token"];

        if (in_array(Scope::UserRead, $response["scope"])) {
            $user = new AuthenticatedUser();
            $user->setAuthToken($token);
            $user->loadUserInfo();
        } else {
            $response = self::get(self::buildUri(""), [], [], $token);
            $body = $response->getBody();

            $username = $body["token"]["user_name"];

            $user = self::getUser($username);
            $user->setAuthToken($token);
        }

        return $user;
    }

    public static function getAuthenticationUrl() {
        $params = [
                "response_type" => "code",
                "client_id" => self::$config["ClientId"],
                "redirect_uri" => self::$config["RedirectUri"],
                "scope" => implode("+", self::$config["Scope"]),
                "state" => self::$config["State"],
        ];
        return self::buildUri("/oauth2/authorize") . "?" . http_build_query($params);
    }

    public function getAuthToken() {
        return $this->authToken;
    }

    protected function setAuthToken($token) {
        $this->authToken = $token;
    }
}
