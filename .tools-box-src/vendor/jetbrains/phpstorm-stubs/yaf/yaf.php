<?php





define('YAF_VERSION', '3.3.3', true);
define('YAF_ENVIRON', 'product', true);
define('YAF_ERR_STARTUP_FAILED', 512, true);
define('YAF_ERR_ROUTE_FAILED', 513, true);
define('YAF_ERR_DISPATCH_FAILED', 514, true);
define('YAF_ERR_NOTFOUND_MODULE', 515, true);
define('YAF_ERR_NOTFOUND_CONTROLLER', 516, true);
define('YAF_ERR_NOTFOUND_ACTION', 517, true);
define('YAF_ERR_NOTFOUND_VIEW', 518, true);
define('YAF_ERR_CALL_FAILED', 519, true);
define('YAF_ERR_AUTOLOAD_FAILED', 520, true);
define('YAF_ERR_TYPE_ERROR', 521, true);
define('YAF_ERR_ACCESS_ERROR', 522);













final class Yaf_Application
{



protected static $_app;




protected $config;




protected $dispatcher;




protected $_modules;




protected $_running = "";




protected $_environ = YAF_ENVIRON;





protected $_err_no = 0;





protected $_err_msg = "";
















































public function __construct($config, $environ = null) {}

public function getInstance() {}








public function run() {}










public function execute(callable $entry, ...$_) {}








public static function app() {}








public function environ() {}









public function bootstrap($bootstrap = null) {}






public function getConfig() {}








public function getModules() {}






public function getDispatcher() {}









public function setAppDirectory($directory) {}







public function getAppDirectory() {}







public function getLastErrorNo() {}







public function getLastErrorMsg() {}





public function clearLastError() {}




public function __destruct() {}




private function __clone() {}




private function __sleep() {}




private function __wakeup() {}
}







final class Yaf_Dispatcher
{



protected static $_instance;




protected $_router;




protected $_view;




protected $_request;




protected $_plugins;




protected $_auto_render = true;




protected $_return_response = "";




protected $_instantly_flush = "";




protected $_default_module;




protected $_default_controller;




protected $_default_action;




private function __construct() {}




private function __clone() {}




private function __sleep() {}




private function __wakeup() {}








public function enableView() {}

public function getResponse() {}

public function getDefaultModule() {}

public function getDefaultController() {}

public function getDefaultAction() {}










public function disableView() {}










public function initView($templates_dir, ?array $options = null) {}









public function setView($view) {}







public function setRequest($request) {}







public function getApplication() {}






public function getRouter() {}






public function getRequest() {}












public function setErrorHandler($callback, $error_types = YAF_ERR_TYPE_ERROR) {}









public function setDefaultModule($module) {}









public function setDefaultController($controller) {}









public function setDefaultAction($action) {}







public function returnResponse($flag) {}











public function autoRender($flag = null) {}









public function flushInstantly($flag = null) {}






public static function getInstance() {}
























public function dispatch($request) {}










public function throwException($flag = null) {}










public function catchException($flag = null) {}









public function registerPlugin($plugin) {}

public function setResponse($response) {}
}














class Yaf_Loader
{



protected $_local_ns;





protected $_library;




protected $_global_library;




protected static $_instance;




private function __construct() {}




private function __clone() {}




private function __sleep() {}




private function __wakeup() {}








public function autoload($class_name) {}









public static function getInstance($local_library_path = null, $global_library_path = null) {}















public function registerLocalNamespace($namespace, $path = '') {}






public function getLocalNamespace() {}

public function getNamespaces() {}




public function clearLocalNamespace() {}








public function isLocalName($class_name) {}








public static function import($file) {}










public function setLibraryPath($library_path, $is_global = false) {}








public function getLibraryPath($is_global = false) {}

public function registerNamespace($namespace, $path = '') {}

public function getNamespacePath($class_name) {}
}





