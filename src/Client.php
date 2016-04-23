<?php namespace MagnesiumOxide\TwitchApi;

use MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException;
use MagnesiumOxide\TwitchApi\Exception\NotAuthenticatedException;
use MagnesiumOxide\TwitchApi\Model\Block;

/**
 * A wrapper for the Twitch.tv API.
 * @package MagnesiumOxide\TwitchApi
 */
class Client {
    /** @const The current base url that all requests will use */
    CONST BASE_URL = "https://api.twitch.tv/kraken";
    /** @const The API version used is denoted with the Accept header so it will be attached to all requests. */
    CONST ACCEPT_HEADER = "application/vnd.twitchtv.v3+json";
    /** @var RequestInterface The client that will be used to make the requests. */
    protected $client;
    /** @var ConfigRepository A clean wrapper around the configuration file. */
    protected $config;
    /** @var null|string The access token for returned by the API once authenticated, null otherwise. */
    protected $token = null;
    /** @var null|string The authenticated user's username on Twitch, null otherwise. */
    protected $username = null;

    /**
     * Construct an instance of the Client. Takes a RequestInterface object and an optional
     * ConfigRepository to use. If a ConfigRepository is not given, it creates one using the
     * default constructor.
     *
     * @param ConfigRepository $config
     * @param RequestInterface $client
     */
    public function __construct(RequestInterface $client = null, ConfigRepository $config = null) {
        $this->config = $config ?: new ConfigRepository();
        $this->client = $client ?: new Request();
    }

    /**
     * Gets the config that the client was initialised with.
     *
     * @return ConfigRepository
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Gets the current authenticated user's username or null, if no one is authenticated.
     *
     * @return null|string
     */
    public function getUsername() {
        return $this->username;
    }

    /**
     * Gets the scopes that have been required as specified in the config.
     *
     * @return array
     */
    public function getScope() {
        return $this->config["Scope"];
    }

    /**
     * Builds a Uri from a given path while substituting in the given parameters.
     *
     * @param $path
     * @param array $params
     * @return string
     */
    protected static function buildUri($path, array $params = []) {
        $parts = array_map(function ($item) use ($params) {
            if (strpos($item, ":") === 0) {
                return $params[substr($item, 1)];
            }
            return $item;
        }, explode("/", $path));
        return Client::BASE_URL . implode("/", $parts);
    }

    /**
     * Get the URL to direct a user at to authenticate.
     *
     * @return string
     */
    public function getAuthenticationUrl() {
        $params = [
            "response_type" => "code",
            "client_id" => $this->config["ClientId"],
            "redirect_uri" => $this->config["RedirectUri"],
            "scope" => implode("+", $this->config["Scope"]),
            "state" => $this->config["State"],
        ];
        return Client::buildUri("/oauth2/authorize") . "?" . http_build_query($params);
    }

    /**
     * TO DO
     *
     * @param $code
     * @throws \Exception
     */
    public function authenticate($code) {
        $params = [
            "client_id" => $this->config["ClientId"],
            "client_secret" => $this->config["ClientSecret"],
            "grant_type" => "authorization_code",
            "redirect_uri" => $this->config["RedirectUri"],
            "code" => $code,
            "state" => $this->config["State"],
        ];

        $response = $this->post(Client::BASE_URL . "/oauth2/token", $params);
        if ($response->getStatusCode() != 200) {
            // ALL OF THIS NEEDS TESTING.
            throw new \Exception;
            /*if (isset($response->error)) {
                throw new \Exception; // REVISE THIS. Test what errors could be given.
            }*/
        }

        $response = $response->getBody();

        $this->token = $response["access_token"];
        //$this->scope = $response["scope"]; // Assume that it will be what was passed to it.

        $response = $this->get(Client::BASE_URL);
        $response = $response->getBody();

        $this->username = $response["token"]["user_name"];
    }

    /**
     * A clean shorthand to throw an exception when there is no authenticated user.
     *
     * @throws NotAuthenticatedException
     */
    protected function requireAuthentication() {
        if ($this->token === null) {
            throw new NotAuthenticatedException();
        }
    }

    /**
     * A clean shorthand to throw an exception when the required scope isn't available to the application.
     *
     * @param string $scope
     * @throws InsufficientScopeException
     */
    protected function requireScope($scope) {
        if (!in_array($scope, $this->getScope())) {
            throw InsufficientScopeException::createException($scope, $this->getScope());
        }
    }

