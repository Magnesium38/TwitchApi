<?php namespace MagnesiumOxide\TwitchApi\Model;

class User extends BaseModel {
    public function getType() {
        return $this->getHelper("type");
    }

    public function getName() {
        return $this->getHelper("name");
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
}
