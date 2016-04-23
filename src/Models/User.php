<?php namespace MagnesiumOxide\TwitchApi\Model;

use MagnesiumOxide\TwitchApi\Helpers\ClientInterface;
use MagnesiumOxide\TwitchApi\Scope;

class User extends BaseModel {
    protected $authToken = null;

    public static function getUser($username) {
        $uri = self::buildUri("/users/:user", ["user" => $username]);
        $response = self::$client->get($uri);

        return new User(json_decode($response->getBody(), true));
    }

    public function getType() {
        return $this->getHelper("type");
    }

    public function getName() {
        return $this->getHelper("name");
    }

    public function getUsername() {
        return $this->getName();
    }

    public function getCreatedAt() {
        return $this->getHelper("created_at");
    }

    public function getUpdatedAt() {
        return $this->getHelper("updated_at");
    }

    public function getLinks() {
        return $this->getHelper("_links");
    }

    public function getLogo() {
        return $this->getHelper("logo");
    }

    public function getId() {
        return $this->getHelper("_id");
    }

    public function getDisplayName() {
        return $this->getHelper("display_name");
    }

    public function getEmail() {
        return $this->getHelper("email");
    }

    public function getPartneredStatus() {
        return $this->getHelper("partnered");
    }

    public function getBio() {
        return $this->getHelper("bio");
    }

    public function getNotificationStatus() {
        return $this->getHelper("notifications");
    }

    public function getAuthToken() {
        return $this->authToken;
    }

    protected function setAuthToken($token) {
        $this->authToken = $token;
    }

    public static function getAuthenticatedUser($code) {
        $params = [
                "client_id" => self::$config["ClientId"],
                "client_secret" => self::$config["ClientSecret"],
                "grant_type" => "authorization_code",
                "redirect_uri" => self::$config["RedirectUri"],
                "code" => $code,
                "state" => self::$config["State"],
        ];

        $response = self::$client->post(ClientInterface::BASE_URL . "/oauth2/token", $params);

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
            $user = new User([]);
            $user->setAuthToken($token);
            $user->loadUserInfo();
        } else {
            $response = self::$client->get(ClientInterface::BASE_URL, [], [], $token);
            $response = $response->getBody();

            $username = $response["token"]["user_name"];

            $user = self::getUser($username);
            $user->setAuthToken($token);
        }

        return $user;
    }

    protected function loadUserInfo() {
        $this->requireScope(Scope::UserRead);

        $uri = self::buildUri("/user");
        $response = self::$client->get($uri);

        $this->loadObject(json_decode($response->getBody(), true));
    }
}
