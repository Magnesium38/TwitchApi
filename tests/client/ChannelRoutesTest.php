<?php

use MagnesiumOxide\TwitchApi\Client as Api;

class ChannelRoutesTest extends BaseClientTest {
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
}