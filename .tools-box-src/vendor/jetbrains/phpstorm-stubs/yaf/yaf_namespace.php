<?php

namespace {
define('YAF\VERSION', '3.0.8', true);
define('YAF\ENVIRON', 'product', true);
define('YAF\ERR\STARTUP\FAILED', 512, true);
define('YAF\ERR\ROUTE\FAILED', 513, true);
define('YAF\ERR\DISPATCH\FAILED', 514, true);
define('YAF\ERR\NOTFOUND\MODULE', 515, true);
define('YAF\ERR\NOTFOUND\CONTROLLER', 516, true);
define('YAF\ERR\NOTFOUND\ACTION', 517, true);
define('YAF\ERR\NOTFOUND\VIEW', 518, true);
define('YAF\ERR\CALL\FAILED', 519, true);
define('YAF\ERR\AUTOLOAD\FAILED', 520, true);
define('YAF\ERR\TYPE\ERROR', 521, true);
}

namespace Yaf {
use Yaf;











final class Application
{



protected static $_app;




protected $config;




protected $dispatcher;




protected $_modules;




protected $_running = "";




protected $_environ = YAF_ENVIRON;





protected $_err_no = 0;





protected $_err_msg = "";
















































public function __construct($config, $envrion = null) {}








public function run() {}










public function execute(callable $entry, ...$_) {}








public static function app() {}








public function environ() {}









public function bootstrap(Yaf\Bootstrap_Abstract $bootstrap = null) {}






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





final class Dispatcher
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










public function disableView() {}










public function initView($templates_dir, array $options = null) {}









public function setView(Yaf\View_Interface $view) {}







public function setRequest(Yaf\Request_Abstract $request) {}







public function getApplication() {}






public function getRouter() {}






public function getRequest() {}












public function setErrorHandler(callable $callback, $error_types) {}









public function setDefaultModule($module) {}









public function setDefaultController($controller) {}









public function setDefaultAction($action) {}







public function returnResponse($flag) {}











public function autoRender($flag = null) {}









public function flushInstantly($flag = null) {}






public static function getInstance() {}
























public function dispatch(Yaf\Request_Abstract $request) {}










public function throwException($flag = null) {}










public function catchException($flag = null) {}









public function registerPlugin(Yaf\Plugin_Abstract $plugin) {}
}












class Loader
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














public function registerLocalNamespace($name_prefix) {}






public function getLocalNamespace() {}




public function clearLocalNamespace() {}








public function isLocalName($class_name) {}








public static function import($file) {}










public function setLibraryPath($directory, $global = false) {}









public function getLibraryPath($is_global = false) {}
}



final class Registry
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



final class Session implements \Iterator, \Traversable, \ArrayAccess, \Countable
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
}












class Router
{



protected $_routes;




protected $_current;




public function __construct() {}













public function addRoute($name, Yaf\Route_Interface $route) {}










public function addConfig(Yaf\Config_Abstract $config) {}








public function route(Yaf\Request_Abstract $request) {}










public function getRoute($name) {}






public function getRoutes() {}











public function getCurrentRoute() {}
}






abstract class Bootstrap_Abstract {}















abstract class Controller_Abstract
{




public $actions;




protected $_module;




protected $_name;




protected $_request;




protected $_response;




protected $_invoke_args;




protected $_view;









protected function render($tpl, array $parameters = null) {}









protected function display($tpl, array $parameters = null) {}








public function getRequest() {}








public function getResponse() {}








public function getModuleName() {}








public function getView() {}









public function initView(array $options = null) {}








public function setViewpath($view_directory) {}






public function getViewpath() {}





















public function forward($module, $controller = null, $action = null, array $parameters = null) {}










public function redirect($url) {}






public function getInvokeArgs() {}







public function getInvokeArg($name) {}






public function init() {}












final public function __construct(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response, Yaf\View_Interface $view, array $invokeArgs = null) {}




final private function __clone() {}
}






abstract class Action_Abstract extends \Yaf\Controller_Abstract
{



protected $_controller;











abstract public function execute();








public function getController() {}
}


abstract class Config_Abstract
{



protected $_config = null;




protected $_readonly = true;







abstract public function get($name = null);








abstract public function set($name, $value);






abstract public function readonly();






abstract public function toArray();
}


abstract class Request_Abstract
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






public function isPost() {}






public function isPut() {}






public function isHead() {}






public function isOptions() {}






public function isCli() {}






public function isDispatched() {}






public function isRouted() {}






public function isXmlHttpRequest() {}











public function getServer($name = null, $default = null) {}











public function getEnv($name = null, $default = null) {}









public function getParam($name, $default = null) {}






public function getParams() {}






public function getException() {}






public function getModuleName() {}






public function getControllerName() {}






public function getActionName() {}









public function setParam($name, $value = null) {}








public function setModuleName($module) {}








public function setControllerName($controller) {}








public function setActionName($action) {}






public function getMethod() {}






public function getLanguage() {}













public function setBaseUri($uri) {}






public function getBaseUri() {}






public function getRequestUri() {}







public function setRequestUri($uri) {}








public function setDispatched() {}








public function setRouted() {}
}







