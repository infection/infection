<?php



class ZendAPI_Queue
{
public $_jobqueue_url;

/**
@removed





*/
public function zendapi_queue($queue_url) {}









public function login($password, $application_id = null) {}









public function addJob($job) {}







public function getJob($job_id) {}









public function updateJob($job) {}







public function removeJob($job_id) {}







public function suspendJob($job_id) {}







public function resumeJob($job_id) {}







public function requeueJob($job) {}













public function getStatistics() {}






public function isScriptExists($path) {}





public function isSuspend() {}













public function getJobsInQueue($filter_options = null, $max_jobs = -1, $with_globals_and_output = false) {}







public function getNumOfJobsInQueue($filter_options = null) {}





public function getAllhosts() {}





public function getAllApplicationIDs() {}














public function getHistoricJobs($status, $start_time, $end_time, $index, $count, &$total) {}





public function suspendQueue() {}





public function resumeQueue() {}






public function getLastError() {}





public function setMaxHistoryTime() {}
}







class ZendAPI_Job
{





public $_id;






public $_script;






public $_host;






public $_name;






public $_output;








public $_status = JOB_QUEUE_STATUS_WAITING;








public $_application_id = null;







public $_priority = JOB_QUEUE_PRIORITY_NORMAL;










public $_user_variables = [];














public $_global_variables = 0;









public $_predecessor = null;








public $_scheduled_time = 0;








public $_interval = 0;










public $_end_time = null;







public $_preserved = 0;

/**
@removed





*/
public function ZendAPI_Job($script) {}













public function addJobToQueue($jobqueue_url, $password) {}






public function setJobPriority($priority) {}


public function setJobName($name) {}

public function setScript($script) {}

public function setApplicationID($app_id) {}

public function setUserVariables($vars) {}

public function setGlobalVariables($vars) {}

public function setJobDependency($job_id) {}

public function setScheduledTime($timestamp) {}

public function setRecurrenceData($interval, $end_time = null) {}

public function setPreserved($preserved) {}






public function getProperties() {}






public function getOutput() {}


public function getID() {}

public function getHost() {}

public function getScript() {}

public function getJobPriority() {}

public function getJobName() {}

public function getApplicationID() {}

public function getUserVariables() {}

public function getGlobalVariables() {}

public function getJobDependency() {}

public function getScheduledTime() {}

public function getInterval() {}

public function getEndTime() {}

public function getPreserved() {}










public function getJobStatus() {}







public function getTimeToNextRepeat() {}







public function getLastPerformedStatus() {}
}






function accelerator_set_status($status) {}





function output_cache_disable() {}







function output_cache_disable_compression() {}








function output_cache_fetch($key, $function, $lifetime) {}








function output_cache_output($key, $function, $lifetime) {}






function output_cache_remove($filename) {}






function output_cache_remove_url($url) {}






function output_cache_remove_key($key) {}







function output_cache_put($key, $data) {}







function output_cache_get($key, $lifetime) {}








function output_cache_exists($key, $lifetime) {}






function output_cache_stop() {}












function monitor_pass_error($errno, $errstr, $errfile, $errline) {}






function monitor_set_aggregation_hint($hint) {}









function monitor_custom_event($class, $text, $severe = null, $user_data = null) {}








function monitor_httperror_event($error_code, $url, $severe = null) {}







function monitor_license_info() {}












function register_event_handler($event_handler_func, $handler_register_name, $event_type_mask) {}






function unregister_event_handler($handler_name) {}








function zend_send_file($filename, $mime_type, $custom_headers) {}








function zend_send_buffer($buffer, $mime_type, $custom_headers) {}

class java
{
/**
@removed





*/
public function java($classname) {}
};

class JavaException
{





public function getCause() {}
};
