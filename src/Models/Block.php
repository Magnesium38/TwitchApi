<?php namespace MagnesiumOxide\TwitchApi\Model;

class Block extends BaseModel {
    public function getLinks() {
        return $this->getHelper("_links");
    }

    public function getLastUpdated() {
        return $this->getHelper("updated_at");
    }

    public function getBlockedUser() {
        return $this->getHelper("user");
    }

    public function getId() {
        return $this->getHelper("_id");
    }
}