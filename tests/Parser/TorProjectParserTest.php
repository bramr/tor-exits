<?php

namespace BramR\TorExits\Parser;

use BramR\TorExits\Collector\CollectorFetchException;
use BramR\TorExits\IpList;
use GuzzleHttp\Psr7;

class TorProjectParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $logObserver = $this->createMock('\Psr\Log\LoggerInterface');
        $logObserver->expects($this->once())->method('info');
        $collectorStub = $this->createMock('BramR\TorExits\Collector\CollectorInterface');
        $collectorStub->method('fetch')->willReturn($this->validData());

        $parser = new TorProjectParser();
        $parser->setParseWarningThreshold(100);
        $parser->setLogger($logObserver);
        $parser->setCollector($collectorStub);

        $result = $parser->parse();

        $this->assertCount(112, $result);
    }

    public function testParseInvalidData()
    {
        $parser = new TorProjectParser();
        $logObserver = $this->createMock('\Psr\Log\LoggerInterface');
        $logObserver->expects($this->once())->method('warning');
        $parser->setLogger($logObserver);

        $result = $parser->parse($this->invalidData());
        $this->assertCount(0, $result);
    }

    /**
     * @expectedException InvalidArgumentException
     **/
    public function testParseWithoutCollector()
    {
        $parser = new TorProjectParser();
        $logObserver = $this->createMock('\Psr\Log\LoggerInterface');
        $logObserver->expects($this->once())->method('error');
        $parser->setLogger($logObserver);
        $parser->parse();
    }


    /**
     * @expectedException BramR\TorExits\Collector\CollectorFetchException
     **/
    public function testCollectorException()
    {
        $logObserver = $this->createMock('\Psr\Log\LoggerInterface');
        $logObserver->expects($this->once())->method('error');
        $collectorStub = $this->createMock('BramR\TorExits\Collector\CollectorInterface');
        $collectorStub->method('fetch')->will($this->throwException(new CollectorFetchException));

        $parser = new TorProjectParser();
        $parser->setLogger($logObserver);
        $parser->setCollector($collectorStub);

        $parser->parse();
    }

    protected function validData()
    {
        $resource = fopen(__DIR__.'/../fixtures/exit-addresses', 'r');
        return Psr7\stream_for($resource);
    }

    protected function invalidData()
    {
        $resource = fopen(__DIR__.'/../fixtures/expired-cache.json', 'r');
        return Psr7\stream_for($resource);
    }
}
