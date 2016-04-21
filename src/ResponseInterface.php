<?php namespace MagnesiumOxide\TwitchApi;

/**
 * @package MagnesiumOxide\TwitchApi
 */
interface ResponseInterface {
    /**
     * @return null|int
     */
    public function getResponseCode();

    /**
     * @return array
     */
    public function getBody();
}