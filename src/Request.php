<?php namespace MagnesiumOxide\TwitchApi;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;

/**
 * @package MagnesiumOxide\TwitchApi
 */
class Request implements RequestInterface {
    /** @var ClientInterface The actual client that makes the requests. */
    private $client;

    public function __construct(ClientInterface $client = null) {
        $this->client = $client ?: new Client(["http_errors" => false]);
    }

    /**
     * Takes a uri and an optional query array and performs the DELETE request.
     *
     * @param $uri
     * @param array $query
     * @param array $headers
     * @return ResponseInterface
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
     * @return ResponseInterface
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
     * @return ResponseInterface
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
     * @return ResponseInterface
     */
    public function put($uri, array $parameters = [], array $headers = []) {
        $options = [];
        $options["form_params"] = $parameters;
        $options["headers"] = $headers;

        return $this->request("PUT", $uri, $options);
    }

    /**
     * The actual method that calls the requests. Will return only the json body of the response as an array.
     *
     * @param $method
     * @param $uri
     * @param $options
     * @return ResponseInterface
     */
    private function request($method, $uri, $options) {
        try {
            $response = $this->client->request($method, $uri, $options);
            $body = json_decode($response->getBody(), true);

            $result = new Response($response->getStatusCode(), $body);
        } catch (RequestException $e) {
            $body = json_decode($e->getResponse()->getBody(), true);
            $result = new Response($e->getResponse()->getStatusCode(), $body);
        }
        return $result;
    }

}