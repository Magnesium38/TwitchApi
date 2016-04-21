<?php namespace MagnesiumOxide\TwitchApi\Model;

class Subscription extends BaseModel {
    public function getId() {
        return $this->getHelper("_id");
    }

    public function getUser() {
        return $this->getHelper("user");
    }

    public function getCreatedAt() {
        return $this->getHelper("created_at");
    }

    public function getLinks() {
        return $this->getHelper("_links");
    }
}
