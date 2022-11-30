<?php



define('GEARMAN_DEFAULT_TCP_HOST', '127.0.0.1');




define('GEARMAN_DEFAULT_TCP_PORT', 4730);




define('GEARMAN_DEFAULT_SOCKET_TIMEOUT', 10);




define('GEARMAN_DEFAULT_SOCKET_SEND_SIZE', 32768);




define('GEARMAN_DEFAULT_SOCKET_RECV_SIZE', 32768);




define('GEARMAN_MAX_ERROR_SIZE', 1024);




define('GEARMAN_PACKET_HEADER_SIZE', 12);




define('GEARMAN_JOB_HANDLE_SIZE', 64);




define('GEARMAN_OPTION_SIZE', 64);




define('GEARMAN_UNIQUE_SIZE', 64);




define('GEARMAN_MAX_COMMAND_ARGS', 8);




define('GEARMAN_ARGS_BUFFER_SIZE', 128);




define('GEARMAN_SEND_BUFFER_SIZE', 8192);




define('GEARMAN_RECV_BUFFER_SIZE', 8192);




define('GEARMAN_WORKER_WAIT_TIMEOUT', 10000);




define('GEARMAN_SUCCESS', 0);




define('GEARMAN_IO_WAIT', 1);




define('GEARMAN_SHUTDOWN', 2);




define('GEARMAN_SHUTDOWN_GRACEFUL', 3);




define('GEARMAN_ERRNO', 4);




define('GEARMAN_EVENT', 5);




define('GEARMAN_TOO_MANY_ARGS', 6);




define('GEARMAN_NO_ACTIVE_FDS', 7);




define('GEARMAN_INVALID_MAGIC', 8);




define('GEARMAN_INVALID_COMMAND', 9);




define('GEARMAN_INVALID_PACKET', 10);




define('GEARMAN_UNEXPECTED_PACKET', 11);




define('GEARMAN_GETADDRINFO', 12);




define('GEARMAN_NO_SERVERS', 13);




define('GEARMAN_LOST_CONNECTION', 14);




define('GEARMAN_MEMORY_ALLOCATION_FAILURE', 15);




define('GEARMAN_JOB_EXISTS', 16);




define('GEARMAN_JOB_QUEUE_FULL', 17);




define('GEARMAN_SERVER_ERROR', 18);




define('GEARMAN_WORK_ERROR', 19);




define('GEARMAN_WORK_DATA', 20);




define('GEARMAN_WORK_WARNING', 21);




define('GEARMAN_WORK_STATUS', 22);




define('GEARMAN_WORK_EXCEPTION', 23);




define('GEARMAN_WORK_FAIL', 24);




define('GEARMAN_NOT_CONNECTED', 25);




define('GEARMAN_COULD_NOT_CONNECT', 26);




define('GEARMAN_SEND_IN_PROGRESS', 27);




define('GEARMAN_RECV_IN_PROGRESS', 28);




define('GEARMAN_NOT_FLUSHING', 29);




define('GEARMAN_DATA_TOO_LARGE', 30);




define('GEARMAN_INVALID_FUNCTION_NAME', 31);




define('GEARMAN_INVALID_WORKER_FUNCTION', 32);




define('GEARMAN_NO_REGISTERED_FUNCTIONS', 34);




define('GEARMAN_NO_JOBS', 35);




define('GEARMAN_ECHO_DATA_CORRUPTION', 36);




define('GEARMAN_NEED_WORKLOAD_FN', 37);




define('GEARMAN_PAUSE', 38);




define('GEARMAN_UNKNOWN_STATE', 39);




define('GEARMAN_PTHREAD', 40);




define('GEARMAN_PIPE_EOF', 41);




define('GEARMAN_QUEUE_ERROR', 42);




define('GEARMAN_FLUSH_DATA', 43);




define('GEARMAN_SEND_BUFFER_TOO_SMALL', 44);




define('GEARMAN_IGNORE_PACKET', 45);




