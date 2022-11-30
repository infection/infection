<?php




class Stomp
{








public function __construct($broker = null, $username = null, $password = null, array $headers = []) {}






public function getSessionId() {}






public function disconnect() {}









public function send($destination, $msg, array $headers = []) {}








public function subscribe($destination, array $headers = []) {}








public function unsubscribe($destination, array $headers = []) {}






public function hasFrame() {}







public function readFrame($className = 'stompFrame') {}







public function begin($transaction_id) {}







public function commit($transaction_id) {}







public function abort($transaction_id) {}








public function ack($msg, array $headers = []) {}






public function error() {}








public function setTimeout($seconds, $microseconds = 0) {}






public function getTimeout() {}
}

class StompFrame
{




public $command;





public $headers;





public $body;
}

class StompException extends Exception
{





public function getDetails() {}
}






function stomp_version() {}










function stomp_connect($broker = null, $username = null, $password = null, array $headers = []) {}







function stomp_get_session_id($link) {}







function stomp_close($link) {}










function stomp_send($link, $destination, $msg, array $headers = []) {}









function stomp_subscribe($link, $destination, array $headers = []) {}









function stomp_unsubscribe($link, $destination, array $headers = []) {}







function stomp_has_frame($link) {}







function stomp_read_frame($link) {}








function stomp_begin($link, $transaction_id) {}








function stomp_commit($link, $transaction_id) {}








function stomp_abort($link, $transaction_id) {}









function stomp_ack($link, $msg, array $headers = []) {}







function stomp_error($link) {}









function stomp_set_timeout($link, $seconds, $microseconds = 0) {}







function stomp_get_timeout($link) {}
