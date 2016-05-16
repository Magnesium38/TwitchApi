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

    /**
     * Returns an array of ingest objects.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/ingests.md#get-ingests
     *
     * @return array
     */
    public static function getIngests() {
        $uri = self::buildUri("/ingests");
        return self::responseToArray(self::get($uri), "ingests");
    }
}
