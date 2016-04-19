<?php use MagnesiumOxide\TwitchApi\ConfigRepository;

class ConfigRepositoryTest extends PHPUnit_Framework_TestCase {
    private $config;

    public function setUp() {
        $this->config = new ConfigRepository();
    }

    public function testOffsetExists() {
        $this->assertTrue(isset($this->config["ClientId"]));
        $this->assertFalse(isset($this->config["NonexistentKey"]));
    }

    public function testOffsetGet() {
        $this->assertEquals("YOUR_CLIENT_ID", $this->config["ClientId"]);
        $this->assertEquals([], $this->config["Scope"]);
    }

    public function testOffsetSet() {
        $this->assertEquals("YOUR_CLIENT_ID", $this->config["ClientId"]);
        $this->config["ClientId"] = "TheClientId";
        $this->assertEquals("TheClientId", $this->config["ClientId"]);
    }

    public function testOffsetUnset() {
        $this->assertTrue(isset($this->config["ClientId"]));
        unset($this->config["ClientId"]);
        $this->assertFalse(isset($this->config["ClientId"]));
    }
}