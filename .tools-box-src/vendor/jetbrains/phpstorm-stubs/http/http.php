<?php



use JetBrains\PhpStorm\Pure;

class HttpException extends Exception
{
public $innerException;
}
class HttpRuntimeException extends HttpException {}
class HttpInvalidParamException extends HttpException {}
class HttpHeaderException extends HttpException {}
class HttpMalformedHeadersException extends HttpException {}
class HttpRequestMethodException extends HttpException {}
class HttpMessageTypeException extends HttpException {}
class HttpEncodingException extends HttpException {}
class HttpRequestException extends HttpException {}
class HttpRequestPoolException extends HttpException {}
class HttpSocketException extends HttpException {}
class HttpResponseException extends HttpException {}
class HttpUrlException extends HttpException {}
class HttpQueryStringException extends HttpException {}




class HttpDeflateStream
{
public const TYPE_GZIP = 16;
public const TYPE_ZLIB = 0;
public const TYPE_RAW = 32;
public const LEVEL_DEF = 0;
public const LEVEL_MIN = 1;
public const LEVEL_MAX = 9;
public const STRATEGY_DEF = 0;
public const STRATEGY_FILT = 256;
public const STRATEGY_HUFF = 512;
public const STRATEGY_RLE = 768;
public const STRATEGY_FIXED = 1024;
public const FLUSH_NONE = 0;
public const FLUSH_SYNC = 1048576;
public const FLUSH_FULL = 2097152;









public function __construct($flags = null) {}










public function update($data) {}










public function flush($data = null) {}










public function finish($data = null) {}













public static function factory($flags = null, $class_name = null) {}
}




class HttpInflateStream
{
public const FLUSH_NONE = 0;
public const FLUSH_SYNC = 1048576;
public const FLUSH_FULL = 2097152;









public function __construct($flags = null) {}










public function update($data) {}










public function flush($data = null) {}










public function finish($data = null) {}













public static function factory($flags = null, $class_name = null) {}
}




class HttpMessage implements Countable, Serializable, Iterator
{
public const TYPE_NONE = 0;
public const TYPE_REQUEST = 1;
public const TYPE_RESPONSE = 2;
protected $type;
protected $body;
protected $requestMethod;
protected $requestUrl;
protected $responseStatus;
protected $responseCode;
protected $httpVersion;
protected $headers;
protected $parentMessage;









public function __construct($message = null) {}







#[Pure]
public function getBody() {}










public function setBody($body) {}










#[Pure]
public function getHeader($header) {}







#[Pure]
public function getHeaders() {}










public function setHeaders(array $header) {}















public function addHeaders(array $headers, $append = null) {}







#[Pure]
public function getType() {}










public function setType($type) {}

#[Pure]
public function getInfo() {}




public function setInfo($http_info) {}







#[Pure]
public function getResponseCode() {}











public function setResponseCode($code) {}








#[Pure]
public function getResponseStatus() {}











public function setResponseStatus($status) {}








#[Pure]
public function getRequestMethod() {}











public function setRequestMethod($method) {}








#[Pure]
public function getRequestUrl() {}











public function setRequestUrl($url) {}







#[Pure]
public function getHttpVersion() {}










public function setHttpVersion($version) {}













public function guessContentType($magic_file, $magic_mode = null) {}







#[Pure]
public function getParentMessage() {}







public function send() {}










public function toString($include_parent = null) {}







public function toMessageTypeObject() {}

public function count() {}

public function serialize() {}




public function unserialize($serialized) {}

public function rewind() {}

public function valid() {}

public function current() {}

public function key() {}

public function next() {}




public function __toString() {}













public static function factory($raw_message = null, $class_name = null) {}













public static function fromString($raw_message = null, $class_name = null) {}













public static function fromEnv($message_type, $class_name = null) {}







public function detach() {}













public function prepend(HttpMessage $message, $top = null) {}







public function reverse() {}
}




