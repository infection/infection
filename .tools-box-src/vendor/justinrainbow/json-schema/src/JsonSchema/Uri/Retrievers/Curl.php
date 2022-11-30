<?php

namespace _HumbugBoxb47773b41c19\JsonSchema\Uri\Retrievers;

use _HumbugBoxb47773b41c19\JsonSchema\Exception\RuntimeException;
use _HumbugBoxb47773b41c19\JsonSchema\Validator;
class Curl extends AbstractRetriever
{
    protected $messageBody;
    public function __construct()
    {
        if (!\function_exists('curl_init')) {
            throw new RuntimeException('cURL not installed');
        }
    }
    public function retrieve($uri)
    {
        $ch = \curl_init();
        \curl_setopt($ch, \CURLOPT_URL, $uri);
        \curl_setopt($ch, \CURLOPT_HEADER, \true);
        \curl_setopt($ch, \CURLOPT_RETURNTRANSFER, \true);
        \curl_setopt($ch, \CURLOPT_HTTPHEADER, array('Accept: ' . Validator::SCHEMA_MEDIA_TYPE));
        $response = \curl_exec($ch);
        if (\false === $response) {
            throw new \_HumbugBoxb47773b41c19\JsonSchema\Exception\ResourceNotFoundException('JSON schema not found');
        }
        $this->fetchMessageBody($response);
        $this->fetchContentType($response);
        \curl_close($ch);
        return $this->messageBody;
    }
    private function fetchMessageBody($response)
    {
        \preg_match("/(?:\r\n){2}(.*)\$/ms", $response, $match);
        $this->messageBody = $match[1];
    }
    protected function fetchContentType($response)
    {
        if (0 < \preg_match("/Content-Type:(\\V*)/ims", $response, $match)) {
            $this->contentType = \trim($match[1]);
            return \true;
        }
        return \false;
    }
}
