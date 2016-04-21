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
     * @param $responseCode
     * @param array $body
     */
    public function __construct($responseCode, array $body) {
        $this->code = $responseCode;
        $this->body = $body;
    }

    /**
     * @return int|null
     */
    public function getResponseCode() {
        return $this->getResponseCode();
    }

    /**
     * @return array
     */
    public function getBody() {
        return $this->getBody();
    }
}