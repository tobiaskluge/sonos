<?php

namespace duncan3dc\SonosTests\Services;

use duncan3dc\Sonos\Controller;
use duncan3dc\Sonos\Services\Radio;
use duncan3dc\Sonos\Tracks\Stream;
use duncan3dc\SonosTests\MockTest;
use Mockery;

class QueueTest extends MockTest
{

    public function setUp()
    {
        parent::setUp();

        $this->controller = Mockery::mock(Controller::class);
        $this->radio = new Radio($this->controller);
    }


    public function testGetFavouriteStations()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/0",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<item><title>Station 1</title><res>URI</res></item>",
        ]);

        $result = $this->radio->getFavouriteStations();

        $this->assertEquals([new Stream("URI", "Station 1")], $result);
    }


    public function testGetFavouriteStationExact()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/0",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<item><title>Station 1</title><res>URI</res></item>",
        ]);

        $result = $this->radio->getFavouriteStation("Station 1");

        $this->assertEquals(new Stream("URI", "Station 1"), $result);
    }


    public function testGetFavouriteStationRough()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/0",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<item><title>Station 1</title><res>URI</res></item>",
        ]);

        $result = $this->radio->getFavouriteStation("STATION 1");

        $this->assertEquals(new Stream("URI", "Station 1"), $result);
    }



    public function testGetFavouriteStationFail()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/0",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<item><title>Station 1</title><res>URI</res></item>",
        ]);

        $result = $this->radio->getFavouriteStation("Station 2");

        $this->assertNull($result);
    }


    public function testGetFavouriteShows()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/1",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<container><title>Show 1</title><res>URI</res></container>",
        ]);

        $result = $this->radio->getFavouriteShows();

        $this->assertEquals([new Stream("URI", "Show 1")], $result);
    }


    public function testGetFavouriteShowExact()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/1",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<container><title>Show 1</title><res>URI</res></container>",
        ]);

        $result = $this->radio->getFavouriteShow("Show 1");

        $this->assertEquals(new Stream("URI", "Show 1"), $result);
    }


    public function testGetFavouriteShowRough()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/1",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<container><title>Show 1</title><res>URI</res></container>",
        ]);

        $result = $this->radio->getFavouriteShow("show 1");

        $this->assertEquals(new Stream("URI", "Show 1"), $result);
    }



    public function testGetFavouriteShowFail()
    {
        $this->controller->shouldReceive("soap")->once()->with("ContentDirectory", "Browse", [
            "ObjectID"          =>  "R:0/1",
            "BrowseFlag"        =>  "BrowseDirectChildren",
            "Filter"            =>  "*",
            "StartingIndex"     =>  0,
            "RequestedCount"    =>  100,
            "SortCriteria"      =>  "",
        ])->andReturn([
            "Result"    =>  "<container><title>Show 1</title><res>URI</res></container>",
        ]);

        $result = $this->radio->getFavouriteShow("Show 2");

        $this->assertNull($result);
    }
}
