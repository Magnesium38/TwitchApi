<?php namespace MagnesiumOxide\TwitchApi\Model;

use MagnesiumOxide\TwitchApi\Scope;

class AuthenticatedUser extends BaseModel {
    protected $token = null;
    protected $scopes = [];
    /** @var User */
    protected $user = null;
    protected $notificationStatus = null;
    protected $email = null;
    protected $isPartnered = null;

    public static function getAuthenticatedUser($code) {
        $params = [
            "client_id" => self::$config->get("ClientId"),
            "client_secret" => self::$config->get("ClientSecret"),
            "grant_type" => "authorization_code",
            "redirect_uri" => self::$config->get("RedirectUri"),
            "code" => $code,
            "state" => self::$config->get("State"),
        ];

        $url = self::buildUri("/oauth2/token");
        $response = self::post($url, $params);

        if ($response->getStatusCode() != 200) {
            // TEST THIS
            throw new \Exception($response);
        }

        $user = new AuthenticatedUser();

        $body = json_decode($response->getBody(), true);

        $user->setToken($body["access_token"]);
        $user->setScopes($body["scope"]);
        $user->loadUserInfo();

        return $user;
    }

    public static function getAuthenticationUrl() {
        $params = [
            "response_type" => "code",
            "client_id" => self::$config->get("ClientId"),
            "redirect_uri" => self::$config->get("RedirectUri"),
            "scope" => implode("+", self::$config->get("Scope")),
            "state" => self::$config->get("State"),
        ];
        return self::buildUri("/oauth2/authorize") . "?" . http_build_query($params);
    }

    public function getToken() {
        return $this->token;
    }

    private function setToken($token) {
        $this->token = $token;
    }

    private function setScopes($scopes) {
        $this->scopes = $scopes;
    }

    private function loadUserInfo() {
        if (in_array(Scope::UserRead, $this->scopes)) {
            $response = self::get(self::buildUri("/user"), [], $this->getToken());

            $body = json_decode($response->getBody(), true);

            $this->notificationStatus = $body["notifications"];
            $this->email = $body["email"];
            $this->isPartnered = $body["partnered"];

            unset($body["notifications"]);
            unset($body["email"]);
            unset($body["partnered"]);

            $this->user = User::create($body);
        } else {
            $response = self::get(self::buildUri(""), [], $this->getToken());
            $body = $response->getBody();

            $username = $body["token"]["user_name"];
            $this->user = User::getUser($username);
        }
    }

    public function getNotificationStatus() {
        return $this->notificationStatus;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPartneredStatus() {
        return $this->isPartnered;
    }

    public function getUsername() {
        return $this->user->getUsername();
    }
}
