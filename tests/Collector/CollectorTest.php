<?php

namespace BramR\TorExits\Collector;

use BramR\TorExits\IpList;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use Psr\Log\LoggerInterface;

class CollectorTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultLocation()
    {
        $mockClient = $this->getMockBuilder('\GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $collector = new Collector($mockClient);

        $this->assertEquals('', $collector->getLocation());
    }

    public function testSetLocation()
    {
        $mockClient = $this->getMockBuilder('\GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $collector = new Collector($mockClient);
        $collector->setLocation('http://foo.bar');

        $this->assertEquals('http://foo.bar', $collector->getLocation());
    }

    public function testRetreive()
    {
        $mockClient = $this->getMockBuilder('\GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $mockClient->method('request')->willReturn($this->successfulResponse());

        $collector = new Collector($mockClient);
        $collector->setLocation('http://localhost');
        $stream = $collector->fetch();

        $this->assertEquals('tor-exit-node', $stream->getContents());
    }

    public function testRetreiveWithoutLocation()
    {
        $mockClient = $this->getMockBuilder('\GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $collector = new Collector($mockClient);
        $stream = $collector->fetch();

        $this->assertNull($collector->fetch());
    }

    /**
     * @expectedException BramR\TorExits\Collector\CollectorFetchException
     **/
    public function testForbiddenRequest()
    {
        $mockClient = $this->getMockBuilder('\GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $mockClient->method('request')->willReturn($this->forbiddenResponse());

        $collector = new Collector($mockClient);
        $collector->setLocation('http://localhost');

        $expectException = $collector->fetch();
    }

    /**
     * @expectedException BramR\TorExits\Collector\CollectorFetchException
     **/
    public function testFailedRequest()
    {
        $mockClient = $this->getMockBuilder('\GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->getMock();
        $mockClient->method('request')->willReturn('this should never happen');

        $collector = new Collector($mockClient);
        $collector->setLocation('http://localhost');

        $expectException = $collector->fetch();
    }

    protected function successfulResponse()
    {
        $stream = $this->createMock('Psr\Http\Message\StreamInterface');
        $stream->method('getContents')->willReturn('tor-exit-node');

        $stub = $this->getMockBuilder('GuzzleHttp\Psr7\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $stub->method('getStatusCode')->willReturn(200);
        $stub->method('getBody')->willReturn($stream);
        return $stub;
    }

    protected function forbiddenResponse()
    {
        $stub = $this->getMockBuilder('GuzzleHttp\Psr7\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $stub->method('getStatusCode')->willReturn(403);
        return $stub;
    }
}
