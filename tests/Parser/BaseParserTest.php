<?php

namespace BramR\TorExits\Parser;

class BaseParserTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructorSetsCollector()
    {
        $collectorMock = $this->createMock('\BramR\TorExits\Collector\CollectorInterface');

        $mock = $this->getMockBuilder('\BramR\TorExits\Parser\BaseParser')
            ->disableOriginalConstructor()
            ->setMethods(array('setCollector'))
            ->getMockForAbstractClass();

        $mock->expects($this->once())
            ->method('setCollector')
            ->with($collectorMock);

        $reflected = new \ReflectionClass('\BramR\TorExits\Parser\BaseParser');
        $reflected->getConstructor()->invoke($mock, $collectorMock);
    }
}
