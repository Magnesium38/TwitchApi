<?php

use MagnesiumOxide\TwitchApi\Model\Video;

class VideoTest extends BaseModelTest {
    /** @var Video */
    private $video;
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

        $this->video = Video::create($videoArray);
    }

    public function testGetTitle() {
        $this->assertEquals("Twitch Weekly - February 6, 2015", $this->video->getTitle());
    }
}