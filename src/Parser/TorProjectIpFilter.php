<?php

namespace BramR\TorExits\Parser;

class TorProjectIpFilter extends \php_user_filter
{
    protected $first = true;
    protected $remainder;

    /**
     * {@inheritdoc}
     */
    public function filter($in, $out, &$consumed, $closing)
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket  = $this->processBucket($bucket);
            $consumed = $bucket->datalen;
            stream_bucket_prepend($out, $bucket);
        }
        return PSFS_PASS_ON;
    }

    /**
     * processBucket
     *
     * @param $bucket php_user_filter bucket object
     * @return modified bucket
     */
    protected function processBucket($bucket)
    {
        $bucketlines = explode("\n", $this->remainder . $bucket->data);
        $bucket->data = '';
        //skip last line, which is most likely cut off by bucket size
        for ($i = 0; $i < count($bucketlines) - 1; $i++) {
            $bucket->data .= $this->processLine($bucketlines[$i]);
        }
        $this->remainder = $bucketlines[$i];
        return $bucket;
    }


    /**
     * processLine from tor project exit address list
     *
     * @param string $line
     * @return string
     */
    protected function processLine($line)
    {
        if (preg_match("/ExitAddress\s(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/", $line, $out)) {
            if ($this->first) {
                $this->first = false;
                return $out[1];
            } else {
                return ',' . $out[1];
            }
        }
        return '';
    }
}
