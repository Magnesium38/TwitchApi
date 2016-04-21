<?php namespace MagnesiumOxide\TwitchApi\Model;

class Game extends BaseModel {
    public function getName() {
        return $this->getHelper("name");
    }

    public function getBoxArtLinks() {
        return $this->getHelper("box");
    }

    public function getLogoLinks() {
        return $this->getHelper("logo");
    }

    public function getLinks() {
        return $this->getHelper("_links");
    }

    public function getId() {
        return $this->getHelper("_id");
    }

    public function getGiantbombId() {
        return $this->getHelper("giantbomb_id");
    }
}