class HttpQueryString implements Serializable, ArrayAccess
{
public const TYPE_BOOL = 3;
public const TYPE_INT = 1;
public const TYPE_FLOAT = 2;
public const TYPE_STRING = 6;
public const TYPE_ARRAY = 4;
public const TYPE_OBJECT = 5;
private static $instance;
private $queryArray;
private $queryString;













final public function __construct($global = null, $add = null) {}







public function toArray() {}







public function toString() {}




public function __toString() {}



















#[Pure]
public function get($key = null, $type = null, $defval = null, $delete = null) {}










public function set($params) {}










public function mod($params) {}






#[Pure]
public function getBool($name, $defval, $delete) {}






#[Pure]
public function getInt($name, $defval, $delete) {}






#[Pure]
public function getFloat($name, $defval, $delete) {}






#[Pure]
public function getString($name, $defval, $delete) {}






#[Pure]
public function getArray($name, $defval, $delete) {}






#[Pure]
public function getObject($name, $defval, $delete) {}






public static function factory($global, $params, $class_name) {}











public static function singleton($global = null) {}













public function xlate($ie, $oe) {}







public function serialize() {}










public function offsetGet($offset) {}










public function unserialize($serialized) {}













public function offsetExists($offset) {}













public function offsetSet($offset, $value) {}










public function offsetUnset($offset) {}
}




class HttpRequest
{
public const METH_GET = 1;
public const METH_HEAD = 2;
public const METH_POST = 3;
public const METH_PUT = 4;
public const METH_DELETE = 5;
public const METH_OPTIONS = 6;
public const METH_TRACE = 7;
public const METH_CONNECT = 8;
public const METH_PROPFIND = 9;
public const METH_PROPPATCH = 10;
public const METH_MKCOL = 11;
public const METH_COPY = 12;
public const METH_MOVE = 13;
public const METH_LOCK = 14;
public const METH_UNLOCK = 15;
public const METH_VERSION_CONTROL = 16;
public const METH_REPORT = 17;
public const METH_CHECKOUT = 18;
public const METH_CHECKIN = 19;
public const METH_UNCHECKOUT = 20;
public const METH_MKWORKSPACE = 21;
public const METH_UPDATE = 22;
public const METH_LABEL = 23;
public const METH_MERGE = 24;
public const METH_BASELINE_CONTROL = 25;
public const METH_MKACTIVITY = 26;
public const METH_ACL = 27;
public const VERSION_1_0 = 1;
public const VERSION_1_1 = 2;
public const VERSION_NONE = 0;
public const VERSION_ANY = 0;
public const SSL_VERSION_TLSv1 = 1;
public const SSL_VERSION_SSLv2 = 2;
public const SSL_VERSION_SSLv3 = 3;
public const SSL_VERSION_ANY = 0;
public const IPRESOLVE_V4 = 1;
public const IPRESOLVE_V6 = 2;
public const IPRESOLVE_ANY = 0;
public const AUTH_BASIC = 1;
public const AUTH_DIGEST = 2;
public const AUTH_NTLM = 8;
public const AUTH_GSSNEG = 4;
public const AUTH_ANY = -1;
public const PROXY_SOCKS4 = 4;
public const PROXY_SOCKS5 = 5;
public const PROXY_HTTP = 0;
private $options;
private $postFields;
private $postFiles;
private $responseInfo;
private $responseMessage;
private $responseCode;
private $responseStatus;
private $method;
private $url;
private $contentType;
private $requestBody;
private $queryData;
private $putFile;
private $putData;
private $history;
public $recordHistory;















public function __construct($url = null, $request_method = null, ?array $options = null) {}












public function setOptions(?array $options = null) {}







#[Pure]
public function getOptions() {}











public function setSslOptions(?array $options = null) {}







#[Pure]
public function getSslOptions() {}










public function addSslOptions(array $option) {}










public function addHeaders(array $headers) {}







#[Pure]
public function getHeaders() {}











public function setHeaders(?array $headers = null) {}










public function addCookies(array $cookies) {}







#[Pure]
public function getCookies() {}











public function setCookies(?array $cookies = null) {}







public function enableCookies() {}










public function resetCookies($session_only = null) {}

public function flushCookies() {}










public function setMethod($request_method) {}







#[Pure]
public function getMethod() {}










public function setUrl($url) {}







#[Pure]
public function getUrl() {}











public function setContentType($content_type) {}







#[Pure]
public function getContentType() {}












public function setQueryData($query_data) {}







#[Pure]
public function getQueryData() {}










public function addQueryData(array $query_params) {}











public function setPostFields(array $post_data) {}







#[Pure]
public function getPostFields() {}










public function addPostFields(array $post_data) {}




public function setBody($request_body_data) {}

#[Pure]
public function getBody() {}




public function addBody($request_body_data) {}










public function setRawPostData($raw_post_data = null) {}







#[Pure]
public function getRawPostData() {}










public function addRawPostData($raw_post_data) {}











public function setPostFiles(array $post_files) {}

















public function addPostFile($name, $file, $content_type = null) {}







#[Pure]
public function getPostFiles() {}











public function setPutFile($file = null) {}







#[Pure]
public function getPutFile() {}










public function setPutData($put_data = null) {}







#[Pure]
public function getPutData() {}










public function addPutData($put_data) {}







public function send() {}









#[Pure]
public function getResponseData() {}











#[Pure]
public function getResponseHeader($name = null) {}













#[Pure]
public function getResponseCookies($flags = null, ?array $allowed_extras = null) {}







#[Pure]
public function getResponseCode() {}







#[Pure]
public function getResponseStatus() {}







#[Pure]
public function getResponseBody() {}













#[Pure]
public function getResponseInfo($name = null) {}







#[Pure]
public function getResponseMessage() {}







#[Pure]
public function getRawResponseMessage() {}







#[Pure]
public function getRequestMessage() {}







#[Pure]
public function getRawRequestMessage() {}







#[Pure]
public function getHistory() {}







public function clearHistory() {}







public static function factory($url, $method, $options, $class_name) {}






public static function get($url, $options, &$info) {}






public static function head($url, $options, &$info) {}







public static function postData($url, $data, $options, &$info) {}







public static function postFields($url, $data, $options, &$info) {}







public static function putData($url, $data, $options, &$info) {}







public static function putFile($url, $file, $options, &$info) {}







public static function putStream($url, $stream, $options, &$info) {}




public static function methodRegister($method_name) {}




public static function methodUnregister($method) {}




public static function methodName($method_id) {}




public static function methodExists($method) {}





public static function encodeBody($fields, $files) {}
}