define('GEARMAN_UNKNOWN_OPTION', 46);




define('GEARMAN_TIMEOUT', 47);




define('GEARMAN_MAX_RETURN', 49);




define('GEARMAN_VERBOSE_NEVER', 0);




define('GEARMAN_VERBOSE_FATAL', 1);




define('GEARMAN_VERBOSE_ERROR', 2);




define('GEARMAN_VERBOSE_INFO', 3);




define('GEARMAN_VERBOSE_DEBUG', 4);




define('GEARMAN_VERBOSE_CRAZY', 5);




define('GEARMAN_VERBOSE_MAX', 6);




define('GEARMAN_NON_BLOCKING', 0);




define('GEARMAN_DONT_TRACK_PACKETS', 1);




define('GEARMAN_CON_READY', 0);




define('GEARMAN_CON_PACKET_IN_USE', 1);




define('GEARMAN_CON_EXTERNAL_FD', 2);




define('GEARMAN_CON_IGNORE_LOST_CONNECTION', 3);




define('GEARMAN_CON_CLOSE_AFTER_FLUSH', 4);




define('GEARMAN_CON_SEND_STATE_NONE', 0);




define('GEARMAN_CON_RECV_STATE_READ_DATA', 2);




define('GEARMAN_MAGIC_TEXT', 0);




define('GEARMAN_MAGIC_REQUEST', 1);




define('GEARMAN_MAGIC_RESPONSE', 2);




define('GEARMAN_COMMAND_TEXT', 0);




define('GEARMAN_COMMAND_CAN_DO', 1);




define('GEARMAN_COMMAND_CANT_DO', 2);




define('GEARMAN_COMMAND_RESET_ABILITIES', 3);




define('GEARMAN_COMMAND_PRE_SLEEP', 4);




define('GEARMAN_COMMAND_UNUSED', 5);




define('GEARMAN_COMMAND_NOOP', 6);




define('GEARMAN_COMMAND_SUBMIT_JOB', 7);




define('GEARMAN_COMMAND_JOB_CREATED', 8);




define('GEARMAN_COMMAND_GRAB_JOB', 9);




define('GEARMAN_COMMAND_NO_JOB', 10);




define('GEARMAN_COMMAND_JOB_ASSIGN', 11);




define('GEARMAN_COMMAND_WORK_STATUS', 12);




define('GEARMAN_COMMAND_WORK_COMPLETE', 13);




define('GEARMAN_COMMAND_WORK_FAIL', 14);




define('GEARMAN_COMMAND_GET_STATUS', 15);




define('GEARMAN_COMMAND_ECHO_REQ', 16);




define('GEARMAN_COMMAND_ECHO_RES', 17);




define('GEARMAN_COMMAND_SUBMIT_JOB_BG', 18);




define('GEARMAN_COMMAND_ERROR', 19);




define('GEARMAN_COMMAND_STATUS_RES', 20);




define('GEARMAN_COMMAND_SUBMIT_JOB_HIGH', 21);




define('GEARMAN_COMMAND_SET_CLIENT_ID', 22);




define('GEARMAN_COMMAND_CAN_DO_TIMEOUT', 23);




define('GEARMAN_COMMAND_ALL_YOURS', 24);




define('GEARMAN_COMMAND_WORK_EXCEPTION', 25);




define('GEARMAN_COMMAND_OPTION_REQ', 26);




define('GEARMAN_COMMAND_OPTION_RES', 27);




define('GEARMAN_COMMAND_WORK_DATA', 28);




define('GEARMAN_COMMAND_WORK_WARNING', 29);




define('GEARMAN_COMMAND_GRAB_JOB_UNIQ', 30);




define('GEARMAN_COMMAND_JOB_ASSIGN_UNIQ', 31);




define('GEARMAN_COMMAND_SUBMIT_JOB_HIGH_BG', 32);




define('GEARMAN_COMMAND_SUBMIT_JOB_LOW', 33);




