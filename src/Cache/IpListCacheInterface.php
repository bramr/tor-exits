<?php

namespace BramR\TorExits\Cache;

use BramR\TorExits\IpList;

/**
 * Interface IpListCacheInterface
 */
interface IpListCacheInterface
{
    /**
     * store IpList in cache
     *
     * @param IpList $ipList
     * @param int $ttl in cache, optional
     * @return bool succesful
     */
    public function store(IpList $ipList, $ttl = null);

    /**
     * fetch IpList from cache
     *
     * @return IpList|null (if not stored in cache/expired)
     */
    public function fetch();
}
