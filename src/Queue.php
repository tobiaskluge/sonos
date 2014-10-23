<?php

namespace duncan3dc\Sonos;

use duncan3dc\DomParser\XmlParser;

/**
 * Provides an interface for managing the queue of a controller.
 */
class Queue
{
    /**
     * The unique id of the queue
     */
    protected $id = false;

    /**
     * The current update id to be issued with upnp requests
     */
    protected $updateId = false;

    /**
     * The Controller instance this queue is for
     */
    protected $controller = false;


    /**
     * Create an instance of the Queue class.
     *
     * @param duncan3dc\Sonos\Controller The Controller instance that this queue is for
     */
    public function __construct(Controller $param)
    {
        $this->id = "Q:0";
        $this->updateId = false;
        $this->controller = $param;
    }


    /**
     * Send a soap request to the controller for this queue.
     *
     * @param string The service to send the request to
     * @param string The action to call
     * @param array The parameters to pass
     *
     * @return mixed
     */
    protected function soap($service, $action, $params = [])
    {
        $params["ObjectID"] = $this->id;

        if ($action == "Browse") {
            $params["Filter"] = "";
            $params["SortCriteria"] = "";
        }

        return $this->controller->soap($service, $action, $params);
    }


    /**
     * Send a browse request to the controller to get queue info.
     *
     * @param string The type of browse request to send
     * @param int The position to start browsing from
     * @param int The number of tracks from the queue to return
     *
     * @return mixed
     */
    protected function browse($type, $start = 0, $limit = 1)
    {
        return $this->soap("ContentDirectory", "Browse", [
            "BrowseFlag"        =>  "Browse" . $type,
            "StartingIndex"     =>  $start,
            "RequestedCount"    =>  $limit,
            "Filter"            =>  "",
            "SortCriteria"      =>  "",
        ]);
    }


    /**
     * Get the next update id, or used the previously cached one.
     *
     * @return int
     */
    protected function getUpdateId()
    {
        if (!$this->updateId || !Network::$cache) {
            $data = $this->browse("DirectChildren");
            $this->updateId = $data["UpdateID"];
        }
        return $this->updateId;
    }


    /**
     * Get tracks from the queue.
     *
     * @param int The zero-based position in the queue to start from
     * @param int The maximum number of tracks to return
     *
     * @return array
     */
    public function getTracks($start = 0, $total = 0)
    {
        $tracks = [];

        if ($total > 0 && $total < 100) {
            $limit = $total;
        } else {
            $limit = 100;
        }

        do {
            $data = $this->browse("DirectChildren", $start, $limit);
            $parser = new XmlParser($data["Result"]);
            foreach ($parser->getTags("item") as $item) {
                $tracks[] = [
                    "id"        =>  $item->getAttribute("id"),
                    "uri"       =>  $item->getTag("res")->nodeValue,
                    "title"     =>  $item->getTag("title")->nodeValue,
                    "artist"    =>  $item->getTag("creator")->nodeValue,
                    "album"     =>  $item->getTag("album")->nodeValue,
                ];
                if ($total > 0 && count($tracks) >= $total) {
                    return $tracks;
                }
            }

            $start += $limit;
        } while ($data["TotalMatches"] && count($tracks) < $data["TotalMatches"]);

        return $tracks;
    }


    /**
     * Add tracks to the queue.
     *
     * @param string|string[] The URI of the track to add, multiple tracks can be added by passing an array of URIs
     * @param int The position to insert the tracks in the queue (zero-based), by default the track(s) will be added to the end of the queue
     *
     * @return boolean
     */
    public function addTracks($tracks, $position = null)
    {
        if ($position === null) {
            $data = $this->browse("DirectChildren");
            $this->updateId = $data["UpdateID"];
            $position = $data["TotalMatches"] + 1;
        }

        if (!is_array($tracks)) {
            $tracks = [$tracks];
        }

        # Ensure the update id is set to begin with
        $this->getUpdateID();

        foreach ($tracks as $uri) {
            $data = $this->soap("AVTransport", "AddURIToQueue", [
                "UpdateID"                          =>  $this->updateId,
                "EnqueuedURI"                       =>  $uri,
                "EnqueuedURIMetaData"               =>  "",
                "DesiredFirstTrackNumberEnqueued"   =>  $position++,
                "EnqueueAsNext"                     =>  0,
            ]);
            $this->updateId = $data["NewUpdateID"];

            if ($data["NumTracksAdded"] != 1) {
                return false;
            }
        }
        return true;
    }


    /**
     * Remove tracks from the queue.
     *
     * @param int|int[] The zero-based position of the track to remove, or an array of positions
     *
     * @return boolean
     */
    public function removeTracks($positions)
    {
        if (!is_array($positions)) {
            $positions = [$positions];
        }

        $ranges = [];
        $key = 0;
        $last = -1;
        sort($positions);
        foreach ($positions as $position) {
            $position++;
            if ($last > -1) {
                if ($position == $last + 1) {
                    $ranges[$key]++;
                    $last = $position;
                    continue;
                }
            }
            $key = $position;
            $ranges[$key] = 1;
            $last = $position;
        }

        $offset = 0;
        foreach ($ranges as $position => $limit) {
            $position -= $offset;
            $data = $this->soap("AVTransport", "RemoveTrackRangeFromQueue", [
                "UpdateID"          =>  $this->getUpdateID(),
                "StartingIndex"     =>  $position,
                "NumberOfTracks"    =>  $limit,
            ]);
            $this->updateId = $data;
            $offset += $limit;
        }
        return true;
    }
}