define('GEARMAN_COMMAND_SUBMIT_JOB_LOW_BG', 34);




define('GEARMAN_COMMAND_SUBMIT_JOB_SCHED', 35);




define('GEARMAN_COMMAND_SUBMIT_JOB_EPOCH', 36);




define('GEARMAN_COMMAND_MAX', 37);




define('GEARMAN_TASK_STATE_NEW', 0);




define('GEARMAN_TASK_STATE_SUBMIT', 1);




define('GEARMAN_TASK_STATE_WORKLOAD', 2);




define('GEARMAN_TASK_STATE_WORK', 3);




define('GEARMAN_TASK_STATE_CREATED', 4);




define('GEARMAN_TASK_STATE_DATA', 5);




define('GEARMAN_TASK_STATE_WARNING', 6);




define('GEARMAN_TASK_STATE_STATUS', 7);




define('GEARMAN_TASK_STATE_COMPLETE', 8);




define('GEARMAN_TASK_STATE_EXCEPTION', 9);




define('GEARMAN_TASK_STATE_FAIL', 10);




define('GEARMAN_TASK_STATE_FINISHED', 11);




define('GEARMAN_JOB_PRIORITY_HIGH', 0);




define('GEARMAN_JOB_PRIORITY_NORMAL', 1);




define('GEARMAN_JOB_PRIORITY_LOW', 2);




define('GEARMAN_JOB_PRIORITY_MAX', 3);




define('GEARMAN_CLIENT_ALLOCATED', 1);




define('GEARMAN_CLIENT_NON_BLOCKING', 2);




define('GEARMAN_CLIENT_TASK_IN_USE', 4);




define('GEARMAN_CLIENT_UNBUFFERED_RESULT', 8);




define('GEARMAN_CLIENT_NO_NEW', 16);




define('GEARMAN_CLIENT_FREE_TASKS', 32);




define('GEARMAN_CLIENT_STATE_IDLE', 0);




define('GEARMAN_CLIENT_STATE_NEW', 1);




define('GEARMAN_CLIENT_STATE_SUBMIT', 2);




define('GEARMAN_CLIENT_STATE_PACKET', 3);




define('GEARMAN_WORKER_ALLOCATED', 1);




define('GEARMAN_WORKER_NON_BLOCKING', 2);




define('GEARMAN_WORKER_PACKET_INIT', 4);




define('GEARMAN_WORKER_GRAB_JOB_IN_USE', 8);




define('GEARMAN_WORKER_PRE_SLEEP_IN_USE', 16);




define('GEARMAN_WORKER_WORK_JOB_IN_USE', 32);




define('GEARMAN_WORKER_CHANGE', 64);




define('GEARMAN_WORKER_GRAB_UNIQ', 128);




define('GEARMAN_WORKER_TIMEOUT_RETURN', 256);




define('GEARMAN_WORKER_STATE_START', 0);




define('GEARMAN_WORKER_STATE_FUNCTION_SEND', 1);




define('GEARMAN_WORKER_STATE_CONNECT', 2);




define('GEARMAN_WORKER_STATE_GRAB_JOB_SEND', 3);




define('GEARMAN_WORKER_STATE_GRAB_JOB_RECV', 4);




define('GEARMAN_WORKER_STATE_PRE_SLEEP', 5);

function gearman_version() {}

function gearman_bugreport() {}




function gearman_verbose_name($verbose) {}




function gearman_client_return_code($client_object) {}




function gearman_client_create($client_object) {}




function gearman_client_clone($client_object) {}




function gearman_client_error($client_object) {}




function gearman_client_errno($client_object) {}




function gearman_client_options($client_object) {}





function gearman_client_set_options($client_object, $option) {}





function gearman_client_add_options($client_object, $option) {}





function gearman_client_remove_options($client_object, $option) {}




function gearman_client_timeout($client_object) {}





function gearman_client_set_timeout($client_object, $timeout) {}




function gearman_client_context($client_object) {}





function gearman_client_set_context($client_object, $context) {}






