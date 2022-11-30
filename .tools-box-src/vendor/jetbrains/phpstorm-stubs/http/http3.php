<?php









namespace http;

use http;
use JetBrains\PhpStorm\Deprecated;




class Client implements \SplSubject, \Countable
{



public const DEBUG_INFO = 0;




public const DEBUG_IN = 1;




public const DEBUG_OUT = 2;




public const DEBUG_HEADER = 16;




public const DEBUG_BODY = 32;




public const DEBUG_SSL = 64;






private $observers = null;






protected $options = null;






protected $history = null;






public $recordHistory = false;













public function __construct(string $driver = null, string $persistent_handle_id = null) {}









public function addCookies(array $cookies = null) {}









public function addSslOptions(array $ssl_options = null) {}










public function attach(\SplObserver $observer) {}













public function configure(array $configuration) {}









public function count() {}












public function dequeue(http\Client\Request $request) {}









public function detach(\SplObserver $observer) {}










#[Deprecated('This method has been deprecated in 2.3.0. Use http\Client::configure() instead')]
public function enableEvents(bool $enable = true) {}










#[Deprecated('This method has been deprecated in 2.3.0. Use http\Client::configure() instead')]
public function enablePipelining(bool $enable = true) {}























public function enqueue(http\Client\Request $request, callable $cb = null) {}









public function getAvailableConfiguration() {}






public function getAvailableDrivers() {}









public function getAvailableOptions() {}







public function getCookies() {}










public function getHistory() {}








public function getObservers() {}







public function getOptions() {}










public function getProgressInfo(http\Client\Request $request) {}













public function getResponse(http\Client\Request $request = null) {}







public function getSslOptions() {}









public function getTransferInfo(http\Client\Request $request) {}










public function notify(http\Client\Request $request = null, $progress = null) {}







public function once() {}













public function requeue(http\Client\Request $request, callable $cb = null) {}






public function reset() {}









public function send() {}









public function setCookies(array $cookies = null) {}












public function setDebug(callable $callback) {}












public function setOptions(array $options = null) {}









public function setSslOptions(array $ssl_options = null) {}








public function wait(float $timeout = 0) {}
}



class Cookie
{



public const PARSE_RAW = 1;




public const SECURE = 16;




public const HTTPONLY = 32;










public function __construct($cookies = null, int $flags = 0, array $allowed_extras = null) {}






public function __toString() {}










public function addCookie(string $cookie_name, string $cookie_value) {}









public function addCookies(array $cookies) {}










public function addExtra(string $extra_name, string $extra_value) {}









public function addExtras(array $extras) {}









public function getCookie(string $cookie_name) {}







public function getCookies() {}







public function getDomain() {}










public function getExpires() {}








public function getExtra(string $name) {}







public function getExtras() {}







public function getFlags() {}










public function getMaxAge() {}







public function getPath() {}













public function setCookie(string $cookie_name, string $cookie_value) {}









public function setCookies(array $cookies = null) {}









public function setDomain(string $value = null) {}









public function setExpires(int $value = -1) {}













public function setExtra(string $extra_name, string $extra_value = null) {}









public function setExtras(array $extras = null) {}









public function setFlags(int $value = 0) {}










public function setMaxAge(int $value = -1) {}









public function setPath(string $path = null) {}






public function toArray() {}







public function toString() {}
}

namespace http\Encoding;

namespace http;




class Env
{








public function getRequestBody(string $body_class_name = null) {}









public function getRequestHeader(string $header_name = null) {}






public function getResponseCode() {}









public function getResponseHeader(string $header_name = null) {}










public function getResponseStatusForAllCodes() {}







public function getResponseStatusForCode(int $code) {}














public function negotiate(string $params, array $supported, string $prim_typ_sep = null, array &$result = null) {}












public function negotiateCharset(array $supported, array &$result = null) {}












public function negotiateContentType(array $supported, array &$result = null) {}












public function negotiateEncoding(array $supported, array &$result = null) {}












public function negotiateLanguage(array $supported, array &$result = null) {}







public function setResponseCode(int $code) {}














public function setResponseHeader(string $header_name, $header_value = null, int $response_code = null, bool $replace = null) {}
}







