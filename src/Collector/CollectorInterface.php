<?php

namespace BramR\TorExits\Collector;

use Psr\Log\LoggerAwareInterface;

/**
 * Interface CollectorInterface
 */
interface CollectorInterface extends LoggerAwareInterface
{
    /**
     * fetch, data from api
     *
     * @throws CollectorFetchException if fetch doesn't get a succesful response.
     * @return \PSR\Http\Message\StreamInterface $tordata
     */
    public function fetch();
}