final class Yaf_Registry
{



protected static $_instance;




protected $_entries;




private function __construct() {}




private function __clone() {}










public static function get($name) {}










public static function has($name) {}









public static function set($name, $value) {}








public static function del($name) {}
}





final class Yaf_Session implements Iterator, ArrayAccess, Countable
{



protected static $_instance;




protected $_session;




protected $_started = true;




private function __construct() {}




private function __clone() {}




private function __sleep() {}




private function __wakeup() {}






public static function getInstance() {}






public function start() {}








public function get($name) {}








public function has($name) {}









public function set($name, $value) {}








public function del($name) {}




public function count() {}




public function rewind() {}




public function current() {}




public function next() {}




public function valid() {}




public function key() {}





public function offsetUnset($name) {}






public function offsetGet($name) {}




public function offsetExists($name) {}







public function offsetSet($name, $value) {}




public function __get($name) {}




public function __isset($name) {}




public function __set($name, $value) {}




public function __unset($name) {}

public function clear() {}
}














class Yaf_Router
{



protected $_routes;




protected $_current;




public function __construct() {}













public function addRoute($name, $route) {}










public function addConfig($config) {}








public function route($request) {}










public function getRoute($name) {}






public function getRoutes() {}











public function getCurrentRoute() {}
}








abstract class Yaf_Bootstrap_Abstract {}

















abstract class Yaf_Controller_Abstract
{




public $actions;




protected $_module;




protected $_name;




protected $_request;




protected $_response;




protected $_invoke_args;




protected $_view;









protected function render($tpl, ?array $parameters = null) {}









protected function display($tpl, ?array $parameters = null) {}








public function getRequest() {}








public function getResponse() {}








public function getModuleName() {}








public function getView() {}

public function getName() {}








public function initView(?array $options = null) {}








public function setViewpath($view_directory) {}






public function getViewpath() {}





















public function forward($module, $controller = null, $action = null, ?array $parameters = null) {}










public function redirect($url) {}






public function getInvokeArgs() {}







public function getInvokeArg($name) {}






public function init() {}











public function __construct($request, $response, $view, ?array $args = null) {}




private function __clone() {}
}








abstract class Yaf_Action_Abstract extends Yaf_Controller_Abstract
{



protected $_controller;











abstract public function execute();








public function getController() {}

public function getControllerName() {}
}




abstract class Yaf_Config_Abstract implements Iterator, ArrayAccess, Countable
{



protected $_config = null;




protected $_readonly = true;







abstract public function get($name = null);








abstract public function set($name, $value);

public function count() {}

public function rewind() {}

public function current() {}

public function key() {}

public function next() {}

public function valid() {}






abstract public function readonly();






abstract public function toArray();

public function offsetSet($name, $value) {}

public function offsetUnset($name) {}

public function offsetExists($name) {}

public function offsetGet($name = '') {}

public function __get($name = '') {}

public function __isset($name) {}
}




abstract class Yaf_Request_Abstract
{
public const SCHEME_HTTP = 'http';
public const SCHEME_HTTPS = 'https';




public $module;




public $controller;




public $action;




public $method;




protected $params;




protected $language;




protected $_exception;




protected $_base_uri = "";




protected $uri = "";




protected $dispatched = "";




protected $routed = "";






public function isGet() {}

public function isDelete() {}

public function isPatch() {}

public function getRaw() {}

public function clearParams() {}






public function isPost() {}






public function isPut() {}






public function isHead() {}






public function isOptions() {}






public function isCli() {}






final public function isDispatched() {}






final public function isRouted() {}






public function isXmlHttpRequest() {}











public function getServer($name = null, $default = null) {}











public function getEnv($name = null, $default = null) {}









public function getParam($name = '', $default = '') {}






public function getParams() {}






public function getException() {}






public function getModuleName() {}






public function getControllerName() {}






public function getActionName() {}









public function setParam($name, $value = null) {}









public function setModuleName($module, $format_name = true) {}









public function setControllerName($controller, $format_name = true) {}









public function setActionName($action, $format_name = true) {}






public function getMethod() {}






public function getLanguage() {}













public function setBaseUri($uri) {}






public function getBaseUri() {}






public function getRequestUri() {}







public function setRequestUri($uri) {}








final public function setDispatched($dispatched = null) {}








final public function setRouted($flag = null) {}

public function get($name = null, $default = null) {}

public function getFiles($name = null, $default = null) {}

public function getCookie($name = null, $default = null) {}

public function getPost($name = null, $default = null) {}

public function getRequest($name = null, $default = null) {}

public function getQuery($name = null, $default = null) {}
}









