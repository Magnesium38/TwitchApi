<?php namespace MagnesiumOxide\TwitchApi\Model;

class Stream extends BaseModel {
    public function getGame() {
        return $this->getHelper("game");
    }

    public function getNumViewers() {
        return $this->getHelper("viewers");
    }

    public function getAverageFps() {
        return $this->getHelper("average_fps");
    }

    public function getDelay() {
        return $this->getHelper("delay");
    }

    public function getVideoHeight() {
        return $this->getHelper("video_height");
    }

    public function getPlaylistStatus() {
        return $this->getHelper("is_playlist");
    }

    public function getCreatedAt() {
        return $this->getHelper("created_at");
    }

    public function getId() {
        return $this->getHelper("_id");
    }

    public function getChannel() {
        return $this->getHelper("channel");
    }

    public function getPreview() {
        return $this->getHelper("preview");
    }

    public function getLinks() {
        return $this->getHelper("_links");
    }
}
