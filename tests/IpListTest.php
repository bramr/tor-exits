<?php

namespace BramR\TorExits;

use GuzzleHttp\Psr7;

class IpListTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider arrayProvider
     */
    public function testAddingAndToArray($input, $expected)
    {
        $ipList = new IpList();

        $ipList->add($input);

        $this->assertEquals($expected, $ipList->toArray());
    }

    public function testAddSingleIp()
    {
        $ipList = new IpList();

        $ipList->add('131.21.111.100');
        $ipList->add('131.121.111.100');

        $this->assertEquals(['131.21.111.100','131.121.111.100'], $ipList->toArray());
    }

    public function testAddDoesNotAddDuplicates()
    {
        $ipList = new IpList(['131.21.111.100']);

        $ipList->add(['131.21.111.100','131.21.111.100']);

        $this->assertEquals(['131.21.111.100'], $ipList->toArray());
    }

    public function testAddingDoesNotClear()
    {
        $testData = $this->arrayProvider();
        $ipList = new IpList();

        $ipList->add($testData[0][0]);
        $ipList->add($testData[1][0]);

        $expected = array_merge($testData[0][1], $testData[1][1]);
        //Should contain all the same values
        $this->assertEquals([], array_diff($expected, $ipList->toArray()));
    }

    public function testRemoveSingleAddress()
    {
        $testdata = $this->arrayProvider()[0][0];
        $ipList = new IpList($testdata);

        $ipList->remove($testdata[0]);
        $this->assertEquals(array_slice($testdata, 1), $ipList->toArray());
    }

    public function testRemoveMultipleAddresses()
    {
        $testdata = $this->arrayProvider();
        $ipList = new IpList(array_merge($testdata[0][0], $testdata[1][0]));

        $ipList->remove($testdata[0][1]);

        $this->assertEquals($testdata[1][1], $ipList->toArray());
    }

    public function testContains()
    {
        $testData = $this->arrayProvider()[0][0];
        $ipList = new IpList($testData);

        $this->assertTrue($ipList->contains('13.34.66.66'));
        $this->assertTrue($ipList->contains('111.111.111.111'));
        $this->assertFalse($ipList->contains('111.111.111.11'));
        $this->assertFalse($ipList->contains('1.2.3.4'));
    }

    public function testNotContains()
    {
        $testData = $this->arrayProvider()[0][0];
        $ipList = new IpList($testData);

        $this->assertTrue($ipList->doesNotContain('12.23.27.23'));
        $this->assertTrue($ipList->doesNotContain('google.com'));
        $this->assertFalse($ipList->doesNotContain('13.34.66.66'));
    }

    public function testEmpty()
    {
        $ipList = new IpList();

        $this->assertTrue($ipList->isEmpty());
    }

    public function testSort()
    {
        $sorted = $this->arrayProvider()[0][0];
        $testData = array($sorted[3], $sorted[0], $sorted[1], $sorted[2]);
        $expected = $this->arrayProvider()[0][1];

        $ipList = (new IpList($testData))->sort();
        $this->assertEquals($expected, $ipList->toArray());
    }

    public function testClear()
    {
        $testData = $this->arrayProvider()[0][0];
        $ipList = new IpList($testData);

        $ipList->clear();

        $this->assertTrue($ipList->isEmpty());
        $this->assertEquals([], $ipList->toArray());
    }

    public function testCountable()
    {
        $testData = $this->arrayProvider()[0][0];
        $ipList = new IpList($testData);

        $this->assertEquals(count($testData), count($ipList));
    }

    public function testDoesNotIterateOverEmptyArray()
    {
        $ipList = new IpList();

        foreach ($ipList as $ip) {
            $this->fail();
        }
    }

    public function testIteratesCorrectly()
    {
        $testData = $this->arrayProvider()[0][0];
        $ipList = new IpList($testData);

        $i = 0;
        foreach ($ipList as $key => $ip) {
            $this->assertEquals($i, $key);
            $this->assertEquals($testData[$i], $ip);
            $i++;
        }
        $this->assertEquals(count($testData), $i);
    }

    public function testJsonSerializable()
    {
        $testData = $this->arrayProvider()[0][0];
        $ipList = new IpList($testData);

        $this->assertJsonStringEqualsJsonString(
            json_encode($testData),
            json_encode($ipList)
        );
    }

    public function testSerialization()
    {
        $testData = $this->arrayProvider()[0][0];
        $ipList = new IpList($testData);

        $listSerialized = $ipList->serialize();
        $serializedIpList = new IpList();
        $serializedIpList->unserialize($listSerialized);

        $this->assertTrue(is_string($listSerialized));
        $this->assertEquals($ipList->toArray(), $serializedIpList->toArray());
    }

    public function testFromParserFactory()
    {
        $stubReturn = new IpList(['127.0.0.1']);
        $parserStub = $this->createMock('BramR\TorExits\Parser\ParserInterface');
        $parserStub->method('parse')
            ->willReturn($stubReturn);

        $ipList = IpList::fromParser($parserStub);
        $this->assertSame($stubReturn, $ipList);
    }

    public function testFromParserWithCacheHitFactory()
    {
        $cacheReturn = new IpList(['192.168.1.1']);
        $cacheStub = $this->createMock('BramR\TorExits\Cache\IpListCacheInterface');
        $cacheStub->method('fetch')->willReturn($cacheReturn);

        $parserStub = $this->createMock('BramR\TorExits\Parser\ParserInterface');
        $parserStub->method('parse')->willReturn(new IpList(['127.0.0.1']));

        $ipList = IpList::fromParserWithCache($parserStub, $cacheStub);
        $this->assertSame($cacheReturn, $ipList);
    }

    public function testFromParserWithCacheMissFactory()
    {
        $cacheStub = $this->createMock('BramR\TorExits\Cache\IpListCacheInterface');
        $cacheStub->method('fetch')->willReturn(null);
        $cacheStub->method('store')->willReturn(true);

        $parserReturn = new IpList(['127.0.0.1']);
        $parserStub = $this->createMock('BramR\TorExits\Parser\ParserInterface');
        $parserStub->method('parse')->willReturn($parserReturn);

        $ipList = IpList::fromParserWithCache($parserStub, $cacheStub);
        $this->assertSame($parserReturn, $ipList);
    }

    /**
     * @expectedException RuntimeException
     **/
    public function testFromParserWithFailingCache()
    {
        $cacheStub = $this->createMock('BramR\TorExits\Cache\IpListCacheInterface');
        $cacheStub->method('fetch')->willReturn(null);
        $cacheStub->method('store')->willReturn(false);

        $parserReturn = new IpList(['127.0.0.1']);
        $parserStub = $this->createMock('BramR\TorExits\Parser\ParserInterface');
        $parserStub->method('parse')->willReturn($parserReturn);

        $ipList = IpList::fromParserWithCache($parserStub, $cacheStub);
    }

    public function arrayProvider()
    {
        return [
            [
                ['12.23.27.22', '13.34.66.66', '111.111.111.111','212.52.33.101'],
                ['12.23.27.22', '13.34.66.66', '111.111.111.111','212.52.33.101']
            ],
            [
                ['123.45.27.22', '256.12.33.55', '12.22.20.45', '12.122.253.179', 'yolobrew.com'],
                ['123.45.27.22','12.22.20.45', '12.122.253.179']
            ],
            [
                ['123.101.27.22', '12.101.20.45', '123.111.253.179'],
                ['123.101.27.22', '12.101.20.45', '123.111.253.179']
            ],
            [
                [],
                []
            ]
        ];
    }
}
