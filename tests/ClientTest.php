<?php namespace MagnesiumOxide\TwitchApi\Tests;

use GuzzleHttp\Client;
use MagnesiumOxide\TwitchApi\Helpers\Guzzle5Client;
use MagnesiumOxide\TwitchApi\Helpers\Guzzle6Client;
use PHPUnit_Framework_TestCase;

class ClientTest extends PHPUnit_Framework_TestCase {
    /** @var \MagnesiumOxide\TwitchApi\Helpers\ClientInterface */
    private $client;
    private $accept = "ACCEPT_HEADER";
    private $auth = "AUTH_HEADER";
    private $clientid = "CLIENT_ID_HEADER";

    public function setUp() {
        if (version_compare(Client::VERSION, '6.0.0', '<')) {
            $this->client = new Guzzle5Client();
        } else {
            $this->client = new Guzzle6Client();
        }
    }

    public function testDelete() {
        $response = $this->client->delete("http://localhost:8000");
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("DELETE", $body["method"]);
    }

    public function testDeleteHasAcceptHeader() {
        $response = $this->client->delete("http://localhost:8000", [], ["Accept" => $this->accept]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("DELETE", $body["method"]);
        $this->assertEquals($this->accept, $body["accept"]);
    }

    public function testDeleteHasAuthorizationHeader() {
        $response = $this->client->delete("http://localhost:8000", [], ["Authorization" => $this->auth]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("DELETE", $body["method"]);
        $this->assertEquals($this->auth, $body["authorization"]);
    }

    public function testDeleteHasClientIdHeader() {
        $response = $this->client->delete("http://localhost:8000", [], ["ClientID" => $this->clientid]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("DELETE", $body["method"]);
        $this->assertEquals($this->clientid, $body["clientid"]);
    }

    public function testGet() {
        $response = $this->client->get("http://localhost:8000");
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("GET", $body["method"]);
    }

    public function testGetHasAcceptHeader() {
        $response = $this->client->get("http://localhost:8000", [], ["Accept" => $this->accept]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("GET", $body["method"]);
        $this->assertEquals($this->accept, $body["accept"]);
    }

    public function testGetHasAuthorizationHeader() {
        $response = $this->client->get("http://localhost:8000", [], ["Authorization" => $this->auth]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("GET", $body["method"]);
        $this->assertEquals($this->auth, $body["authorization"]);
    }

    public function testGetHasClientIdHeader() {
        $response = $this->client->get("http://localhost:8000", [], ["ClientID" => $this->clientid]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("GET", $body["method"]);
        $this->assertEquals($this->clientid, $body["clientid"]);
    }

    public function testPost() {
        $response = $this->client->post("http://localhost:8000");
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("POST", $body["method"]);
    }

    public function testPostHasAcceptHeader() {
        $response = $this->client->post("http://localhost:8000", [], ["Accept" => $this->accept]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("POST", $body["method"]);
        $this->assertEquals($this->accept, $body["accept"]);
    }

    public function testPostHasAuthorizationHeader() {
        $response = $this->client->post("http://localhost:8000", [], ["Authorization" => $this->auth]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("POST", $body["method"]);
        $this->assertEquals($this->auth, $body["authorization"]);
    }

    public function testPostHasClientIdHeader() {
        $response = $this->client->post("http://localhost:8000", [], ["ClientID" => $this->clientid]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("POST", $body["method"]);
        $this->assertEquals($this->clientid, $body["clientid"]);
    }

    public function testPut() {
        $response = $this->client->put("http://localhost:8000");
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("PUT", $body["method"]);
    }

    public function testPutHasAcceptHeader() {
        $response = $this->client->put("http://localhost:8000", [], ["Accept" => $this->accept]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("PUT", $body["method"]);
        $this->assertEquals($this->accept, $body["accept"]);
    }

    public function testPutHasAuthorizationHeader() {
        $response = $this->client->put("http://localhost:8000", [], ["Authorization" => $this->auth]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("PUT", $body["method"]);
        $this->assertEquals($this->auth, $body["authorization"]);
    }

    public function testPutHasClientIdHeader() {
        $response = $this->client->put("http://localhost:8000", [], ["ClientID" => $this->clientid]);
        $contents = $response->getBody()->getContents();
        $body = json_decode($contents, true);
        $this->assertEquals("PUT", $body["method"]);
        $this->assertEquals($this->clientid, $body["clientid"]);
    }
}
