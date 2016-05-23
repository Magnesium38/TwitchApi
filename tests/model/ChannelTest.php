<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use MagnesiumOxide\TwitchApi\Model\BaseModel;
use MagnesiumOxide\TwitchApi\Model\Channel;

class ChannelTest extends BaseTest {
    /** @var Channel */
    private $channel;
    private $channelJson;
    protected $class = Channel::class;

    public function setUp() {
        $channelJson = '{"mature":false,"status":"test status","broadcaster_language":"en","display_name":'
            . '"test_channel","game":"Gaming Talk Shows","delay":null,"language":"en","_id":12345,"name":'
            . '"test_channel","created_at":"2007-05-22T10:39:54Z","updated_at":"2015-02-12T04:15:49Z","logo":'
            . '"http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-profile_image-94a42b3a13c31c02-'
            . '300x300.jpeg","banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-channel_'
            . 'header_image-08dd874c17f39837-640x125.png","video_banner":"http://static-cdn.jtvnw.net/'
            . 'jtv_user_pictures/test_channel-channel_offline_image-b314c834d210dc1a-640x360.png","background":'
            . 'null,"profile_banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-profile_'
            . 'banner-6936c61353e4aeed-480.png","profile_banner_background_color":null,"partner":true,"url":'
            . '"http://www.twitch.tv/test_channel","views":49144894,"followers":215780,"_links":{"self":'
            . '"https://api.twitch.tv/kraken/channels/test_channel","follows":"https://api.twitch.tv/kraken/'
            . 'channels/test_channel/follows","commercial":"https://api.twitch.tv/kraken/channels/test_channel/'
            . 'commercial","stream_key":"https://api.twitch.tv/kraken/channels/test_channel/stream_key","chat":'
            . '"https://api.twitch.tv/kraken/chat/test_channel","features":"https://api.twitch.tv/kraken/'
            . 'channels/test_channel/features","subscriptions":"https://api.twitch.tv/kraken/channels/'
            . 'test_channel/subscriptions","editors":"https://api.twitch.tv/kraken/channels/test_channel/'
            . 'editors","teams":"https://api.twitch.tv/kraken/channels/test_channel/teams","videos":'
            . '"https://api.twitch.tv/kraken/channels/test_channel/videos"}}';
        $channelArray = json_decode($channelJson, true);

        $this->channelJson = $channelJson;
        $this->channel = Channel::create($channelArray);
    }

    public function testIsMature() {
        $this->assertFalse($this->channel->isMature());
    }

    public function testGetStatus() {
        $this->assertEquals("test status", $this->channel->getStatus());
    }

    public function testGetBroadcasterLanguage() {
        $this->assertEquals("en", $this->channel->getBroadcasterLanguage());
    }

    public function testGetDisplayName() {
        $this->assertEquals("test_channel", $this->channel->getDisplayName());
    }

    public function testGetGame() {
        $this->assertEquals("Gaming Talk Shows", $this->channel->getGame());
    }

    public function testGetDelay() {
        $this->assertNull($this->channel->getDelay());
    }

    public function testGetId() {
        $this->assertEquals("12345", $this->channel->getId());
    }

    public function testGetUsername() {
        $this->assertEquals("test_channel", $this->channel->getUsername());
    }

    public function testGetCreatedAt() {
        $this->assertEquals(new \DateTime("2007-05-22T10:39:54Z"), $this->channel->getCreatedAt());
    }

    public function testEmptyCreatedAt() {
        $this->assertEquals(null, Channel::create([])->getCreatedAt());
    }

    public function testGetLastUpdatedAt() {
        $this->assertEquals(new \DateTime("2015-02-12T04:15:49Z"), $this->channel->getLastUpdatedAt());
    }

    public function testEmptyLastUpdatedAt() {
        $this->assertEquals(null, Channel::create([])->getLastUpdatedAt());
    }

    public function testGetLogo() {
        $url = "http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-profile_image-94a42b3a13c31c02-300x300.jpeg";
        $this->assertEquals($url, $this->channel->getLogo());
    }

    public function testGetBanner() {
        $url = 'http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'test_channel-channel_header_image-08dd874c17f39837-640x125.png';
        $this->assertEquals($url, $this->channel->getBanner());
    }

    public function testGetVideoBanner() {
        $url = 'http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'test_channel-channel_offline_image-b314c834d210dc1a-640x360.png';
        $this->assertEquals($url, $this->channel->getVideoBanner());
    }

    public function testGetBackground() {
        $this->assertNull($this->channel->getBackground());
    }

    public function testGetProfileBanner() {
        $url = "http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-profile_banner-6936c61353e4aeed-480.png";
        $this->assertEquals($url, $this->channel->getProfileBanner());
    }

    public function testGetProfileBannerBackgroundColor() {
        $this->assertNull($this->channel->getProfileBannerBackgroundColor());
    }

    public function testIsPartner() {
        $this->assertTrue($this->channel->isPartner());
    }

    public function testGetUrl() {
        $this->assertEquals("http://www.twitch.tv/test_channel", $this->channel->getUrl());
    }

    public function testGetNumViews() {
        $this->assertEquals(49144894, $this->channel->getNumViews());
    }

    public function testGetNumFollowers() {
        $this->assertEquals(215780, $this->channel->getNumFollowers());
    }

    public function testGetChannel() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $channel = "test_channel";

        $mockedResponse = $this->mockResponse($this->channelJson, 200);

        $url = BaseModel::BASE_URL . "/channels/{$channel}";
        $client->get($url, [], $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("12345", Channel::getChannel($channel)->getId());
    }

    public function testSearchChannels() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $query = [
                "query" => "starcraft",
                "limit" => 25,
                "offset" => 0,
        ];

        $body = '{"channels":[' . $this->channelJson . ']}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/search/channels";
        $client->get($url, $query, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("12345", Channel::searchChannels("starcraft")[0]->getId());
    }
}
