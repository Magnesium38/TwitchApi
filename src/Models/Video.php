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

    public static function getVideo($id) {
        $uri = self::buildUri("/videos/:id", ["id" => $id]);
        $response = self::$client->get($uri);
        return static::responseToObject($response);
    }

    public static function getTopVideos($limit = 10, $offset = 0, $game = null, $period = "week") {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }
        if ($period != "week" && $period != "month" && $period != "all") {
            throw new \InvalidArgumentException("Period must be either 'week', 'month' or 'all'.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
            "period" => $period,
        ];

        if ($game !== null) {
            $query["game"] = $game;
        }

        $uri = self::buildUri("/videos/top");
        $response = self::$client->get($uri, $query);
        return self::responseToArray($response, "videos");
    }
}