interface Exception {}



class Header implements \Serializable
{



public const MATCH_LOOSE = 0;




public const MATCH_CASE = 1;




public const MATCH_WORD = 16;




public const MATCH_FULL = 32;




public const MATCH_STRICT = 33;






public $name = null;






public $value = null;









public function __construct(string $name = null, $value = null) {}






public function __toString() {}










public function getParams($ps = null, $as = null, $vs = null, int $flags = null) {}








public function match(string $value, int $flags = null) {}
















public function negotiate(array $supported, array &$result = null) {}










public function parse(string $header, string $header_class = null) {}






public function serialize() {}






public function toString() {}






public function unserialize($serialized) {}
}





class Message implements \Countable, \Serializable, \Iterator
{



public const TYPE_NONE = 0;




public const TYPE_REQUEST = 1;




public const TYPE_RESPONSE = 2;






protected $type = \http\Message::TYPE_NONE;






protected $body = null;






protected $requestMethod = "";






protected $requestUrl = "";






protected $responseStatus = "";






protected $responseCode = 0;






protected $httpVersion = null;






protected $headers = null;






protected $parentMessage;









public function __construct($message = null, bool $greedy = true) {}







public function __toString() {}








public function addBody(http\Message\Body $body) {}









public function addHeader(string $name, $value) {}









public function addHeaders(array $headers, bool $append = false) {}






public function count() {}







public function current() {}







public function detach() {}









public function getBody() {}









public function getHeader(string $header, string $into_class = null) {}







public function getHeaders() {}







public function getHttpVersion() {}















public function getInfo() {}









public function getParentMessage() {}








public function getRequestMethod() {}








public function getRequestUrl() {}








public function getResponseCode() {}








public function getResponseStatus() {}







public function getType() {}










public function isMultipart(string &$boundary = null) {}







public function key() {}





public function next() {}













public function prepend(http\Message $message, bool $top = true) {}












public function reverse() {}




public function rewind() {}






public function serialize() {}










public function setBody(http\Message\Body $body) {}













public function setHeader(string $header, $value = null) {}












public function setHeaders(array $headers = null) {}










public function setHttpVersion(string $http_version) {}










public function setInfo(string $http_info) {}










public function setRequestMethod(string $method) {}










public function setRequestUrl(string $url) {}














public function setResponseCode(int $response_code, bool $strict = true) {}










public function setResponseStatus(string $response_status) {}








public function setType(int $type) {}










public function splitMultipartBody() {}







public function toCallback(callable $callback) {}







public function toStream($stream) {}







public function toString(bool $include_parent = false) {}






public function unserialize($data) {}







public function valid() {}
}



class Params implements \ArrayAccess
{



public const DEF_PARAM_SEP = ',';




public const DEF_ARG_SEP = ';';




public const DEF_VAL_SEP = '=';




public const COOKIE_PARAM_SEP = '';




public const PARSE_RAW = 0;




public const PARSE_DEFAULT = 17;




public const PARSE_ESCAPED = 1;




public const PARSE_URLENCODED = 4;




public const PARSE_DIMENSION = 8;




public const PARSE_QUERY = 12;




public const PARSE_RFC5987 = 16;




public const PARSE_RFC5988 = 32;






public $params = null;






public $param_sep = \http\Params::DEF_PARAM_SEP;






public $arg_sep = \http\Params::DEF_ARG_SEP;






public $val_sep = \http\Params::DEF_VAL_SEP;






public $flags = \http\Params::PARSE_DEFAULT;












public function __construct($params = null, $ps = null, $as = null, $vs = null, int $flags = null) {}







public function __toString() {}







public function offsetExists($name) {}







public function offsetGet($name) {}







public function offsetSet($name, $value) {}






public function offsetUnset($name) {}






public function toArray() {}






public function toString() {}
}



