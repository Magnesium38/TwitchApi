<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use MagnesiumOxide\TwitchApi\Model\BaseModel;
use MagnesiumOxide\TwitchApi\Model\Follow;

class FollowTest extends BaseTest {
    /** @var Follow */
    private $follow;
    private $followJson;
    private $channelFollowJson;
    protected $class = Follow::class;
    private $user = "test_user1";
    private $channel = "test_channel";

    public function setUp() {
        $followJson = '{"created_at":"2013-06-02T09:38:45Z","_links":{"self":"https://api.twitch.tv/'
            . 'kraken/users/test_user1/follows/channels/test_channel"},"notifications":true,"channel":'
            . '{"mature":false,"status":"test status","broadcaster_language":"en","display_name":'
            . '"test_channel","game":"Gaming Talk Shows","delay":0,"language":"en","_id":12345,"name":'
            . '"test_channel","created_at":"2007-05-22T10:39:54Z","updated_at":"2015-02-12T04:15:49Z",'
            . '"logo":"http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-profile_image-'
            . '94a42b3a13c31c02-300x300.jpeg","banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'test_channel-channel_header_image-08dd874c17f39837-640x125.png","video_banner":'
            . '"http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-channel_offline_image-'
            . 'b314c834d210dc1a-640x360.png","background":null,"profile_banner":"http://static-cdn.'
            . 'jtvnw.net/jtv_user_pictures/test_channel-profile_banner-6936c61353e4aeed-480.png",'
            . '"profile_banner_background_color":"null","partner":true,"url":"http://www.twitch.tv/'
            . 'test_channel","views":49144894,"followers":215780,"_links":{"self":"https://api.'
            . 'twitch.tv/kraken/channels/test_channel","follows":"https://api.twitch.tv/kraken/'
            . 'channels/test_channel/follows","commercial":"https://api.twitch.tv/kraken/channels/'
            . 'test_channel/commercial","stream_key":"https://api.twitch.tv/kraken/channels/'
            . 'test_channel/stream_key","chat":"https://api.twitch.tv/kraken/chat/test_channel"'
            . ',"features":"https://api.twitch.tv/kraken/channels/test_channel/features","subscriptions":'
            . '"https://api.twitch.tv/kraken/channels/test_channel/subscriptions","editors":"https://api.'
            . 'twitch.tv/kraken/channels/test_channel/editors","teams":"https://api.twitch.tv/kraken/'
            . 'channels/test_channel/teams","videos":"https://api.twitch.tv/kraken/channels/test_channel/videos"}}}';
        $followArray = json_decode($followJson, true);

        $this->followJson = $followJson;

        $this->channelFollowJson = '{"created_at":"2013-06-02T09:38:45Z","_links":{"self":'
            . '"https://api.twitch.tv/kraken/users/test_user2/follows/channels/test_user1"},'
            . '"notifications":true,"user":{"_links":{"self":"https://api.twitch.tv/kraken/'
            . 'users/test_user2"},"type":"user","bio":"test user\'s bio","logo":null,'
            . '"display_name":"test_user2","created_at":"2013-02-06T21:21:57Z","updated_at":'
            . '"2013-02-13T20:59:42Z","_id":40091581,"name":"test_user2"}}';

        $this->follow = Follow::create($followArray);
    }

    public function testGetCreatedAt() {
        $this->assertEquals(new \DateTime("2013-06-02T09:38:45Z"), $this->follow->getCreatedAt());
    }

    public function testGetNotificationStatus() {
        $this->assertTrue($this->follow->getNotificationStatus());
    }

    public function testGetUser() {
        $this->assertNull($this->follow->getUser());
    }

    public function testGetChannel() {
        $this->assertEquals("test_channel", $this->follow->getChannel()->getDisplayName());
    }

    public function testGetUserFollowers() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $query = [
                "limit" => 25,
                "offset" => 0,
                "direction" => "desc",
                "sortby" => "created_at",
        ];

        $body = '{"follows":[' . $this->followJson . ']}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/users/{$this->user}/follows/channels";
        $client->get($url, $query, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertTrue(Follow::getUserFollowers($this->user)[0]->getNotificationStatus());
    }

    public function testDoesUserFollowChannel() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $mockedResponse = $this->mockResponse($this->followJson, 200);

        $url = BaseModel::BASE_URL . "/users/{$this->user}/follows/channels/{$this->channel}";
        $client->get($url, [], $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertNotNull(Follow::doesUserFollowChannel($this->user, $this->channel));
    }

    public function testDoesUserFollowChannel404s() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $mockedResponse = $this->mockResponse($this->followJson, 404);

        $url = BaseModel::BASE_URL . "/users/{$this->user}/follows/channels/{$this->channel}";
        $client->get($url, [], $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertNull(Follow::doesUserFollowChannel($this->user, $this->channel));
    }

    public function testGetChannelFollowers() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $channel = "test_user1";

        $query = [
            "limit" => 25,
            "direction" => "desc",
        ];

        $body = '{"follows":[' . $this->channelFollowJson . ']}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/channels/{$channel}/follows";
        $client->get($url, $query, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertTrue(Follow::getChannelFollowers($channel)[0]->getNotificationStatus());
    }
}
