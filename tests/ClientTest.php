<?php

use MagnesiumOxide\TwitchApi\Scope;
use MagnesiumOxide\TwitchApi\Client as Api;
use MagnesiumOxide\TwitchApi\ConfigRepository;
use MagnesiumOxide\TwitchApi\RequestClient;
use Prophecy\Argument;

class ClientTest extends PHPUnit_Framework_TestCase {
    private $client;
    private $username = "TestUser";
    private $token = "TestUserAuthToken";

    public function setUp() {
        $this->client = $this->prophesize(RequestClient::class);
    }

    private function getBaseLinks() {
        return [
                "_links" => [
                        "user" => "https://api.twitch.tv/kraken/user",
                        "channel" => "https://api.twitch.tv/kraken/channel",
                        "search" => "https://api.twitch.tv/kraken/search",
                        "streams" => "https://api.twitch.tv/kraken/streams",
                        "ingests" => "https://api.twitch.tv/kraken/ingests",
                        "teams" => "https://api.twitch.tv/kraken/teams",
                ],
                "token" => [
                        "valid" => false,
                        "authorization" => null,
                ]
        ];
    }

    private function mockConfig(array $config = []) {
        $mockedConfig = $this->prophesize(ConfigRepository::class);
        $mockedConfig->offsetExists(Argument::any())
            ->will(function ($arg) {
                if ($arg == "ClientId") return true;
                if ($arg == "ClientSecret") return true;
                if ($arg == "RedirectUri") return true;
                if ($arg == "State") return true;
                if ($arg == "Scope") return true;
                return false;
            });

        $mockedConfig->offsetGet(Argument::any())
            ->will(function ($args) use ($config) {
                $arg = $args[0];
                if ($arg == "ClientId") {
                    if (isset($config["ClientId"])) {
                        return $config["ClientId"];
                    } else {
                        return "YOUR_CLIENT_ID";
                    }
                }
                if ($arg == "ClientSecret") {
                    if (isset($config["ClientSecret"])) {
                        return $config["ClientSecret"];
                    } else {
                        return "YOUR_CLIENT_SECRET";
                    }
                }
                if ($arg == "RedirectUri") {
                    if (isset($config["RedirectUri"])) {
                        return $config["RedirectUri"];
                    } else {
                        return "YOUR_REDIRECT_URI";
                    }
                }
                if ($arg == "State") {
                    if (isset($config["State"])) {
                        return $config["State"];
                    } else {
                        return "YOUR_STATE";
                    }
                }
                if ($arg == "Scope") {
                    if (isset($config["scopes"])) {
                        return $config["scopes"];
                    } else {
                        return [];
                    }
                }
                return null;
            });

        return $mockedConfig;
    }

    private function getApi(array $config = []) {
        $mockedConfig = $this->mockConfig($config);
        return new Api($this->client->reveal(), $mockedConfig->reveal());
    }

    private function requestShouldBeMade($method, $uri, $params, $response, $authenticated = false) {
        $headers = ["Accept" => Api::ACCEPT_HEADER];
        if ($authenticated) {
            $headers["Authorization"] = "OAuth " . $this->token;
        }

        if ($method == "DELETE") {
            $this->client->delete($uri, $params, $headers)
                    ->shouldBeCalled()
                    ->willReturn($response);
        } else if ($method == "GET") {
            $this->client->get($uri, $params, $headers)
                    ->shouldBeCalled()
                    ->willReturn($response, $headers);
        } else if ($method == "POST") {
            $this->client->post($uri, $params, $headers)
                    ->shouldBeCalled()
                    ->willReturn($response);
        } else if ($method == "PUT") {
            $this->client->put($uri, $params, $headers)
                    ->shouldBeCalled()
                    ->willReturn($response);
        }
    }

    private function authenticate(Api $api, array $scopes) {
        $this->mockConfig($scopes);
        $code = "ThisIsTheBestAuthenticationCode";
        $response = ["access_token" => $this->token, "scope" => implode(",", $scopes)];
        $params = [
            "client_id" => $api->getConfig()["ClientId"],
            "client_secret" => $api->getConfig()["ClientSecret"],
            "grant_type" => "authorization_code",
            "redirect_uri" => $api->getConfig()["RedirectUri"],
            "code" => $code,
            "state" => $api->getConfig()["State"],
        ];

        $this->requestShouldBeMade("POST", Api::BASE_URL . "/oauth2/token", $params, $response);

        $response2 = [
            "token" => [
                "authorization" => [
                    "scopes" => implode(",", $scopes),
                    "created_at" => "2012-05-08T21:55:12Z",
                    "updated_at" => "2012-05-17T21:32:13Z",
                ],
                "user_name" => $this->username,
                "valid" => "true",
            ],
        ];

        $this->requestShouldBeMade("GET", Api::BASE_URL, [], $response2, true);

        $api->authenticate($code);
    }

