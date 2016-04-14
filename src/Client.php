<?php namespace MagnesiumOxide\TwitchApi;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException;
use MagnesiumOxide\TwitchApi\Exception\NotAuthenticatedException;

/**
 * A wrapper on for the Twitch.tv API.
 * @package MagnesiumOxide\TwitchApi
 */
class Client {
    /** @const The current base url that all requests will use */
    CONST BASE_URL = "https://api.twitch.tv/kraken";
    /** @const The API version used is denoted with the Accept header so it will be attached to all requests. */
    CONST ACCEPT_HEADER = "application/vnd.twitchtv.v3+json";
    /** @var ClientInterface The client that will be used to make the requests. */
    protected $client;
    /** @var ConfigRepository A clean wrapper around the configuration file. */
    protected $config;
    /** @var null|string The access token for returned by the API once authenticated, null otherwise. */
    protected $token = null;
    /** @var array List of the scopes that have been granted. */
    protected $scope = [];
    /** @var array List of links that have been returned by the API. Considering hardcoding it.*/
    protected $links = [];
    /** @var null|string The authenticated user's username on Twitch, null otherwise. */
    protected $username = null;

    public function __construct(ConfigRepository $config, ClientInterface $client) {
        $this->config = $config ? $config : new ConfigRepository();
        $this->client = $client;

        $this->updateLinks();
    }

    /** @return array */
    public function getLinks() {
        return $this->links;
    }

    /** @return ConfigRepository */
    public function getConfig() {
        return $this->config;
    }

    /** @return null|string */
    public function getUsername() {
        return $this->username;
    }

    /**  @return array */
    public function getScope() {
        return $this->scope;
    }

    /**
     * Get the URL to send direct a user at to authenticate.
     *
     * @return string
     */
    public function getAuthenticationUrl() {
        $params = [
            "response_type" => "code",
            "client_id" => $this->config["ClientId"],
            "redirect_uri" => $this->config["RedirectUri"],
            "scope" => implode("+", $this->config["scope"]),
            "state" => $this->config["state"],
        ];
        return Client::BASE_URL . "/oauth2/authorize?" . http_build_query($params);
    }

    public function authenticate($code) {
        $params = [
            "client_id" => $this->config["ClientId"],
            "client_secret" => $this->config["client_secret"],
            "grant_type" => "authorization_code",
            "redirect_uri" => $this->config["redirect_uri"],
            "code" => $code,
            "state" => $this->config["state"],
        ];

        $response = $this->post(Client::BASE_URL . "oauth2/token", $params);
        if (isset($response->error)) {
            throw new \Exception; // REVISE THIS. Test what errors could be given.
        }
        $this->token = $response["access_token"];
        $this->scope = $response["scope"];

        $response = $this->get(Client::BASE_URL);

        $this->links = $response["_links"];
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
        if (!in_array($scope, $this->scope)) {
            throw InsufficientScopeException::createException($scope);
        }
    }

    public function getBlockedUsers($limit = 25, $offset = 0) {
        $this->requireAuthentication();
        $this->requireScope(Scope::ReadUserBlocks);

        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        return $this->get($this->links["users"] . "/blocks", ["limit" => $limit, "offset" => $offset]);
    }