class QueryString implements \Serializable, \ArrayAccess, \IteratorAggregate
{



public const TYPE_BOOL = 16;




public const TYPE_INT = 4;




public const TYPE_FLOAT = 5;




public const TYPE_STRING = 6;




public const TYPE_ARRAY = 7;




public const TYPE_OBJECT = 8;






private $instance = null;






private $queryArray = null;







public function __construct($params = null) {}






public function __toString() {}
















public function get(string $name = null, $type = null, $defval = null, bool $delete = false) {}










public function getArray(string $name, $defval = null, bool $delete = false) {}










public function getBool(string $name, $defval = null, bool $delete = false) {}










public function getFloat(string $name, $defval = null, bool $delete = false) {}







public function getGlobalInstance() {}










public function getInt(string $name, $defval = null, bool $delete = false) {}







public function getIterator() {}










public function getObject(string $name, $defval = null, bool $delete = false) {}










public function getString(string $name, $defval = null, bool $delete = false) {}












public function mod($params = null) {}







public function offsetExists($name) {}








public function offsetGet($offset) {}







public function offsetSet($name, $data) {}






public function offsetUnset($name) {}







public function serialize() {}







public function set($params) {}






public function toArray() {}






public function toString() {}







public function unserialize($serialized) {}













public function xlate(string $from_enc, string $to_enc) {}
}



class Url
{



public const REPLACE = 0;




public const JOIN_PATH = 1;




public const JOIN_QUERY = 2;




public const STRIP_USER = 4;




public const STRIP_PASS = 8;




public const STRIP_AUTH = 12;




public const STRIP_PORT = 32;




public const STRIP_PATH = 64;




public const STRIP_QUERY = 128;




public const STRIP_FRAGMENT = 256;




public const STRIP_ALL = 492;




public const FROM_ENV = 4096;




public const SANITIZE_PATH = 8192;




public const PARSE_MBUTF8 = 131072;




public const PARSE_MBLOC = 65536;




public const PARSE_TOIDN = 1048576;




public const PARSE_TOIDN_2003 = 9437184;




public const PARSE_TOIDN_2008 = 5242880;




public const PARSE_TOPCT = 2097152;




public const IGNORE_ERRORS = 268435456;




public const SILENT_ERRORS = 536870912;





public const STDFLAGS = 3350531;






public $scheme = null;






public $user = null;






public $pass = null;






public $host = null;






public $port = null;






public $path = null;






public $query = null;






public $fragment = null;















public function __construct($old_url = null, $new_url = null, int $flags = 0) {}






public function __toString() {}













public function mod($parts, int $flags = \http\Url::JOIN_PATH|\http\Url::JOIN_QUERY|\http\Url::SANITIZE_PATH) {}






public function toArray() {}






public function toString() {}
}




namespace http\Client\Curl;





const FEATURES = 4179869;





const VERSIONS = 'libcurl/7.64.0 OpenSSL/1.1.1b zlib/1.2.11 libidn2/2.0.5 libpsl/0.20.2 (+libidn2/2.0.5) libssh2/1.8.0 nghttp2/1.36.0 librtmp/2.3';



const HTTP_VERSION_1_0 = 1;



const HTTP_VERSION_1_1 = 2;



const HTTP_VERSION_2_0 = 3;



const HTTP_VERSION_2TLS = 4;



const HTTP_VERSION_ANY = 0;



const SSL_VERSION_TLSv1_0 = 4;



const SSL_VERSION_TLSv1_1 = 5;



const SSL_VERSION_TLSv1_2 = 6;



const SSL_VERSION_TLSv1 = 1;



const SSL_VERSION_SSLv2 = 2;



const SSL_VERSION_SSLv3 = 3;



const SSL_VERSION_ANY = 0;



const TLSAUTH_SRP = 1;



const IPRESOLVE_V4 = 1;



const IPRESOLVE_V6 = 2;



const IPRESOLVE_ANY = 0;



const AUTH_BASIC = 1;



const AUTH_DIGEST = 2;



const AUTH_DIGEST_IE = 16;



