<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use MagnesiumOxide\TwitchApi\Model\BaseModel;
use MagnesiumOxide\TwitchApi\Model\Team;

class TeamTest extends BaseTest {
    protected $class = Team::class;
    private $teamJson;
    /** @var Team */
    private $team;

    public function setUp() {
        $this->teamJson = '{"info":"<p>Team Info</p>","_links":{"self":"https://api.twitch.tv/kraken/teams/testteam"}'
                . ',"background":"http://static-cdn.jtvnw.net/jtv_user_pictures/team-testteam-background_image-'
                . 'c72e038f428c9c7d.png","banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/team-testteam-'
                . 'banner_image-cc318b0f084cb67c-640x125.jpeg","name":"testteam","_id":1,"updated_at":"2012-11-'
                . '14T01:30:00Z","display_name":"test","created_at":"2011-10-11T22:49:05Z","logo":"http://static-'
                . 'cdn.jtvnw.net/jtv_user_pictures/team-testteam-team_logo_image-46943237490be5e7-300x300.jpeg"}';
        $teamArray = json_decode($this->teamJson, true);

        $this->team = Team::create($teamArray);
    }

    public function testGetInfo() {
        $this->assertEquals("<p>Team Info</p>", $this->team->getInfo());
    }

    public function testGetBackground() {
        $background = 'http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'team-testteam-background_image-c72e038f428c9c7d.png';
        $this->assertEquals($background, $this->team->getBackground());
    }

    public function testGetBanner() {
        $banner = 'http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'team-testteam-banner_image-cc318b0f084cb67c-640x125.jpeg';
        $this->assertEquals($banner, $this->team->getBanner());
    }

    public function testGetName() {
        $this->assertEquals("testteam",  $this->team->getName());
    }

    public function testGetUpdatedAt() {
        $this->assertEquals(new \DateTime("2012-11-14T01:30:00Z"), $this->team->getUpdatedAt());
    }

    public function testGetDisplayName() {
        $this->assertEquals("test", $this->team->getDisplayName());
    }

    public function testGetCreatedAt() {
        $this->assertEquals(new \DateTime("2011-10-11T22:49:05Z"), $this->team->getCreatedAt());
    }

    public function testGetLogo() {
        $logo = 'http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'team-testteam-team_logo_image-46943237490be5e7-300x300.jpeg';
        $this->assertEquals($logo, $this->team->getLogo());
    }

    public function testGetAllTeams() {
        $json = '{"_links":{"next":"https://api.twitch.tv/kraken/teams?limit=25&offset=25",'
            . '"self":"https://api.twitch.tv/kraken/teams?limit=25&offset=0"},"teams":[{'
            . '"info":"<p>Team Info</p>","_links":{"self":"https://api.twitch.tv/kraken/teams/testteam"}'
            . ',"background":"http://static-cdn.jtvnw.net/jtv_user_pictures/team-testteam-background_'
            . 'image-c72e038f428c9c7d.png","banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/team-'
            . 'testteam-banner_image-cc318b0f084cb67c-640x125.jpeg","name":"testteam","_id":1,'
            . '"updated_at":"2012-11-14T01:30:00Z","display_name":"test","created_at":"2011-'
            . '10-11T22:49:05Z","logo":"http://static-cdn.jtvnw.net/jtv_user_pictures/team-'
            . 'testteam-team_logo_image-46943237490be5e7-300x300.jpeg"}]}';

        $client = $this->mockClient();
        $config = $this->mockConfig();

        $query = [
            "limit" => 25,
            "offset" => 0,
        ];

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $mockedResponse = $this->mockResponse($json, 200);

        $url = BaseModel::BASE_URL . "/teams";

        $client->get($url, $query, $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $teams = Team::getAllTeams();
        $this->assertEquals("testteam", $teams[0]->getName());
    }

    public function testGetTeam() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $mockedResponse = $this->mockResponse($this->teamJson, 200);

        $url = BaseModel::BASE_URL . "/teams/testteam";

        $client->get($url, [], $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $teams = Team::getTeam("testteam");
        $this->assertEquals("testteam", $teams->getName());
    }

    public function testGetTeamsFor() {
        $json = '{"_links":{"self":"http://api.twitch.tv/kraken/channels/test_channel/teams"},'
            . '"teams":[{"_links":{"self":"https://api.twitch.tv/kraken/teams/staff"},"_id":10,'
            . '"name":"staff","info":"We save the world..\n\n\n","display_name":"Twitch Staff",'
            . '"created_at":"2011-10-25T23:55:47Z","updated_at":"2013-05-24T00:17:12Z","logo":'
            . '"http://static-cdn.jtvnw.net/jtv_user_pictures/team-staff-team_logo_image-'
            . 'e26f89ac4f424216-300x300.png","banner":"http://static-cdn.jtvnw.net/jtv_user_pictures/'
            . 'team-staff-banner_image-c81e25b281c06e8f-640x125.png","background":null}]}';

        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
            "Client-ID" => $config->reveal()["ClientId"],
            "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $mockedResponse = $this->mockResponse($json, 200);
        $channel = "test_channel";

        $url = BaseModel::BASE_URL . "/channels/{$channel}/teams";

        $client->get($url, [], $headers)
            ->shouldBeCalled()
            ->willReturn($mockedResponse);

        $teams = Team::getTeamsFor($channel);
        $this->assertEquals("staff", $teams[0]->getName());
    }
}
