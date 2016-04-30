<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use MagnesiumOxide\TwitchApi\Model\BaseModel;
use MagnesiumOxide\TwitchApi\Model\Game;

class GameTest extends BaseTest {
    protected $class = Game::class;

    public function testGetTopGames() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $body = '{"_links":{"self":"https://api.twitch.tv/kraken/games/top?limit=10&offset=0",'
            . '"next":"https://api.twitch.tv/kraken/games/top?limit=10&offset=10"},"_total":322,'
            . '"top":[{"game":{"name":"Counter-Strike: Global Offensive","box":{"large":'
            . '"http://static-cdn.jtvnw.net/ttv-boxart/Counter-Strike:%20Global%20Offensive-272x380.jpg",'
            . '"medium":"http://static-cdn.jtvnw.net/ttv-boxart/Counter-Strike:%20Global%20Offensive-136x190.jpg",'
            . '"small":"http://static-cdn.jtvnw.net/ttv-boxart/Counter-Strike:%20Global%20Offensive-52x72.jpg",'
            . '"template":"http://static-cdn.jtvnw.net/ttv-boxart/Counter-Strike:%20Global%20Offensive-'
            . '{width}x{height}.jpg"},"logo":{"large":"http://static-cdn.jtvnw.net/ttv-logoart/Counter-Strike'
            . ':%20Global%20Offensive-240x144.jpg","medium":"http://static-cdn.jtvnw.net/ttv-logoart/Counter-'
            . 'Strike:%20Global%20Offensive-120x72.jpg","small":"http://static-cdn.jtvnw.net/ttv-logoart/'
            . 'Counter-Strike:%20Global%20Offensive-60x36.jpg","template":"http://static-cdn.jtvnw.net/'
            . 'ttv-logoart/Counter-Strike:%20Global%20Offensive-{width}x{height}.jpg"},"_links":{},'
            . '"_id":32399,"giantbomb_id":36113},"viewers":23873,"channels":305}]}';

        $mockedResponse = $this->mockResponse($body, 200);

        $query = [
            "limit" => 10,
            "offset" => 0,
        ];

        $url = BaseModel::BASE_URL . "/games/top";
        $client->get($url, $query, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $topGames = Game::getTopGames();
        $this->assertEquals("Counter-Strike: Global Offensive", $topGames[0]->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGetTopGamesThrowsInvalidArgumentException() {
        Game::getTopGames(1000, 0);
    }

    public function testSearchGames() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $body = '{"_links":{"self":"https://api.twitch.tv/kraken/search/games?q=star&type=suggest"},"games":'
            . '[{"box":{"large":"http://static-cdn.jtvnw.net/ttv-boxart/StarCraft%20II:%20Wings%20of%20Liberty-'
            . '272x380.jpg","medium":"http://static-cdn.jtvnw.net/ttv-boxart/StarCraft%20II:%20Wings%20of%20'
            . 'Liberty-136x190.jpg","small":"http://static-cdn.jtvnw.net/ttv-boxart/StarCraft%20II:%20Wings%20'
            . 'of%20Liberty-52x72.jpg","template":"http://static-cdn.jtvnw.net/ttv-boxart/StarCraft%20II:%20'
            . 'Wings%20of%20Liberty-{width}x{height}.jpg"},"logo":{"large":"http://static-cdn.jtvnw.net/ttv-'
            . 'logoart/StarCraft%20II:%20Wings%20of%20Liberty-240x144.jpg","medium":"http://static-cdn.jtvnw.net/'
            . 'ttv-logoart/StarCraft%20II:%20Wings%20of%20Liberty-120x72.jpg","small":"http://static-cdn.jtvnw.net/'
            . 'ttv-logoart/StarCraft%20II:%20Wings%20of%20Liberty-60x36.jpg","template":"http://static-cdn.jtvnw'
            . 'net/ttv-logoart/StarCraft%20II:%20Wings%20of%20Liberty-{width}x{height}.jpg"},"popularity":114,'
            . '"name":"StarCraft II: Wings of Liberty","_id":63011880,"_links":{},"giantbomb_id":20674}]}';

        $mockedResponse = $this->mockResponse($body, 200);

        $q = "star";
        $query = [
            "query" => urlencode($q),
            "type" => "suggest",
        ];

        $url = BaseModel::BASE_URL . "/search/games";
        $client->get($url, $query, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $games = Game::searchGames($q);
        $this->assertEquals("StarCraft II: Wings of Liberty", $games[0]->getName());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSearchGamesThrowsInvalidArgumentException() {
        Game::searchGames("", "");
    }
}