const AUTH_NTLM = 8;



const AUTH_GSSNEG = 4;



const AUTH_SPNEGO = 4;



const AUTH_ANY = -17;



const PROXY_SOCKS4 = 4;



const PROXY_SOCKS4A = 5;



const PROXY_SOCKS5_HOSTNAME = 5;



const PROXY_SOCKS5 = 5;



const PROXY_HTTP = 0;



const PROXY_HTTP_1_0 = 1;



const POSTREDIR_301 = 1;



const POSTREDIR_302 = 2;



const POSTREDIR_303 = 4;



const POSTREDIR_ALL = 7;

namespace http\Client;






class Request extends \http\Message
{





protected $options = null;











public function __construct(string $meth = null, string $url = null, array $headers = null, http\Message\Body $body = null) {}










public function addQuery($query_data) {}









public function addSslOptions(array $ssl_options = null) {}








public function getContentType() {}







public function getOptions() {}







public function getQuery() {}







public function getSslOptions() {}









public function setContentType(string $content_type) {}














public function setOptions(array $options = null) {}










public function setQuery($query_data) {}









public function setSslOptions(array $ssl_options = null) {}
}



class Response extends \http\Message
{








public function getCookies(int $flags = 0, array $allowed_extras = null) {}












public function getTransferInfo(string $name = null) {}
}

namespace http\Client\Curl;







interface User
{



public const POLL_NONE = 0;




public const POLL_IN = 1;




public const POLL_OUT = 2;




public const POLL_INOUT = 3;




public const POLL_REMOVE = 4;











public function init(callable $run);







public function once();







public function send();







public function socket($socket, int $poll);






public function timer(int $timeout_ms);









public function wait(int $timeout_ms = null);
}







namespace http\Client\Curl\Features;




const ASYNCHDNS = 128;



const GSSAPI = 131072;



const GSSNEGOTIATE = 32;



const HTTP2 = 65536;



const IDN = 1024;



const IPV6 = 1;



const KERBEROS4 = 2;



const KERBEROS5 = 262144;



const LARGEFILE = 512;



const LIBZ = 8;



const NTLM = 16;



const NTLM_WB = 32768;



const PSL = 1048576;



const SPNEGO = 256;



const SSL = 4;



const SSPI = 2048;



const TLSAUTH_SRP = 16384;



const UNIX_SOCKETS = 524288;







namespace http\Client\Curl\Versions;




const CURL = '7.64.0';



const SSL = 'OpenSSL/1.1.1b';



const LIBZ = '1.2.11';



const ARES = null;



const IDN = null;

namespace http\Encoding;




abstract class Stream
{



public const FLUSH_NONE = 0;




public const FLUSH_SYNC = 1048576;




public const FLUSH_FULL = 2097152;








public function __construct(int $flags = 0) {}






public function done() {}







public function finish() {}







public function flush() {}







public function update(string $data) {}
}

namespace http\Encoding\Stream;







class Debrotli extends \http\Encoding\Stream
{






public function decode(string $data) {}
}



class Dechunk extends \http\Encoding\Stream
{











public function decode(string $data, int &$decoded_len = 0) {}
}



class Deflate extends \http\Encoding\Stream
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








public function encode(string $data, int $flags = 0) {}
}






class Enbrotli extends \http\Encoding\Stream
{



public const LEVEL_DEF = null;




public const LEVEL_MIN = null;




public const LEVEL_MAX = null;




public const WBITS_DEF = null;




public const WBITS_MIN = null;




public const WBITS_MAX = null;




public const MODE_GENERIC = null;




public const MODE_TEXT = null;




public const MODE_FONT = null;








public function encode(string $data, int $flags = 0) {}
}



class Inflate extends \http\Encoding\Stream
{






public function decode(string $data) {}
}

namespace http\Env;






class Request extends \http\Message
{





protected $query = null;






protected $form = null;






protected $files = null;






protected $cookie = null;











public function __construct() {}
















public function getCookie(string $name = null, $type = null, $defval = null, bool $delete = false) {}






public function getFiles() {}
















public function getForm(string $name = null, $type = null, $defval = null, bool $delete = false) {}
















public function getQuery(string $name = null, $type = null, $defval = null, bool $delete = false) {}
}





