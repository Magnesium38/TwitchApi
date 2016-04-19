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
    public function testGetBlockedUsers() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testBlockUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testUnblockUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }

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
    public function testGetAuthenticatedChannel() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetChannelVideos() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetChannelFollowers() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetEditors() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testUpdateChannel() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testResetStreamKey() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testRunCommercial() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetTeamsFor() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Channel Feed Routes
    public function testGetChannelPosts() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testPostToFeed() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetPost() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testDeletePost() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testReactToPost() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testDeleteReactionToPost() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Chat Routes
    public function testGetChatEndpoints() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetBadges() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetAllEmoticons() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetEmoticonImages() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Follows Routes
    public function testGetUsersFollowers() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testDoesUserFollowChannel() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testFollowChannel() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testUnfollowChannel() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetFollowedStreams() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Games Routes
    public function testGetTopGames() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Ingests Routes
    public function testGetIngests() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Root Routes
    public function testGetRoot() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Search Routes
    public function testSearchChannels() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testSearchStreams() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testSearchGames() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Streams Routes
    public function testGetLiveChannel() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetStreams() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetFeaturedStreams() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetStreamSummary() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Subscriptions Routes
    public function testGetSubscribers() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testIsSubscribed() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetSubscribedChannel() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Teams Routes
    public function testGetTeams() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetTeamInfo() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Users Routes
    public function testGetUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetAuthenticatedUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }

    // Videos Routes
    public function testGetVideo() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetTopVideos() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetFollowedVideos() { $this->markTestIncomplete('This test has not been implemented yet.'); }
}