    /**
     * Returns a list of block objects on the authenticated user's block list. Sorted by recency.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/blocks.md#get-usersuserblocks
     *
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws InsufficientScopeException
     * @throws \InvalidArgumentException
     * @throws NotAuthenticatedException
     */
    public function getBlockedUsers($limit = 25, $offset = 0) {
        $this->requireAuthentication();
        $this->requireScope(Scope::ReadUserBlocks);

        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $uri = Client::buildUri("/users/:user/blocks", ["user" => $this->username]);
        $result = $this->get($uri, ["limit" => $limit, "offset" => $offset]);
        $blocks = [];

        foreach ($result["blocks"] as $item) {
            $blocks[] = Block::create($item);
        }

        return $blocks;
    }

    /**
     * Adds $target to the authenticated user's block list. Returns a blocks object.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/blocks.md#put-usersuserblockstarget
     *
     * @param $target
     * @return Block
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function blockUser($target) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditUserBlocks);

        $uri = Client::buildUri("/users/:user/blocks/:target", ["user" => $this->username, "target" => $target]);
        return Block::create($this->put($uri)->getBody());
    }

    /**
     * Removes $target from the authenticated user's block list.
     * Returns true on success, null on user wasn't blocked, and false on deleting the block failed.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/blocks.md#delete-usersuserblockstarget
     *
     * @param $target
     * @return bool|null
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function unblockUser($target) {

        $this->requireAuthentication();
        $this->requireScope(Scope::EditUserBlocks);

        $uri = Client::buildUri("/users/:user/blocks/:target", ["user" => $this->username, "target" => $target]);
        $result = $this->delete($uri)->getStatusCode();

        if ($result == 204) {
            return true;
        } elseif ($result == 404) {
            return null;
        } else {
            return false;
        }
    }

    /**
     * Returns a channel object.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#get-channelschannel
     *
     * @param $channel
     * @return array
     */
    public function getChannel($channel) {
        $uri = Client::buildUri("/channels/:channel", ["channel" => $channel]);
        return $this->get($uri);
    }

    /**
     * Returns a channel object for the authenticated user. Channel object includes stream key.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#get-channel
     *
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function getAuthenticatedChannel() {
        $this->requireAuthentication();
        $this->requireScope(Scope::ReadChannel);

        $uri = Client::buildUri("/channels/:channel", ["channel" => $this->username]);
        return $this->get($uri);
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
    public function getChannelVideos($channel, $limit = 10, $offset = 0, $broadcasts = false, $hls = false) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
            "broadcasts" => $broadcasts,
            "hls" => $hls,
        ];

        $uri = Client::buildUri("/channels/:channel/videos", ["channel" => $channel]);
        return $this->get($uri, $query);
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
    public function getChannelFollowers($channel, $limit, $cursor = null, $direction = "desc") {
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

        $uri = Client::buildUri("/channels/:channel/follows", ["channel" => $channel]);
        return $this->get($uri, $query);
    }

    /**
     * Returns a list of user objects who are editors of the authenticated user.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#get-channelschanneleditors
     *
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function getEditors() {
        $this->requireAuthentication();
        $this->requireScope(Scope::ReadChannel);

        $uri = Client::buildUri("/channels/:channel/editors", ["channel" => $this->username]);
        return $this->get($uri);
    }

    /**
     * Update $channel's properties.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#put-channelschannel
     *
     * @param $channel
     * @param $status
     * @param $game
     * @param $delay
     * @param $channel_feed
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    protected function updateChannel($channel, $status, $game, $delay, $channel_feed) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditChannel);

        $params = [];
        if ($status !== null) $params["status"] = $status;
        if ($game !== null) $params["game"] = $game;
        if ($delay !== null) $params["delay"] = $delay;
        if ($channel_feed !== null) $params["channel_feed_enabled"] = $channel_feed;

        $uri = Client::buildUri("/channels/:channel", ["channel" => $channel]);
        return $this->put($uri, ["channel" => $params]);
    }

    /**
     * Update $channel's $title.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#put-channelschannel
     *
     * @param $channel
     * @param $title
     * @return array
     */
    public function updateChannelTitle($channel, $title) {
        return $this->updateChannel($channel, $title, null, null, null);
    }