class Response extends \http\Message
{



public const CONTENT_ENCODING_NONE = 0;




public const CONTENT_ENCODING_GZIP = 1;




public const CACHE_NO = 0;




public const CACHE_HIT = 1;




public const CACHE_MISS = 2;






protected $request = null;






protected $contentType = null;






protected $contentDisposition = null;






protected $contentEncoding = null;






protected $cacheControl = null;






protected $etag = null;






protected $lastModified = null;






protected $throttleDelay = null;






protected $throttleChunk = null;






protected $cookies = null;







public function __construct() {}









public function __invoke(string $data, int $ob_flags = 0) {}








public function isCachedByEtag(string $header_name = "If-None-Match") {}








public function isCachedByLastModified(string $header_name = "If-Modified-Since") {}








public function send($stream = null) {}








public function setCacheControl(string $cache_control) {}








public function setContentDisposition(array $disposition_params) {}









public function setContentEncoding(int $content_encoding) {}








public function setContentType(string $content_type) {}









public function setCookie($cookie) {}








public function setEnvRequest(http\Message $env_request) {}











public function setEtag(string $etag) {}











public function setLastModified(int $last_modified) {}












public function setThrottleRate(int $chunk_size, float $delay = 1) {}
}








class Url extends \http\Url {}

namespace http\Exception;




class BadConversionException extends \DomainException implements \http\Exception {}



class BadHeaderException extends \DomainException implements \http\Exception {}



class BadMessageException extends \DomainException implements \http\Exception {}



class BadMethodCallException extends \BadMethodCallException implements \http\Exception {}



class BadQueryStringException extends \DomainException implements \http\Exception {}



class BadUrlException extends \DomainException implements \http\Exception {}



class InvalidArgumentException extends \InvalidArgumentException implements \http\Exception {}



class RuntimeException extends \RuntimeException implements \http\Exception {}



class UnexpectedValueException extends \UnexpectedValueException implements \http\Exception {}

namespace http\Header;







class Parser
{



public const CLEANUP = 1;




public const STATE_FAILURE = -1;




public const STATE_START = 0;




public const STATE_KEY = 1;




public const STATE_VALUE = 2;




public const STATE_VALUE_EX = 3;




public const STATE_HEADER_DONE = 4;







public const STATE_DONE = 5;








public function getState() {}










public function parse(string $data, int $flags, array &$header = null) {}











public function stream($stream, int $flags, array &$headers) {}
}

namespace http\Message;







class Body implements \Serializable
{







public function __construct($stream = null) {}






public function __toString() {}












































public function addForm(array $fields = null, array $files = null) {}









public function addPart(http\Message $part) {}









public function append(string $data) {}








public function etag() {}








public function getBoundary() {}






public function getResource() {}







public function serialize() {}








public function stat(string $field = null) {}









public function toCallback(callable $callback, int $offset = 0, int $maxlen = 0) {}









public function toStream($stream, int $offset = 0, int $maxlen = 0) {}







public function toString() {}






public function unserialize($serialized) {}
}






class Parser
{



public const CLEANUP = 1;




public const DUMB_BODIES = 2;




public const EMPTY_REDIRECTS = 4;




public const GREEDY = 8;




public const STATE_FAILURE = -1;




public const STATE_START = 0;




public const STATE_HEADER = 1;




public const STATE_HEADER_DONE = 2;




public const STATE_BODY = 3;




public const STATE_BODY_DUMB = 4;




public const STATE_BODY_LENGTH = 5;




public const STATE_BODY_CHUNKED = 6;




public const STATE_BODY_DONE = 7;




public const STATE_UPDATE_CL = 8;







public const STATE_DONE = 9;








public function getState() {}










public function parse(string $data, int $flags, http\Message $message) {}











public function stream($stream, int $flags, http\Message $message) {}
}
