<?php











use JetBrains\PhpStorm\Deprecated;

define('AMQP_NOPARAM', 0);






define('AMQP_JUST_CONSUME', 1);




define('AMQP_DURABLE', 2);




define('AMQP_PASSIVE', 4);




define('AMQP_EXCLUSIVE', 8);








define('AMQP_AUTODELETE', 16);




define('AMQP_INTERNAL', 32);




define('AMQP_NOLOCAL', 64);





define('AMQP_AUTOACK', 128);




define('AMQP_IFEMPTY', 256);





define('AMQP_IFUNUSED', 512);




define('AMQP_MANDATORY', 1024);




define('AMQP_IMMEDIATE', 2048);







define('AMQP_MULTIPLE', 4096);





define('AMQP_NOWAIT', 8192);




define('AMQP_REQUEUE', 16384);




define('AMQP_EX_TYPE_DIRECT', 'direct');




define('AMQP_EX_TYPE_FANOUT', 'fanout');




define('AMQP_EX_TYPE_TOPIC', 'topic');




define('AMQP_EX_TYPE_HEADERS', 'headers');




define('AMQP_OS_SOCKET_TIMEOUT_ERRNO', 536870923);




define('PHP_AMQP_MAX_CHANNELS', 256);




define('AMQP_SASL_METHOD_PLAIN', 0);




define('AMQP_SASL_METHOD_EXTERNAL', 1);




class AMQPBasicProperties
{
















public function __construct(
$content_type = "",
$content_encoding = "",
array $headers = [],
$delivery_mode = 2,
$priority = 0,
$correlation_id = "",
$reply_to = "",
$expiration = "",
$message_id = "",
$timestamp = 0,
$type = "",
$user_id = "",
$app_id = "",
$cluster_id = ""
) {}






public function getContentType() {}






public function getContentEncoding() {}






public function getHeaders() {}






public function getDeliveryMode() {}






public function getPriority() {}






public function getCorrelationId() {}






public function getReplyTo() {}






public function getExpiration() {}






public function getMessageId() {}






public function getTimestamp() {}






public function getType() {}






public function getUserId() {}






public function getAppId() {}






public function getClusterId() {}
}




class AMQPChannel
{









public function commitTransaction() {}











public function __construct(AMQPConnection $amqp_connection) {}






public function isConnected() {}




public function close() {}






public function getChannelId() {}
























public function qos($size, $count, $global = false) {}













public function rollbackTransaction() {}













public function setPrefetchCount($count) {}






public function getPrefetchCount() {}

















public function setPrefetchSize($size) {}






public function getPrefetchSize() {}













public function setGlobalPrefetchCount($count) {}






public function getGlobalPrefetchCount() {}

















public function setGlobalPrefetchSize($size) {}






public function getGlobalPrefetchSize() {}











public function startTransaction() {}






public function getConnection() {}






public function basicRecover($requeue = true) {}




public function confirmSelect() {}

















public function setConfirmCallback(callable $ack_callback = null, callable $nack_callback = null) {}










public function waitForConfirm($timeout = 0.0) {}

















public function setReturnCallback(callable $return_callback = null) {}








public function waitForBasicReturn($timeout = 0.0) {}






public function getConsumers() {}
}




class AMQPChannelException extends AMQPException {}




class AMQPConnection
{








public function connect() {}







































public function __construct(array $credentials = []) {}








public function disconnect() {}






public function getHost() {}






public function getLogin() {}






public function getPassword() {}






public function getPort() {}






public function getVhost() {}








public function isConnected() {}










public function pconnect() {}











public function pdisconnect() {}






public function reconnect() {}






public function preconnect() {}










public function setHost($host) {}











public function setLogin($login) {}











public function setPassword($password) {}











public function setPort($port) {}











public function setVhost($vhost) {}










#[Deprecated(replacement: "%class%->setReadTimout(%parameter0%)")]
public function setTimeout($timeout) {}







#[Deprecated(replacement: '%class%->getReadTimout(%parameter0%)')]
public function getTimeout() {}










public function setReadTimeout($timeout) {}







public function getReadTimeout() {}










public function setWriteTimeout($timeout) {}







public function getWriteTimeout() {}










public function setRpcTimeout($timeout) {}







public function getRpcTimeout() {}






public function getUsedChannels() {}









public function getMaxChannels() {}









public function getMaxFrameSize() {}









public function getHeartbeatInterval() {}








public function isPersistent() {}






public function getCACert() {}






public function setCACert($cacert) {}






public function getCert() {}






public function setCert($cert) {}






public function getKey() {}






public function setKey($key) {}






public function getVerify() {}






public function setVerify($verify) {}






public function setSaslMethod($method) {}






public function getSaslMethod() {}





public function getConnectionName() {}






public function setConnectionName($connection_name) {}
}