    /**
     * Update $channel's $game.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#put-channelschannel
     *
     * @param $channel
     * @param $game
     * @return array
     */
    public function updateChannelGame($channel, $game) {
        return $this->updateChannel($channel, null, $game, null, null);
    }

    /**
     * Set the authenticated user's stream delay.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#put-channelschannel
     *
     * @param $delay
     * @return array
     */
    public function updateChannelDelay($delay) {
        return $this->updateChannel($this->username, null, null, $delay, null);
    }

    /**
     * Enable the (currently beta) channel feed for the authenticated user.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#put-channelschannel
     *
     * @return array
     */
    public function enableChannelFeed() {
        return $this->updateChannel($this->username, null, null, null, true);
    }

    /**
     * Disable the (currently beta) channel feed for the authenticated user.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#put-channelschannel
     *
     * @return array
     */
    public function disableChannelFeed() {
        return $this->updateChannel($this->username, null, null, null, false);
    }

    /**
     * Resets the authenticated user's stream key.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#delete-channelschannelstream_key
     *
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function refreshStreamKey() {
        $this->requireAuthentication();
        $this->requireScope(Scope::StreamKeyReset);

        $uri = Client::buildUri("/channels/:channel/stream_key");
        return $this->delete($uri);
    }

    /**
     * Start a commercial on the authenticated user's channel.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#post-channelschannelcommercial
     *
     * @param int $length
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function runCommercial($length = 30) {
        $this->requireAuthentication();
        $this->requireScope(Scope::RunCommercial);

        if (!in_array($length, [30, 60, 90, 120, 150, 180])) {
            throw new \InvalidArgumentException("Length must be one of the following: 30, 60, 90, 120, 150, 180.");
        }

        $uri = Client::buildUri("/channels/:channel/commercial");
        return $this->post($uri, ["length" => $length]);
    }

    /**
     * Returns a list of team objects that $channel belongs to.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#get-channelschannelteams
     *
     * @param $channel
     * @return array
     */
    public function getTeamsFor($channel) {
        $uri = Client::buildUri("/channels/:channel/teams", ["channel" => $channel]);
        return $this->get($uri);
    }

    /**
     * Returns a list of posts that belong to the $channel's feed. Uses limit and cursor pagination.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channel_feed.md#get-feedchannelposts
     *
     * @param $channel
     * @param int $limit
     * @param null $cursor
     * @return array
     */
    public function getChannelPosts($channel, $limit = 10, $cursor = null) {
        // Make sure to test with authentication and scope differences.
        // Documentation isn't entirely clear on what is received under all situations.
        //$this->requireAuthentication();
        //$this->requireScope(Scope::ReadFeed);
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $uri = Client::buildUri("/feed/:channel/posts", ["channel" => $channel]);
        return $this->get($uri, ["limit" => $limit, "cursor" => $cursor]);
    }

    /**
     * Create a post for the authenticated user's feed. Use $share=true to tweet out the post if Twitter is connected.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channel_feed.md#post-feedchannelposts
     *
     * @param $content
     * @param null $share
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function postToFeed($content, $share = null) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditFeed);

        $params = [
            "content" => $content,
        ];

        if ($share !== null) {
            $params["share"] = $share;
        }

        $uri = Client::buildUri("/feed/:channel/posts", ["channel" => $this->username]);
        return $this->post($uri, $params);
    }

    /**
     * Returns a post with the specified $id belonging to the authenticated user.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channel_feed.md#get-feedchannelpostsid
     *
     * @param $postId
     * @return array
     */
    public function getPost($postId) {
        // Make sure to test with authentication and scope differences.
        // Documentation isn't entirely clear on what is received under all situations.
        //$this->requireAuthentication();
        //$this->requireScope(Scope::ReadFeed);

        $uri = Client::buildUri("/feed/:channel/posts/:id", ["channel" => $this->username, "id" => $postId]);
        return $this->get($uri);
    }

