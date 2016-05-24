<?php namespace MagnesiumOxide\TwitchApi\Model;

class Channel extends CreatableModel {
    public function isMature() {
        return $this->getHelper("mature");
    }

    public function getStatus() {
        return $this->getHelper("status");
    }

    public function getBroadcasterLanguage() {
        return $this->getHelper("broadcaster_language");
    }

    public function getDisplayName() {
        return $this->getHelper("display_name");
    }

    public function getGame() {
        return $this->getHelper("game");
    }

    public function getDelay() {
        return $this->getHelper("delay");
    }

    /**
     * I think this is the same as broadcaster_language. So I'm hiding this one since it seems like a duplicate.
     *
     * @return mixed
     */
    protected function getLanguage() {
        return $this->getHelper("language");
    }

    /**
     * Returns the internal id of the object.
     * I don't see anywhere in the API where this value could be used.
     *
     * @return mixed
     */
    public function getId() {
        return $this->getHelper("_id");
    }

    public function getUsername() {
        return $this->getHelper("name");
    }

    public function getCreatedAt() {
        $datetime = $this->getHelper("created_at");

        if (is_null($datetime)) {
            return null;
        }

        return new \DateTime($datetime);
    }

    public function getLastUpdatedAt() {
        $datetime = $this->getHelper("updated_at");

        if (is_null($datetime)) {
            return null;
        }

        return new \DateTime($datetime);
    }

    public function getLogo() {
        return $this->getHelper("logo");
    }

    public function getBanner() {
        return $this->getHelper("banner");
    }

    public function getVideoBanner() {
        return $this->getHelper("video_banner");
    }

    public function getBackground() {
        return $this->getHelper("background");
    }

    public function getProfileBanner() {
        return $this->getHelper("profile_banner");
    }

    public function getProfileBannerBackgroundColor() {
        return $this->getHelper("profile_banner_background_color");
    }

    public function isPartner() {
        return $this->getHelper("partner");
    }

    public function getUrl() {
        return $this->getHelper("url");
    }

    public function getNumViews() {
        return $this->getHelper("views");
    }

    public function getNumFollowers() {
        return $this->getHelper("followers");
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
     * Returns a channel object.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#get-channelschannel
     *
     * @param $channel
     * @return Channel
     */
    public static function getChannel($channel) {
        $uri = self::buildUri("/channels/:channel", ["channel" => $channel]);
        return static::responseToObject(self::get($uri));
    }

    /**
     * Returns a list of channel objects matching the search query.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md#get-searchchannels
     *
     * @param $query
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function searchChannels($query, $limit = 25, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $q = [
                "query" => urlencode($query),
                "limit" => $limit,
                "offset" => $offset,
        ];

        $uri = self::buildUri("/search/channels");
        return static::responseToArray(self::get($uri, $q), "channels");
    }
}