function gearman_client_add_server($client_object, $host, $port) {}





function gearman_client_add_servers($client_object, $servers) {}




function gearman_client_wait($client_object) {}







function gearman_client_do($client_object, $function_name, $workload, $unique) {}







function gearman_client_do_high($client_object, $function_name, $workload, $unique) {}







function gearman_client_do_normal($client_object, $function_name, $workload, $unique) {}







function gearman_client_do_low($client_object, $function_name, $workload, $unique) {}




function gearman_client_do_job_handle($client_object) {}




function gearman_client_do_status($client_object) {}







function gearman_client_do_background($client_object, $function_name, $workload, $unique) {}







function gearman_client_do_high_background($client_object, $function_name, $workload, $unique) {}







function gearman_client_do_low_background($client_object, $function_name, $workload, $unique) {}





function gearman_client_job_status($client_object, $job_handle) {}





function gearman_client_echo($client_object, $workload) {}








function gearman_client_add_task($client_object, $function_name, $workload, $context, $unique) {}








function gearman_client_add_task_high($client_object, $function_name, $workload, $context, $unique) {}








function gearman_client_add_task_low($client_object, $function_name, $workload, $context, $unique) {}








function gearman_client_add_task_background($client_object, $function_name, $workload, $context, $unique) {}








function gearman_client_add_task_high_background($client_object, $function_name, $workload, $context, $unique) {}








function gearman_client_add_task_low_background($client_object, $function_name, $workload, $context, $unique) {}






function gearman_client_add_task_status($client_object, $job_handle, $context) {}





function gearman_client_set_workload_fn($client_object, $callback) {}





function gearman_client_set_created_fn($client_object, $callback) {}





function gearman_client_set_data_fn($client_object, $callback) {}





function gearman_client_set_warning_fn($client_object, $callback) {}





function gearman_client_set_status_fn($client_object, $callback) {}





function gearman_client_set_complete_fn($client_object, $callback) {}





function gearman_client_set_exception_fn($client_object, $callback) {}





function gearman_client_set_fail_fn($client_object, $callback) {}




function gearman_client_clear_fn($client_object) {}




function gearman_client_run_tasks($data) {}




function gearman_task_return_code($task_object) {}




function gearman_task_function_name($task_object) {}




function gearman_task_unique($task_object) {}




function gearman_task_job_handle($task_object) {}




function gearman_task_is_known($task_object) {}




function gearman_task_is_running($task_object) {}




function gearman_task_numerator($task_object) {}




function gearman_task_denominator($task_object) {}





function gearman_task_send_workload($task_object, $data) {}




function gearman_task_data($task_object) {}




function gearman_task_data_size($task_object) {}





function gearman_task_recv_data($task_object, $data_len) {}




function gearman_worker_return_code($worker_object) {}

function gearman_worker_create() {}




function gearman_worker_clone($worker_object) {}




function gearman_worker_error($worker_object) {}




function gearman_worker_errno($worker_object) {}




function gearman_worker_options($worker_object) {}





function gearman_worker_set_options($worker_object, $option) {}





function gearman_worker_add_options($worker_object, $option) {}





function gearman_worker_remove_options($worker_object, $option) {}




function gearman_worker_timeout($worker_object) {}





function gearman_worker_set_timeout($worker_object, $timeout) {}






function gearman_worker_add_server($worker_object, $host, $port) {}





function gearman_worker_add_servers($worker_object, $servers) {}




function gearman_worker_wait($worker_object) {}






function gearman_worker_register($worker_object, $function_name, $timeout) {}





function gearman_worker_unregister($worker_object, $function_name) {}




function gearman_worker_unregister_all($worker_object) {}




function gearman_worker_grab_job($worker_object) {}








function gearman_worker_add_function($worker_object, $function_name, $function, $data, $timeout) {}




function gearman_worker_work($worker_object) {}





function gearman_worker_echo($worker_object, $workload) {}




function gearman_job_return_code($job_object) {}





