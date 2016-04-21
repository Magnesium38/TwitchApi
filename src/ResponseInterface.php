<?php namespace MagnesiumOxide\TwitchApi;

/**
 * @package MagnesiumOxide\TwitchApi
 */
interface ResponseInterface extends \ArrayAccess {
    /**
     * @return null|int
     */
    public function getStatusCode();

    /**
     * @return array
     */
    public function getBody();
}