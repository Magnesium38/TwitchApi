<?php namespace MagnesiumOxide\TwitchApi\Model;

use GuzzleHttp\Client;
use MagnesiumOxide\TwitchApi\ConfigRepository;
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
     * Takes an associative array and loads it into an object. Fields that the class uses have their own getters.
     * Protected so that the ::create method must be used.
     *
     * @param array $object
     */
    protected function __construct(array $object = []) {
        $this->loadObject($object);
    }

    /**
     * Magic setter to store everything in a data array as lowercase.
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value) {
        $this->data[strtolower($name)] = $value;
    }

    /**
     * Static method to create the implementing classes objects.
     *
     * @param array $object
     * @return static
     */
    public static function create(array $object) {
        return new static($object);
    }

    /**
     * Magic method to determine if a variable is set.
     *
     * @param string $name
     * @return bool
     */
    public function __isset($name) {
        $name = strtolower($name);
        return isset($this->data[$name]);
    }

    /**
     * Loads an array into the $data array using the __set method.
     *
     * @param array $object
     */
    protected function loadObject(array $object) {
        foreach ($object as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Avoiding a __get magic method and just calling this in getters.
     *
     * @param string $name
     * @return mixed
     */
    protected function getHelper($name) {
        $name = strtolower($name);
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }

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

    protected static function buildHeaders() {
        $headers = [
            "Client-ID" => self::$config["ClientId"],
            "Accept" => self::ACCEPT_HEADER,
        ];

        return $headers;
    }


    /**
     * @param \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface $response
     * @param $key
     * @return array
     */
    protected static function responseToArray($response, $key) {
        $body = json_decode($response->getBody(), true);

        $objects = [];
        foreach ($body[$key] as $item) {
            $objects[] = static::create($item);
        }

        return $objects;
    }

    /**
     * @param \GuzzleHttp\Message\ResponseInterface|\Psr\Http\Message\ResponseInterface $response
     * @return static
     */
    protected static function responseToObject($response) {
        $body = json_decode($response->getBody(), true);

        return static::create($body);
    }
}

if (version_compare(Client::VERSION, '6.0.0', '<')) {
    BaseModel::setClient(new Guzzle5Client());
} else {
    BaseModel::setClient(new Guzzle6Client());
}
