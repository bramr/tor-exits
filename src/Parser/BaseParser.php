<?php

namespace BramR\TorExits\Parser;

use BramR\TorExits\Collector\CollectorInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use Psr\Http\Message\StreamInterface;

abstract class BaseParser implements ParserInterface
{
    use LoggerAwareTrait;

    protected $collector;
    protected $parseWarningThreshold;

    const DEFAULT_PARSE_WARNING_THRESHOLD = 500;

    public function __construct(CollectorInterface $collector = null)
    {
        $this->setLogger(new NullLogger());
        if ($collector) {
            $this->setCollector($collector);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setCollector(CollectorInterface $collector)
    {
        $this->collector = $collector;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCollector()
    {
        return $this->collector;
    }

    /**
     * Getter for parseWarningThreshold
     *
     * return string
     */
    public function getParseWarningThreshold()
    {
        return is_null($this->parseWarningThreshold) ?
            self::DEFAULT_PARSE_WARNING_THRESHOLD :
            $this->parseWarningThreshold;
    }

    /**
     * Setter for parseWarningThreshold
     *
     * @param int $parseWarningThreshold
     * @return BaseParser
     */
    public function setParseWarningThreshold($parseWarningThreshold)
    {
        $this->parseWarningThreshold = (int) $parseWarningThreshold;
        return $this;
    }


    /**
     * Return data stream, either from passed in stream or from the collector.
     *
     * @throws Exception if no StreamInterface can be retrurned
     *
     * @param StreamInterface $data = null
     * @return StreamInterface
     */
    protected function getStreamData(StreamInterface $data = null)
    {
        if (! is_null($data)) {
            return $data;
        }
        if ($this->getCollector()) {
            try {
                return $this->getCollector()->fetch();
            } catch (\Exception $e) {
                $this->logger->error('Failed getting data from collector: ' . $e->getMessage());
                throw $e;
            }
        } else {
            $this->logger->error('Cannot find stream to parse');
            throw new \InvalidArgumentException('Cannot find stream to parse.');
        }
    }


    /**
     * {@inheritdoc}
     */
    abstract public function parse(StreamInterface $data = null);
}
