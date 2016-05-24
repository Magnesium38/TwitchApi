<?php namespace MagnesiumOxide\TwitchApi\Model;

use GuzzleHttp\Client;
use MagnesiumOxide\Config\Repository as ConfigRepository;
use MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException;
use MagnesiumOxide\TwitchApi\Helpers\ClientInterface;
use MagnesiumOxide\TwitchApi\Helpers\Guzzle5Client;
use MagnesiumOxide\TwitchApi\Helpers\Guzzle6Client;

/**
 * BaseModel that all other models extend.
 * @package MagnesiumOxide\TwitchApi\Model
 */
abstract class BaseModel {
    /** @const The API version used is denoted with the Accept header so it will be attached to all requests. */
    CONST ACCEPT_HEADER = "application/vnd.twitchtv.v3+json";
    /** @const The current base url that all requests will use */
    CONST BASE_URL = "https://api.twitch.tv/kraken";
    /** @var array The array that holds everything. */
    protected $data = [];
    /** @var ClientInterface The client that is used to make API requests. */
    protected static $client = null;
    /** @var ConfigRepository The config that contains the application information. */
    protected static $config = null;

    /**
     * Sets the ConfigRepository that will be used by all models.
     *
     * @param ConfigRepository $config
     */
    public static function setConfig(ConfigRepository $config) {
        self::$config = $config;
    }


    /**
     * Sets the Client that will be used by all models to make requests.
     *
     * @param ClientInterface $client
     */
    public static function setClient(ClientInterface $client) {
        self::$client = $client;
    }

    /**
     * A clean shorthand to throw an exception when the required scope isn't available to the application.
     *
     * @param string $scope
     * @throws InsufficientScopeException
     */
    protected static function requireScope($scope) {
        if (!in_array($scope, self::$config["Scope"])) {
            throw InsufficientScopeException::createException($scope, self::$config["Scope"]);
        }
    }

    /**
     * Builds a Uri from a given path while substituting in the given parameters.
     *
     * @param $path
     * @param array $params
     * @return string
     */
    protected static function buildUri($path, array $params = []) {
        $parts = array_map(function ($item) use ($params) {
            if (strpos($item, ":") === 0) {
                return $params[substr($item, 1)];
            }
            return $item;
        }, explode("/", $path));
        return self::BASE_URL . implode("/", $parts);
    }

    protected static function buildHeaders($authToken = null) {
        $headers = [
            "Client-ID" => self::$config["ClientId"],
            "Accept" => self::ACCEPT_HEADER,
        ];

        if (!is_null($authToken)) {
            $headers["Authorization"] = "OAuth " . $authToken;
        }

        return $headers;
    }

    protected static function delete($url, array $query = [], $authToken = null, array $headers = []) {
        if (empty($headers)) {
            $headers = self::buildHeaders($authToken);
        }

        return self::$client->delete($url, $query, $headers);
    }

    protected static function get($url, array $query = [], $authToken = null, array $headers = []) {
        if (empty($headers)) {
            $headers = self::buildHeaders($authToken);
        }

        return self::$client->get($url, $query, $headers);
    }

    protected static function post($url, array $parameters = [], $authToken = null, array $headers = []) {
        if (empty($headers)) {
            $headers = self::buildHeaders($authToken);
        }

        return self::$client->post($url, $parameters, $headers);
    }

    protected static function put($url, array $parameters = [], $authToken = null, array $headers = []) {
        if (empty($headers)) {
            $headers = self::buildHeaders($authToken);
        }

        return self::$client->put($url, $parameters, $headers);
    }
}

if (version_compare(Client::VERSION, '6.0.0', '<')) {
    BaseModel::setClient(new Guzzle5Client());
} else {
    BaseModel::setClient(new Guzzle6Client());
}
