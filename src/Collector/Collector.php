<?php

namespace BramR\TorExits\Collector;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Collector
 */
class Collector implements CollectorInterface
{
    use LoggerAwareTrait;

    protected $client;
    protected $location;

    public function __construct(Client $client = null)
    {
        $this->client = is_null($client) ? new Client() : $client;
        $this->setLogger(new NullLogger());
    }

    /**
     * Getter for location
     *
     * return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Setter for location
     *
     * @param string $location
     * @return Collector
     */
    public function setLocation($location)
    {
        $this->location = $location;
        return $this;
    }

    /**
     * fetch
     * @return \PSR\Http\Message\StreamInterface $tordata | null
     */
    public function fetch()
    {
        if ($this->getLocation()) {
            try {
                $response = $this->client->request('GET', $this->getLocation(), ['stream' => true]);
                if ($response instanceof ResponseInterface) {
                    if ($response->getStatusCode() === 200) {
                        return $response->getBody();
                    } else {
                        throw new CollectorFetchException(
                            'Failed fetching with response status code: '.$response->getStatusCode()
                        );
                    }
                } else {
                    //Should always return a response, better be safe
                    throw new CollectorFetchException('Failed without response.');
                }
            } catch (\Exception $e) {
                $this->logger->error('Failed fetching response, error: ' . $e->getMessage());
                throw $e;
            }
        } else {
            $this->logger->error('Tried to fetch data from empty location.');
        }
    }
}
