<?php

use GuzzleHttp\Client;
use MagnesiumOxide\TwitchApi\Helpers\Guzzle5Client;
use MagnesiumOxide\TwitchApi\Helpers\Guzzle6Client;

class ClientTest extends PHPUnit_Framework_TestCase {
    /** @var \MagnesiumOxide\TwitchApi\Helpers\ClientInterface */
    private $client;

    public function setUp() {
        if (version_compare(Client::VERSION, '6.0.0', '<')) {
            $this->client = new Guzzle5Client();
        } else {
            $this->client = new Guzzle6Client();
        }
    }

    public function testDelete() {
        $response = $this->client->delete("http://localhost:8000");
        $this->assertEquals("DELETE", $response->getBody());
    }

    public function testGet() {
        $response = $this->client->get("http://localhost:8000");
        $this->assertEquals("GET", $response->getBody());
    }

    public function testPost() {
        $response = $this->client->post("http://localhost:8000");
        $this->assertEquals("POST", $response->getBody());
    }

    public function testPut() {
        $response = $this->client->put("http://localhost:8000");
        $this->assertEquals("PUT", $response->getBody());
    }
}