class HttpRequestDataShare implements Countable
{
private static $instance;
public $cookie;
public $dns;
public $ssl;
public $connect;

public function __destruct() {}

public function count() {}




public function attach(HttpRequest $request) {}




public function detach(HttpRequest $request) {}

public function reset() {}





public static function factory($global, $class_name) {}




public static function singleton($global) {}
}




class HttpRequestPool implements Countable, Iterator
{








public function __construct(?HttpRequest $request = null) {}







public function __destruct() {}










public function attach(HttpRequest $request) {}










public function detach(HttpRequest $request) {}







public function send() {}







public function reset() {}







protected function socketPerform() {}







protected function socketSelect() {}

public function valid() {}

public function current() {}

public function key() {}

public function next() {}

public function rewind() {}

public function count() {}







#[Pure]
public function getAttachedRequests() {}







#[Pure]
public function getFinishedRequests() {}




public function enablePipelining($enable) {}




public function enableEvents($enable) {}
}




class HttpResponse
{
public const REDIRECT = 0;
public const REDIRECT_PERM = 301;
public const REDIRECT_FOUND = 302;
public const REDIRECT_POST = 303;
public const REDIRECT_PROXY = 305;
public const REDIRECT_TEMP = 307;
private static $sent;
private static $catch;
private static $mode;
private static $stream;
private static $file;
private static $data;
protected static $cache;
protected static $gzip;
protected static $eTag;
protected static $lastModified;
protected static $cacheControl;
protected static $contentType;
protected static $contentDisposition;
protected static $bufferSize;
protected static $throttleDelay;

















public static function setHeader($name, $value = null, $replace = null) {}












public static function getHeader($name = null) {}










public static function setETag($etag) {}







public static function getETag() {}










public static function setLastModified($timestamp) {}







public static function getLastModified() {}














public static function setContentDisposition($filename, $inline = null) {}







public static function getContentDisposition() {}











public static function setContentType($content_type) {}







public static function getContentType() {}













public static function guessContentType($magic_file, $magic_mode = null) {}










public static function setCache($cache) {}







public static function getCache() {}
















public static function setCacheControl($control, $max_age = null, $must_revalidate = null) {}







public static function getCacheControl() {}










public static function setGzip($gzip) {}







public static function getGzip() {}










public static function setThrottleDelay($seconds) {}







public static function getThrottleDelay() {}










public static function setBufferSize($bytes) {}







public static function getBufferSize() {}










public static function setData($data) {}







public static function getData() {}










public static function setFile($file) {}







public static function getFile() {}










public static function setStream($stream) {}







public static function getStream() {}










public static function send($clean_ob = null) {}







public static function capture() {}











public static function redirect($url = null, ?array $params = null, $session = null, $status = null) {}








public static function status($status) {}







public static function getRequestHeaders() {}







public static function getRequestBody() {}







public static function getRequestBodyStream() {}
}

