<?php

namespace BramR\TorExits\Parser;

use BramR\TorExits\Collector\CollectorInterface;
use PSR\Log\LoggerAwareInterface;
use PSR\Http\Message\StreamInterface;

/**
 * Interface ParserInterface
 */
interface ParserInterface extends LoggerAwareInterface
{
    /**
     * setCollector default data stream
     *
     * @param \BramR\TorExits\Collector\CollectorInterface $collector
     * @return ParserInterface
     */
    public function setCollector(CollectorInterface $collector);

    /**
     * getCollector, source for datastream
     *
     * @return \BramR\TorExits\Collector\CollectorInterface
     */
    public function getCollector();

    /**
     * parse, data from api collector
     * by default get the data stream from the collector
     *
     * @param \PSR\Http\Message\StreamInterface $data
     * @return IpList
     */
    public function parse(StreamInterface $data = null);
}
