<?php









define('E_ERROR', 1);









define('E_RECOVERABLE_ERROR', 4096);






define('E_WARNING', 2);






define('E_PARSE', 4);







define('E_NOTICE', 8);







define('E_STRICT', 2048);






define('E_DEPRECATED', 8192);






define('E_CORE_ERROR', 16);







define('E_CORE_WARNING', 32);






define('E_COMPILE_ERROR', 64);







define('E_COMPILE_WARNING', 128);







define('E_USER_ERROR', 256);







define('E_USER_WARNING', 512);







define('E_USER_NOTICE', 1024);







define('E_USER_DEPRECATED', 16384);








define('E_ALL', 32767);
define('DEBUG_BACKTRACE_PROVIDE_OBJECT', 1);
define('DEBUG_BACKTRACE_IGNORE_ARGS', 2);
define('S_MEMORY', 1);
define('S_VARS', 4);
define('S_FILES', 8);
define('S_INCLUDE', 16);
define('S_SQL', 32);
define('S_EXECUTOR', 64);
define('S_MAIL', 128);
define('S_SESSION', 256);
define('S_MISC', 2);
define('S_INTERNAL', 536870912);
define('S_ALL', 511);

define('true', (bool)1, true);
define('false', (bool)0, true);
define('null', null, true);
define('ZEND_THREAD_SAFE', false);
define('ZEND_DEBUG_BUILD', false);
define('PHP_WINDOWS_VERSION_BUILD', 0);
define('PHP_WINDOWS_VERSION_MAJOR', 0);
define('PHP_WINDOWS_VERSION_MINOR', 0);
define('PHP_WINDOWS_VERSION_PLATFORM', 0);
define('PHP_WINDOWS_VERSION_PRODUCTTYPE', 0);
define('PHP_WINDOWS_VERSION_SP_MAJOR', 0);
define('PHP_WINDOWS_VERSION_SP_MINOR', 0);
define('PHP_WINDOWS_VERSION_SUITEMASK', 0);
define('PHP_WINDOWS_NT_DOMAIN_CONTROLLER', 2);
define('PHP_WINDOWS_NT_SERVER', 3);
define('PHP_WINDOWS_NT_WORKSTATION', 1);



define('PHP_WINDOWS_EVENT_CTRL_C', 0);



define('PHP_WINDOWS_EVENT_CTRL_BREAK', 1);
define('PHP_VERSION', "5.3.6-13ubuntu3.2");
define('PHP_MAJOR_VERSION', 5);
define('PHP_MINOR_VERSION', 3);
define('PHP_RELEASE_VERSION', 6);
define('PHP_EXTRA_VERSION', "-13ubuntu3.2");
define('PHP_VERSION_ID', 50306);
define('PHP_ZTS', 1);
define('PHP_DEBUG', 0);
define('PHP_OS', "Linux");




define('PHP_OS_FAMILY', "Linux");
define('PHP_SAPI', "cli");



define('PHP_CLI_PROCESS_TITLE', 1);
define('DEFAULT_INCLUDE_PATH', ".:/usr/share/php:/usr/share/pear");
define('PEAR_INSTALL_DIR', "/usr/share/php");
define('PEAR_EXTENSION_DIR', "/usr/lib/php5/20090626");
define('PHP_EXTENSION_DIR', "/usr/lib/php5/20090626");




define('PHP_BINARY', '/usr/local/php/bin/php');
define('PHP_PREFIX', "/usr");
define('PHP_BINDIR', "/usr/bin");
define('PHP_LIBDIR', "/usr/lib/php5");
define('PHP_DATADIR', "/usr/share");
define('PHP_SYSCONFDIR', "/etc");
define('PHP_LOCALSTATEDIR', "/var");
define('PHP_CONFIG_FILE_PATH', "/etc/php5/cli");
define('PHP_CONFIG_FILE_SCAN_DIR', "/etc/php5/cli/conf.d");
define('PHP_SHLIB_SUFFIX', "so");
define('PHP_EOL', "\n");
define('SUHOSIN_PATCH', 1);
define('SUHOSIN_PATCH_VERSION', "0.9.10");
define('PHP_MAXPATHLEN', 4096);
define('PHP_INT_MAX', 9223372036854775807);
define('PHP_INT_MIN', -9223372036854775808);
define('PHP_INT_SIZE', 8);




define('PHP_FLOAT_DIG', 15);




define('PHP_FLOAT_EPSILON', 2.2204460492503e-16);





define('PHP_FLOAT_MAX', 1.7976931348623e+308);




define('PHP_FLOAT_MIN', 2.2250738585072e-308);
define('ZEND_MULTIBYTE', 0);
define('PHP_OUTPUT_HANDLER_START', 1);
define('PHP_OUTPUT_HANDLER_CONT', 2);
define('PHP_OUTPUT_HANDLER_END', 4);
define('UPLOAD_ERR_OK', 0);
define('UPLOAD_ERR_INI_SIZE', 1);
define('UPLOAD_ERR_FORM_SIZE', 2);
define('UPLOAD_ERR_PARTIAL', 3);
define('UPLOAD_ERR_NO_FILE', 4);
define('UPLOAD_ERR_NO_TMP_DIR', 6);
define('UPLOAD_ERR_CANT_WRITE', 7);
define('UPLOAD_ERR_EXTENSION', 8);
define('STDIN', fopen('php://stdin', 'r'));
define('STDOUT', fopen('php://stdout', 'w'));
define('STDERR', fopen('php://stderr', 'w'));

define('PHP_FD_SETSIZE', 1024);


define('PHP_OUTPUT_HANDLER_WRITE', 0);

define('PHP_OUTPUT_HANDLER_FLUSH', 4);

define('PHP_OUTPUT_HANDLER_CLEAN', 2);

define('PHP_OUTPUT_HANDLER_FINAL', 8);

define('PHP_OUTPUT_HANDLER_CLEANABLE', 16);

define('PHP_OUTPUT_HANDLER_FLUSHABLE', 32);

define('PHP_OUTPUT_HANDLER_REMOVABLE', 64);

define('PHP_OUTPUT_HANDLER_STDFLAGS', 112);

define('PHP_OUTPUT_HANDLER_STARTED', 4096);

define('PHP_OUTPUT_HANDLER_DISABLED', 8192);






define('PHP_MANDIR', '/usr/local/php/php/man');
