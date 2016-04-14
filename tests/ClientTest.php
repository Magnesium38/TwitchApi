<?php

use GuzzleHttp\Client;
use MagnesiumOxide\TwitchApi\Client as Api;
use MagnesiumOxide\TwitchApi\ConfigRepository;
use Psr\Http\Message\ResponseInterface;
use Prophecy\Argument;

class ClientTest extends PHPUnit_Framework_TestCase {
    private $client;
    private $response;
    private $config;
    private $username = "TestUser";
    private $token = "TestUserAuthToken";

    public function setUp() {
        $this->client = $this->prophesize(Client::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->config = $this->prophesize(ConfigRepository::class);

        /*$this->config->offsetExists(Argument::any())
            ->will(function ($arg) {
                if ($arg == "ClientId") return true;
                if ($arg == "ClientSecret") return true;
                if ($arg == "RedirectUrl") return true;
                if ($arg == "State") return true;
                if ($arg == "Scope") return true;
                return false;
        });

        $this->config->offsetGet(Argument::any())
            ->will(function ($args) {
                $arg = $args[0];
                if ($arg == "ClientId") return "YOUR_CLIENT_ID";
                if ($arg == "ClientSecret") return "YOUR_CLIENT_SECRET";
                if ($arg == "RedirectUrl") return "YOUR_REDIRECT_URI";
                if ($arg == "State") return "YOUR_STATE";
                if ($arg == "Scope") return [];
                return null;
        });*/
    }

    private function mockConfig(array $scopes) {
        $this->config->offsetExists(Argument::any())
            ->will(function ($arg) {
                if ($arg == "ClientId") return true;
                if ($arg == "ClientSecret") return true;
                if ($arg == "RedirectUrl") return true;
                if ($arg == "State") return true;
                if ($arg == "Scope") return true;
                return false;
            });

        $this->config->offsetGet(Argument::any())
            ->will(function ($args) use ($scopes) {
                $arg = $args[0];
                if ($arg == "ClientId") return "YOUR_CLIENT_ID";
                if ($arg == "ClientSecret") return "YOUR_CLIENT_SECRET";
                if ($arg == "RedirectUrl") return "YOUR_REDIRECT_URI";
                if ($arg == "State") return "YOUR_STATE";
                if ($arg == "Scope") return $scopes;
                return null;
            });
    }

    private function requestShouldBeMade($method, $uri, $params, $response, $authenticated = false) {
        if ($params !== null) {
            $params = $params + ["headers" => ["Accept" => Api::ACCEPT_HEADER]];
        } else {
            $params = ["headers" => ["Accept" => Api::ACCEPT_HEADER]];
        }

        if ($authenticated === true) {
            $params["headers"] = $params["headers"] + ["Authorization" => "OAuth " . $this->token];
        }


        $mockedResponse = $this->prophesize(ResponseInterface::class);
        $mockedResponse->getBody()
            ->shouldBeCalled()
            ->willReturn($response);

        $this->client->request($method, $uri, $params)
            ->shouldbeCalled()
            ->willReturn($mockedResponse);
    }

    public function authenticate(Api $api, array $scopes) {
        $this->mockConfig($scopes);

        $code = "ThisIsTheBestAuthenticationCode";
        $response = '{"access_token":"' . $this->token . '",'
            .'"scope":["' . implode(",", $scopes) . '"]}';

        $params = [
            "form_params" => [
                "client_id" => $api->getConfig()["ClientId"],
                "client_secret" => $api->getConfig()["client_secret"],
                "grant_type" => "authorization_code",
                "redirect_uri" => $api->getConfig()["redirect_uri"],
                "code" => $code,
                "state" => $api->getConfig()["state"],
            ],
        ];

        $this->requestShouldBeMade("POST", Api::BASE_URL . "oauth2/token", $params, $response);

        $response2 = '{"token":{"authorization":{'
            . '"scopes":["' . implode('","', $scopes) . '""],'
            . '"created_at":"2012-05-08T21:55:12Z","updated_at":"2012-05-17T21:32:13Z"},'
            . '"user_name":"test_user1","valid":true},'
            . '"_links":{"channel":"https://api.twitch.tv/kraken/channel",'
            . '"users":"https://api.twitch.tv/kraken/users/'. $this->username .'",'
            . '"user":"https://api.twitch.tv/kraken/user",'
            . '"channels":"https://api.twitch.tv/kraken/channels/'. $this->username .'",'
            . '"chat":"https://api.twitch.tv/kraken/chat/'. $this->username .'",'
            . '"streams":"https://api.twitch.tv/kraken/streams",'
            . '"ingests":"https://api.twitch.tv/kraken/ingests",'
            . '"teams":"https://api.twitch.tv/kraken/teams",'
            . '"search":"https://api.twitch.tv/kraken/search"}}';
        $this->requestShouldBeMade("GET", Api::BASE_URL, null, $response2, true);

        $api->authenticate($code);

        /*$this->client->request("POST", Api::BASE_URL . "oauth2/token", $params)
            ->shouldBeCalled()
            ->willReturn($this->response);

        $this->response->getBody()
                ->shouldBeCalled()
                ->willReturn($response);*/
    }

    public function testConstructor() {
        $response = '{"_links":{"user":"https://api.twitch.tv/kraken/user",'
            . '"channel":"https://api.twitch.tv/kraken/channel",'
            . '"search":"https://api.twitch.tv/kraken/search",'
            . '"streams":"https://api.twitch.tv/kraken/streams",'
            . '"ingests":"https://api.twitch.tv/kraken/ingests",'
            . '"teams":"https://api.twitch.tv/kraken/teams"},'
            . '"token":{"valid":false,"authorization":null}}';

        $links = (object) [
                "user" => "https://api.twitch.tv/kraken/user",
                "channel" => "https://api.twitch.tv/kraken/channel",
                "search" => "https://api.twitch.tv/kraken/search",
                "streams" => "https://api.twitch.tv/kraken/streams",
                "ingests" => "https://api.twitch.tv/kraken/ingests",
                "teams" => "https://api.twitch.tv/kraken/teams",
        ];

        $this->requestShouldBeMade("GET", Api::BASE_URL, null, $response);

        /*$this->client->request("GET", Api::BASE_URL, $headers)
            ->shouldbeCalled()
            ->willReturn($this->response);

        $this->response->getBody()
            ->shouldBeCalled()
            ->willReturn($response);*/

        $api = new Api($this->config->reveal(), $this->client->reveal());
        $this->assertEquals($links, $api->getLinks());

        $this->authenticate($api, []);
    }
}

/*
class TwitchAlertsApiTest extends PHPUnit_Framework_TestCase {
    private $client;
    private $api;
    private $response;

    public function setUp() {
        $this->client = $this->prophesize(Client::class);
        $this->response = $this->prophesize(ResponseInterface::class);
        $this->api = new TwitchAlerts($this->client->reveal());
    }

    public function testClientGetsDonationsRequest() {
        $array = [
                'access_token' => 'abcdefg',
                'limit' => '23',
                'currency' => 'USD',
        ];

        $this->client->get(TwitchAlerts::BASE_URI. "donations", ['form_params' => $array])
                ->shouldBeCalled()
                ->willReturn($this->response);

        $this->api->getDonations($array['access_token'], $array['limit']);
    }

    public function testAuthorizeUrl() {
        $expected = TwitchAlerts::BASE_URI. 'authorize?response_type=code&client_id=abc&redirect_uri=url&scope=read';
        $this->assertEquals($expected, $this->api->getAuthorizeUri("abc", "url", "read"));
    }

    public function testPostDonation() {
        $array = [
                'access_token' => "abcdefg",
                'name' => "JohnDoe",
                'identifier' => "email@yes.com",
                'amount' => "3.50",
                'currency' => "USD",
        ];

        $this->client->post(TwitchAlerts::BASE_URI. "donations", ['form_params' => $array])
                ->shouldBeCalled()
                ->willReturn($this->response);

        $this->api->postDonation($array['access_token'], $array['name'],
                $array['identifier'], $array['amount'], $array['currency']);
    }
}
 */
