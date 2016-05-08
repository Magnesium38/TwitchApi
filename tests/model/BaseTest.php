<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use GuzzleHttp\Client;
use MagnesiumOxide\Config\Repository as ConfigRepository;
use MagnesiumOxide\TwitchApi\Helpers\ClientInterface;
use MagnesiumOxide\TwitchApi\Model\BaseModel;
use MagnesiumOxide\TwitchApi\Model\AuthenticatedUser;
use PHPUnit_Framework_TestCase;
use Prophecy\Argument;

abstract class BaseTest extends PHPUnit_Framework_TestCase {
    protected $class = null;
    protected $testAuthUsername = "TestUserName";
    protected $testAuthToken = "TestUserAuthToken";

    public function testCreate() {
        $class = $this->class;

        if ($class === null) {
            $this->fail("Child classes must override the \$class variable.");
        }

        $this->assertTrue($class::create([]) instanceof $this->class);
    }

    protected function mockAuthUser() {
        $mockedUser = $this->prophesize(AuthenticatedUser::class);
        $mockedUser->getName()->willReturn($this->testAuthUsername);
        $mockedUser->getUsername()->willReturn($this->testAuthUsername);
        $mockedUser->getAuthToken()->willReturn($this->testAuthToken);

        return $mockedUser;
    }

    protected function mockClient() {
        $mockedClient = $this->prophesize(ClientInterface::class);
        BaseModel::setClient($mockedClient->reveal());
        return $mockedClient;
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

        BaseModel::setConfig($mockedConfig->reveal());
        return $mockedConfig;
    }

    protected function mockResponse($body, $statusCode) {
        if (version_compare(Client::VERSION, '6.0.0', '<')) {
            $response = $this->prophesize(\GuzzleHttp\Message\ResponseInterface::class);
        } else {
            $response = $this->prophesize(\Psr\Http\Message\ResponseInterface::class);
        }
        $response->getBody()->willReturn($body);
        $response->getStatusCode()->willReturn($statusCode);

        return $response;
    }
}
