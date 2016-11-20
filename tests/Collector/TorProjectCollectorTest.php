<?php

namespace BramR\TorExits\Collector;

use BramR\TorExits\IpList;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Psr\Log\LoggerInterface;

class TorProjectCollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultLocation()
    {
        $mockClient = $this->getMockBuilder('\GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $collector = new TorProjectCollector($mockClient);

        $this->assertEquals(TorProjectCollector::DEFAULT_LOCATION, $collector->getLocation());
    }

    public function testSetLocation()
    {
        $mockClient = $this->getMockBuilder('\GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $collector = new TorProjectCollector($mockClient);
        $collector->setLocation('http://foo.bar');

        $this->assertEquals('http://foo.bar', $collector->getLocation());
    }
}
