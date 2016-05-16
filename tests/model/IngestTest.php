<?php namespace MagnesiumOxide\TwitchApi\Tests\Models;

use MagnesiumOxide\TwitchApi\Model\BaseModel;
use MagnesiumOxide\TwitchApi\Model\Ingest;

class IngestTest extends BaseTest {
    /** @var Ingest */
    private $ingest;
    private $ingestJson;
    protected $class = Ingest::class;

    public function setUp() {
        $ingestJson = '{"name":"EU: Amsterdam, NL","default":false,"_id":'
            . '24,"url_template":"rtmp://live-ams.twitch.tv/app/{stream_key}","availability":1.0}';
        $ingestArray = json_decode($ingestJson, true);

        $this->ingestJson = $ingestJson;
        $this->ingest = Ingest::create($ingestArray);
    }

    public function testGetName() {
        $this->assertEquals("EU: Amsterdam, NL", $this->ingest->getName());
    }

    public function testGetDefaultStatus() {
        $this->assertFalse($this->ingest->getDefaultStatus());
    }

    public function testGetId() {
        $this->assertEquals("24", $this->ingest->getId());
    }

    public function testGetUrlTemplate() {
        $this->assertEquals("rtmp://live-ams.twitch.tv/app/{stream_key}", $this->ingest->getUrlTemplate());
    }

    public function testGetAvailability() {
        $this->assertEquals("1.0", $this->ingest->getAvailability());
    }

    public function testGetIngests() {
        $client = $this->mockClient();
        $config = $this->mockConfig();

        $headers = [
                "Client-ID" => $config->reveal()["ClientId"],
                "Accept" => BaseModel::ACCEPT_HEADER,
        ];

        $body = '{"ingests":[' . $this->ingestJson . ']}';

        $mockedResponse = $this->mockResponse($body, 200);

        $url = BaseModel::BASE_URL . "/ingests";
        $client->get($url, [], $headers)
                ->shouldBeCalled()
                ->willReturn($mockedResponse);

        $this->assertEquals("24", Ingest::getIngests()[0]->getId());
    }
}
