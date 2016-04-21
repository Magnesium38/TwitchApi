<?php namespace MagnesiumOxide\TwitchApi\Model;

class Follow extends BaseModel {
    public function getCreatedAt() {
        return $this->getHelper("created_at");
    }

    public function getLinks() {
        return $this->getHelper("_links");
    }

    public function getNotificationStatus() {
        return $this->getHelper("notifications");
    }

    public function getUser() {
        return $this->getHelper("user");
    }

    public function getChannel() {
        return $this->getHelper("channel");
    }
}