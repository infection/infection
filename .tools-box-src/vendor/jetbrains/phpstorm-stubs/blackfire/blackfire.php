<?php





final class BlackfireProbe
{





public static function getMainInstance() {}






public static function isEnabled() {}






public static function addMarker($markerName = '') {}











public function __construct($query, $envId = null, $envToken = null, $agentSocket = null) {}








public function isVerified() {}




public function setConfiguration($configuration) {}










public function getResponseLine() {}






public function enable() {}








public function discard() {}









public function disable() {}






public function close() {}







public function createSubProfileQuery() {}








public static function setTransactionName(string $transactionName) {}






public static function ignoreTransaction() {}






public static function startTransaction() {}






public static function stopTransaction() {}
}