class HttpUtil
{



public static function date($timestamp) {}







public static function buildUrl($url, $parts, $flags, &$composed) {}






public static function buildStr($query, $prefix, $arg_sep) {}





public static function negotiateLanguage($supported, &$result) {}





public static function negotiateCharset($supported, &$result) {}





public static function negotiateContentType($supported, &$result) {}





public static function matchModified($last_modified, $for_range) {}





public static function matchEtag($plain_etag, $for_range) {}






public static function matchRequestHeader($header_name, $header_value, $case_sensitive) {}




public static function parseMessage($message_string) {}




public static function parseHeaders($headers_string) {}




public static function parseCookie($cookie_string) {}




public static function buildCookie($cookie_array) {}





public static function parseParams($param_string, $flags) {}




public static function chunkedDecode($encoded_string) {}





public static function deflate($plain, $flags) {}




public static function inflate($encoded) {}




public static function support($feature) {}
}










#[Pure]
function http_date($timestamp = null) {}




















function http_build_url($url = null, $parts = null, $flags = null, ?array &$new_url = null) {}
















#[Pure]
function http_build_str(array $query, $prefix = null, $arg_separator = null) {}













function http_negotiate_language(array $supported, ?array &$result = null) {}













function http_negotiate_charset(array $supported, ?array &$result = null) {}













function http_negotiate_content_type(array $supported, ?array &$result = null) {}



















function http_redirect($url = null, ?array $params = null, $session = null, $status = null) {}













function http_throttle($sec = null, $bytes = null) {}










function http_send_status($status) {}











function http_send_last_modified($timestamp = null) {}










function http_send_content_type($content_type = null) {}














function http_send_content_disposition($filename, $inline = null) {}













#[Pure]
function http_match_modified($timestamp = null, $for_range = null) {}













#[Pure]
function http_match_etag($etag, $for_range = null) {}











#[Pure]
function http_cache_last_modified($timestamp_or_expires = null) {}











#[Pure]
function http_cache_etag($etag = null) {}










function http_send_data($data) {}










function http_send_file($file) {}










function http_send_stream($stream) {}










#[Pure]
function http_chunked_decode($encoded) {}










#[Pure]
function http_parse_message($message) {}










#[Pure]
function http_parse_headers($header) {}

















#[Pure]
function http_parse_cookie($cookie, $flags = null, ?array $allowed_extras = null) {}










#[Pure]
function http_build_cookie(array $cookie) {}













#[Pure]
function http_parse_params($param, $flags = null) {}







#[Pure]
function http_get_request_headers() {}







#[Pure]
function http_get_request_body() {}







#[Pure]
function http_get_request_body_stream() {}
















#[Pure]
function http_match_request_header($header, $value, $match_case = null) {}