abstract class Yaf_Plugin_Abstract
{










public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}











public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}









public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}











public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}









public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}









public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}









public function preResponse(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response) {}
}




abstract class Yaf_Response_Abstract
{
public const DEFAULT_BODY = "content";




protected $_header;




protected $_body;




protected $_sendheader;




public function __construct() {}




public function __destruct() {}




private function __clone() {}




public function __toString() {}







public function response() {}











public function setHeader($name, $value, $rep = false) {}














public function setBody($body, $name = self::DEFAULT_BODY) {}














public function appendBody($body, $name = self::DEFAULT_BODY) {}














public function prependBody($body, $name = self::DEFAULT_BODY) {}













public function clearBody($name = self::DEFAULT_BODY) {}













public function getBody($name = self::DEFAULT_BODY) {}
}






interface Yaf_View_Interface
{









public function assign($name, $value = '');










public function display($tpl, $tpl_vars = null);






public function getScriptPath($request = null);










public function render($tpl, $tpl_vars = null);








public function setScriptPath($template_dir);
}






interface Yaf_Route_Interface
{











public function route($request);












public function assemble(array $info, ?array $query = null);
}




class Yaf_Exception extends Exception
{
protected $message;
protected $code;
protected $previous;
}

class Yaf_Response_Http extends Yaf_Response_Abstract
{



protected $_response_code = 0;

private function __clone() {}




public function __toString() {}











public function setHeader($name, $value, $rep = false, $response_code = 0) {}








public function setAllHeaders($headers) {}








public function getHeader($name = null) {}







public function clearHeaders() {}








public function setRedirect($url) {}








public function response() {}
}

class Yaf_Response_Cli extends Yaf_Response_Abstract
{
private function __clone() {}




public function __toString() {}
}




class Yaf_Request_Http extends Yaf_Request_Abstract
{










public function getQuery($name = null, $default = null) {}











public function getRequest($name = null, $default = null) {}











public function getPost($name = null, $default = null) {}











public function getCookie($name = null, $default = null) {}











public function getFiles($name = null, $default = null) {}











public function get($name, $default = null) {}













public function isXmlHttpRequest() {}







public function __construct($request_uri = '', $base_uri = '') {}




private function __clone() {}
}





class Yaf_Request_Simple extends Yaf_Request_Abstract
{










public function getQuery($name = null, $default = null) {}











public function getRequest($name = null, $default = null) {}











public function getPost($name = null, $default = null) {}











public function getCookie($name = null, $default = null) {}







public function getFiles($name = null, $default = null) {}











public function get($name, $default = null) {}













public function isXmlHttpRequest() {}












public function __construct($method = '', $module = '', $controller = '', $action = '', $params = null) {}




private function __clone() {}
}







class Yaf_Config_Ini extends Yaf_Config_Abstract implements Iterator, ArrayAccess, Countable
{



public function __get($name = null) {}




public function __set($name, $value) {}




public function get($name = null) {}





public function set($name, $value) {}




public function toArray() {}




public function readonly() {}









public function __construct($config_file, $section = null) {}





public function __isset($name) {}




public function count() {}




public function rewind() {}




public function current() {}




public function next() {}




public function valid() {}




public function key() {}





public function offsetUnset($name) {}




public function offsetGet($name = '') {}




public function offsetExists($name) {}




public function offsetSet($name, $value) {}
}




