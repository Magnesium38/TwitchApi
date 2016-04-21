<?php namespace MagnesiumOxide\TwitchApi\Model;

class Video extends BaseModel {
    public function getTitle() {
        return $this->getHelper("title");
    }

    public function getDescription() {
        return $this->getHelper("description");
    }

    public function getBroadcastId() {
        return $this->getHelper("broadcast_id");
    }

    public function getStatus() {
        return $this->getHelper("status");
    }

    public function getId() {
        return $this->getHelper("_id");
    }

    public function getTagList() {
        return $this->getHelper("tag_list");
    }

    public function getRecordedAt() {
        return $this->getHelper("recorded_at");
    }

    public function getGame() {
        return $this->getHelper("game");
    }

    public function getLength() {
        return $this->getHelper("length");
    }

    public function getPreview() {
        return $this->getHelper("preview");
    }

    public function getUrl() {
        return $this->getHelper("url");
    }

    public function getViews() {
        return $this->getHelper("views");
    }

    public function getBroadcastType() {
        return $this->getHelper("broadcast_type");
    }

    public function getLinks() {
        return $this->getHelper("_links");
    }

    public function getChannelInfo() {
        return $this->getHelper("channel");
    }
}