    public function testConstructor() {
        $response = $this->getBaseLinks();
        $this->requestShouldBeMade("GET", Api::BASE_URL, [], $response, true);

        $api = new Api($this->client->reveal(), $this->prophesize(ConfigRepository::class)->reveal());
        $this->assertNull($api->getUsername());

        $this->authenticate($api, []);
        $this->assertEquals($this->username, $api->getUsername());
    }

    // Misc methods
    /*public function testBuildUri() {
        $expected = "https://api.twitch.tv/kraken/test/channel";
        $params = [
                "channel" => "chan",
                "test" => "hi",
        ];

        $this->assertEquals($expected, Api::buildUri("/test/channel"));
        $this->assertEquals($expected, Api::buildUri("/test/channel", []));
        $this->assertEquals($expected, Api::buildUri("/test/channel", $params));
        $this->assertNotEquals($expected, Api::buildUri("/test/:channel", $params));
        $this->assertEquals("https://api.twitch.tv/kraken/test/chan", Api::buildUri("/test/:channel", $params));
        $this->assertEquals("https://api.twitch.tv/kraken/hi/chan", Api::buildUri("/:test/:channel", $params));
        $this->assertEquals("https://api.twitch.tv/kraken/hi/channel", Api::buildUri("/:test/channel", $params));
    }*/
    public function testGetAuthenticationUrl() {
        $config = [
            "ClientId" => "MyClientId",
            "ClientSecret" => "MyClientSecret",
            "RedirectUri" => "MyRedirectUri",
            "State" => "MyState",
            "scopes" => [
                Scope::EditFeed,
                Scope::ReadFeed,
            ],
        ];

        $api = $this->getApi($config);

        $authUrl = "https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=MyClientId&"
                . "redirect_uri=MyRedirectUri&scope=channel_feed_edit%2Bchannel_feed_read&state=MyState";
        $this->assertEquals($authUrl, $api->getAuthenticationUrl());

        $api = $this->getApi();

        $authUrl = "https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=YOUR_CLIENT_ID&"
                . "redirect_uri=YOUR_REDIRECT_URI&scope=&state=YOUR_STATE";
        $this->assertEquals($authUrl, $api->getAuthenticationUrl());
    }
    public function testAuthenticate() {
        $this->authenticate($this->getApi(), [Scope::ChatLogin, Scope::ReadSubscribers]);
    }

    // Require methods
    //public function testRequireAuthentication() {}
    //public function testRequireScope() {}

    // Blocks Routes
    public function testGetBlockedUsers() {}
    public function testBlockUser() {}
    public function testUnblockUser() {}

    // Channels Routes
    public function testGetChannel() {
        $response = [
            "mature" => false,
            "status" => "test status",
            "url" => "http://www.twitch.tv/test_channel",
            "_links" => [
                "self" => "https://api.twitch.tv/kraken/channels/test_channel",
                "follows" => "https://api.twitch.tv/kraken/channels/test_channel/follows",
                "commercial" => "https://api.twitch.tv/kraken/channels/test_channel/commercial",
            ],
        ];

        $this->requestShouldBeMade("GET", Api::BASE_URL . "/channels/test_channel", [], $response);

        $api = $this->getApi();
        $result = $api->getChannel("test_channel");

        $this->assertEquals("http://www.twitch.tv/test_channel", $result["url"]);

        $this->authenticate($api, []);

        $this->requestShouldBeMade("GET", Api::BASE_URL . "/channels/test_channel", [], $response, true);
        $result = $api->getChannel("test_channel");

        $this->assertEquals("http://www.twitch.tv/test_channel", $result["url"]);
    }
    public function testGetAuthenticatedChannel() {}
    public function testGetChannelVideos() {}
    public function testGetChannelFollowers() {}
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
    //public function testGetChannelsFollowers() {}
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

    // Request Methods
    //   These are tested through the actual api.
    // public function testDelete() {}
    // public function testGet() {}
    // public function testPost() {}
    // public function testPut() {}
    // public function testAddHeaders() {}
}