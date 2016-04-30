<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use MagnesiumOxide\TwitchApi\Model\User;

class UserTest extends BaseTest {
    protected $class = User::class;
    public function testGetUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testGetAuthenticationUrl() {
        $this->markTestIncomplete('This test has not been implemented yet.');
        /*
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
        */
    }

    public function testGetAuthenticatedUser() {
        $this->markTestIncomplete('This test has not been implemented yet.');
        /*
        public function testAuthenticate() {
            $api = $this->getApi(["scopes" => [Scope::ChatLogin, Scope::ReadSubscribers]]);
            $this->authenticate($api, [Scope::ChatLogin, Scope::ReadSubscribers]);
            $this->assertEquals($api->getScope(), [Scope::ChatLogin, Scope::ReadSubscribers]);
        }
        */
    }
}
