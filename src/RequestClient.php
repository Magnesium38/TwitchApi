<?php namespace MagnesiumOxide\TwitchApi;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * @package MagnesiumOxide\TwitchApi
 */
class RequestClient implements RequestInterface {
    /** @var ClientInterface The actual client that makes the requests. */
    private $client;

    public function __construct(ClientInterface $client) {
        $this->client = $client;
    }

    /**
     * Takes a uri and an optional query array and performs the DELETE request.
     *
     * @param $uri
     * @param array $query
     * @param array $headers
     * @return array
     */
    public function delete($uri, array $query = [], array $headers = []) {
        $options = [];
        $options["query"] = $query;
        $options["headers"] = $headers;

        return $this->request("DELETE", $uri, $options);
    }

    /**
     * Takes a uri and an optional query array and performs the GET request.
     *
     * @param $uri
     * @param array $query
     * @param array $headers
     * @return array
     */
    public function get($uri, array $query = [], array $headers = []) {
        $options = [];
        $options["query"] = $query;
        $options["headers"] = $headers;

        return $this->request("GET", $uri, $options);
    }

    /**
     * Takes a uri and an optional parameter array and performs the POST request.
     *
     * @param $uri
     * @param array $parameters
     * @param array $headers
     * @return array
     */
    public function post($uri, array $parameters = [], array $headers = []) {
        $options = [];
        $options["form_params"] = $parameters;
        $options["headers"] = $headers;

        return $this->request("POST", $uri, $options);
    }

    /**
     * Takes a uri and an optional parameter array and performs the PUT request.
     *
     * @param $uri
     * @param array $parameters
     * @param array $headers
     * @return array
     */
    public function put($uri, array $parameters = [], array $headers = []) {
        $options = [];
        $options["form_params"] = $parameters;
        $options["headers"] = $headers;

        return $this->request("PUT", $uri, $options);
    }

    /**
     * The actual method that calls the requests. Will only return the json body of the response as an array.
     *
     * @param $method
     * @param $uri
     * @param $options
     * @return array
     */
    private function request($method, $uri, $options) {
        try {
            $response = $this->client->request($method, $uri, $options);
            $result = json_decode($response->getBody(), true);
        } catch (RequestException $e) {
            $result = json_decode($e->getResponse()->getBody(), true);
        }
        return $result;
    }

}