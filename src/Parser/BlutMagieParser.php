<?php

namespace BramR\TorExits\Parser;

use BramR\TorExits\IpList;
use Psr\Http\Message\StreamInterface;

/**
 * Class BlutMagieParser
 */
class BlutMagieParser extends BaseParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(StreamInterface $data = null)
    {
        $ips = explode("\n", $this->getStreamData($data)->getContents());
        $ips = array_filter($ips, 'ip2long');
        if (count($ips) < $this->getParseWarningThreshold()) {
            $this->logger->warning(
                'Number of Tor exit nodes found when parsing is low.',
                ['exit-node-count' => count($ips)]
            );
        } else {
            $this->logger->info(
                'Tor exit nodes parsed from blutmagie.de',
                ['exit-node-count' => count($ips)]
            );
        }
        return new IpList($ips);
    }
}
