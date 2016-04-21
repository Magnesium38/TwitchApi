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
        $mockedResponse->getBody()->willReturn($response);
        $mockedResponse->getStatusCode()->willReturn(200);

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
        $result = $this->client->delete($uri, $query)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesDeleteRequestWithHeaders() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $headers = ["Auth" => "My Token"];
        $this->requestShouldBeMade("DELETE", $uri, ["query" => $query], ["Auth" => "My Token"], $response);
        $result = $this->client->delete($uri, $query, $headers)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesDeleteRequestThrowsException() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("DELETE", $uri, ["query" => $query], [], $response, true);
        $result = $this->client->delete($uri, $query)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesGetRequest() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("GET", $uri, ["query" => $query], [], $response);
        $result = $this->client->get($uri, $query)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesGetRequestWithHeaders() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $headers = ["Auth" => "My Token"];
        $this->requestShouldBeMade("GET", $uri, ["query" => $query], ["Auth" => "My Token"], $response);
        $result = $this->client->get($uri, $query, $headers)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesGetRequestThrowsException() {
        $uri = "http://www.example.com";
        $query = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("GET", $uri, ["query" => $query], [], $response, true);
        $result = $this->client->get($uri, $query)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesPostRequest() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("POST", $uri, ["form_params" => $params], [], $response);
        $result = $this->client->post($uri, $params)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesPostRequestWithHeaders() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $headers = ["Auth" => "My Token"];
        $this->requestShouldBeMade("POST", $uri, ["form_params" => $params], ["Auth" => "My Token"], $response);
        $result = $this->client->post($uri, $params, $headers)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesPostRequestThrowsException() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("POST", $uri, ["form_params" => $params], [], $response, true);
        $result = $this->client->post($uri, $params)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesPutRequest() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("PUT", $uri, ["form_params" => $params], [], $response);
        $result = $this->client->put($uri, $params)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesPutRequestWithHeaders() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $headers = ["Auth" => "My Token"];
        $this->requestShouldBeMade("PUT", $uri, ["form_params" => $params], ["Auth" => "My Token"], $response);
        $result = $this->client->put($uri, $params, $headers)->getBody();
        $this->assertEquals("true", $result["success"]);
    }

    public function testMakesPutRequestThrowsException() {
        $uri = "http://www.example.com";
        $params = ["one" => "a"];
        $response = '{"success": "true"}';

        $this->requestShouldBeMade("PUT", $uri, ["form_params" => $params], [], $response, true);
        $result = $this->client->put($uri, $params)->getBody();
        $this->assertEquals("true", $result["success"]);
    }
}