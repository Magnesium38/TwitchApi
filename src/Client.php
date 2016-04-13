<?php namespace MagnesiumOxide\TwitchApi;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

class Client {
    CONST BASE_URL = "https://api.twitch.tv/kraken/";
    protected $client;
    protected $config;
    protected $token = null;
    protected $scope = [];
    protected $links = [];
    protected $username = null;

    /*
     * Links should end up with the following.
     *  /user
     *  /channel
     *  /search
     *  /streams
     *  /ingests
     *  /teams
     */

    public function __construct(ConfigRepository $config, ClientInterface $client) {
        $this->config = $config ? $config : new ConfigRepository();
        $this->client = $client;

        $this->updateLinks();
    }

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
            throw new \Exception; // REVISE THIS. Probably create an exception class.
        }
        $this->token = $response->access_token;
        $this->scope = explode(" ", $response->scope);

        $response = $this->get(Client::BASE_URL);
        $this->links = $response["_links"];
        $this->username = $response["token"]["user_name"];
    }

    protected function requireAuthentication() {
        if ($this->token === null) {
            throw new \Exception; // REVISE THIS. Create an exception to better show the issue.
        }
    }

    protected function requireScope($scope) {
        if (!in_array($scope, $this->scope)) {
            throw new \Exception; // REVISE THIS. Create an exception class that'll reflect better.
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

    public function getFollowers($user, $limit = 25, $offset = 0, $direction = "desc", $sort = "created_at") {
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

    private function get($uri, $query = [], $options = []) {
        if (!empty($query)) {
            $options["query"] = $query + $options["query"];
        }

        return $this->request('GET', $uri, $options);
    }

    /*private function head($uri, $query = [], $options = []) {
        if (!empty($query)) {
            $options["query"] = $query + $options["query"];
        }

        return $this->request('HEAD', $uri, $options);
    }*/

    private function put($uri, $parameters = [], $options = []) {
        if (!empty($parameters)) {
            $options["form_params"] = $parameters + $options["form_params"];
        }

        return $this->request('PUT', $uri, $options);
    }

    private function post($uri, $parameters = [], $options = []) {
        if (!empty($parameters)) {
            $options["form_params"] = $parameters + $options["form_params"];
        }

        return $this->request('POST', $uri, $options);
    }

    /*private function patch($uri, $parameters = [], $options = []) {
        return $this->request('PATCH', $uri, $options);
    }*/

    private function delete($uri) {
        return $this->request('DELETE', $uri, []);
    }

    private function request($method, $uri, $options) {
        // If the user is authenticated, there is no harm in also sending the authorisation code.
        // I think.
        if ($this->token !== null) {
            $options["headers"] = $options["headers"] + ["Authorization" => "OAuth " . $this->token];
        }

        // Make sure to add the Accept header to every request since it has the API version on it.
        // If Accept already exists, trust the user with what they want there.
        $options["headers"] = $options["headers"] + ["Accept" => "application/vnd.twitchtv.v3+json"];

        try {
            $response = $this->client->request($method, $uri, $options);
            $result = json_decode($response->getBody());
        } catch (RequestException $e) {
            $result = json_decode($e->getResponse()->getBody());
        }

        return $result;
    }
}