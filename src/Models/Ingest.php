<?php namespace MagnesiumOxide\TwitchApi\Model;

class Ingest extends BaseModel {

    public function getName() {
        return $this->getHelper("name");
    }

    public function getDefaultStatus() {
        return $this->getHelper("default");
    }

    public function getId() {
        return $this->getHelper("_id");
    }

    public function getUrlTemplate() {
        return $this->getHelper("url_template");
    }

    public function getAvailability() {
        return $this->getHelper("availability");
    }
}