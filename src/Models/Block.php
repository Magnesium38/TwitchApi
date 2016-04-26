<?php namespace MagnesiumOxide\TwitchApi\Model;

use MagnesiumOxide\TwitchApi\Scope;

/**
 * @package MagnesiumOxide\TwitchApi\Model
 */
class Block extends BaseModel {
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
     * Returns a DateTime object that corresponds to when the user was blocked.
     *
     * @return \DateTime
     */
    public function getBlockDate() {
        return new \DateTime($this->getHelper("updated_at"));
    }

    /**
     * Returns the user object that the block corresponds to.
     *
     * @return User
     */
    public function getBlockedUser() {
        return User::create($this->getHelper("user"));
    }

    /**
     * Returns the internal id of the object.
     * I don't see anywhere in the API where this value could be used.
     *
     * @return mixed
     */
    protected function getId() {
        return $this->getHelper("_id");
    }

    /**
     * Returns a list of block objects on the authenticated user's block list. Sorted by recency.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/blocks.md#get-usersuserblocks
     *
     * @param User $user
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \InvalidArgumentException
     * @throws \MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException
     */
    public static function getBlockedUsers(AuthenticatedUser $user, $limit = 25, $offset = 0) {
        self::requireScope(Scope::ReadUserBlocks);

        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $uri = self::buildUri("/users/:user/blocks", ["user" => $user->getName()]);
        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];
        $headers = self::buildHeaders();

        $result = self::$client->get($uri, $query, $headers, $user->getAuthToken());

        $body = json_decode($result->getBody(), true);

        $blocks = [];
        foreach ($body["blocks"] as $item) {
            $blocks[] = Block::create($item);
        }

        return $blocks;
    }

    /**
     * Adds $target to the authenticated user's block list. Returns a blocks object.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/blocks.md#put-usersuserblockstarget
     *
     * @param User $user
     * @param $target
     * @return Block
     * @throws \MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException
     */
    public static function blockUser(AuthenticatedUser $user, $target) {
        self::requireScope(Scope::EditUserBlocks);

        $uri = self::buildUri("/users/:user/blocks/:target", ["user" => $user->getName(), "target" => $target]);
        $headers = self::buildHeaders();
        $response = self::$client->put($uri, [], $headers, $user->getAuthToken());
        $body = json_decode($response->getBody(), true);

        return Block::create($body);
    }

    /**
     * Removes $target from the authenticated user's block list.
     * Returns true on success, null on user wasn't blocked, and false on deleting the block failed.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/blocks.md#delete-usersuserblockstarget
     *
     * @param User $user
     * @param $target
     * @return bool|null
     * @throws \MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException
     */
    public static function unblockUser(AuthenticatedUser $user, $target) {
        self::requireScope(Scope::EditUserBlocks);

        $uri = self::buildUri("/users/:user/blocks/:target", ["user" => $user->getName(), "target" => $target]);
        $headers = self::buildHeaders();
        $result = self::$client->delete($uri, [], $headers, $user->getAuthToken())->getStatusCode();

        if ($result == 204) {
            return true;
        } elseif ($result == 404) {
            return null;
        } else {
            return false;
        }
    }
}
