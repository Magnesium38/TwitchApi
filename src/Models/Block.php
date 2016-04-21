<?php namespace MagnesiumOxide\TwitchApi\Model;

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
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws \MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException
     * @throws \InvalidArgumentException
     * @throws \MagnesiumOxide\TwitchApi\Exception\NotAuthenticatedException
     */
    public function getBlockedUsers($limit = 25, $offset = 0) {
        return $this->client->getBlockedUsers($limit, $offset);
    }

    /**
     * Adds $target to the authenticated user's block list. Returns a blocks object.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/blocks.md#put-usersuserblockstarget
     *
     * @param $target
     * @return Block
     * @throws \MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException
     * @throws \MagnesiumOxide\TwitchApi\Exception\NotAuthenticatedException
     */
    public function blockUser($target) {
        return $this->client->blockUser($target);
    }

    /**
     * Removes $target from the authenticated user's block list.
     * Returns true on success, null on user wasn't blocked, and false on deleting the block failed.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/blocks.md#delete-usersuserblockstarget
     *
     * @param $target
     * @return bool|null
     * @throws \MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException
     * @throws \MagnesiumOxide\TwitchApi\Exception\NotAuthenticatedException
     */
    public function unblockUser($target) {
        return $this->client->unblockUser($target);
    }
}