abstract class Plugin_Abstract
{










public function routerStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {}











public function routerShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {}









public function dispatchLoopStartup(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {}











public function dispatchLoopShutdown(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {}









public function preDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {}









public function postDispatch(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {}









public function preResponse(Yaf\Request_Abstract $request, Yaf\Response_Abstract $response) {}
}


abstract class Response_Abstract
{
public const DEFAULT_BODY = "content";




protected $_header;




protected $_body;




protected $_sendheader;




public function __construct() {}




public function __destruct() {}




private function __clone() {}




public function __toString() {}














public function setBody($content, $key = self::DEFAULT_BODY) {}














public function appendBody($content, $key = self::DEFAULT_BODY) {}














public function prependBody($content, $key = self::DEFAULT_BODY) {}













public function clearBody($key = self::DEFAULT_BODY) {}













public function getBody($key = self::DEFAULT_BODY) {}
}




interface View_Interface
{









public function assign($name, $value);










public function display($tpl, array $tpl_vars = null);






public function getScriptPath();










public function render($tpl, array $tpl_vars = null);








public function setScriptPath($template_dir);
}




interface Route_Interface
{











public function route(Yaf\Request_Abstract $request);












public function assemble(array $info, array $query = null);
}


class Exception extends \Exception {}









class Route_Static implements \Yaf\Route_Interface
{








public function match($uri) {}








public function route(Yaf\Request_Abstract $request) {}










public function assemble(array $info, array $query = null) {}
}}

namespace Yaf\Response {
class Http extends \Yaf\Response_Abstract
{



protected $_response_code = 0;

private function __clone() {}




private function __toString() {}











public function setHeader($name, $value, $replace = false, $response_code = 0) {}








public function setAllHeaders(array $headers) {}








public function getHeader($name = null) {}







public function clearHeaders() {}








public function setRedirect($url) {}








public function response() {}
}
class Cli extends \Yaf\Response_Abstract
{
private function __clone() {}




private function __toString() {}
}}

namespace Yaf\Request {



class Http extends \Yaf\Request_Abstract
{










public function getQuery($name = null, $default = null) {}











public function getRequest($name = null, $default = null) {}











public function getPost($name = null, $default = null) {}











public function getCookie($name = null, $default = null) {}











public function getFiles($name = null, $default = null) {}











public function get($name, $default = null) {}













public function isXmlHttpRequest() {}







public function __construct($request_uri, $base_uri) {}




private function __clone() {}
}



class Simple extends \Yaf\Request_Abstract
{










public function getQuery($name = null, $default = null) {}











public function getRequest($name = null, $default = null) {}











public function getPost($name = null, $default = null) {}











public function getCookie($name = null, $default = null) {}







public function getFiles($name = null, $default = null) {}











public function get($name, $default = null) {}













public function isXmlHttpRequest() {}











public function __construct($method, $controller, $action, $params = null) {}




private function __clone() {}
}}

namespace Yaf\Config {






class Ini extends \Yaf\Config_Abstract implements \Iterator, \Traversable, \ArrayAccess, \Countable
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




public function offsetGet($name) {}




public function offsetExists($name) {}




public function offsetSet($name, $value) {}
}


class Simple extends \Yaf\Config_Abstract implements \Iterator, \Traversable, \ArrayAccess, \Countable
{



public function __get($name = null) {}




public function __set($name, $value) {}




public function get($name = null) {}




public function set($name, $value) {}




public function toArray() {}




public function readonly() {}







public function __construct(array $array, $readonly = null) {}





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
}}

namespace Yaf\View {













class Simple implements \Yaf\View_Interface
{



protected $_tpl_dir;




protected $_tpl_vars;




protected $_options;












final public function __construct($template_dir, array $options = null) {}






public function __isset($name) {}










public function assign($name, $value = null) {}











public function render($tpl, array $tpl_vars = null) {}













public function display($tpl, array $tpl_vars = null) {}










public function assignRef($name, &$value) {}









public function clear($name = null) {}








public function setScriptPath($template_dir) {}






public function getScriptPath() {}














public function __get($name = null) {}









public function __set($name, $value = null) {}
}}

namespace Yaf\Route {









final class Simple implements \Yaf\Route_Interface
{



protected $controller;




protected $module;




protected $action;












public function __construct($module_name, $controller_name, $action_name) {}










public function route(Yaf\Request_Abstract $request) {}










public function assemble(array $info, array $query = null) {}
}


final class Supervar implements \Yaf\Route_Interface
{



protected $_var_name;










public function __construct($supervar_name) {}








public function route(Yaf\Request_Abstract $request) {}










public function assemble(array $info, array $query = null) {}
}




final class Rewrite extends \Yaf\Router implements \Yaf\Route_Interface
{



protected $_route;




protected $_default;




protected $_verify;













public function __construct($match, array $route, array $verify = null, $reverse = null) {}








public function route(Yaf\Request_Abstract $request) {}










public function assemble(array $info, array $query = null) {}
}




final class Regex extends \Yaf\Router implements \Yaf\Route_Interface
{



protected $_route;




protected $_default;




protected $_maps;




protected $_verify;




protected $_reverse;














public function __construct($match, array $route, array $map = null, array $verify = null, $reverse = null) {}










public function route(Yaf\Request_Abstract $request) {}










public function assemble(array $info, array $query = null) {}
}






final class Map implements \Yaf\Route_Interface
{



protected $_ctl_router = '';




protected $_delimiter;







public function __construct($controller_prefer = false, $delimiter = '') {}








public function route(Yaf\Request_Abstract $request) {}










public function assemble(array $info, array $query = null) {}
}}

namespace Yaf\Exception {



class TypeError extends \Yaf\Exception {}


class StartupError extends \Yaf\Exception {}


class RouterFailed extends \Yaf\Exception {}


class DispatchFailed extends \Yaf\Exception {}


class LoadFailed extends \Yaf\Exception {}}

namespace Yaf\Exception\LoadFailed {



class Module extends \Yaf\Exception\LoadFailed {}


class Controller extends \Yaf\Exception\LoadFailed {}


class Action extends \Yaf\Exception\LoadFailed {}


class View extends \Yaf\Exception\LoadFailed {}
}
