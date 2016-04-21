<?php namespace MagnesiumOxide\TwitchApi\Model;

use MagnesiumOxide\TwitchApi\Client;

abstract class BaseModel {
    protected $data = [];
    protected $client;

    public function __construct(array $object) {
        $this->loadObject($object);
        $this->client = new Client();
    }

    public function __set($name, $value) {
        $this->data[strtolower($name)] = $value;
    }

    public static function create(array $object) {
        return new static($object);
    }

    public function __isset($name) {
        $name = strtolower($name);
        return isset($this->data[$name]);
    }

    protected function loadObject(array $object) {
        foreach ($object as $key => $value) {
            $this->$key = $value;
        }
    }

    protected function getHelper($name) {
        $name = strtolower($name);
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }
        return null;
    }
}