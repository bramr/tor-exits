<?php

namespace BramR\TorExits\Parser;

use GuzzleHttp\Psr7;

class TorProjectIpFilterTest extends \PHPUnit_Framework_TestCase
{
    public function testFilterIntegration()
    {
        stream_filter_register("torporject", "BramR\\TorExits\\Parser\\TorProjectIpFilter");
        $resource = $this->getFixtureStream();
        stream_filter_prepend($resource, 'torporject');

        $result = stream_get_contents($resource);

        //Check if result contains only valid ipv4 addresses seperated by commas
        $this->assertContainsOnly('int', array_map('ip2long', explode(',', $result)));
        $this->assertEquals(113, substr_count($result, ','));
        $this->assertTrue(strpos($result, '185.86.148.27') !== false);
    }

    protected function getFixtureStream()
    {
        return fopen(__DIR__.'/../fixtures/exit-addresses', 'r+');
    }
}
