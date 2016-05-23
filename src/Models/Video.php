<?php namespace MagnesiumOxide\TwitchApi\Model;

/**
 * @package MagnesiumOxide\TwitchApi\Model
 */
class Video extends BaseModel {
    /**
     * Returns the title for the video object.
     *
     * @return string|null
     */
    public function getTitle() {
        return $this->getHelper("title");
    }

    /**
     * Returns the description for the video object.
     *
     * @return string|null
     */
    public function getDescription() {
        return $this->getHelper("description");
    }

    /**
     * Returns the broadcast id for the video object.
     *
     * @return string|null
     */
    public function getBroadcastId() {
        return $this->getHelper("broadcast_id");
    }

    /**
     * Returns the status for the video object.
     *
     * @return string|null
     */
    public function getStatus() {
        return $this->getHelper("status");
    }

    /**
     * Returns the status for the video object.
     *
     * @return string|null
     */
    public function getId() {
        return $this->getHelper("_id");
    }

    /**
     * Returns the tag list for the video object.
     *
     * @return string|null
     */
    public function getTagList() {
        return $this->getHelper("tag_list");
    }

    /**
     * Returns the recorded at datetime object for the video object.
     *
     * @return \DateTime
     */
    public function getRecordedAt() {
        return new \DateTime($this->getHelper("recorded_at"));
    }

    /**
     * Returns the Game for the video object.
     *
     * @return string|null
     */
    public function getGame() {
        return $this->getHelper("game");
    }

    /**
     * Returns the length for the video object.
     *
     * @return string|null
     */
    public function getLength() {
        return $this->getHelper("length");
    }

    /**
     * Returns the preview for the video object.
     *
     * @return string|null
     */
    public function getPreview() {
        return $this->getHelper("preview");
    }

    /**
     * Returns the url for the video object.
     *
     * @return string|null
     */
    public function getUrl() {
        return $this->getHelper("url");
    }

    /**
     * Returns the views for the video object.
     *
     * @return string|null
     */
    public function getViews() {
        return $this->getHelper("views");
    }

    /**
     * Returns the broadcast type for the video object.
     *
     * @return string|null
     */
    public function getBroadcastType() {
        return $this->getHelper("broadcast_type");
    }

    /**
     * Returns the self-referencing links provided by the API.
     * I don't see a reason to expose this method. Leaving protected in case there's a niche use case.
     *
     * @return array|null
     */
    protected function getLinks() {
        return $this->getHelper("_links");
    }

    /**
     * Returns the channel info for the video object
     *
     * @return string|null
     */
    public function getChannelInfo() {
        return $this->getHelper("channel");
    }

    /**
     * Get a video object with $id.
     *
     * @param $id
     * @return static
     */
    public static function getVideo($id) {
        $uri = self::buildUri("/videos/:id", ["id" => $id]);
        $response = self::get($uri);
        return static::responseToObject($response);
    }

    /**
     * Get an array of video objects for the top videos.
     *
     * @param int $limit
     * @param int $offset
     * @param null $game
     * @param string $period
     * @return array
     */
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
        $response = self::get($uri, $query);
        return self::responseToArray($response, "videos");
    }

    /**
     * Returns a list of videos ordered by creation time from $channel.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/videos.md#get-channelschannelvideos
     *
     * @param $channel
     * @param int $limit
     * @param int $offset
     * @param bool|false $broadcasts
     * @param bool|false $hls
     * @return array
     */
    public static function getChannelVideos($channel, $limit = 10, $offset = 0, $broadcasts = false, $hls = false) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
                "limit" => $limit,
                "offset" => $offset,
                "broadcasts" => $broadcasts,
                "hls" => $hls,
        ];

        $uri = self::buildUri("/channels/:channel/videos", ["channel" => $channel]);
        return self::responseToArray(self::get($uri, $query), "videos");
    }
}