class Yaf_Config_Simple extends Yaf_Config_Abstract implements Iterator, ArrayAccess, Countable
{



public function __get($name = null) {}




public function __set($name, $value) {}




public function get($name = null) {}




public function set($name, $value) {}




public function toArray() {}




public function readonly() {}







public function __construct($config, $readonly = null) {}





public function __isset($name) {}




public function count() {}




public function rewind() {}




public function current() {}




public function next() {}




public function valid() {}




public function key() {}




public function offsetUnset($name) {}




public function offsetGet($name) {}




public function offsetExists($name) {}




public function offsetSet($name, $value) {}
}





class Yaf_View_Simple implements Yaf_View_Interface
{



protected $_tpl_dir;




protected $_tpl_vars;




protected $_options;












final public function __construct($template_dir, ?array $options = null) {}






public function __isset($name) {}










public function assign($name, $value = null) {}











public function render($tpl, $tpl_vars = null) {}













public function display($tpl, $tpl_vars = null) {}










public function assignRef($name, &$value) {}









public function clear($name = null) {}








public function setScriptPath($template_dir) {}






public function getScriptPath($request = null) {}














public function __get($name = null) {}









public function __set($name, $value = null) {}








public function eval($tpl_str, $vars = null) {}

public function get($name = '') {}
}











class Yaf_Route_Static implements Yaf_Route_Interface
{







public function match($uri) {}








public function route($request) {}










public function assemble(array $info, ?array $query = null) {}
}










final class Yaf_Route_Simple implements Yaf_Route_Interface
{



protected $controller;




protected $module;




protected $action;












public function __construct($module_name, $controller_name, $action_name) {}










public function route($request) {}










public function assemble(array $info, ?array $query = null) {}
}




final class Yaf_Route_Supervar implements Yaf_Route_Interface
{



protected $_var_name;










public function __construct($supervar_name) {}








public function route($request) {}










public function assemble(array $info, ?array $query = null) {}
}






final class Yaf_Route_Rewrite extends Yaf_Router implements Yaf_Route_Interface
{



protected $_route;




protected $_default;




protected $_verify;













public function __construct($match, array $route, array $verify = null, $reverse = null) {}








public function route($request) {}










public function assemble(array $info, ?array $query = null) {}

public function match($uri) {}
}






final class Yaf_Route_Regex extends Yaf_Router implements Yaf_Route_Interface
{



protected $_route;




protected $_default;




protected $_maps;




protected $_verify;




protected $_reverse;














public function __construct($match, array $route, ?array $map = null, ?array $verify = null, $reverse = null) {}










public function route($request) {}










public function assemble(array $info, ?array $query = null) {}

public function match($uri) {}
}








final class Yaf_Route_Map implements Yaf_Route_Interface
{



protected $_ctl_router = '';




protected $_delimiter;







public function __construct($controller_prefer = false, $delimiter = '') {}








public function route($request) {}










public function assemble(array $info, ?array $query = null) {}
}




class Yaf_Exception_TypeError extends Yaf_Exception {}




class Yaf_Exception_StartupError extends Yaf_Exception {}




class Yaf_Exception_RouterFailed extends Yaf_Exception {}




class Yaf_Exception_DispatchFailed extends Yaf_Exception {}




class Yaf_Exception_LoadFailed extends Yaf_Exception {}




class Yaf_Exception_LoadFailed_Module extends Yaf_Exception_LoadFailed {}




class Yaf_Exception_LoadFailed_Controller extends Yaf_Exception_LoadFailed {}




class Yaf_Exception_LoadFailed_Action extends Yaf_Exception_LoadFailed {}




class Yaf_Exception_LoadFailed_View extends Yaf_Exception_LoadFailed {}
