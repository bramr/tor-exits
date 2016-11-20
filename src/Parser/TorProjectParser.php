<?php

namespace BramR\TorExits\Parser;

use BramR\TorExits\IpList;
use GuzzleHttp\Psr7\StreamWrapper;
use Psr\Http\Message\StreamInterface;

/**
 * Class TorProjectParser
 */
class TorProjectParser extends BaseParser
{
    /**
     * {@inheritdoc}
     */
    public function parse(StreamInterface $data = null)
    {
        stream_filter_register('torporject', 'BramR\TorExits\Parser\TorProjectIpFilter');
        $resource = StreamWrapper::getResource($this->getStreamData($data));
        stream_filter_prepend($resource, 'torporject');
        $contents = stream_get_contents($resource);

        $ips = $contents ? explode(',', $contents) : [];
        if (count($ips) < $this->getParseWarningThreshold()) {
            $this->logger->warning(
                'Number of Tor exit nodes found when parsing is low.',
                ['exit-node-count' => count($ips)]
            );
        } else {
            $this->logger->info(
                'Tor exit-nodes parsed from Tor project.',
                ['exit-node-count' => count($ips)]
            );
        }
        return new IpList($ips);
    }
}