class AMQPConnectionException extends AMQPException {}




final class AMQPDecimal
{
public const EXPONENT_MIN = 0;
public const EXPONENT_MAX = 255;
public const SIGNIFICAND_MIN = 0;
public const SIGNIFICAND_MAX = 4294967295;







public function __construct($exponent, $significand) {}


public function getExponent() {}


public function getSignificand() {}
}




class AMQPEnvelope extends AMQPBasicProperties
{
public function __construct() {}






public function getBody() {}






public function getRoutingKey() {}






public function getConsumerTag() {}






public function getDeliveryTag() {}






public function getExchangeName() {}











public function isRedelivery() {}









public function getHeader($header_key) {}








public function hasHeader($header_key) {}
}




class AMQPEnvelopeException extends AMQPException
{



public $envelope;
}




class AMQPException extends Exception {}




class AMQPExchange
{














public function bind($exchange_name, $routing_key = '', array $arguments = []) {}















public function unbind($exchange_name, $routing_key = '', array $arguments = []) {}















public function __construct(AMQPChannel $amqp_channel) {}










public function declareExchange() {}
















public function delete($exchangeName = null, $flags = AMQP_NOPARAM) {}










public function getArgument($key) {}








public function hasArgument($key) {}






public function getArguments() {}







public function getFlags() {}






public function getName() {}






public function getType() {}






















public function publish(
$message,
$routing_key = null,
$flags = AMQP_NOPARAM,
array $attributes = []
) {}









public function setArgument($key, $value) {}








public function setArguments(array $arguments) {}











public function setFlags($flags) {}








public function setName($exchange_name) {}











public function setType($exchange_type) {}






public function getChannel() {}






public function getConnection() {}









#[Deprecated]
public function declare() {}
}




class AMQPExchangeException extends AMQPException {}




class AMQPQueue
{

















public function ack($delivery_tag, $flags = AMQP_NOPARAM) {}













public function bind($exchange_name, $routing_key = null, array $arguments = []) {}


















public function cancel($consumer_tag = '') {}










public function __construct(AMQPChannel $amqp_channel) {}








































public function consume(
callable $callback = null,
$flags = AMQP_NOPARAM,
$consumerTag = null
) {}










public function declareQueue() {}
















public function delete($flags = AMQP_NOPARAM) {}
























public function get($flags = AMQP_NOPARAM) {}










public function getArgument($key) {}






public function getArguments() {}







public function getFlags() {}






public function getName() {}
























public function nack($delivery_tag, $flags = AMQP_NOPARAM) {}


















public function reject($delivery_tag, $flags = AMQP_NOPARAM) {}









public function purge() {}









public function setArgument($key, $value) {}










public function setArguments(array $arguments) {}








public function hasArgument($key) {}










public function setFlags($flags) {}








public function setName($queue_name) {}















public function unbind($exchange_name, $routing_key = null, array $arguments = []) {}






public function getChannel() {}






public function getConnection() {}






public function getConsumerTag() {}








#[Deprecated]
public function declare() {}
}




class AMQPQueueException extends AMQPException {}




final class AMQPTimestamp
{
public const MIN = "0";
public const MAX = "18446744073709551616";






public function __construct($timestamp) {}


public function getTimestamp() {}


public function __toString() {}
}




class AMQPExchangeValue extends AMQPException {}




class AMQPValueException extends AMQPException {}
