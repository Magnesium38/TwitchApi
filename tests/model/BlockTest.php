<?php

use MagnesiumOxide\TwitchApi\Model\Block;

class BlockTest extends BaseModelTest {
    /** @var Block */
    private $block;
    protected $class = Block::class;

    public function setUp() {
        $blockJson = '{"_links":{"self":"https://api.twitch.tv/kraken/users/test_user1/blocks/test_user_troll"},'
            . '"updated_at":"2013-02-07T01:04:43Z","user":{'
            . '"_links":{"self":"https://api.twitch.tv/kraken/users/test_user_troll"},'
            . '"updated_at":"2013-01-18T22:33:55Z","logo":"http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'test_user_troll-profile_image-c3fa99f314dd9477-300x300.jpeg","type":"user",'
            . '"bio":"I\'m a troll.. Kappa","display_name":"test_user_troll","name":"test_user_troll",'
            . '"_id":22125774,"created_at":"2011-05-01T14:50:12Z"},"_id":287813}';
        $blockArray = json_decode($blockJson, true);

        $this->block = Block::create($blockArray);
    }

    public function testGetBlockDate() {
        $this->assertEquals(new DateTime("2013-02-07T01:04:43Z"), $this->block->getBlockDate());
    }

    public function testGetBlockedUser() {
        $this->assertTrue($this->block->getBlockedUser() instanceof \MagnesiumOxide\TwitchApi\Model\User);
    }
}