    public function blockUser($target) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditUserBlocks);

        return $this->put($this->links["users"] . "/blocks/" . $target);
    }

    public function unblockUser($target) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditUserBlocks);

        return $this->delete($this->links["users"] . "/blocks/" . $target);
    }

    public function getChannel($channel) {
        return $this->get($this->links["channel"] . "s/" . $channel);
    }

    public function getAuthenticatedChannel() {
        $this->requireAuthentication();
        $this->requireScope(Scope::ReadChannel);

        return $this->getChannel($this->username);
    }

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

        return $this->get($this->links["channel"] . "s/". $channel . "/videos", $query);
    }

    public function getFollowing($channel, $limit, $cursor = null, $direction = "desc") {
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

        return $this->get($this->links["channel"] . "s/" . $channel . "/follows", $query);
    }

    public function getEditors() {
        $this->requireAuthentication();
        $this->requireScope(Scope::ReadChannel);

        return $this->get($this->links["channels"] . "/editors");
    }

    protected function updateChannel($channel, $status, $game, $delay, $channel_feed) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditChannel);

        $params = [];
        if ($status !== null) $params["status"] = $status;
        if ($game !== null) $params["game"] = $game;
        if ($delay !== null) $params["delay"] = $delay;
        if ($channel_feed !== null) $params["channel_feed_enabled"] = $channel_feed;

        return $this->put($this->links["channel"] . "s/" . $channel, $params);
    }

    public function updateChannelTitle($channel, $title) {
        return $this->updateChannel($channel, $title, null, null, null);
    }

    public function updateChannelGame($channel, $game) {
        return $this->updateChannel($channel, null, $game, null, null);
    }

    public function updateChannelDelay($delay) {
        return $this->updateChannel($this->username, null, null, $delay, null);
    }

    public function enableChannelFeed() {
        return $this->updateChannel($this->username, null, null, null, true);
    }

    public function disableChannelFeed() {
        return $this->updateChannel($this->username, null, null, null, false);
    }

    public function refreshStreamKey() {
        $this->requireAuthentication();
        $this->requireScope(Scope::StreamKeyReset);

        return $this->delete($this->links["channels"] . "/stream_key");
    }

    public function runCommercial($length = 30) {
        $this->requireAuthentication();
        $this->requireScope(Scope::RunCommercial);

        if (!in_array($length, [30, 60, 90, 120, 150, 180])) {
            throw new \InvalidArgumentException("Length must be one of the following: 30, 60, 90, 120, 150, 180.");
        }

        return $this->post($this->links["channels"] . "/commercial", [$length]);
    }

    public function getTeamsFor($channel) {
        return $this->get($this->links["channel"] . "s/" . $channel . "/teams");
    }

    public function getChannelPosts($channel, $limit = 10, $cursor = null) {
        // Make sure to test with authentication and scope differences.
        // Documentation isn't entirely clear on what is received under all situations.
        //$this->requireAuthentication();
        //$this->requireScope(Scope::ReadFeed);
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        return $this->get(Client::BASE_URL . "/feed/" . $channel . "/posts", ["limit" => $limit, "cursor" => $cursor]);
    }

    public function postToFeed($content, $share = null) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditFeed);

        $params = [
            "content" => $content,
        ];

        if ($share !== null) {
            $params["share"] = $share;
        }

        return $this->post(Client::BASE_URL . "/feed" . $this->username . "/posts", $params);
    }

    public function getPost($postId) {
        // Make sure to test with authentication and scope differences.
        // Documentation isn't entirely clear on what is received under all situations.
        //$this->requireAuthentication();
        //$this->requireScope(Scope::ReadFeed);

        return $this->get(Client::BASE_URL . "/feed/" . $this->username . "/posts/" . $postId);
    }

    public function deletePost($postId) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditFeed);

        return $this->delete(Client::BASE_URL . "/feed/" . $this->username . "/posts/" . $postId);
    }

    public function reactToPost($id, $emote) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditFeed);

        $params = ["emote" => $emote];

        return $this->post(Client::BASE_URL . "/feed" . $this->username . "/posts/" . $id . "/reactions", $params);
    }

    public function deleteReactionToPost($id) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditFeed);

        return $this->post(Client::BASE_URL . "/feed" . $this->username . "/posts/" . $id . "/reactions");
    }

    public function getChatEndpoints($channel) {
        return $this->get(Client::BASE_URL . "/chat/" . $channel);
    }

    public function getBadges($channel) {
        return $this->get(Client::BASE_URL . "/chat/" . $channel . "/badges");
    }

    public function getAllEmoticons() {
        return $this->get(Client::BASE_URL . "/chat/emoticons");
    }

    public function getEmoticonImages($emoteSets = null) {
        // This function seems to be the exact same as getAllEmoticons() or so, reevaluate having this.
        $params = [];
        if ($emoteSets !== null) {
            $params["emotesets"] = $emoteSets;
        }

        return $this->get(Client::BASE_URL . "/chat/emoticon_images");
    }

    public function getChannelsFollowers() {
        throw new \Exception("Not yet implemented.");
    }

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

        return $this->get($this->links["user"] . "s/" . $user . "/follows/channels", $query);
    }

    public function doesUserFollowsChannel($user, $channel) {
        return $this->get($this->links["user"] . "s/" . $user . "/follows/channels/" . $channel);
    }

    public function followChannel($channel, $getNotifications = false) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditUserFollows);

        $params = ["notifications" => $getNotifications];
        return $this->put($this->links["users"] . "/follows/channels/" . $channel, $params);
    }

    public function unfollowChannel($channel) {
        $this->requireAuthentication();
        $this->requireScope(Scope::EditUserFollows);

        return $this->delete($this->links["users"] . "/follows/channels/" . $channel);
    }

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

        return $this->get($this->links["streams"] . "/followed", $query);
    }

    public function getTopGames($limit = 10, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];

        return $this->get(Client::BASE_URL . "/games/top", $query);
    }

    public function getIngests() {
        return $this->get($this->links["ingest"]);
    }

    public function getRoot() {
        return $this->get(Client::BASE_URL);
    }

    public function getTokenStatus() {
        return $this->getRoot()["token"];
    }

    public function updateLinks() {
        $this->links = $this->getRoot()["_links"];
    }

    public function searchChannels($query, $limit = 25, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $q = [
                "query" => urlencode($query),
                "limit" => $limit,
                "offset" => $offset,
        ];

        return $this->get($this->links["search"] . "/channels", $q);
    }

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

        return $this->get($this->links["search"] . "/streams", $q);
    }

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

        return $this->get($this->links["search"] . "/streams", $q);
    }

    public function getLiveChannel($channel) {
        return $this->get($this->links["streams"] . "/" . $channel);
    }

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

        return $this->get($this->links["streams"], $query);
    }

    public function getFeaturedStreams($limit = 25, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];

        return $this->get($this->links["streams"] . "/featured", $query);
    }

    public function getStreamSummary($game) {
        return $this->get($this->links["streams"] . "/summary", ["game" => $game]);
    }

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

        return $this->get($this->links["channels"] . "/subscriptions", $query);
    }

    public function isSubscribed($user) {
        $this->requireAuthentication();
        $this->requireScope(Scope::CheckSubscription);

        return $this->get($this->links["channels"] . "/subscriptions/" . $user);
    }

    public function getSubscribedChannel($channel) {
        $this->requireAuthentication();
        $this->requireScope(Scope::ReadSubscriptions);

        return $this->get($this->links["users"] . "/subscriptions/" . $channel);
    }

    public function getTeams($limit = 25, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];

        return $this->get($this->links["teams"], $query);
    }

    public function getTeamInfo($team) {
        return $this->get($this->links["teams"] . "/" . $team);
    }

    public function getUser($user) {
        return $this->get($this->links["user"] . "s/" . $user);
    }

    public function getAuthenticatedUser() {
        $this->requireAuthentication();
        $this->requireScope(Scope::UserRead);

        return $this->get($this->links["user"]);
    }

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

        return $this->get(Client::BASE_URL . "/videos/followed", $query);
    }

    public function getVideo($id) {
        return $this->get(Client::BASE_URL . "/videos/" . $id);
    }

    /**
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

        return $this->get(Client::BASE_URL . "/videos/top", $query);
    }

    /**
     * @param $uri
     * @param array $query
     * @param array $options
     * @return array
     */
    private function get($uri, $query = [], $options = []) {
        if (!empty($query)) {
            if (isset($options["query"])) {
                $options["query"] = $query + $options["query"];
            } else {
                $options["query"] = $query;
            }
        }

        return $this->request('GET', $uri, $options);
    }

    /**
     * @param $uri
     * @param array $parameters
     * @param array $options
     * @return array
     */
    private function put($uri, $parameters = [], $options = []) {
        if (!empty($parameters)) {
            if (isset($options["form_params"])) {
                $options["form_params"] = $parameters + $options["form_params"];
            } else {
                $options["form_params"] = $parameters;
            }
        }

        return $this->request('PUT', $uri, $options);
    }

    /**
     * @param $uri
     * @param array $parameters
     * @param array $options
     * @return array
     */
    private function post($uri, $parameters = [], $options = []) {
        if (!empty($parameters)) {
            if (isset($options["form_params"])) {
                $options["form_params"] = $parameters + $options["form_params"];
            } else {
                $options["form_params"] = $parameters;
            }
        }

        return $this->request('POST', $uri, $options);
    }

    /**
     * @param $uri
     * @return array
     */
    private function delete($uri) {
        return $this->request('DELETE', $uri, []);
    }

    /**
     * @param $method
     * @param $uri
     * @param $options
     * @return array
     */
    private function request($method, $uri, $options) {
        // Make sure to add the Accept header to every request since it has the API version on it.
        // If Accept already exists, trust the user with what they want there.
        if (isset($options["headers"])) {
            $options["headers"] = $options["headers"] + ["Accept" => Client::ACCEPT_HEADER];
        } else {
            $options["headers"] = ["Accept" => Client::ACCEPT_HEADER];
        }

        // If the user is authenticated, there is no harm in also sending the authorisation code.
        // I think.
        if ($this->token !== null) {
            $options["headers"] = $options["headers"] + ["Authorization" => "OAuth " . $this->token];
        }

        try {
            $response = $this->client->request($method, $uri, $options);
            $result = json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            $result = json_decode($e->getResponse()->getBody(), true);
        }

        return $result;
    }
}