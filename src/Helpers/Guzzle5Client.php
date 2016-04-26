<?php namespace MagnesiumOxide\TwitchApi\Helpers;

use GuzzleHttp\Client as GuzzleClient;

class Guzzle5Client implements ClientInterface {
    protected $client;

    /**
     * Guzzle5Client constructor.
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client = null) {
        $this->client = $client ?: new GuzzleClient(["http_errors" => false, "allow_redirects" => true]);
    }

    /**
     * @param $url
     * @param array $query
     * @param array $headers
     * @param null $authToken
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function delete($url, array $query = [], array $headers = [], $authToken = null) {
        $options = [];
        $options["query"] = $query;
        $options["headers"] = $headers;

        return $this->client->delete($url, $options);
    }

    /**
     * @param $url
     * @param array $query
     * @param array $headers
     * @param null $authToken
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function get($url, array $query = [], array $headers = [], $authToken = null) {
        $options = [];
        $options["query"] = $query;
        $options["headers"] = $headers;

        return $this->client->get($url, $options);
    }

    /**
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @param null $authToken
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function post($url, array $parameters = [], array $headers = [], $authToken = null) {
        $options = [];
        $options["body"] = $parameters;
        $options["headers"] = $headers;

        return $this->client->post($url, $options);
    }

    /**
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @param null $authToken
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function put($url, array $parameters = [], array $headers = [], $authToken = null) {
        $options = [];
        $options["body"] = $parameters;
        $options["headers"] = $headers;

        return $this->client->put($url, $options);
    }
}