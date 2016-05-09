<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use MagnesiumOxide\TwitchApi\Model\BaseModel;
use MagnesiumOxide\TwitchApi\Model\Stream;

class StreamTest extends BaseTest {
    /** @var Stream */
    private $stream;
    private $streamJson;
    protected $class = Stream::class;

    public function setUp() {
        $this->streamJson = '{"game":"StarCraft II: Heart of the Swarm","viewers":2123,"average_fps":29.9880749574,'
            . '"delay":0,"video_height":720,"is_playlist":false,"created_at":"2015-02-12T04:42:31Z","_id":4989654544,'
            . '"channel":{"mature":false,"status":"test status","broadcaster_language":"en","display_name":'
            . '"test_channel","game":"StarCraft II: Heart of the Swarm","delay":null,"language":"en","_id":12345,'
            . '"name":"test_channel","created_at":"2007-05-22T10:39:54Z","updated_at":"2015-02-12T04:15:49Z","logo":'
            . '"http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-profile_image-94a42b3a13c31c02-'
            . '300x300.jpeg","banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-channel_header'
            . '_image-08dd874c17f39837-640x125.png","video_banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'test_channel-channel_offline_image-b314c834d210dc1a-640x360.png","background":null,"profile_banner"'
            . ':"http://static-cdn.jtvnw.net/jtv_user_pictures/test_channel-profile_banner-6936c61353e4aeed-480.png"'
            . ',"profile_banner_background_color":"null","partner":true,"url":"http://www.twitch.tv/test_channel",'
            . '"views":49144894,"followers":215780,"_links":{"self":"https://api.twitch.tv/kraken/channels/'
            . 'test_channel","follows":"https://api.twitch.tv/kraken/channels/test_channel/follows","commercial"'
            . ':"https://api.twitch.tv/kraken/channels/test_channel/commercial","stream_key":"https://api.twitch.'
            . 'tv/kraken/channels/test_channel/stream_key","chat":"https://api.twitch.tv/kraken/chat/test_channel"'
            . ',"features":"https://api.twitch.tv/kraken/channels/test_channel/features","subscriptions":"https://'
            . 'api.twitch.tv/kraken/channels/test_channel/subscriptions","editors":"https://api.twitch.tv/kraken/'
            . 'channels/test_channel/editors","teams":"https://api.twitch.tv/kraken/channels/test_channel/teams",'
            . '"videos":"https://api.twitch.tv/kraken/channels/test_channel/videos"}},"preview":{"small":"http://'
            . 'static-cdn.jtvnw.net/previews-ttv/live_user_test_channel-80x45.jpg","medium":"http://static-cdn.'
            . 'jtvnw.net/previews-ttv/live_user_test_channel-320x180.jpg","large":"http://static-cdn.jtvnw.net/'
            . 'previews-ttv/live_user_test_channel-640x360.jpg","template":"http://static-cdn.jtvnw.net/previews-'
            . 'ttv/live_user_test_channel-{width}x{height}.jpg"},"_links":{"self":"https://api.twitch.tv/kraken/'
            . 'streams/test_channel"}}';
        $streamArray = json_decode($this->streamJson, true);

        $this->stream = Stream::create($streamArray);
    }

    public function testGetGame() {
        $this->assertEquals("StarCraft II: Heart of the Swarm", $this->stream->getGame());
    }

    public function testGetNumViewers() {
        $this->assertEquals("2123", $this->stream->getNumViewers());
    }

    public function testGetAverageFps() {
        $this->assertEquals("29.9880749574", $this->stream->getAverageFps());
    }

    public function testGetDelay() {
        $this->assertEquals("0", $this->stream->getDelay());
    }

    public function testGetVideoHeight() {
        $this->assertEquals("720", $this->stream->getVideoHeight());
    }

    public function testGetPlaylistStatus() {
        $this->assertEquals(false, $this->stream->getPlaylistStatus());
    }

    public function testGetCreatedAt() {
        $this->assertEquals(new \DateTime("2015-02-12T04:42:31Z"), $this->stream->getCreatedAt());
    }

    public function testGetId() {
        $this->assertEquals("4989654544", $this->stream->getId());
    }

    public function testGetChannel() {
        $this->assertEquals("test_channel", $this->stream->getChannel()->getUsername());
    }

    public function testGetPreview() {
        $url = "http://static-cdn.jtvnw.net/previews-ttv/live_user_test_channel-80x45.jpg";
        $this->assertEquals($url, $this->stream->getPreview()["small"]);
    }

    public function testGetLiveChannel() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $channel = "test_channel";

        $body = '{"stream":' . $this->streamJson . '}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/streams/{$channel}";
        $client->get($url, [], $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("4989654544", Stream::getLiveChannel($channel)->getId());
    }

    public function testGetStreams() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $game = "StarCraft II: Heart of the Swarm";
        $channels = ["test_channel", "test_channel2"];
        $limit = 25;
        $offset = 0;
        $type = null;

        $query = [
            "game" => $game,
            "channel" => implode(",", $channels),
            "limit" => $limit,
            "offset" => $offset,
        ];

        $body = '{"streams":[' . $this->streamJson . ']}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/streams";
        $client->get($url, $query, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("4989654544", Stream::getStreams($game, $channels, $limit, $offset, $type)[0]->getId());
    }

    public function testGetStreamSummary() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $body = '{"viewers":194774,"_links":{"self":"https://api.twitch.tv/kraken/streams/summary"},"channels":4144}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/streams/summary";
        $client->get($url, [], $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("194774", Stream::getStreamSummary()["viewers"]);
    }

    public function testSearch() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $query = "starcraft";
        $limit = 25;
        $offset = 0;

        $q = [
            "query" => $query,
            "limit" => $limit,
            "offset" => $offset,
        ];

        $body = '{"streams":['. $this->streamJson . ']}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/search/streams";
        $client->get($url, $q, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("4989654544", Stream::search($query)[0]->getId());
    }
}
