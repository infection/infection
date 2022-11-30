<?php











namespace Grpc;








const CALL_OK = 0;




const CALL_ERROR = 1;




const CALL_ERROR_NOT_ON_SERVER = 2;




const CALL_ERROR_NOT_ON_CLIENT = 3;




const CALL_ERROR_ALREADY_ACCEPTED = 4;




const CALL_ERROR_ALREADY_INVOKED = 5;




const CALL_ERROR_NOT_INVOKED = 6;





const CALL_ERROR_ALREADY_FINISHED = 7;




const CALL_ERROR_TOO_MANY_OPERATIONS = 8;




const CALL_ERROR_INVALID_FLAGS = 9;




const CALL_ERROR_INVALID_METADATA = 10;




const CALL_ERROR_INVALID_MESSAGE = 11;





const CALL_ERROR_NOT_SERVER_COMPLETION_QUEUE = 12;




const CALL_ERROR_BATCH_TOO_BIG = 13;




const CALL_ERROR_PAYLOAD_TYPE_MISMATCH = 14;










const WRITE_BUFFER_HINT = 1;





const WRITE_NO_COMPRESS = 2;








const STATUS_OK = 0;




const STATUS_CANCELLED = 1;








const STATUS_UNKNOWN = 2;







const STATUS_INVALID_ARGUMENT = 3;








const STATUS_DEADLINE_EXCEEDED = 4;




const STATUS_NOT_FOUND = 5;




const STATUS_ALREADY_EXISTS = 6;









const STATUS_PERMISSION_DENIED = 7;





const STATUS_UNAUTHENTICATED = 16;





const STATUS_RESOURCE_EXHAUSTED = 8;






















const STATUS_FAILED_PRECONDITION = 9;








const STATUS_ABORTED = 10;


















const STATUS_OUT_OF_RANGE = 11;




const STATUS_UNIMPLEMENTED = 12;






const STATUS_INTERNAL = 13;









const STATUS_UNAVAILABLE = 14;




const STATUS_DATA_LOSS = 15;











const OP_SEND_INITIAL_METADATA = 0;






const OP_SEND_MESSAGE = 1;







const OP_SEND_CLOSE_FROM_CLIENT = 2;








const OP_SEND_STATUS_FROM_SERVER = 3;







const OP_RECV_INITIAL_METADATA = 4;






const OP_RECV_MESSAGE = 5;








const OP_RECV_STATUS_ON_CLIENT = 6;








const OP_RECV_CLOSE_ON_SERVER = 7;








const CHANNEL_IDLE = 0;




const CHANNEL_CONNECTING = 1;




const CHANNEL_READY = 2;




const CHANNEL_TRANSIENT_FAILURE = 3;




const CHANNEL_SHUTDOWN = 4;
const CHANNEL_FATAL_FAILURE = 4;





class Server
{





public function __construct(array $args) {}







public function requestCall($tag_new, $tag_cancel) {}








public function addHttp2Port($addr) {}









public function addSecureHttp2Port($addr, $creds_obj) {}




public function start() {}
}





class ServerCredentials
{










public static function createSsl(
$pem_root_certs,
$pem_private_key,
$pem_cert_chain
) {}
}





class Channel
{










public function __construct($target, $args = []) {}






public function getTarget() {}









public function getConnectivityState($try_to_connect = false) {}











public function watchConnectivityState($last_state, Timeval $deadline_obj) {}




public function close() {}
}





class ChannelCredentials
{







public static function setDefaultRootsPem($pem_roots) {}






public static function createDefault() {}











public static function createSsl(
string $pem_root_certs = null,
string $pem_private_key = null,
string $pem_cert_chain = null
) {}










public static function createComposite(
ChannelCredentials $cred1,
CallCredentials $cred2
) {}






public static function createInsecure() {}
}





class Call
{











public function __construct(
Channel $channel,
$method,
Timeval $absolute_deadline,
$host_override = null
) {}










public function startBatch(array $batch) {}









public function setCredentials(CallCredentials $creds_obj) {}






public function getPeer() {}





public function cancel() {}
}





class CallCredentials
{









public static function createComposite(
CallCredentials $cred1,
CallCredentials $cred2
) {}









public static function createFromPlugin(\Closure $callback) {}
}






class Timeval
{





public function __construct($usec) {}










public function add(Timeval $other) {}











public static function compare(Timeval $a, Timeval $b) {}






public static function infFuture() {}






public static function infPast() {}






public static function now() {}











public static function similar(Timeval $a, Timeval $b, Timeval $threshold) {}




public function sleepUntil() {}










public function subtract(Timeval $other) {}






public static function zero() {}
}