function http_persistent_handles_count() {}








function http_persistent_handles_clean($ident = null) {}










function http_persistent_handles_ident($ident) {}















function http_get($url, ?array $options = null, ?array &$info = null) {}














function http_head($url = null, ?array $options = null, ?array &$info = null) {}

















function http_post_data($url, $data = null, ?array $options = null, ?array &$info = null) {}




















function http_post_fields($url, ?array $data = null, ?array $files = null, ?array $options = null, ?array &$info = null) {}

















function http_put_data($url, $data = null, ?array $options = null, ?array &$info = null) {}

















function http_put_file($url, $file = null, ?array $options = null, ?array &$info = null) {}

















function http_put_stream($url, $stream = null, ?array $options = null, ?array &$info = null) {}




















function http_request($method, $url = null, $body = null, ?array $options = null, ?array &$info = null) {}













#[Pure]
function http_request_body_encode(array $fields, array $files) {}










function http_request_method_register($method) {}










function http_request_method_unregister($method) {}










#[Pure]
function http_request_method_exists($method) {}










#[Pure]
function http_request_method_name($method) {}









#[Pure]
function ob_etaghandler($data, $mode) {}













#[Pure]
function http_deflate($data, $flags = null) {}










#[Pure]
function http_inflate($data) {}









function ob_deflatehandler($data, $mode) {}









function ob_inflatehandler($data, $mode) {}











#[Pure]
function http_support($feature = null) {}





define('HTTP_COOKIE_PARSE_RAW', 1);





define('HTTP_COOKIE_SECURE', 16);





define('HTTP_COOKIE_HTTPONLY', 32);
define('HTTP_DEFLATE_LEVEL_DEF', 0);
define('HTTP_DEFLATE_LEVEL_MIN', 1);
define('HTTP_DEFLATE_LEVEL_MAX', 9);
define('HTTP_DEFLATE_TYPE_ZLIB', 0);
define('HTTP_DEFLATE_TYPE_GZIP', 16);
define('HTTP_DEFLATE_TYPE_RAW', 32);
define('HTTP_DEFLATE_STRATEGY_DEF', 0);
define('HTTP_DEFLATE_STRATEGY_FILT', 256);
define('HTTP_DEFLATE_STRATEGY_HUFF', 512);
define('HTTP_DEFLATE_STRATEGY_RLE', 768);
define('HTTP_DEFLATE_STRATEGY_FIXED', 1024);





define('HTTP_ENCODING_STREAM_FLUSH_NONE', 0);





define('HTTP_ENCODING_STREAM_FLUSH_SYNC', 1048576);





define('HTTP_ENCODING_STREAM_FLUSH_FULL', 2097152);





define('HTTP_AUTH_BASIC', 1);





define('HTTP_AUTH_DIGEST', 2);





define('HTTP_AUTH_NTLM', 8);





define('HTTP_AUTH_GSSNEG', 4);





define('HTTP_AUTH_ANY', -1);
define('HTTP_VERSION_NONE', 0);





define('HTTP_VERSION_1_0', 1);





define('HTTP_VERSION_1_1', 2);





define('HTTP_VERSION_ANY', 0);





define('HTTP_SSL_VERSION_TLSv1', 1);





define('HTTP_SSL_VERSION_SSLv2', 2);





define('HTTP_SSL_VERSION_SSLv3', 3);





define('HTTP_SSL_VERSION_ANY', 0);





define('HTTP_IPRESOLVE_V4', 1);





define('HTTP_IPRESOLVE_V6', 2);





define('HTTP_IPRESOLVE_ANY', 0);





define('HTTP_PROXY_SOCKS4', 4);





define('HTTP_PROXY_SOCKS5', 5);





