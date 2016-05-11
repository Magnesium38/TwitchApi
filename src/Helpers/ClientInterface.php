<?php namespace MagnesiumOxide\TwitchApi\Helpers;

interface ClientInterface {
    /**
     * @param $url
     * @param array $query
     * @param array $headers
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function delete($url, array $query = [], array $headers = []);

    /**
     * @param $url
     * @param array $query
     * @param array $headers
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function get($url, array $query = [], array $headers = []);

    /**
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function post($url, array $parameters = [], array $headers = []);

    /**
     * @param $url
     * @param array $parameters
     * @param array $headers
     * @return \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface
     */
    public function put($url, array $parameters = [], array $headers = []);
}
