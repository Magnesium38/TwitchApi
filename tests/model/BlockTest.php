<?php

use MagnesiumOxide\TwitchApi\Model\BaseModel;
use MagnesiumOxide\TwitchApi\Model\Block;
use MagnesiumOxide\TwitchApi\Model\User;
use MagnesiumOxide\TwitchApi\Scope;

class BlockTest extends BaseModelTest {
    /** @var Block */
    private $block;
    private $blockJson;
    protected $class = Block::class;

    public function setUp() {
        $this->blockJson = '{"_links":{"self":"https://api.twitch.tv/kraken/users/test_user1/blocks/test_user_troll"},'
            . '"updated_at":"2013-02-07T01:04:43Z","user":{'
            . '"_links":{"self":"https://api.twitch.tv/kraken/users/test_user_troll"},'
            . '"updated_at":"2013-01-18T22:33:55Z","logo":"http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'test_user_troll-profile_image-c3fa99f314dd9477-300x300.jpeg","type":"user",'
            . '"bio":"I\'m a troll.. Kappa","display_name":"test_user_troll","name":"test_user_troll",'
            . '"_id":22125774,"created_at":"2011-05-01T14:50:12Z"},"_id":287813}';
        $blockArray = json_decode($this->blockJson, true);

        $this->block = Block::create($blockArray);
    }

    public function testGetBlockDate() {
        $this->assertEquals(new DateTime("2013-02-07T01:04:43Z"), $this->block->getBlockDate());
    }

    public function testGetBlockedUser() {
        $this->assertTrue($this->block->getBlockedUser() instanceof \MagnesiumOxide\TwitchApi\Model\User);
    }

    public function testGetBlockedUsers() {
        $client = $this->mockClient();
        $config = $this->mockConfig(["scopes" => [Scope::ReadUserBlocks]]);

        $user = $this->mockAuthUser();

        $limit = 25;
        $offset = 0;

        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];

        $headers = [
            "Client-ID" => $config->reveal()["ClientId"],
            "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $mockedUser = $user->reveal();

        $body = '{"blocks":[' . $this->blockJson . ']}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/users/{$mockedUser->getUsername()}/blocks";
        $client->get($url, $query, $headers, $mockedUser->getAuthToken())
            ->shouldBeCalled()
            ->willReturn($mockedResponse);

        $blocks = Block::getBlockedUsers($user->reveal(), $limit, $offset);

        $this->assertEquals(1, count($blocks));
        $this->assertEquals("test_user_troll", $blocks[0]->getBlockedUser()->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetBlockedUsersThrowsInvalidArgumentException() {
        Block::getBlockedUsers(User::create([]), 1000, 0);
    }

    /**
     * @expectedException \MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException
     */
    public function testGetBlockedUsersThrowsInsufficientScopeException() {
        $this->mockConfig();
        Block::getBlockedUsers(User::create([]), 25, 0);
    }

    public function testBlockUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testUnblockUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }
}