function gearman_job_send_data($job_object, $data) {}





function gearman_job_send_warning($job_object, $warning) {}






function gearman_job_send_status($job_object, $numerator, $denominator) {}





function gearman_job_send_complete($job_object, $result) {}





function gearman_job_send_exception($job_object, $exception) {}




function gearman_job_send_fail($job_object) {}





function gearman_job_handle($job_object) {}




function gearman_job_function_name($job_object) {}




function gearman_job_unique($job_object) {}




function gearman_job_workload($job_object) {}




function gearman_job_workload_size($job_object) {}




class GearmanClient
{






public function __construct() {}







public function returnCode() {}







public function error() {}







public function getErrno() {}

public function options() {}








public function setOptions($options) {}








public function addOptions($options) {}








public function removeOptions($options) {}








public function timeout() {}








public function setTimeout($timeout) {}







public function context() {}









public function setContext($context) {}










public function addServer($host = '127.0.0.1', $port = 4730) {}










public function addServers($servers = '127.0.0.1:4730') {}

public function wait() {}













public function doHigh($function_name, $workload, $unique = null) {}













public function doNormal($function_name, $workload, $unique = null) {}













public function doLow($function_name, $workload, $unique = null) {}









public function doJobHandle() {}









public function doStatus() {}











public function doBackground($function_name, $workload, $unique = null) {}












public function doHighBackground($function_name, $workload, $unique = null) {}












public function doLowBackground($function_name, $workload, $unique = null) {}












public function jobStatus($job_handle) {}














public function addTask($function_name, $workload, $context = null, $unique = null) {}














public function addTaskHigh($function_name, $workload, $context = null, $unique = null) {}














public function addTaskLow($function_name, $workload, $context = null, $unique = null) {}













public function addTaskBackground($function_name, $workload, $context = null, $unique = null) {}














public function addTaskHighBackground($function_name, $workload, $context = null, $unique = null) {}














public function addTaskLowBackground($function_name, $workload, $context = null, $unique = null) {}











public function addTaskStatus($job_handle, $context = null) {}











public function setWorkloadCallback($callback) {}









public function setCreatedCallback($callback) {}









public function setDataCallback($callback) {}









public function setWarningCallback($callback) {}









public function setStatusCallback($callback) {}









public function setCompleteCallback($callback) {}








public function setExceptionCallback($callback) {}









public function setFailCallback($callback) {}







public function clearCallbacks() {}











public function runTasks() {}









public function ping($workload) {}
}




class GearmanTask
{






public function returnCode() {}








public function functionName() {}









public function unique() {}







public function jobHandle() {}








public function isKnown() {}







public function isRunning() {}








public function taskNumerator() {}








public function taskDenominator() {}








public function sendWorkload($data) {}







public function data() {}







public function dataSize() {}









public function recvData($data_len) {}
}




class GearmanWorker
{






public function __construct() {}







public function returnCode() {}







public function error() {}







public function getErrno() {}







public function options() {}








public function setOptions($option) {}








public function addOptions($option) {}








public function removeOptions($option) {}








public function timeout() {}









public function setTimeout($timeout) {}









public function setId($id) {}










public function addServer($host = '127.0.0.1', $port = 4730) {}










public function addServers($servers = '127.0.0.1:4730') {}









public function wait() {}












public function register($function_name, $timeout) {}










public function unregister($function_name) {}








public function unregisterAll() {}

public function grabJob() {}
















public function addFunction($function_name, $function, $context = null, $timeout = 0) {}









public function work() {}
}




class GearmanJob
{






public function returnCode() {}








public function setReturn($gearman_return_t) {}








public function sendData($data) {}








public function sendWarning($warning) {}












public function sendStatus($numerator, $denominator) {}








public function sendComplete($result) {}








public function sendException($exception) {}








public function sendFail() {}







public function handle() {}








public function functionName() {}








public function unique() {}








public function workload() {}








public function workloadSize() {}
}




class GearmanException extends Exception {}
