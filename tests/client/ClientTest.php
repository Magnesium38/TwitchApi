<?php

use MagnesiumOxide\TwitchApi\Client as Api;
use MagnesiumOxide\TwitchApi\ConfigRepository;
use MagnesiumOxide\TwitchApi\Scope;

class ClientTest extends BaseClientTest {
    public function testConstructor() {
        $response = $this->getBaseLinks();
        $this->requestShouldBeMade("GET", Api::BASE_URL, [], $response, 200, true);

        $api = new Api($this->client->reveal(), $this->prophesize(ConfigRepository::class)->reveal());
        $this->assertNull($api->getUsername());

        $this->authenticate($api, []);
        $this->assertEquals($this->username, $api->getUsername());
    }

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
        $api = $this->getApi(["scopes" => [Scope::ChatLogin, Scope::ReadSubscribers]]);
        $this->authenticate($api, [Scope::ChatLogin, Scope::ReadSubscribers]);
        $this->assertEquals($api->getScope(), [Scope::ChatLogin, Scope::ReadSubscribers]);
    }
}
