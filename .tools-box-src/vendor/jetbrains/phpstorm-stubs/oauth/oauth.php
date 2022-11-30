<?php

define('OAUTH_SIG_METHOD_RSASHA1', 'RSA-SHA1');
define('OAUTH_SIG_METHOD_HMACSHA1', 'HMAC-SHA1');
define('OAUTH_SIG_METHOD_HMACSHA256', 'HMAC-SHA256');

define('OAUTH_AUTH_TYPE_AUTHORIZATION', 3);
define('OAUTH_AUTH_TYPE_NONE', 2);
define('OAUTH_AUTH_TYPE_URI', 1);
define('OAUTH_AUTH_TYPE_FORM', 2);

define('OAUTH_HTTP_METHOD_GET', 'GET');
define('OAUTH_HTTP_METHOD_POST', 'POST');
define('OAUTH_HTTP_METHOD_PUT', 'PUT');
define('OAUTH_HTTP_METHOD_HEAD', 'HEAD');
define('OAUTH_HTTP_METHOD_DELETE', 'DELETE');

define('OAUTH_REQENGINE_STREAMS', 1);
define('OAUTH_REQENGINE_CURL', 2);

define('OAUTH_OK', 0);
define('OAUTH_BAD_NONCE', 4);
define('OAUTH_BAD_TIMESTAMP', 8);
define('OAUTH_CONSUMER_KEY_UNKNOWN', 16);
define('OAUTH_CONSUMER_KEY_REFUSED', 32);
define('OAUTH_INVALID_SIGNATURE', 64);
define('OAUTH_TOKEN_USED', 128);
define('OAUTH_TOKEN_EXPIRED', 256);
define('OAUTH_TOKEN_REJECTED', 1024);
define('OAUTH_VERIFIER_INVALID', 2048);
define('OAUTH_PARAMETER_ABSENT', 4096);
define('OAUTH_SIGNATURE_METHOD_REJECTED', 8192);









function oauth_get_sbs($http_method, $uri, $request_parameters = []) {}







function oauth_urlencode($uri) {}




class OAuth
{



public $debug;




public $sslChecks;




public $debugInfo;









public function __construct($consumer_key, $consumer_secret, $signature_method = OAUTH_SIG_METHOD_HMACSHA1, $auth_type = OAUTH_AUTH_TYPE_AUTHORIZATION) {}





public function disableDebug() {}





public function disableRedirects() {}





public function disableSSLChecks() {}





public function enableDebug() {}





public function enableRedirects() {}





public function enableSSLChecks() {}






public function setTimeout($timeout) {}










public function fetch($protected_resource_url, $extra_parameters = [], $http_method = null, $http_headers = []) {}









public function getAccessToken($access_token_url, $auth_session_handle = null, $verifier_token = null) {}





public function getCAPath() {}





public function getLastResponse() {}





public function getLastResponseHeaders() {}





public function getLastResponseInfo() {}








public function getRequestHeader($http_method, $url, $extra_parameters = '') {}









public function getRequestToken($request_token_url, $callback_url = null, $http_method = 'GET') {}






public function setAuthType($auth_type) {}







public function setCAPath($ca_path = null, $ca_info = null) {}






public function setNonce($nonce) {}





public function setRequestEngine($reqengine) {}






public function setRSACertificate($cert) {}






public function setTimestamp($timestamp) {}







public function setToken($token, $token_secret) {}






public function setVersion($version) {}
}

class OAuthException extends Exception
{




public $lastResponse;




public $debugInfo;
}

;




class OAuthProvider
{




final public function addRequiredParameter($req_params) {}




public function callconsumerHandler() {}




public function callTimestampNonceHandler() {}




public function calltokenHandler() {}






public function checkOAuthRequest($uri = '', $method = '') {}




public function __construct($params_array) {}





public function consumerHandler($callback_function) {}






final public static function generateToken($size, $strong = false) {}





public function is2LeggedEndpoint($params_array) {}





public function isRequestTokenEndpoint($will_issue_request_token) {}





final public function removeRequiredParameter($req_params) {}






final public static function reportProblem($oauthexception, $send_headers = true) {}






final public function setParam($param_key, $param_val = null) {}





final public function setRequestTokenPath($path) {}





public function timestampNonceHandler($callback_function) {}





public function tokenHandler($callback_function) {}
}
