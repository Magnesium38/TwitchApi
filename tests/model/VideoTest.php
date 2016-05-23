<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use MagnesiumOxide\TwitchApi\Model\BaseModel;
use MagnesiumOxide\TwitchApi\Model\Video;
use Prophecy\Exception\InvalidArgumentException;

class VideoTest extends BaseTest {
    /** @var Video */
    private $video;
    private $videoJson;
    protected $class = Video::class;

    public function setUp() {
        $videoJson = '{"title":"Twitch Weekly - February 6, 2015",'
                . '"description":"Twitch Weekly LIVE on February 6, 2015!","broadcast_id":13019796368,'
                . '"status":"recorded","_id":"c6055863","tag_list":"","recorded_at":"2015-02-06T21:01:09Z",'
                . '"game":null,"length":4015,'
                . '"preview":"http://static-cdn.jtvnw.net/jtv.thumbs/archive-621292653-320x240.jpg",'
                . '"url":"http://www.twitch.tv/twitch/c/6055863","views":318,"broadcast_type":"highlight",'
                . '"_links":{"self":"https://api.twitch.tv/kraken/videos/c6055863",'
                . '"channel":"https://api.twitch.tv/kraken/channels/twitch"},'
                . '"channel":{"name":"twitch","display_name":"Twitch"}}';
        $videoArray = json_decode($videoJson, true);

        $this->videoJson = $videoJson;
        $this->video = Video::create($videoArray);
    }

    public function testGetTitle() {
        $this->assertEquals("Twitch Weekly - February 6, 2015", $this->video->getTitle());
    }

    public function testGetDescription() {
        $this->assertEquals("Twitch Weekly LIVE on February 6, 2015!", $this->video->getDescription());
    }

    public function testGetBroadcastId() {
        $this->assertEquals("13019796368", $this->video->getBroadcastId());
    }

    public function testGetStatus() {
        $this->assertEquals("recorded", $this->video->getStatus());
    }

    public function testGetId() {
        $this->assertEquals("c6055863", $this->video->getId());
    }

    public function testGetTagList() {
        $this->assertEquals("", $this->video->getTagList());
    }

    public function testGetRecordedAt() {
        $this->assertEquals(new \DateTime("2015-02-06T21:01:09Z"), $this->video->getRecordedAt());
    }

    public function testGetGame() {
        $this->assertEquals(null, $this->video->getGame());
    }

    public function testGetLength() {
        $this->assertEquals("4015", $this->video->getLength());
    }

    public function testGetPreview() {
        $preview = "http://static-cdn.jtvnw.net/jtv.thumbs/archive-621292653-320x240.jpg";
        $this->assertEquals($preview, $this->video->getPreview());
    }

    public function testGetUrl() {
        $this->assertEquals("http://www.twitch.tv/twitch/c/6055863", $this->video->getUrl());
    }

    public function testGetViews() {
        $this->assertEquals("318", $this->video->getViews());
    }

    public function testGetBroadcastType() {
        $this->assertEquals("highlight", $this->video->getBroadcastType());
    }

    public function testGetChannelInfo() {
        $this->assertEquals(["name" => "twitch", "display_name" => "Twitch"], $this->video->getChannelInfo());
    }

    public function testGetVideo() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $body = $this->videoJson;

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/videos/c6055863";
        $client->get($url, [], $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("c6055863", Video::getVideo("c6055863")->getId());
    }

    public function testGetTopVideos() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $query = [
            "limit" => 10,
            "offset" => 0,
            "period" => "week",
        ];

        $body = '{"videos":[' . $this->videoJson . ']}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/videos/top";
        $client->get($url, $query, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("c6055863", Video::getTopVideos()[0]->getId());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTopVideosThrowsExceptionOnInvalidLimit() {
        Video::getTopVideos(3000);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTopVideosThrowsExceptionOnInvalidPeriod() {
        Video::getTopVideos(10, 0, null, "fortnight");
    }

    public function testGetChannelVideos() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $channel = "twitch";

        $query = [
            "limit" => 10,
            "offset" => 0,
            "broadcasts" => false,
            "hls" => false,
        ];

        $body = '{"videos":[' . $this->videoJson . ']}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/channels/{$channel}/videos";
        $client->get($url, $query, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("c6055863", Video::getChannelVideos($channel)[0]->getId());
    }
}