    /**
     * Delete the post with $postId belonging to the authenticated user.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channel_feed.md#delete-feedchannelpostsid
     *
     * @param $postId
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function deletePost($postId) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditFeed);

        $uri = Client::buildUri("/feed/:channel/posts/:id", ["channel" => $this->username, "id" => $postId]);
        return $this->delete($uri);
    }

    /**
     * Create a reaction of $emote for the post with $id on $channel.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channel_feed.md#post-feedchannelpostsidreactions
     *
     * @param $channel
     * @param $id
     * @param $emote
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function reactToPost($channel, $id, $emote) {
        // Check if this $channel should be another channel, or yourself.
        $this->requireAuthentication();
        $this->requireScope(Scope::EditFeed);

        $params = ["emote" => $emote];

        $uri = Client::buildUri("/feed/:channel/posts/:id/reactions", ["channel" => $channel, "id" => $id]);
        return $this->post($uri, $params);
    }

    /**
     * Delete a reaction to a post with $id on $channel.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channel_feed.md#delete-feedchannelpostsidreactions
     *
     * @param $channel
     * @param $id
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function deleteReactionToPost($channel, $id) {
        // Check if this $channel should be another channel, or yourself.
        $this->requireAuthentication();
        $this->requireScope(Scope::EditFeed);

        $uri = Client::buildUri("/feed/:channel/posts/:id/reactions", ["channel" => $channel, "id" => $id]);
        return $this->delete($uri);
    }

    /**
     * Returns a links object to all other chat endpoints.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/chat.md#get-chatchannel
     *
     * @param $channel
     * @return array
     */
    public function getChatEndpoints($channel) {
        $uri = Client::buildUri("/chat/:channel", ["channel" => $channel]);
        return $this->get($uri);
    }

    /**
     * Returns a list of chat badges that can be used in the $channel's chat.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/chat.md#get-chatchannelbadges
     *
     * @param $channel
     * @return array
     */
    public function getBadges($channel) {
        $uri = Client::buildUri("/chat/:channel/badges", ["channel" => $channel]);
        return $this->get($uri);
    }

    /**
     * Returns a list of all emoticon objects for Twitch.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/chat.md#get-chatemoticons
     *
     * @return array
     */
    public function getAllEmoticons() {
        $uri = Client::buildUri("/chat/emoticons");
        return $this->get($uri);
    }

