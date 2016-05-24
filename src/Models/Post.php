<?php namespace MagnesiumOxide\TwitchApi\Model;

class Post extends CreatableModel {
    public function getId() {
        return $this->getHelper("id");
    }

    public function getCreatedAt() {
        return $this->getHelper("created_at");
    }

    public function getDeleted() {
        return $this->getHelper("deleted");
    }

    public function getEmotes() {
        return $this->getHelper("emotes");
    }

    public function getReactions() {
        return $this->getHelper("reactions");
    }

    public function getBody() {
        return $this->getHelper("body");
    }

    public function getUser() {
        return $this->getHelper("user");
    }
}
