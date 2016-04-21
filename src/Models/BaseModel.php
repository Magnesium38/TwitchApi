<?php namespace MagnesiumOxide\TwitchApi\Model;

use MagnesiumOxide\TwitchApi\Client;

/**
 * BaseModel that all other models extend.
 * @package MagnesiumOxide\TwitchApi\Model
 */
abstract class BaseModel {
    /** @var array The array that holds everything. */
    protected $data = [];
    /** @var Client The client that is used to make API requests. */
    protected $client;


    /**
     * Takes an associative array and loads it into an object. Fields that the class uses have their own getters.
     * Protected so that the ::create method must be used.
     *
     * @param array $object
     */
    protected function __construct(array $object) {
        $this->loadObject($object);
        $this->client = new Client();
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
     * Authenticates the underlying client with a user using the configuration file values.
     *
     * @param $code
     * @throws \Exception
     */
    public function authenticate($code) {
        // REVISE DOC BLOCK
        // Once auth errors and a proper exception is made.
        $this->client->authenticate($code);
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
}
