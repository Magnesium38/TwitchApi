<?php namespace MagnesiumOxide\TwitchApi\Model;

class Follow extends BaseModel {
    /**
     * Returns a DateTime representing when the follow occurred.
     *
     * @return \DateTime
     */
    public function getCreatedAt() {
        return new \DateTime($this->getHelper("created_at"));
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
     * Returns a boolean representing if the user receives notifications for the followed user/channel.
     *
     * @return bool|null
     */
    public function getNotificationStatus() {
        return $this->getHelper("notifications");
    }

    /**
     * Gets the user associated with this object. Returns null if there is no associated user.
     *
     * @return null|User
     */
    public function getUser() {
        $user = $this->getHelper("user");

        if (!is_null($user)) {
            return User::create($user);
        }

        return null;
    }

    /**
     * Gets the channel associated with this object. Returns null if there is no associated channel.
     *
     * @return Channel|null
     */
    public function getChannel() {
        $channel = $this->getHelper("channel");

        if (!is_null($channel)) {
            return Channel::create($channel);
        }

        return null;
    }

    /**
     * Returns a list of follow objects.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/follows.md#get-channelschannelfollows
     *
     * @param $channel
     * @param $limit
     * @param null $cursor
     * @param string $direction
     * @return array
     */
    public static function getChannelFollowers($channel, $limit = 25, $cursor = null, $direction = "desc") {
        if ($direction != "desc" && $direction != "asc") {
            throw new \InvalidArgumentException("Direction must be either 'asc' or 'desc'.");
        }
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }
        $query = [
                "limit" => $limit,
                "direction" => $direction,
        ];

        if ($cursor !== null) {
            $query["cursor"] = $cursor;
        }

        $uri = self::buildUri("/channels/:channel/follows", ["channel" => $channel]);
        return self::responseToArray(self::get($uri, $query), "follows");
    }

    /**
     * Returns a list of follows objects.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/follows.md#get-usersuserfollowschannels
     *
     * @param $user
     * @param int $limit
     * @param int $offset
     * @param string $direction
     * @param string $sort
     * @return array
     */
    public static function getUserFollowers($user, $limit = 25, $offset = 0, $direction = "desc", $sort = "created_at") {
        if ($direction != "desc" && $direction != "asc") {
            throw new \InvalidArgumentException("Direction must be either 'asc' or 'desc'.");
        }
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }
        if ($sort != "created_at" && $sort != "last_broadcast" && $sort != "login") {
            throw new \InvalidArgumentException("Sort by must be either 'created_at', 'last_broadcast' or 'login'.");
        }

        $query = [
                "limit" => $limit,
                "offset" => $offset,
                "direction" => $direction,
                "sortby" => $sort,
        ];

        $uri = self::buildUri("/users/:user/follows/channels", ["user" => $user]);
        return static::responseToArray(self::get($uri, $query), "follows");
    }

    /**
     * Returns 404 Not Found if $user is not following $channel. Returns a follow object otherwise.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/follows.md#get-usersuserfollowschannelstarget
     *
     * @param $user
     * @param $channel
     * @return Follow
     */
    public static function doesUserFollowChannel($user, $channel) {
        $uri = self::buildUri("/users/:user/follows/channels/:target", ["user" => $user, "target" => $channel]);
        $response = self::get($uri);

        if ($response->getStatusCode() == 404) {
            return null;
        }

        return static::responseToObject($response);
    }
}
