<?php






define('YAR_VERSION', '2.2.0');
define('YAR_CLIENT_PROTOCOL_HTTP', 1);
define('YAR_OPT_PACKAGER', 1);
define('YAR_OPT_TIMEOUT', 4);
define('YAR_OPT_CONNECT_TIMEOUT', 8);
define('YAR_OPT_PERSISTENT', 2);



define('YAR_OPT_HEADER', 16);
define('YAR_PACKAGER_PHP', 'PHP');
define('YAR_PACKAGER_JSON', 'JSON');
define('YAR_ERR_OUTPUT', 8);
define('YAR_ERR_OKEY', 0);
define('YAR_ERR_TRANSPORT', 16);
define('YAR_ERR_REQUEST', 4);
define('YAR_ERR_PROTOCOL', 2);
define('YAR_ERR_PACKAGER', 1);
define('YAR_ERR_EXCEPTION', 64);

define('YAR_CLIENT_PROTOCOL_TCP', 2);
define('YAR_CLIENT_PROTOCOL_UNIX', 4);

define('YAR_OPT_RESOLVE', 32);






class Yar_Server
{
protected $_executor;









final public function __construct($obj, $protocol = null) {}











public function handle() {}
}

class Yar_Client
{
protected $_protocol;
protected $_uri;
protected $_options;
protected $_running;









public function __call($method, $parameters) {}







final public function __construct($url, $async = null) {}

public function call($method, $parameters) {}














public function setOpt($type, $value) {}

public function getOpt($type) {}
}

class Yar_Concurrent_Client
{
protected static $_callstack;
protected static $_callback;
protected static $_error_callback;
protected static $_start;












public static function call($uri, $method, $parameters, callable $callback = null, callable $error_callback, array $options) {}











public static function loop($callback = null, $error_callback = null) {}







public static function reset() {}
}







class Yar_Server_Exception extends Exception
{
protected $_type;







public function getType() {}
}







class Yar_Client_Exception extends Exception
{





public function getType() {}
}

class Yar_Server_Request_Exception extends Yar_Server_Exception {}

class Yar_Server_Protocol_Exception extends Yar_Server_Exception {}

class Yar_Server_Packager_Exception extends Yar_Server_Exception {}

class Yar_Server_Output_Exception extends Yar_Server_Exception {}

class Yar_Client_Transport_Exception extends Yar_Client_Exception {}

class Yar_Client_Packager_Exception extends Yar_Client_Exception {}

class Yar_Client_Protocol_Exception extends Yar_Client_Exception {}
