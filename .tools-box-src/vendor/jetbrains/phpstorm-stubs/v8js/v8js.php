<?php

class V8Js
{


public const V8_VERSION = '';
public const FLAG_NONE = 1;
public const FLAG_FORCE_ARRAY = 2;
public const FLAG_PROPAGATE_PHP_EXCEPTIONS = 4;












public function __construct($object_name = "PHP", array $variables = [], array $extensions = [], $report_uncaught_exceptions = true, $snapshot_blob = null) {}






public function setModuleLoader(callable $loader) {}











public function setModuleNormaliser(callable $normaliser) {}











public function executeString($script, $identifier = '', $flags = V8Js::FLAG_NONE, $time_limit = 0, $memory_limit = 0) {}







public function compileString($script, $identifier = '') {}









public function executeScript($script, $flags = V8Js::FLAG_NONE, $time_limit = 0, $memory_limit = 0) {}






public function setTimeLimit($limit) {}





public function setMemoryLimit($limit) {}






public function setAverageObjectSize($average_object_size) {}





public function getPendingException() {}




public function clearPendingException() {}













public static function registerExtension($extension_name, $code, array $dependencies, $auto_enable = false) {}





public static function getExtensions() {}








public static function createSnapshot($embed_source) {}
}

final class V8JsScriptException extends Exception
{



final public function getJsFileName() {}




final public function getJsLineNumber() {}




final public function getJsStartColumn() {}




final public function getJsEndColumn() {}




final public function getJsSourceLine() {}




final public function getJsTrace() {}
}

final class V8JsTimeLimitException extends Exception {}

final class V8JsMemoryLimitException extends Exception {}
