# TorExits

A simple library to retreive a convenient list of current [Tor](https://torproject.org) exit nodes/relays.

## Installation

The easiest way to install tor-exits is with [Composer](https://getcomposer.org).

```
composer require bramr/tor-exits
```

## Usage

```php
$clientIp = '127.0.0.1';

//simplest example using tor project exit node list
$parser = new TorProjectParser(new TorProjectCollector());
$ipList = IpList::fromParserWithCache($parser, new IpListJsonCache());

if ($ipList->contains($clientIp)) {
    echo 'client uses Tor.';
}
```

There is a lot more to configure, for more complex examples see: example.php

## Important

Please don't use this code to outright block users coming from the Tor network.  
There are perfectly valid reasons to use Tor. (and better ways to block all Tor traffic to your site)


Also never refresh the list more than once an hour.  
The consensus document is only updated hourly, so fetching an exit node list more often is pointless.


I don't use this code in production myself. I use most of it for a personal project.  
If you're running into problems feel free to contact me. I don't mind helping out.

## License

MIT, for details see LICENSE file.
