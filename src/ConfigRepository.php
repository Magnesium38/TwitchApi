<?php namespace MagnesiumOxide\TwitchApi;

use ArrayAccess;

class ConfigRepository implements ArrayAccess {
    protected $file;
    protected $config = [];

    public function __construct($file = null) {
        $this->file = $file ? $file : __DIR__."/../config/TwitchApi.php";
        $this->config = require $this->file;
    }

    public function offsetExists($offset) {
        return isset($this->config[$offset]);
    }

    public function offsetGet($offset) {
        return $this->config[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->config[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->config[$offset]);
    }
}