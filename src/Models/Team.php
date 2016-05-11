<?php namespace MagnesiumOxide\TwitchApi\Model;

class Team extends BaseModel {
    public function getInfo() {
        return $this->getHelper("info");
    }

    protected function getLinks() {
        return $this->getHelper("_links");
    }

    public function getBackground() {
        return $this->getHelper("background");
    }

    public function getBanner() {
        return $this->getHelper("banner");
    }

    public function getName() {
        return $this->getHelper("name");
    }

    protected function getId() {
        return $this->getHelper("_id");
    }

    public function getUpdatedAt() {
        return new \DateTime($this->getHelper("updated_at"));
    }

    public function getDisplayName() {
        return $this->getHelper("display_name");
    }

    public function getCreatedAt() {
        return new \DateTime($this->getHelper("created_at"));
    }

    public function getLogo() {
        return $this->getHelper("logo");
    }

    /**
     * Returns a list of active teams.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/teams.md#get-teams
     *
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public static function getAllTeams($limit = 25, $offset = 0) {
        if ($limit > 100) {
            throw new \InvalidArgumentException("Limit cannot be greater than 100.");
        }

        $query = [
            "limit" => $limit,
            "offset" => $offset,
        ];

        $uri = self::buildUri("/teams");
        $response = self::get($uri, $query);

        return self::responseToArray($response, "teams");
    }

    /**
     * Returns a team object for $team.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/teams.md#get-teamsteam
     *
     * @param $team
     * @return array
     */
    public static function getTeam($team) {
        $uri = self::buildUri("/teams/:team", ["team" => $team]);

        $response = self::get($uri);
        $body = json_decode($response->getBody(), true);

        return static::create($body);
    }

    /**
     * Returns a list of team objects that $channel belongs to.
     * https://github.com/justintv/Twitch-API/blob/master/v3_resources/channels.md#get-channelschannelteams
     *
     * @param $channel
     * @return array
     */
    public static function getTeamsFor($channel) {
        $uri = self::buildUri("/channels/:channel/teams", ["channel" => $channel]);

        $response = self::get($uri);
        return static::responseToArray($response, "teams");
    }
}
