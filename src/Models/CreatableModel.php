<?php namespace MagnesiumOxide\TwitchApi\Model;

abstract class CreatableModel extends BaseModel {
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
     * @param null|string $key
     * @return static
     */
    protected static function responseToObject($response, $key = null) {
        if (is_null($key)) {
            $body = json_decode($response->getBody(), true);
        } else {
            $body = json_decode($response->getBody(), true)[$key];
        }

        return static::create($body);
    }
}
