<?php namespace MagnesiumOxide\TwitchApi\Model;

use MagnesiumOxide\TwitchApi\Scope;

class User extends BaseModel {
    protected $authToken = null;

    public static function getUser($username) {
        $uri = self::buildUri("/users/:user", ["user" => $username]);
        $response = self::get($uri);

        return new static(json_decode($response->getBody(), true));
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

    protected function loadUserInfo() {
        $this->requireScope(Scope::UserRead);

        $uri = self::buildUri("/user");
        $response = self::get($uri);

        $this->loadObject(json_decode($response->getBody(), true));
    }
}
