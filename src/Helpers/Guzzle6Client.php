<?php namespace MagnesiumOxide\TwitchApi\Helpers;

use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Guzzle6Client
 * @package MagnesiumOxide\TwitchApi\Helpers
 */
class Guzzle6Client implements ClientInterface {
    /**
     * @var GuzzleClient
     */
    protected $client;

    /**
     * @param GuzzleClient|null $client
     */
    public function __construct(GuzzleClient $client = null) {
        $this->client = $client ?: new GuzzleClient(["http_errors" => false, "allow_redirects" => true]);
    }

    /**
     * @param $url
     * @param array $query
     * @param array $headers
     * @param null $authToken
     * @return ResponseInterface
     */
    public function delete($url, array $query = [], array $headers = [], $authToken = null) {
        $options = [];
        $options["query"] = $query;
        $options["headers"] = $headers;

        return $this->client->request("DELETE", $url, $options);
    }

    /**
     * @param $url
     * @param array $query
     * @param array $headers
     * @param null $authToken
     * @return ResponseInterface
     */
    public function get($url, array $query = [], array $headers = [], $authToken = null) {
        $options = [];
        $options["query"] = $query;
        $options["headers"] = $headers;

        return $this->client->request("GET", $url, $options);
    }

    /**
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @param null $authToken
     * @return ResponseInterface
     */
    public function post($url, array $parameters = [], array $headers = [], $authToken = null) {
        $options = [];
        $options["form_params"] = $parameters;
        $options["headers"] = $headers;

        return $this->client->request("POST", $url, $options);
    }

    /**
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @param null $authToken
     * @return ResponseInterface
     */
    public function put($url, array $parameters = [], array $headers = [], $authToken = null) {
        $options = [];
        $options["form_params"] = $parameters;
        $options["headers"] = $headers;

        return $this->client->request("PUT", $url, $options);
    }
}
