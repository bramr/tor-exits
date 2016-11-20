<?php

namespace BramR\TorExits\Collector;

/**
 * Class TorProjectCollector
 */
class TorProjectCollector extends Collector
{
    const DEFAULT_LOCATION = 'https://check.torproject.org/exit-addresses';

    /**
     * Getter for location
     *
     * return string
     */
    public function getLocation()
    {
        return $this->location ? $this->location : self::DEFAULT_LOCATION;
    }
}
