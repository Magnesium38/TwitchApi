<?php namespace MagnesiumOxide\TwitchApi;

/**
 * @package MagnesiumOxide\TwitchApi
 */
class Response implements ResponseInterface {
    /** @var array */
    protected $body;
    /** @var int */
    protected $code;

    /**
     * @param int $statusCode
     * @param array $body
     */
    public function __construct($statusCode, array $body) {
        $this->code = $statusCode;
        $this->body = $body;
    }

    /**
     * @return int|null
     */
    public function getStatusCode() {
        return $this->code;
    }

    /**
     * @return array
     */
    public function getBody() {
        return $this->body;
    }

    public function offsetExists($offset) {
        return isset($this->body[$offset]);
    }

    public function offsetGet($offset) {
        return $this->body[$offset];
    }

    public function offsetSet($offset, $value) {
        $this->body[$offset] = $value;
    }

    public function offsetUnset($offset) {
        unset($this->body[$offset]);
    }
}