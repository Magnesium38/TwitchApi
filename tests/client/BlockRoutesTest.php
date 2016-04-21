<?php

use MagnesiumOxide\TwitchApi\Client as Api;
use MagnesiumOxide\TwitchApi\Exception\NotAuthenticatedException;
use MagnesiumOxide\TwitchApi\Exception\InsufficientScopeException;
use MagnesiumOxide\TwitchApi\Scope;

class BlockRoutesTest extends BaseClientTest {
    public function testGetBlockedUsers() {
        $api = $this->getApi(["scopes" => [Scope::ReadUserBlocks]]);
        $this->authenticate($api, [Scope::ReadUserBlocks]);
        $responseBody = ["blocks" => []];
        $query = [
                "limit" => 25,
                "offset" => 0,
        ];

        $response = $this->mockResponse($responseBody, 200);
        $response->offsetGet("blocks")->willReturn([]);

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

    public function testBlockUser() {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testBlockUserThrowsInsufficientScopeException() {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testBlockUserThrowsNotAuthenticatedException() {
        $this->expectException(InsufficientScopeException::class);

        $api = $this->getApi(["scopes" => []]);
        $this->authenticate($api, []);

        $target = "test_channel_two";

        $api->blockUser($target);
    }

    public function testUnblockUser() { $this->markTestIncomplete('This test has not been implemented yet.'); }

}