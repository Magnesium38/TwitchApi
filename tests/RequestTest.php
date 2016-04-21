<?php namespace MagnesiumOxide\TwitchApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

class RequestTest extends \PHPUnit_Framework_TestCase {
    private $clientInterface;
    private $response;
    private $client;

    public function setUp() {
        $this->clientInterface = $this->prophesize(Client::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->client = new Request($this->clientInterface->reveal());
    }

    private function requestShouldBeMade($method, $uri, $params, $headers, $response, $throwException = false) {
        if ($params !== null) {
            $params = $params + ["headers" => $headers];
        } else {
            $params = ["headers" => $headers];
        }

        $mockedResponse = $this->prophesize(ResponseInterface::class);
        $mockedResponse->getBody()
            ->shouldBeCalled()
            ->willReturn($response);

        if ($throwException === false) {
            $this->clientInterface->request($method, $uri, $params)
                ->shouldbeCalled()
                ->willReturn($mockedResponse);
        } else {
            $mockedException = $this->prophesize(RequestException::class);
            $mockedException->getResponse()
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

            $this->clientInterface->request($method, $uri, $params)
                ->shouldbeCalled()
                ->willThrow($mockedException->reveal());
        }
    }

    public function testMakesDeleteRequest() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("DELETE", $uri, ["query" => $query], [], $response);
        $this->assertEquals("true", $this->client->delete($uri, $query)["success"]);
    }

    public function testMakesDeleteRequestWithHeaders() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $headers = ["Auth" => "My Token"];
        $this->requestShouldBeMade("DELETE", $uri, ["query" => $query], ["Auth" => "My Token"], $response);
        $this->assertEquals("true", $this->client->delete($uri, $query, $headers)["success"]);
    }

    public function testMakesDeleteRequestThrowsException() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("DELETE", $uri, ["query" => $query], [], $response, true);
        $this->assertEquals("true", $this->client->delete($uri, $query)["success"]);
    }

    public function testMakesGetRequest() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("GET", $uri, ["query" => $query], [], $response);
        $this->assertEquals("true", $this->client->get($uri, $query)["success"]);
    }

    public function testMakesGetRequestWithHeaders() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $headers = ["Auth" => "My Token"];
        $this->requestShouldBeMade("GET", $uri, ["query" => $query], ["Auth" => "My Token"], $response);
        $this->assertEquals("true", $this->client->get($uri, $query, $headers)["success"]);
    }

    public function testMakesGetRequestThrowsException() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("GET", $uri, ["query" => $query], [], $response, true);
        $this->assertEquals("true", $this->client->get($uri, $query)["success"]);
    }

    public function testMakesPostRequest() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("POST", $uri, ["form_params" => $params], [], $response);
        $this->assertEquals("true", $this->client->post($uri, $params)["success"]);
    }

    public function testMakesPostRequestWithHeaders() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $headers = ["Auth" => "My Token"];
        $this->requestShouldBeMade("POST", $uri, ["form_params" => $params], ["Auth" => "My Token"], $response);
        $this->assertEquals("true", $this->client->post($uri, $params, $headers)["success"]);
    }

    public function testMakesPostRequestThrowsException() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("POST", $uri, ["form_params" => $params], [], $response, true);
        $this->assertEquals("true", $this->client->post($uri, $params)["success"]);
    }

    public function testMakesPutRequest() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("PUT", $uri, ["form_params" => $params], [], $response);
        $this->assertEquals("true", $this->client->put($uri, $params)["success"]);
    }

    public function testMakesPutRequestWithHeaders() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $headers = ["Auth" => "My Token"];
        $this->requestShouldBeMade("PUT", $uri, ["form_params" => $params], ["Auth" => "My Token"], $response);
        $this->assertEquals("true", $this->client->put($uri, $params, $headers)["success"]);
    }

    public function testMakesPutRequestThrowsException() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("PUT", $uri, ["form_params" => $params], [], $response, true);
        $this->assertEquals("true", $this->client->put($uri, $params)["success"]);
    }
}