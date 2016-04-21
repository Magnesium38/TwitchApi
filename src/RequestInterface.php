<?php namespace MagnesiumOxide\TwitchApi;

/**
 * @package MagnesiumOxide\TwitchApi
 */
interface RequestInterface {
    /**
     * Takes a uri and an optional query array and performs the DELETE request.
     *
     * @param $uri
     * @param array $query
     * @param array $headers
     * @return ResponseInterface
     */
    public function delete($uri, array $query = [], array $headers = []);

    /**
     * Takes a uri and an optional query array and performs the GET request.
     *
     * @param $uri
     * @param array $query
     * @param array $headers
     * @return ResponseInterface
     */
    public function get($uri, array $query = [], array $headers = []);

    /**
     * Takes a uri and an optional parameter array and performs the POST request.
     *
     * @param $uri
     * @param array $parameters
     * @param array $headers
     * @return ResponseInterface
     */
    public function post($uri, array $parameters = [], array $headers = []);

    /**
     * Takes a uri and an optional parameter array and performs the PUT request.
     *
     * @param $uri
     * @param array $parameters
     * @param array $headers
     * @return ResponseInterface
     */
    public function put($uri, array $parameters = [], array $headers = []);
}