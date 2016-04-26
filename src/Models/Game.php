<?php namespace MagnesiumOxide\TwitchApi\Model;

use SebastianBergmann\RecursionContext\InvalidArgumentException;

class Game extends BaseModel {
    protected $numViewers = null;
    protected $numChannels = null;

    public function getName() {
        return $this->getHelper("name");
    }

    public function getBoxArtLinks() {
        return $this->getHelper("box");
    }

    public function getLogoLinks() {
        return $this->getHelper("logo");
    }

    public function getLinks() {
        return $this->getHelper("_links");
    }

    public function getId() {
        return $this->getHelper("_id");
    }

    public function getGiantbombId() {
        return $this->getHelper("giantbomb_id");
    }

    public function getNumViewers() {
        return $this->numViewers;
    }

    public function getNumChannels() {
        return $this->numChannels;
    }

    protected function setNumViewers($numViewers) {
        $this->numViewers = $numViewers;
    }

    protected function setNumChannels($numChannels) {
        $this->numChannels = $numChannels;
    }

    /**
     * Returns a list of the games objects sorted by number of current viewers on Twitch, most popular first.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/games.md#get-gamestop
     *
     * @param int $limit
     * @param int $offset
     * @throws InvalidArgumentException
     * @return array
     */
    public static function getTopGames($limit = 10, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
                "limit" => $limit,
                "offset" => $offset,
        ];

        $uri = self::buildUri("/games/top");
        $headers = self::buildHeaders();
        $result = json_decode(self::$client->get($uri, $query, $headers)->getBody(), true);

        $games = [];
        foreach ($result["top"] as $gameArray) {
            $game = Game::create($gameArray["game"]);
            $game->setNumViewers($gameArray["viewers"]);
            $game->setNumChannels($gameArray["channels"]);
            $games[] = $game;
        }

        return $games;
    }

    /**
     * Returns a list of game objects matching the search query.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/search.md#get-searchgames
     *
     * @param $query
     * @param null $live
     * @return array
     * @throws InvalidArgumentException
     * @throws \Exception
     */
    public static function searchGames($query, $live = null) {
        if ($live !== null && $live !== false && $live !== true) {
            throw new \InvalidArgumentException("Live must be true or false.");
        }

        $q = [
            "query" => urlencode($query),
            "type" => "suggest",
        ];

        if ($live !== null) {
            $q["live"] = $live;
        }

        $uri = self::buildUri("/search/games");
        $headers = self::buildHeaders();
        $result = self::$client->get($uri, $q, $headers);

        if ($result->getStatusCode() == 503) {
            throw new \Exception();
            // REVISE THIS. It means unable to get results
        }

        $body = json_decode($result->getBody(), true);

        $games = [];
        foreach ($body["games"] as $game) {
            $games[] = Game::create($game);
        }

        return $games;
    }
}
