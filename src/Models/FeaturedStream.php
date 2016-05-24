<?php namespace MagnesiumOxide\TwitchApi\Model;

class FeaturedStream extends CreatableModel {
    public function getImage() {
        return $this->getHelper("image");
    }

    public function getText() {
        return $this->getHelper("text");
    }

    public function getTitle() {
        return $this->getHelper("title");
    }

    public function getIsSponsored() {
        return $this->getHelper("sponsored");
    }

    public function getIsScheduled() {
        return $this->getHelper("scheduled");
    }

    public function getStream() {
        return Stream::create($this->getHelper("stream"));
    }

    /**
     * Returns a list of featured (promoted) stream objects.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/streams.md#get-streamsfeatured
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getFeatured($limit = 25, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
                "limit" => $limit,
                "offset" => $offset,
        ];

        $uri = self::buildUri("/streams/featured");
        return self::responseToArray(self::get($uri, $query), "featured");
    }
}
