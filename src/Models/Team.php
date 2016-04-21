<?php namespace MagnesiumOxide\TwitchApi\Model;

class Team extends BaseModel {
    public function getInfo() {
        return $this->getHelper("info");
    }

    public function getLinks() {
        return $this->getHelper("_links");
    }

    public function getBackground() {
        return $this->getHelper("background");
    }

    public function getBanner() {
        return $this->getHelper("banner");
    }

    public function getName() {
        return $this->getHelper("name");
    }

    public function getId() {
        return $this->getHelper("_id");
    }

    public function getUpdatedAt() {
        return $this->getHelper("updated_at");
    }

    public function getDisplayName() {
        return $this->getHelper("display_name");
    }

    public function getCreatedAt() {
        return $this->getHelper("created_at");
    }

    public function getLogo() {
        return $this->getHelper("logo");
    }
}
