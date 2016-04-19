<?php

use MagnesiumOxide\TwitchApi\Client as Api;
use MagnesiumOxide\TwitchApi\Exception\NotAuthenticatedException;
use MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException;
use MagnesiumOxide\TwitchApi\Scope;

class BlockRoutesTest extends BaseTest {
    public function testGetBlockedUsers() {
        $api = $this->getApi(["scopes" => [Scope::ReadUserBlocks]]);
        $this->authenticate($api, [Scope::ReadUserBlocks]);
        $response = [];
        $query = [
                "limit" => 25,
                "offset" => 0,
        ];

        $uri = Api::BASE_URL . "/users/" . $this->username . "/blocks";
        $this->requestShouldBeMade("GET", $uri, $query, $response, true);

        $api->getBlockedUsers();
    }

    public function testGetBlockedUsersThrowsInvalidArgumentException() {
        $this->expectException(\InvalidArgumentException::class);

        $api = $this->getApi(["scopes" => [Scope::ReadUserBlocks]]);
        $this->authenticate($api, [Scope::ReadUserBlocks]);
        $api->getBlockedUsers(300, 25);
    }

    public function testGetBlockedUsersThrowsNotAuthenticatedException() {
        $this->expectException(NotAuthenticatedException::class);

        $api = $this->getApi();
        $api->getBlockedUsers();
    }

    public function testGetBlockedUsersThrowsInsufficientScopeException() {
        $this->expectException(InsufficientScopeException::class);

        $api = $this->getApi(["scopes" => []]);
        $this->authenticate($api, []);

        $api->getBlockedUsers();
    }

    public function testBlockUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }
    public function testUnblockUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }

}