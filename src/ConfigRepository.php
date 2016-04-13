<?php namespace MagnesiumOxide\TwitchApi;

use ArrayAccess;

class ConfigRepository implements ArrayAccess {
    protected $file;
    protected $config = [];

    public function __construct($file = null) {
        $this->file = $file ? $file : __DIR__."/../config/TwitchApi.php";
        $this->config = require $this->file;
    }

    public function offsetExists($key) {
        return isset($this->config[$key]);
    }

    public function offsetGet($key) {
        return $this->config[$key];
    }

    public function offsetSet($key, $value) {
        $this->config[$key] = $value;
    }

    public function offsetUnset($key) {
        unset($this->config[$key]);
    }
}