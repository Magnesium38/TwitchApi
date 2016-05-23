<?php namespace MagnesiumOxide\TwitchApi\Helpers;

use GuzzleHttp\Client as GuzzleClient;

class Guzzle5Client implements ClientInterface {
    protected $client;

    /**
     * Guzzle5Client constructor.
     * @param GuzzleClient $client
     */
    public function __construct(GuzzleClient $client = null) {
        $defaults = [
            "exceptions" => false,
            "allow_redirects" => true,
            //"verify" => false, // this fixes cURL error 60.
        ];
        $this->client = $client ?: new GuzzleClient(["defaults" => $defaults]);
    }

    /**
     * @param $url
     * @param array $query
     * @param array $headers
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function delete($url, array $query = [], array $headers = []) {
        $options = [];
        $options["query"] = $query;
        $options["headers"] = $headers;

        return $this->client->delete($url, $options);
    }

    /**
     * @param $url
     * @param array $query
     * @param array $headers
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function get($url, array $query = [], array $headers = []) {
        $options = [];
        $options["query"] = $query;
        $options["headers"] = $headers;

        return $this->client->get($url, $options);
    }

    /**
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function post($url, array $parameters = [], array $headers = []) {
        $options = [];
        $options["body"] = $parameters;
        $options["headers"] = $headers;

        return $this->client->post($url, $options);
    }

    /**
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function put($url, array $parameters = [], array $headers = []) {
        $options = [];
        $options["body"] = $parameters;
        $options["headers"] = $headers;

        return $this->client->put($url, $options);
    }
}