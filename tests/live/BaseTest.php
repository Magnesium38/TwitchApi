<?php namespace MagnesiumOxide\TwitchApi\Tests\Live;

use MagnesiumOxide\Config\Repository;
use MagnesiumOxide\TwitchApi\Model\BaseModel;
use PHPUnit_Framework_TestCase;

abstract class BaseTest extends PHPUnit_Framework_TestCase {
    protected $config;

    public function setUp() {
        $config = [
            "ClientId" => "tmi1rmnfyphfdkxxmztjbozxg1v41di",
            "RedirectUri" => "http://localhost",
            "Scope" => array(),
        ];
        $this->config = new Repository($config);

        BaseModel::setConfig($this->config);
    }
}
