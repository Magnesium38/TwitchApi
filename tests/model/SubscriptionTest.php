<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use MagnesiumOxide\TwitchApi\Model\Subscription;

class SubscriptionTest extends BaseTest {
    /** @var Subscription */
    private $subscription;
    private $subscriptionJson;
    protected $class = Subscription::class;

    public function setUp() {
        $subscriptionJson = '{"_id":"88d4621871b7274c34d5c3eb5dad6780c8533318","user":{"_id":38248673,'
            . '"logo":null,"type":"user","bio":"I\'m testuser","created_at":"2012-12-06T00:32:36Z",'
            . '"name":"testuser","updated_at":"2013-02-06T21:27:46Z","display_name":"testuser",'
            . '"_links":{"self":"https://api.twitch.tv/kraken/users/testuser"}},"created_at":"2013-02-06T21:33:33Z",'
            . '"_links":{"self":"https://api.twitch.tv/kraken/channels/test_channel/subscriptions/testuser"}}';
        $subscriptionArray = json_decode($subscriptionJson, true);

        $this->subscriptionJson = $subscriptionJson;
        $this->subscription = Subscription::create($subscriptionArray);
    }

    public function testGetId() {
        $this->assertEquals("88d4621871b7274c34d5c3eb5dad6780c8533318", $this->subscription->getId());
    }

    public function testGetUser() {
        $this->assertEquals("38248673", $this->subscription->getUser()->getId());
    }

    public function testGetCreatedAt() {
        $this->assertEquals(new \DateTime("2013-02-06T21:33:33Z"), $this->subscription->getCreatedAt());
    }

    public function testInvalidGetCreatedAt() {
        $this->assertNull(Subscription::create([])->getCreatedAt());
    }
}
