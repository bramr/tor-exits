<?php

namespace BramR\TorExits\Cache;

use BramR\TorExits\IpList;
use phpmock\spy\Spy;
use phpmock\MockBuilder;

class IpListJsonCacheTest extends \PHPUnit_Framework_TestCase
{
    public function testOverwriteLocation()
    {
        $cache = (new IpListJsonCache())
            ->setLocation('/tmp/unittest');

        $this->assertEquals('/tmp/unittest', $cache->getLocation());
    }

    public function testOverwriteTtl()
    {
        $cache = (new IpListJsonCache())
            ->setTtl(36000);

        $this->assertEquals(36000, $cache->getTtl());
    }

    public function testInvalidTtl()
    {
        $this->expectException(\InvalidArgumentException::class);

        $cache = (new IpListJsonCache())
            ->setTtl('a very long time');
    }

    public function testFetchInvalidLocation()
    {
        $cache = (new IpListJsonCache())
            ->setLocation('./this.does.not.exist');

        $ipList = $cache->fetch();

        $this->assertNull($ipList);
    }

    public function testInvalidFileFormat()
    {
        $cache = (new IpListJsonCache())
            ->setLocation(__DIR__.'/../fixtures/exit-addresses');

        $ipList = $cache->fetch();

        $this->assertNull($ipList);
    }

    public function testExpiredCache()
    {
        $cache = (new IpListJsonCache())
            ->setLocation(__DIR__.'/../fixtures/expired-cache.json');
        $ipList = $cache->fetch();

        $this->assertNull($ipList);
    }

    public function testValidCache()
    {
        $data = json_decode(file_get_contents(__DIR__.'/../fixtures/expired-cache.json'));
        $data->expires = (new \DateTime('+ 10 minutes'))->format('c'); //unexpire cache
        file_put_contents(__DIR__.'/../../build/valid-cache.json', json_encode($data));

        $cache = (new IpListJsonCache())
            ->setLocation(__DIR__.'/../../build/valid-cache.json');
        $ipList = $cache->fetch();

        $this->assertEquals($this->getTestIpList()->toArray(), $ipList->toArray());
    }

    public function testStore()
    {
        //setup spy & mock
        $isWritableMock = $this->mockPhpFunc('is_writable', true);
        $putContentsSpy = $this->spyPhpFunc('file_put_contents');
        $isWritableMock->enable();
        $putContentsSpy->enable();

        $ipList = $this->getTestIpList();
        $cache = new IpListJsonCache();
        $cache->setLocation('/tmp/place.json');

        //test
        $cache->store($ipList);

        //disable mocks
        $isWritableMock->disable();
        $putContentsSpy->disable();

        $spyInvoked= $putContentsSpy->getInvocations();
        $isInvokedOnce = isset($spyInvoked[0])  && ! isset($spyInvoked[1]);
        $this->assertTrue($isInvokedOnce);
        $this->assertEquals('/tmp/place.json', $spyInvoked[0]->getArguments()[0]);
        $this->assertContains(json_encode($ipList), $spyInvoked[0]->getArguments()[1]);
    }

    public function testStoreUnwritableLocation()
    {
        $isWritableMock = $this->mockPhpFunc('is_writable', false);
        $isWritableMock->enable();

        $ipList = $this->getTestIpList();
        $cache = new IpListJsonCache();

        //test
        $result = $cache->store($ipList);
        $isWritableMock->disable();

        $this->assertFalse($result);
    }

    public function testStoreWithInvalidTtl()
    {
        $this->expectException(\InvalidArgumentException::class);

        $ipList = $this->getTestIpList();
        $cache = new IpListJsonCache();
        $cache->store($ipList, 'a very long time');
    }

    public function mockPhpFunc($function, $result)
    {
        $builder = new MockBuilder();
        $builder->setNameSpace(__NAMESPACE__)
            ->setName($function)
            ->setFunction(function () use ($result) {
                return $result;
            });
        return $builder->build();
    }

    public function spyPhpFunc($function)
    {
        return new Spy(__NAMESPACE__, $function);
    }

    public function getTestIpList()
    {
        return new IpList([
            '23.45.67.89',
            '42.48.167.139',
            '57.222.22.151',
            '58.137.214.251',
            '99.12.2.15'
        ]);
    }
}
