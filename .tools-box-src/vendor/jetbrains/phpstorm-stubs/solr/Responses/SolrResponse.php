<?php

namespace _HumbugBoxb47773b41c19;

abstract class SolrResponse
{
    public const PARSE_SOLR_OBJ = 0;
    public const PARSE_SOLR_DOC = 1;
    protected $http_status;
    protected $parser_mode;
    protected $success;
    protected $http_status_message;
    protected $http_request_url;
    protected $http_raw_request_headers;
    protected $http_raw_request;
    protected $http_raw_response_headers;
    protected $http_raw_response;
    protected $http_digested_response;
    public function getDigestedResponse()
    {
    }
    public function getHttpStatus()
    {
    }
    public function getHttpStatusMessage()
    {
    }
    public function getRawRequest()
    {
    }
    public function getRawRequestHeaders()
    {
    }
    public function getRawResponse()
    {
    }
    public function getRawResponseHeaders()
    {
    }
    public function getRequestUrl()
    {
    }
    public function getResponse()
    {
    }
    public function setParseMode($parser_mode = 0)
    {
    }
    public function success()
    {
    }
}
\class_alias('_HumbugBoxb47773b41c19\\SolrResponse', 'SolrResponse', \false);
