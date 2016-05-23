<?php namespace MagnesiumOxide\TwitchApi\Tests\Live;

use MagnesiumOxide\TwitchApi\Model\Follow;

class FollowTest extends BaseTest {
    public function testDoesUserFollowChannel() {
        // Make dummy users to test with. Until then, the Twitch channel shouldn't follow me.
        $this->assertNull(Follow::doesUserFollowChannel("Twitch", "MagnesiumOxide"));

        // On the other hand, I shouldn't be unfollowing the Twitch channel any time soon.
        $result = Follow::doesUserFollowChannel("MagnesiumOxide", "Twitch")->getCreatedAt();
        $this->assertEquals(new \DateTime("2013-06-10T21:13:23+00:00"), $result);
    }
}
