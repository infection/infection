<?php

namespace Mosquitto;




class Client
{
/**
@const */
public const LOG_DEBUG = 16;

/**
@const */
public const LOG_INFO = 1;

/**
@const */
public const LOG_NOTICE = 2;

/**
@const */
public const LOG_WARNING = 4;

/**
@const */
public const LOG_ERR = 8;

/**
@const */
public const SSL_VERIFY_NONE = 0;

/**
@const */
public const SSL_VERIFY_PEER = 1;







public function __construct($id = null, $cleanSession = true) {}







public function setCredentials($username, $password) {}
















public function setTlsCertificates($caPath, $certFile = null, $keyFile = null, $password = null) {}








public function setTlsInsecure($value) {}









public function setTlsOptions($certReqs, $tlsVersion = null, $ciphers = null) {}










public function setTlsPSK($psk, $identity, $ciphers = null) {}










public function setWill($topic, $payload, $qos = 0, $retain = false) {}




public function clearWill() {}










public function setReconnectDelay($reconnectDelay, $exponentialDelay = 0, $exponentialBackoff = false) {}










public function connect($host, $port = 1883, $keepalive = 60, $interface = null) {}




public function disconnect() {}
















public function onConnect($callback) {}














public function onDisconnect($callback) {}
















public function onLog($callback) {}









public function onSubscribe($callback) {}









public function onUnsubscribe($callback) {}









public function onMessage($callback) {}












public function onPublish($callback) {}










public function setMaxInFlightMessages($maxInFlightMessages) {}







public function setMessageRetry($messageRetryPeriod) {}











public function publish($topic, $payload, $qos = 0, $retain = false) {}









public function subscribe($topic, $qos) {}









public function unsubscribe($topic, $qos) {}










public function loop($timeout = 1000) {}









public function loopForever($timeout = 1000) {}





public function exitLoop() {}
}




class Message
{

public $topic;


public $payload;


public $mid;


public $qos;


public $retain;








public static function topicMatchesSub($topic, $subscription) {}







public static function tokeniseTopic($topic) {}
}




class Exception extends \Exception {}
