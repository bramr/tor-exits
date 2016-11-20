<?php
require './vendor/autoload.php';

use BramR\TorExits\IpList;
use BramR\TorExits\Cache\IpListJsonCache;
use BramR\TorExits\Collector\BlutMagieCollector;
use BramR\TorExits\Collector\TorProjectCollector;
use BramR\TorExits\Parser\BlutMagieParser;
use BramR\TorExits\Parser\TorProjectParser;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$clientIp = '192.42.116.16';

$logger = new Logger('tor-exits');
$logger->pushHandler(new StreamHandler('/tmp/tor-exits.log', Logger::INFO));

//$collector = new BlutMagieCollector();
$collector = new TorProjectCollector();
$collector->setLogger($logger);

//$parser = new BlutMagieParser($collector);
$parser = new TorProjectParser($collector);
$parser->setLogger($logger);
try {
//more real world example
    $ipList = IpList::fromParserWithCache($parser, new IpListJsonCache());
    $somewhereInRequest = ['uses-tor' => $ipList && $ipList->contains($clientIp)];
} catch (Exception $e) {
    //Uses tor not available, do some additional error handeling if you like
    $somewhereInRequest = ['uses-tor' => null];
}
var_dump($somewhereInRequest);
