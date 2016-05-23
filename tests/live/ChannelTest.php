<?php namespace MagnesiumOxide\TwitchApi\Tests\Live;

use MagnesiumOxide\TwitchApi\Model\Channel;

class ChannelTest extends BaseTest {
    public function testGetChannel() {
        $this->assertEquals("42171491", Channel::getChannel("MagnesiumOxide")->getId());
    }
}
