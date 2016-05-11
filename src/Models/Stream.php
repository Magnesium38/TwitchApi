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
        return new \DateTime($this->getHelper("created_at"));
    }

    public function getId() {
        return $this->getHelper("_id");
    }

    public function getChannel() {
        return Channel::create($this->getHelper("channel"));
    }

    public function getPreview() {
        return $this->getHelper("preview");
    }

    protected function getLinks() {
        return $this->getHelper("_links");
    }

    /**
     * Returns a stream object if $channel is live.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/streams.md#get-streamschannel
     *
     * @param $channel
     * @return Stream
     */
    public static function getLiveChannel($channel) {
        $uri = self::buildUri("/streams/:channel", ["channel" => $channel]);
        return self::responseToObject(self::get($uri), "stream");
    }

    /**
     * Returns a list of stream objects that are queries by the parameters sorted by number of viewers.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/streams.md#get-streams
     *
     * @param null $game
     * @param array $channels
     * @param int $limit
     * @param int $offset
     * @param null $streamType
     * @return array
     */
    public static function getStreams($game = null, $channels = [], $limit = 25, $offset = 0, $streamType = null) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        // This route can also take a client_id, but I'm not sure on what that is.
        // I'm leaving it out for now.
        $query = [];

        if ($game !== null) $query["game"] = $game;
        if ($limit !== null) $query["limit"] = $limit;
        if ($offset !== null) $query["offset"] = $offset;
        if ($streamType !== null) $query["stream_type"] = $streamType;
        if (!empty($channels)) {
            $query["channel"] = implode(",", $channels);
        }

        $uri = self::buildUri("/streams");
        return self::responseToArray(self::get($uri, $query), "streams");
    }

    /**
     * Returns an array containing the summary of current streams for all of Twitch.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/streams.md#get-streamssummary
     *
     * @param $game
     * @return array
     */
    public static function getStreamSummary($game = null) {
        $uri = self::buildUri("/streams/summary");

        $query = [];
        if (!is_null($game)) $query["game"] = $game;

        $result = json_decode(self::get($uri, $query)->getBody(), true);
        unset($result["_links"]); // Remove the self-referencing links. I don't see a reason to include it.

        return $result;
    }

    /**
     * Returns a list of stream objects matching the search query.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md#get-searchstreams
     *
     * @param $query
     * @param int $limit
     * @param int $offset
     * @param bool|null $hls
     * @return array
     */
    public static function search($query, $limit = 25, $offset = 0, $hls = null) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $q = [
                "query" => urlencode($query),
                "limit" => $limit,
                "offset" => $offset,
        ];

        if ($hls !== null) {
            $q["hls"] = $hls;
        }

        $uri = self::buildUri("/search/streams");
        return self::responseToArray(self::get($uri, $q), "streams");
    }
}
