<?php

namespace BramR\TorExits;

use BramR\TorExits\Parser\ParserInterface;
use BramR\TorExits\Cache\IpListCacheInterface;

/**
 * Class IpList, contains a list of IPv4 addresses
 */
class IpList implements \Countable, \Iterator, \Serializable, \JsonSerializable
{
    protected $position = 0;
    protected $addresses = array();
    protected $addressInList = array();

    /**
     * @param array $addresses of IPv4 addresses as string
     */
    public function __construct(array $addresses = array())
    {
        $this->position = 0;
        if (! empty($addresses)) {
            $this->add($addresses);
        }
    }

    /**
     * fromParser, factory method for creating an ipList from parser
     *
     * @param ParserInterface $parser
     * @return IpList
     */
    public static function fromParser(ParserInterface $parser)
    {
        return $parser->parse();
    }

    /**
     * fromParserWithCache, factory method for creating an ipList from parser while
     * checking a cache layer first.
     * If the cache layer is not filled and a nonempty list is returned by parser then
     * the cache is set with that list
     *
     * @param ParserInterface $parser
     * @param IpListCacheInterface $cache
     * @return IpList
     */
    public static function fromParserWithCache(ParserInterface $parser, IpListCacheInterface $cache)
    {
        $ipList = $cache->fetch();
        if (! is_null($ipList)) {
            return $ipList;
        } else {
            $ipList = self::fromParser($parser);
            if (! empty($ipList)) {
                if (! $cache->store($ipList)) {
                    throw new \RuntimeException('Failed storing new tor exit node list in cache.');
                }
            }
            return $ipList;
        }
    }


    /**
     * add addresses to an IpList
     * Ignores invalid IPv4 Addresses
     *
     * @param mixed $addresses (array|string)
     * @return IpList
     */
    public function add($addresses)
    {
        if (is_string($addresses)) {
            $addresses = (array) $addresses;
        }

        foreach ($addresses as $address) {
            $ip = ip2long($address);
            if ($ip && ! isset($this->addressInList[$ip])) {
                $this->addresses[] = $ip;
                $this->addressInList[$ip] = true;
            }
        }
        return $this;
    }

    /**
     * fromArray load an IpList from an array
     * Ignores invalid IPv4 Addresses
     *
     * @param mixed $addresses (array|string)
     * @return IpList
     */
    public function remove($addresses)
    {
        if (is_string($addresses)) {
            $addresses = (array) $addresses;
        }

        foreach ($addresses as $address) {
            $ip = ip2long($address);
            if ($ip && isset($this->addressInList[$ip])) {
                unset($this->addressInList[$ip]);
                if (false !== $key = array_search($ip, $this->addresses)) {
                    unset($this->addresses[$key]);
                }
            }
        }
        return $this;
    }

    /**
     * toArray, convert to array
     * @return array (with IPv4 addresses as strings)
     */
    public function toArray()
    {
        $result = array();
        foreach ($this->addresses as $ip) {
            $result[] = long2ip($ip);
        }
        return $result;
    }

    /**
     * contains, an ip address
     * @param string $address ip
     * @return boolean
     */
    public function contains($address)
    {
        $ip = ip2long($address);
        return $ip ? isset($this->addressInList[$ip]) : false;
    }

    /**
     * doesNotContain, an ip address (for semantics)
     * @param string $address ip
     * @return boolean
     */
    public function doesNotContain($address)
    {
        return ! $this->contains($address);
    }

    /**
     * isEmpty
     * @return boolean
     */
    public function isEmpty()
    {
        return empty($this->addresses);
    }


    public function clear()
    {
        $this->position = 0;
        $this->addresses = array();
        $this->addressInList = array();
        return $this;
    }

    //Implement Countable
    public function count()
    {
        return count($this->addresses);
    }

    //Implement Iterator
    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return long2ip($this->addresses[$this->position]);
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return isset($this->addresses[$this->position]);
    }

    public function sort()
    {
        sort($this->addresses);
        return $this;
    }

    //Implement JsonSerializable
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    //Implement Serializable
    public function serialize()
    {
        return serialize(array(
            $this->addresses,
            $this->addressInList
        ));
    }

    public function unserialize($data)
    {
        list(
            $this->addresses,
            $this->addressInList
        ) = unserialize($data);
    }
}
