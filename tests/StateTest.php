<?php

namespace duncan3dc\SonosTests;

use duncan3dc\DomParser\XmlParser;
use duncan3dc\Sonos\State;
use duncan3dc\SonosTests\Tracks\TrackTest;
use Mockery;

class StateTest extends TrackTest
{
    public function setUp()
    {
        $controller = Mockery::mock("duncan3dc\Sonos\Controller");
        $controller->ip = "192.168.0.66";

        $xml = new XmlParser($this->xml1);
        $this->track1 = State::createFromXml($xml->getTag("track"), $controller);

        $xml = new XmlParser($this->xml2);
        $this->track2 = State::createFromXml($xml->getTag("track"), $controller);
    }


    public function testTrackNumber()
    {
        $this->assertSame(3, $this->track1->trackNumber);
    }
}