define('HTTP_PROXY_HTTP', 0);
define('HTTP_METH_GET', 1);
define('HTTP_METH_HEAD', 2);
define('HTTP_METH_POST', 3);
define('HTTP_METH_PUT', 4);
define('HTTP_METH_DELETE', 5);
define('HTTP_METH_OPTIONS', 6);
define('HTTP_METH_TRACE', 7);
define('HTTP_METH_CONNECT', 8);
define('HTTP_METH_PROPFIND', 9);
define('HTTP_METH_PROPPATCH', 10);
define('HTTP_METH_MKCOL', 11);
define('HTTP_METH_COPY', 12);
define('HTTP_METH_MOVE', 13);
define('HTTP_METH_LOCK', 14);
define('HTTP_METH_UNLOCK', 15);
define('HTTP_METH_VERSION_CONTROL', 16);
define('HTTP_METH_REPORT', 17);
define('HTTP_METH_CHECKOUT', 18);
define('HTTP_METH_CHECKIN', 19);
define('HTTP_METH_UNCHECKOUT', 20);
define('HTTP_METH_MKWORKSPACE', 21);
define('HTTP_METH_UPDATE', 22);
define('HTTP_METH_LABEL', 23);
define('HTTP_METH_MERGE', 24);
define('HTTP_METH_BASELINE_CONTROL', 25);
define('HTTP_METH_MKACTIVITY', 26);
define('HTTP_METH_ACL', 27);





define('HTTP_REDIRECT', 0);





define('HTTP_REDIRECT_PERM', 301);












define('HTTP_REDIRECT_FOUND', 302);





define('HTTP_REDIRECT_POST', 303);





define('HTTP_REDIRECT_PROXY', 305);





define('HTTP_REDIRECT_TEMP', 307);





define('HTTP_SUPPORT', 1);





define('HTTP_SUPPORT_REQUESTS', 2);





define('HTTP_SUPPORT_MAGICMIME', 4);





define('HTTP_SUPPORT_ENCODINGS', 8);





define('HTTP_SUPPORT_SSLREQUESTS', 32);
define('HTTP_SUPPORT_EVENTS', 128);





define('HTTP_PARAMS_ALLOW_COMMA', 1);





define('HTTP_PARAMS_ALLOW_FAILURE', 2);





define('HTTP_PARAMS_RAISE_ERROR', 4);





define('HTTP_PARAMS_DEFAULT', 7);





define('HTTP_URL_REPLACE', 0);





define('HTTP_URL_JOIN_PATH', 1);





define('HTTP_URL_JOIN_QUERY', 2);





define('HTTP_URL_STRIP_USER', 4);





define('HTTP_URL_STRIP_PASS', 8);





define('HTTP_URL_STRIP_AUTH', 12);





define('HTTP_URL_STRIP_PORT', 32);





define('HTTP_URL_STRIP_PATH', 64);





define('HTTP_URL_STRIP_QUERY', 128);





define('HTTP_URL_STRIP_FRAGMENT', 256);





define('HTTP_URL_STRIP_ALL', 492);
define('HTTP_URL_FROM_ENV', 4096);





define('HTTP_E_RUNTIME', 1);





define('HTTP_E_INVALID_PARAM', 2);





define('HTTP_E_HEADER', 3);





define('HTTP_E_MALFORMED_HEADERS', 4);





define('HTTP_E_REQUEST_METHOD', 5);





define('HTTP_E_MESSAGE_TYPE', 6);





define('HTTP_E_ENCODING', 7);





define('HTTP_E_REQUEST', 8);





define('HTTP_E_REQUEST_POOL', 9);





define('HTTP_E_SOCKET', 10);





define('HTTP_E_RESPONSE', 11);





define('HTTP_E_URL', 12);





define('HTTP_E_QUERYSTRING', 13);





define('HTTP_MSG_NONE', 0);





define('HTTP_MSG_REQUEST', 1);





define('HTTP_MSG_RESPONSE', 2);
define('HTTP_QUERYSTRING_TYPE_BOOL', 3);
define('HTTP_QUERYSTRING_TYPE_INT', 1);
define('HTTP_QUERYSTRING_TYPE_FLOAT', 2);
define('HTTP_QUERYSTRING_TYPE_STRING', 6);
define('HTTP_QUERYSTRING_TYPE_ARRAY', 4);
define('HTTP_QUERYSTRING_TYPE_OBJECT', 5);
