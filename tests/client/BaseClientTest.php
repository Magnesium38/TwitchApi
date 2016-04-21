<?php

use MagnesiumOxide\TwitchApi\Client as Api;
use MagnesiumOxide\TwitchApi\ConfigRepository;
use MagnesiumOxide\TwitchApi\Request;
use MagnesiumOxide\TwitchApi\ResponseInterface;
use Prophecy\Argument;

abstract class BaseClientTest extends PHPUnit_Framework_TestCase {
    protected $client;
    protected $username = "TestUser";
    protected $token = "TestUserAuthToken";

    public function setUp() {
        $this->client = $this->prophesize(Request::class);
    }

    protected function getBaseLinks() {
        return [
                "_links" => [
                        "user" => "https://api.twitch.tv/kraken/user",
                        "channel" => "https://api.twitch.tv/kraken/channel",
                        "search" => "https://api.twitch.tv/kraken/search",
                        "streams" => "https://api.twitch.tv/kraken/streams",
                        "ingests" => "https://api.twitch.tv/kraken/ingests",
                        "teams" => "https://api.twitch.tv/kraken/teams",
                ],
                "token" => [
                        "valid" => false,
                        "authorization" => null,
                ]
        ];
    }

    protected function mockConfig(array $config = []) {
        $mockedConfig = $this->prophesize(ConfigRepository::class);
        $mockedConfig->offsetExists(Argument::any())
                ->will(function ($arg) {
                    if ($arg == "ClientId") return true;
                    if ($arg == "ClientSecret") return true;
                    if ($arg == "RedirectUri") return true;
                    if ($arg == "State") return true;
                    if ($arg == "Scope") return true;
                    return false;
                });

        $mockedConfig->offsetGet(Argument::any())
                ->will(function ($args) use ($config) {
                    $arg = $args[0];
                    if ($arg == "ClientId") {
                        if (isset($config["ClientId"])) {
                            return $config["ClientId"];
                        } else {
                            return "YOUR_CLIENT_ID";
                        }
                    }
                    if ($arg == "ClientSecret") {
                        if (isset($config["ClientSecret"])) {
                            return $config["ClientSecret"];
                        } else {
                            return "YOUR_CLIENT_SECRET";
                        }
                    }
                    if ($arg == "RedirectUri") {
                        if (isset($config["RedirectUri"])) {
                            return $config["RedirectUri"];
                        } else {
                            return "YOUR_REDIRECT_URI";
                        }
                    }
                    if ($arg == "State") {
                        if (isset($config["State"])) {
                            return $config["State"];
                        } else {
                            return "YOUR_STATE";
                        }
                    }
                    if ($arg == "Scope") {
                        if (isset($config["scopes"])) {
                            return $config["scopes"];
                        } else {
                            return [];
                        }
                    }
                    return null;
                });

        return $mockedConfig;
    }

    protected function mockResponse(array $body, $statusCode) {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn($body);
        $response->getStatusCode()->willReturn($statusCode);

        return $response;
    }

    protected function getApi(array $config = []) {
        $mockedConfig = $this->mockConfig($config);
        return new Api($this->client->reveal(), $mockedConfig->reveal());
    }

    protected function requestShouldBeMade($method, $uri, $params, $response, $authenticated = false) {
        $headers = ["Accept" => Api::ACCEPT_HEADER];
        if ($authenticated) {
            $headers["Authorization"] = "OAuth " . $this->token;
        }

        if ($method == "DELETE") {
            $this->client->delete($uri, $params, $headers)
                    ->shouldBeCalled()
                    ->willReturn($response);
        } else if ($method == "GET") {
            $this->client->get($uri, $params, $headers)
                    ->shouldBeCalled()
                    ->willReturn($response, $headers);
        } else if ($method == "POST") {
            $this->client->post($uri, $params, $headers)
                    ->shouldBeCalled()
                    ->willReturn($response);
        } else if ($method == "PUT") {
            $this->client->put($uri, $params, $headers)
                    ->shouldBeCalled()
                    ->willReturn($response);
        }
    }

    protected function authenticate(Api $api, array $scopes) {
        $code = "ThisIsTheBestAuthenticationCode";

        $responseBody = ["access_token" => $this->token, "scope" => implode(",", $scopes)];
        $params = [
                "client_id" => $api->getConfig()["ClientId"],
                "client_secret" => $api->getConfig()["ClientSecret"],
                "grant_type" => "authorization_code",
                "redirect_uri" => $api->getConfig()["RedirectUri"],
                "code" => $code,
                "state" => $api->getConfig()["State"],
        ];

        $mockedResponse = $this->prophesize(ResponseInterface::class);
        $mockedResponse->getBody()->willReturn($responseBody);
        $mockedResponse->getStatusCode()->willReturn(200);

        $this->requestShouldBeMade("POST", Api::BASE_URL . "/oauth2/token", $params, $mockedResponse);

        $responseBody2 = [
                "token" => [
                        "authorization" => [
                                "scopes" => implode(",", $scopes),
                                "created_at" => "2012-05-08T21:55:12Z",
                                "updated_at" => "2012-05-17T21:32:13Z",
                        ],
                        "user_name" => $this->username,
                        "valid" => "true",
                ],
        ];

        $mockedResponse2 = $this->prophesize(ResponseInterface::class);
        $mockedResponse2->getBody()->willReturn($responseBody2);

        $this->requestShouldBeMade("GET", Api::BASE_URL, [], $mockedResponse2, true);

        $api->authenticate($code);
    }
}
