<?php

namespace BramR\TorExits\Parser;

use BramR\TorExits\IpList;
use GuzzleHttp\Psr7;

class BlutMagieParserTest extends \PHPUnit_Framework_TestCase
{
    public function testParse()
    {
        $parser = new BlutMagieParser();
        $parser->setParseWarningThreshold(100);
        $logObserver = $this->createMock('\Psr\Log\LoggerInterface');
        $logObserver->expects($this->once())->method('info');
        $parser->setLogger($logObserver);

        $result = $parser->parse($this->validData());

        $this->assertCount(114, $result);
    }

    public function testParseInvalidData()
    {
        $parser = new BlutMagieParser();
        $logObserver = $this->createMock('\Psr\Log\LoggerInterface');
        $logObserver->expects($this->once())->method('warning');
        $parser->setLogger($logObserver);

        $result = $parser->parse($this->invalidData());
        $this->assertCount(0, $result);
    }

    protected function validData()
    {
        $resource = fopen(__DIR__.'/../fixtures/blut-magie-addresses.csv', 'r');
        return Psr7\stream_for($resource);
    }

    protected function invalidData()
    {
        return Psr7\stream_for('418 I\'m a teapot');
    }
}
