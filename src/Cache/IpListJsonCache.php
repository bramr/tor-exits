<?php

namespace BramR\TorExits\Cache;

use BramR\TorExits\IpList;

/**
 * Class IpListJsonCache
 */
class IpListJsonCache implements IpListCacheInterface
{
    const DEFAULT_LOCATION = '/tmp/tor-exits.cache.json';
    const DEFAULT_TTL = 7200;

    protected $location;
    protected $ttl;

    /**
     * Setter for location
     *
     * @param string $location
     * @return IpListJsonCache
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Setter for ttl
     *
     * @param int $ttl
     * @return IpListJsonCache
     */
    public function setTtl($ttl)
    {
        if (intval($ttl) < 1) {
            throw new \InvalidArgumentException('Invalid ttl.');
        }
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Getter for location
     *
     * return string
     */
    public function getLocation()
    {
        return $this->location ? $this->location : self::DEFAULT_LOCATION;
    }

    /**
     * Getter for location
     *
     * return int
     */
    public function getTtl()
    {
        return $this->ttl ? $this->ttl : self::DEFAULT_TTL;
    }

    /**
     * {@inheritdoc}
     */
    public function store(IpList $ipList, $ttl = null)
    {
        if (is_null($ttl)) {
            $ttl = $this->getTtl();
        }
        $store = new \stdClass();
        $store->iplist = $ipList;
        $store->expires = $this->calculateExpiration($ttl)->format('c');

        if (! is_writable(dirname($this->getLocation())) ||
            (file_exists($this->getLocation()) && ! is_writable($this->getLocation()))
        ) {
            return false;
        }
        return (bool) file_put_contents($this->getLocation(), json_encode($store));
    }

    /**
     * {@inheritdoc}
     */
    public function fetch()
    {
        if (! is_readable($this->getLocation())) {
            return null;
        }
        $data = json_decode(file_get_contents($this->getLocation()));
        if (! isset($data->iplist) || ! isset($data->expires)) {
            return null;
        }
        if (new \DateTime($data->expires) < new \DateTime()) {
            return null;
        }
        return new IpList($data->iplist);
    }

    /**
     * calculateExpiration, calculate expiration DateTime based on ttl
     *
     * @param int|null $ttl
     * @return DateTime
     */
    protected function calculateExpiration($ttl = null)
    {
        if (! is_int($ttl) && $ttl < 1) {
            throw new \InvalidArgumentException('Invalid ttl.');
        }
        return new \DateTime(sprintf('+%d seconds', $ttl));
    }
}
