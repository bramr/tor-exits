<?php

namespace BramR\TorExits\Collector;

/**
 * Class BlutMagieCollector
 */
class BlutMagieCollector extends Collector
{
    const DEFAULT_LOCATION = 'https://torstatus.blutmagie.de/ip_list_exit.php/Tor_ip_list_EXIT.csv';

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