    /**
     * Returns a list of emoticons.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/chat.md#get-chatemoticon_images
     *
     * @param null $emoteSets
     * @return array
     */
    public function getEmoticonImages($emoteSets = null) {
        // This function seems to be the exact same as getAllEmoticons() or so, reevaluate having this.
        $query = [];
        if ($emoteSets !== null) {
            $query["emotesets"] = $emoteSets;
        }

        $uri = Client::buildUri("/chat/emoticon_images");
        return $this->get($uri, $query);
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
    public function getUsersFollowers($user, $limit = 25, $offset = 0, $direction = "desc", $sort = "created_at") {
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

        $uri = Client::buildUri("/users/:user/follows/channels", ["user" => $user]);
        return $this->get($uri, $query);
    }

    /**
     * Returns 404 Not Found if $user is not following $channel. Returns a follow object otherwise.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/follows.md#get-usersuserfollowschannelstarget
     *
     * @param $user
     * @param $channel
     * @return array
     */
    public function doesUserFollowsChannel($user, $channel) {
        $uri = Client::buildUri("/users/:user/follows/channels/:target", ["user" => $user, "target" => $channel]);
        return $this->get($uri);
    }

    /**
     * Adds the authenticated user to $channel's followers.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/follows.md#put-usersuserfollowschannelstarget
     *
     * @param $channel
     * @param bool|false $getNotifications
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function followChannel($channel, $getNotifications = false) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditUserFollows);

        $params = ["notifications" => $getNotifications];

        $uriParams = ["user" => $this->username, "target" => $channel];
        $uri = Client::buildUri("/users/:user/follows/channels/:target", $uriParams);
        return $this->put($uri, $params);
    }

    /**
     * Removes the authenticated user from $channel's followers.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/follows.md#delete-usersuserfollowschannelstarget
     *
     * @param $channel
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function unfollowChannel($channel) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditUserFollows);

        $uriParams = ["user" => $this->username, "target" => $channel];
        $uri = Client::buildUri("/users/:user/follows/channels/:target", $uriParams);
        return $this->delete($uri);
    }

    /**
     * Returns a list of stream objects that the authenticated user is following.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/users.md#get-streamsfollowed
     *
     * @param int $limit
     * @param int $offset
     * @param null $streamType
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function getFollowedStreams($limit = 25, $offset = 0, $streamType = null) {
        $this->requireAuthentication();
        $this->requireScope(Scope::UserRead);
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }
        if ($streamType != "all" && $streamType != "playlist" && $streamType != "live") {
            throw new \InvalidArgumentException("Stream type must be either 'all', 'playlist' or 'live'.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];

        if ($streamType !== null) {
            $query["stream_type"] = $streamType;
        }

        $uri = Client::buildUri("/streams/followed");
        return $this->get($uri, $query);
    }

    /**
     * Returns a list of the games objects sorted by number of current viewers on Twitch, most popular first.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/games.md#get-gamestop
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTopGames($limit = 10, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];

        $uri = Client::buildUri("/games/top");
        return $this->get($uri, $query);
    }

    /**
     * Returns a list of ingest objects.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/ingests.md#get-ingests
     *
     * @return array
     */
    public function getIngests() {
        $uri = Client::buildUri("/ingests");
        return $this->get($uri);
    }

    /**
     * Gets basic information about the API and authentication status. If authenticated, includes token status.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/root.md#get-
     *
     * @return array
     */
    public function getRoot() {
        $uri = Client::buildUri("/");
        return $this->get($uri);
    }

    /**
     * Uses the result of getRoot() to retrieve only the token status.
     *
     * @return array
     */
    public function getTokenStatus() {
        return $this->getRoot()["token"];
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
    public function searchChannels($query, $limit = 25, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $q = [
                "query" => urlencode($query),
                "limit" => $limit,
                "offset" => $offset,
        ];

        $uri = Client::buildUri("/search/channels");
        return $this->get($uri, $q);
    }

    /**
     * Returns a list of stream objects matching the search query.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md#get-searchstreams
     *
     * @param $query
     * @param int $limit
     * @param int $offset
     * @param null $hls
     * @return array
     */
    public function searchStreams($query, $limit = 25, $offset = 0, $hls = null) {
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

        $uri = Client::buildUri("/streams");
        return $this->get($uri, $q);
    }

    /**
     * Returns a list of game objects matching the search query.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md#get-searchgames
     *
     * @param $query
     * @param null $live
     * @return array
     */
    public function searchGames($query, $live = null) {
        if ($live !== false && $live !== true) {
            throw new \InvalidArgumentException("Live must be true or false.");
        }

        $q = [
                "query" => urlencode($query),
        ];

        if ($live !== null) {
            $q["live"] = $live;
        }

        $uri = Client::buildUri("/search/games");
        return $this->get($uri, $q);
    }

    /**
     * Returns a stream object if $channel is live.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/streams.md#get-streamschannel
     *
     * @param $channel
     * @return array
     */
    public function getLiveChannel($channel) {
        $uri = Client::buildUri("/streams/:channel/", ["channel" => $channel]);
        return $this->get($uri);
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
    public function getStreams($game = null, $channels = [], $limit = 25, $offset = 0, $streamType = null) {
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

        $uri = Client::buildUri("/streams");
        return $this->get($uri, $query);
    }

    /**
     * Returns a list of featured (promoted) stream objects.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/streams.md#get-streamsfeatured
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getFeaturedStreams($limit = 25, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];

        $uri = Client::buildUri("/streams/featured");
        return $this->get($uri, $query);
    }

    /**
     * Returns a summary of current streams for $game.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/streams.md#get-streamssummary
     *
     * @param $game
     * @return array
     */
    public function getStreamSummary($game) {
        $uri = Client::buildUri("/streams/summary");
        return $this->get($uri, ["game" => $game]);
    }

    /**
     * Returns a list of users subscribed to the authenticated channel as subscription objects sorted by creation date.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/subscriptions.md#get-channelschannelsubscriptions
     *
     * @param int $limit
     * @param int $offset
     * @param string $direction
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function getSubscribers($limit = 25, $offset = 0, $direction = "asc") {
        $this->requireAuthentication();
        $this->requireScope(Scope::ReadSubscribers);

        if ($direction != "desc" && $direction != "asc") {
            throw new \InvalidArgumentException("Direction must be either 'asc' or 'desc'.");
        }
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
                "limit" => $limit,
                "offset" => $offset,
                "direction" => $direction,
        ];

        $uri = Client::buildUri("/channels/:channel/subscriptions", ["channel" => $this->username]);
        return $this->get($uri, $query);
    }

    /**
     * Returns a subscription object that includes if $user is subscribed to the authenticated user.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/subscriptions.md#get-channelschannelsubscriptionsuser
     *
     * @param $user
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function isSubscribed($user) {
        $this->requireAuthentication();
        $this->requireScope(Scope::CheckSubscription);

        $uriParams = ["channel" => $this->username, "user" => $user];
        $uri = Client::buildUri("/channels/:channel/subscriptions/:user", $uriParams);
        return $this->get($uri);
    }

    /**
     * Returns a channel object for a channel that the authenticated user is subscribed to.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/subscriptions.md#get-usersusersubscriptionschannel
     *
     * @param $channel
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function getSubscribedChannel($channel) {
        $this->requireAuthentication();
        $this->requireScope(Scope::ReadSubscriptions);

        $uriParams = ["user" => $this->username, "channel" => $channel];
        $uri = Client::buildUri("/users/:user/subscriptions/:channel", $uriParams);
        return $this->get($uri);
    }

    /**
     * Returns a list of active teams.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/teams.md#get-teams
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function getTeams($limit = 25, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];

        $uri = Client::buildUri("/teams");
        return $this->get($uri, $query);
    }

    /**
     * Returns a team object for $team.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/teams.md#get-teamsteam
     *
     * @param $team
     * @return array
     */
    public function getTeamInfo($team) {
        $uri = Client::buildUri("/teams/:team", ["team" => $team]);
        return $this->get($uri);
    }

    /**
     * Returns a user object for $user.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/users.md#get-usersuser
     *
     * @param $user
     * @return array
     */
    public function getUser($user) {
        $uri = Client::buildUri("/users/:user", ["user" => $user]);
        return $this->get($uri);
    }

    /**
     * Returns a user object for the authenticated user.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/users.md#get-user
     *
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function getAuthenticatedUser() {
        $this->requireAuthentication();
        $this->requireScope(Scope::UserRead);

        $uri = Client::buildUri("/user");
        return $this->get($uri);
    }

    /**
     * Returns a video object with $id.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/videos.md#get-videosid
     *
     * @param $id
     * @return array
     */
    public function getVideo($id) {
        $uri = Client::buildUri("/videos/:id", ["id" => $id]);
        return $this->get($uri);
    }

    /**
     * Returns a list of top videos in a given $period sorted by views.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/videos.md#get-videostop
     *
     * @param int $limit
     * @param int $offset
     * @param null $game
     * @param string $period
     * @return array
     */
    public function getTopVideos($limit = 10, $offset = 0, $game = null, $period = "week") {
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

        $uri = Client::buildUri("/videos/top");
        return $this->get($uri, $query);
    }

    /**
     * Returns a list of video objects from channels that the authenticated user is following.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/users.md#get-videosfollowed
     *
     * @param int $limit
     * @param int $offset
     * @return array
     * @throws InsufficientScopeException
     * @throws NotAuthenticatedException
     */
    public function getFollowedVideos($limit = 10, $offset = 0) {
        $this->requireAuthentication();
        $this->requireScope(Scope::UserRead);

        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
                "limit" => $limit,
                "offset" => $offset,
        ];

        $uri = Client::buildUri("/videos/followed");
        return $this->get($uri, $query);
    }

    /**
     * Build a header array to pass to the RequestInterface.
     *
     * @return array
     */
    private function getHeaders() {
        $headers = ["Accept" => Client::ACCEPT_HEADER, "Client-ID" => $this->config["ClientId"]];
        if ($this->token !== null) {
            $headers["Authorization"] = "OAuth " . $this->token;
        }

        return $headers;
    }

    /**
     * Create a GET request through the RequestInterface.
     *
     * @param $uri
     * @param array $query
     * @return ResponseInterface
     */
    private function get($uri, array $query = []) {
        return $this->client->get($uri, $query, $this->getHeaders());
    }

    /**
     * Create a DELETE request through the RequestInterface.
     *
     * @param $uri
     * @param array $query
     * @return ResponseInterface
     */
    private function delete($uri, array $query = []) {
        return $this->client->delete($uri, $query, $this->getHeaders());
    }

    /**
     * Create a POST request through the RequestInterface.
     *
     * @param $uri
     * @param array $parameters
     * @return ResponseInterface
     */
    private function post($uri, array $parameters = []) {
        return $this->client->post($uri, $parameters, $this->getHeaders());
    }

    /**
     * Create a PUT request through the RequestInterface.
     *
     * @param $uri
     * @param array $parameters
     * @return ResponseInterface
     */
    private function put($uri, array $parameters = []) {
        return $this->client->put($uri, $parameters, $this->getHeaders());
    }
}