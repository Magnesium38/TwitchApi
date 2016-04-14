<?php

use GuzzleHttp\Client;
use MagnesiumOxide\TwitchApi\Client as Api;
use MagnesiumOxide\TwitchApi\ConfigRepository;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;

class ClientTest extends PHPUnit_Framework_TestCase {
    private $client;
    private $response;
    private $config;
    private $username = "TestUser";
    private $token = "TestUserAuthToken";

    public function setUp() {
        $this->client = $this->prophesize(Client::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->config = $this->prophesize(ConfigRepository::class);
    }

    private function mockConfig(array $scopes) {
        $this->config->offsetExists(Argument::any())
            ->will(function ($arg) {
                if ($arg == "ClientId") return true;
                if ($arg == "ClientSecret") return true;
                if ($arg == "RedirectUrl") return true;
                if ($arg == "State") return true;
                if ($arg == "Scope") return true;
                return false;
            });

        $this->config->offsetGet(Argument::any())
            ->will(function ($args) use ($scopes) {
                $arg = $args[0];
                if ($arg == "ClientId") return "YOUR_CLIENT_ID";
                if ($arg == "ClientSecret") return "YOUR_CLIENT_SECRET";
                if ($arg == "RedirectUrl") return "YOUR_REDIRECT_URI";
                if ($arg == "State") return "YOUR_STATE";
                if ($arg == "Scope") return $scopes;
                return null;
            });
    }

    private function getApi() {
        $response = '{"_links":{"user":"https://api.twitch.tv/kraken/user",'
                . '"channel":"https://api.twitch.tv/kraken/channel",'
                . '"search":"https://api.twitch.tv/kraken/search",'
                . '"streams":"https://api.twitch.tv/kraken/streams",'
                . '"ingests":"https://api.twitch.tv/kraken/ingests",'
                . '"teams":"https://api.twitch.tv/kraken/teams"},'
                . '"token":{"valid":false,"authorization":null}}';

        $this->requestShouldBeMade("GET", Api::BASE_URL, null, $response);
        return new Api($this->config->reveal(), $this->client->reveal());
    }

    private function requestShouldBeMade($method, $uri, $params, $response, $authenticated = false) {
        if ($params !== null) {
            $params = $params + ["headers" => ["Accept" => Api::ACCEPT_HEADER]];
        } else {
            $params = ["headers" => ["Accept" => Api::ACCEPT_HEADER]];
        }

        if ($authenticated === true) {
            $params["headers"] = $params["headers"] + ["Authorization" => "OAuth " . $this->token];
        }

        $mockedResponse = $this->prophesize(ResponseInterface::class);
        $mockedResponse->getBody()
            ->shouldBeCalled()
            ->willReturn($response);

        $this->client->request($method, $uri, $params)
            ->shouldbeCalled()
            ->willReturn($mockedResponse);
    }

    public function authenticate(Api $api, array $scopes) {
        $this->mockConfig($scopes);

        $code = "ThisIsTheBestAuthenticationCode";
        $response = '{"access_token":"' . $this->token . '",'
            .'"scope":["' . implode(",", $scopes) . '"]}';

        $params = [
            "form_params" => [
                "client_id" => $api->getConfig()["ClientId"],
                "client_secret" => $api->getConfig()["client_secret"],
                "grant_type" => "authorization_code",
                "redirect_uri" => $api->getConfig()["redirect_uri"],
                "code" => $code,
                "state" => $api->getConfig()["state"],
            ],
        ];

        $this->requestShouldBeMade("POST", Api::BASE_URL . "oauth2/token", $params, $response);

        $response2 = '{"token":{"authorization":{'
            . '"scopes":["' . implode('","', $scopes) . '"],'
            . '"created_at":"2012-05-08T21:55:12Z","updated_at":"2012-05-17T21:32:13Z"},'
            . '"user_name":"test_user1","valid":true},'
            . '"_links":{"channel":"https://api.twitch.tv/kraken/channel",'
            . '"users":"https://api.twitch.tv/kraken/users/'. $this->username .'",'
            . '"user":"https://api.twitch.tv/kraken/user",'
            . '"channels":"https://api.twitch.tv/kraken/channels/'. $this->username .'",'
            . '"chat":"https://api.twitch.tv/kraken/chat/'. $this->username .'",'
            . '"streams":"https://api.twitch.tv/kraken/streams",'
            . '"ingests":"https://api.twitch.tv/kraken/ingests",'
            . '"teams":"https://api.twitch.tv/kraken/teams",'
            . '"search":"https://api.twitch.tv/kraken/search"}}';

        $this->requestShouldBeMade("GET", Api::BASE_URL, null, $response2, true);

        $api->authenticate($code);
    }

    public function testConstructor() {
        $response = '{"_links":{"user":"https://api.twitch.tv/kraken/user",'
            . '"channel":"https://api.twitch.tv/kraken/channel",'
            . '"search":"https://api.twitch.tv/kraken/search",'
            . '"streams":"https://api.twitch.tv/kraken/streams",'
            . '"ingests":"https://api.twitch.tv/kraken/ingests",'
            . '"teams":"https://api.twitch.tv/kraken/teams"},'
            . '"token":{"valid":false,"authorization":null}}';

        $links = [
                "user" => "https://api.twitch.tv/kraken/user",
                "channel" => "https://api.twitch.tv/kraken/channel",
                "search" => "https://api.twitch.tv/kraken/search",
                "streams" => "https://api.twitch.tv/kraken/streams",
                "ingests" => "https://api.twitch.tv/kraken/ingests",
                "teams" => "https://api.twitch.tv/kraken/teams",
        ];

        $this->requestShouldBeMade("GET", Api::BASE_URL, null, $response);

        $api = new Api($this->config->reveal(), $this->client->reveal());
        $this->assertEquals($links, $api->getLinks());

        $this->authenticate($api, []);

        $links = [
            "channel" => "https://api.twitch.tv/kraken/channel",
            "users" => "https://api.twitch.tv/kraken/users/". $this->username,
            "user" => "https://api.twitch.tv/kraken/user",
            "channels" => "https://api.twitch.tv/kraken/channels/". $this->username,
            "chat" => "https://api.twitch.tv/kraken/chat/". $this->username,
            "streams" => "https://api.twitch.tv/kraken/streams",
            "ingests" => "https://api.twitch.tv/kraken/ingests",
            "teams" => "https://api.twitch.tv/kraken/teams",
            "search" => "https://api.twitch.tv/kraken/search",
        ];

        $this->assertEquals($links, $api->getLinks());
    }

    // Blocks Routes
    public function testGetBlockedUsers() {}
    public function testBlockUser() {}
    public function testUnblockUser() {}

    // Channels Routes
    public function testGetChannel() {
        $response = '{"mature":false,"status":"test status","broadcaster_language":"en",'
            . '"display_name":"test_channel","game":"Gaming Talk Shows","delay":null,"language":"en",'
            . '"_id":12345,"name":"test_channel","created_at":"2007-05-22T10:39:54Z",'
            . '"updated_at":"2015-02-12T04:15:49Z","logo":"http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'test_channel-profile_image-94a42b3a13c31c02-300x300.jpeg","banner":"http://static-cdn.jtvnw.net/'
            . 'jtv_user_pictures/test_channel-channel_header_image-08dd874c17f39837-640x125.png",'
            . '"video_banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'test_channel-channel_offline_image-b314c834d210dc1a-640x360.png","background":null,'
            . '"profile_banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'test_channel-profile_banner-6936c61353e4aeed-480.png","profile_banner_background_color":"null",'
            . '"partner":true,"url":"http://www.twitch.tv/test_channel","views":49144894,"followers":215780,'
            . '"_links":{"self":"https://api.twitch.tv/kraken/channels/test_channel",'
            . '"follows":"https://api.twitch.tv/kraken/channels/test_channel/follows",'
            . '"commercial":"https://api.twitch.tv/kraken/channels/test_channel/commercial",'
            . '"stream_key":"https://api.twitch.tv/kraken/channels/test_channel/stream_key",'
            . '"chat":"https://api.twitch.tv/kraken/chat/test_channel",'
            . '"features":"https://api.twitch.tv/kraken/channels/test_channel/features",'
            . '"subscriptions":"https://api.twitch.tv/kraken/channels/test_channel/subscriptions",'
            . '"editors":"https://api.twitch.tv/kraken/channels/test_channel/editors",'
            . '"teams":"https://api.twitch.tv/kraken/channels/test_channel/teams",'
            . '"videos":"https://api.twitch.tv/kraken/channels/test_channel/videos"}}';

        $this->requestShouldBeMade("GET", Api::BASE_URL . "/channels/test_channel", null, $response);

        $api = $this->getApi();
        $result = $api->getChannel("test_channel");

        $links = [
            "self" => "https://api.twitch.tv/kraken/channels/test_channel",
            "follows" => "https://api.twitch.tv/kraken/channels/test_channel/follows",
            "commercial" => "https://api.twitch.tv/kraken/channels/test_channel/commercial",
            "stream_key" => "https://api.twitch.tv/kraken/channels/test_channel/stream_key",
            "chat" => "https://api.twitch.tv/kraken/chat/test_channel",
            "features" => "https://api.twitch.tv/kraken/channels/test_channel/features",
            "subscriptions" => "https://api.twitch.tv/kraken/channels/test_channel/subscriptions",
            "editors" => "https://api.twitch.tv/kraken/channels/test_channel/editors",
            "teams" => "https://api.twitch.tv/kraken/channels/test_channel/teams",
            "videos" => "https://api.twitch.tv/kraken/channels/test_channel/videos",
        ];

        $this->assertEquals("http://www.twitch.tv/test_channel", $result["url"]);
        $this->assertEquals($links, $result["_links"]);
        $this->assertEquals("2007-05-22T10:39:54Z", $result["created_at"]);
        $this->assertEquals("12345", $result["_id"]);

        $this->authenticate($api, []);

        $this->requestShouldBeMade("GET", Api::BASE_URL . "/channels/test_channel", null, $response, true);
        $result = $api->getChannel("test_channel");

        $this->assertEquals("http://www.twitch.tv/test_channel", $result["url"]);
        $this->assertEquals($links, $result["_links"]);
        $this->assertEquals("2007-05-22T10:39:54Z", $result["created_at"]);
        $this->assertEquals("12345", $result["_id"]);
    }
    public function testGetAuthenticatedChannel() {}
    public function testGetChannelVideos() {}
    public function testGetFollowing() {}
    public function testGetEditors() {}
    public function testUpdateChannel() {}
    public function testResetStreamKey() {}
    public function testRunCommercial() {}
    public function testGetTeamsFor() {}

    // Channel Feed Routes
    public function testGetChannelPosts() {}
    public function testPostToFeed() {}
    public function testGetPost() {}
    public function testDeletePost() {}
    public function testReactToPost() {}
    public function testDeleteReactionToPost() {}

    // Chat Routes
    public function testGetChatEndpoints() {}
    public function testGetBadges() {}
    public function testGetAllEmoticons() {}
    public function testGetEmoticonImages() {}

    // Follows Routes
    public function testGetChannelsFollowers() {}
    public function testGetUsersFollowers() {}
    public function testDoesUserFollowChannel() {}
    public function testFollowChannel() {}
    public function testUnfollowChannel() {}
    public function testGetFollowedStreams() {}

    // Games Routes
    public function testGetTopGames() {}

    // Ingests Routes
    public function testGetIngests() {}

    // Root Routes
    public function testGetRoot() {}

    // Search Routes
    public function testSearchChannels() {}
    public function testSearchStreams() {}
    public function testSearchGames() {}

    // Streams Routes
    public function testGetLiveChannel() {}
    public function testGetStreams() {}
    public function testGetFeaturedStreams() {}
    public function testGetStreamSummary() {}
    //public function testGetFollowedStreams() {}

    // Subscriptions Routes
    public function testGetSubscribers() {}
    public function testIsSubscribed() {}
    public function testGetSubscribedChannel() {}

    // Teams Routes
    public function testGetTeams() {}
    public function testGetTeamInfo() {}

    // Users Routes
    public function testGetUser() {}
    public function testGetAuthenticatedUser() {}
    //public function testGetFollowedStreams() {}
    //public function testGetFollowedVideos() {}

    // Videos Routes
    public function testGetVideo() {}
    public function testGetTopVideos() {}
    //public function testGetChannelVideos() {}
    public function testGetFollowedVideos() {}

}