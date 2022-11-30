<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Swoole\Coroutine;

class Redis
{
    public $host = '';
    public $port = 0;
    public $setting;
    public $sock = -1;
    public $connected = \false;
    public $errType = 0;
    public $errCode = 0;
    public $errMsg = '';
    public function __construct($config = null)
    {
    }
    public function __destruct()
    {
    }
    public function connect($host, $port = null, $serialize = null)
    {
    }
    public function getAuth()
    {
    }
    public function getDBNum()
    {
    }
    public function getOptions()
    {
    }
    public function setOptions($options)
    {
    }
    public function getDefer()
    {
    }
    public function setDefer($defer)
    {
    }
    public function recv()
    {
    }
    public function request(array $params)
    {
    }
    public function close()
    {
    }
    public function set($key, $value, $timeout = null, $opt = null)
    {
    }
    public function setBit($key, $offset, $value)
    {
    }
    public function setEx($key, $expire, $value)
    {
    }
    public function psetEx($key, $expire, $value)
    {
    }
    public function lSet($key, $index, $value)
    {
    }
    public function get($key)
    {
    }
    public function mGet($keys)
    {
    }
    public function del($key, $other_keys = null)
    {
    }
    public function hDel($key, $member, $other_members = null)
    {
    }
    public function hSet($key, $member, $value)
    {
    }
    public function hMSet($key, $pairs)
    {
    }
    public function hSetNx($key, $member, $value)
    {
    }
    public function delete($key, $other_keys = null)
    {
    }
    public function mSet($pairs)
    {
    }
    public function mSetNx($pairs)
    {
    }
    public function getKeys($pattern)
    {
    }
    public function keys($pattern)
    {
    }
    public function exists($key, $other_keys = null)
    {
    }
    public function type($key)
    {
    }
    public function strLen($key)
    {
    }
    public function lPop($key)
    {
    }
    public function blPop($key, $timeout_or_key, $extra_args = null)
    {
    }
    public function rPop($key)
    {
    }
    public function brPop($key, $timeout_or_key, $extra_args = null)
    {
    }
    public function bRPopLPush($src, $dst, $timeout)
    {
    }
    public function lSize($key)
    {
    }
    public function lLen($key)
    {
    }
    public function sSize($key)
    {
    }
    public function scard($key)
    {
    }
    public function sPop($key)
    {
    }
    public function sMembers($key)
    {
    }
    public function sGetMembers($key)
    {
    }
    public function sRandMember($key, $count = null)
    {
    }
    public function persist($key)
    {
    }
    public function ttl($key)
    {
    }
    public function pttl($key)
    {
    }
    public function zCard($key)
    {
    }
    public function zSize($key)
    {
    }
    public function hLen($key)
    {
    }
    public function hKeys($key)
    {
    }
    public function hVals($key)
    {
    }
    public function hGetAll($key)
    {
    }
    public function debug($key)
    {
    }
    public function restore($ttl, $key, $value)
    {
    }
    public function dump($key)
    {
    }
    public function renameKey($key, $newkey)
    {
    }
    public function rename($key, $newkey)
    {
    }
    public function renameNx($key, $newkey)
    {
    }
    public function rpoplpush($src, $dst)
    {
    }
    public function randomKey()
    {
    }
    public function pfadd($key, $elements)
    {
    }
    public function pfcount($key)
    {
    }
    public function pfmerge($dstkey, $keys)
    {
    }
    public function ping()
    {
    }
    public function auth($password)
    {
    }
    public function unwatch()
    {
    }
    public function watch($key, $other_keys = null)
    {
    }
    public function save()
    {
    }
    public function bgSave()
    {
    }
    public function lastSave()
    {
    }
    public function flushDB()
    {
    }
    public function flushAll()
    {
    }
    public function dbSize()
    {
    }
    public function bgrewriteaof()
    {
    }
    public function time()
    {
    }
    public function role()
    {
    }
    public function setRange($key, $offset, $value)
    {
    }
    public function setNx($key, $value)
    {
    }
    public function getSet($key, $value)
    {
    }
    public function append($key, $value)
    {
    }
    public function lPushx($key, $value)
    {
    }
    public function lPush($key, $value)
    {
    }
    public function rPush($key, $value)
    {
    }
    public function rPushx($key, $value)
    {
    }
    public function sContains($key, $value)
    {
    }
    public function sismember($key, $value)
    {
    }
    public function zScore($key, $member)
    {
    }
    public function zRank($key, $member)
    {
    }
    public function zRevRank($key, $member)
    {
    }
    public function hGet($key, $member)
    {
    }
    public function hMGet($key, $keys)
    {
    }
    public function hExists($key, $member)
    {
    }
    public function publish($channel, $message)
    {
    }
    public function zIncrBy($key, $value, $member)
    {
    }
    public function zAdd($key, $score, $value)
    {
    }
    public function zPopMin($key, $count)
    {
    }
    public function zPopMax($key, $count)
    {
    }
    public function bzPopMin($key, $timeout_or_key, $extra_args = null)
    {
    }
    public function bzPopMax($key, $timeout_or_key, $extra_args = null)
    {
    }
    public function zDeleteRangeByScore($key, $min, $max)
    {
    }
    public function zRemRangeByScore($key, $min, $max)
    {
    }
    public function zCount($key, $min, $max)
    {
    }
    public function zRange($key, $start, $end, $scores = null)
    {
    }
    public function zRevRange($key, $start, $end, $scores = null)
    {
    }
    public function zRangeByScore($key, $start, $end, $options = null)
    {
    }
    public function zRevRangeByScore($key, $start, $end, $options = null)
    {
    }
    public function zRangeByLex($key, $min, $max, $offset = null, $limit = null)
    {
    }
    public function zRevRangeByLex($key, $min, $max, $offset = null, $limit = null)
    {
    }
    public function zInter($key, $keys, $weights = null, $aggregate = null)
    {
    }
    public function zinterstore($key, $keys, $weights = null, $aggregate = null)
    {
    }
    public function zUnion($key, $keys, $weights = null, $aggregate = null)
    {
    }
    public function zunionstore($key, $keys, $weights = null, $aggregate = null)
    {
    }
    public function incrBy($key, $value)
    {
    }
    public function hIncrBy($key, $member, $value)
    {
    }
    public function incr($key)
    {
    }
    public function decrBy($key, $value)
    {
    }
    public function decr($key)
    {
    }
    public function getBit($key, $offset)
    {
    }
    public function lInsert($key, $position, $pivot, $value)
    {
    }
    public function lGet($key, $index)
    {
    }
    public function lIndex($key, $integer)
    {
    }
    public function setTimeout($key, $timeout)
    {
    }
    public function expire($key, $integer)
    {
    }
    public function pexpire($key, $timestamp)
    {
    }
    public function expireAt($key, $timestamp)
    {
    }
    public function pexpireAt($key, $timestamp)
    {
    }
    public function move($key, $dbindex)
    {
    }
    public function select($dbindex)
    {
    }
    public function getRange($key, $start, $end)
    {
    }
    public function listTrim($key, $start, $stop)
    {
    }
    public function ltrim($key, $start, $stop)
    {
    }
    public function lGetRange($key, $start, $end)
    {
    }
    public function lRange($key, $start, $end)
    {
    }
    public function lRem($key, $value, $count)
    {
    }
    public function lRemove($key, $value, $count)
    {
    }
    public function zDeleteRangeByRank($key, $start, $end)
    {
    }
    public function zRemRangeByRank($key, $min, $max)
    {
    }
    public function incrByFloat($key, $value)
    {
    }
    public function hIncrByFloat($key, $member, $value)
    {
    }
    public function bitCount($key)
    {
    }
    public function bitOp($operation, $ret_key, $key, $other_keys = null)
    {
    }
    public function sAdd($key, $value)
    {
    }
    public function sMove($src, $dst, $value)
    {
    }
    public function sDiff($key, $other_keys = null)
    {
    }
    public function sDiffStore($dst, $key, $other_keys = null)
    {
    }
    public function sUnion($key, $other_keys = null)
    {
    }
    public function sUnionStore($dst, $key, $other_keys = null)
    {
    }
    public function sInter($key, $other_keys = null)
    {
    }
    public function sInterStore($dst, $key, $other_keys = null)
    {
    }
    public function sRemove($key, $value)
    {
    }
    public function srem($key, $value)
    {
    }
    public function zDelete($key, $member, $other_members = null)
    {
    }
    public function zRemove($key, $member, $other_members = null)
    {
    }
    public function zRem($key, $member, $other_members = null)
    {
    }
    public function pSubscribe($patterns)
    {
    }
    public function subscribe($channels)
    {
    }
    public function unsubscribe($channels)
    {
    }
    public function pUnSubscribe($patterns)
    {
    }
    public function multi()
    {
    }
    public function exec()
    {
    }
    public function eval($script, $args = null, $num_keys = null)
    {
    }
    public function evalSha($script_sha, $args = null, $num_keys = null)
    {
    }
    public function script($cmd, $args = null)
    {
    }
    public function xLen(string $key)
    {
    }
    public function xAdd(string $key, string $id, array $pairs, array $options = [])
    {
    }
    public function xRead(array $streams, array $options = [])
    {
    }
    public function xDel(string $key, string $id)
    {
    }
    public function xRange(string $key, string $start, string $end, int $count = 0)
    {
    }
    public function xRevRange(string $key, string $start, string $end, int $count = 0)
    {
    }
    public function xTrim(string $key, array $options = [])
    {
    }
    public function xGroupCreate(string $key, string $group_name, string $id, bool $mkstream = \false)
    {
    }
    public function xGroupSetId(string $key, string $group_name, string $id)
    {
    }
    public function xGroupDestroy(string $key, string $group_name)
    {
    }
    public function xGroupCreateConsumer(string $key, string $group_name, string $consumer_name)
    {
    }
    public function xGroupDelConsumer(string $key, string $group_name, string $consumer_name)
    {
    }
    public function xReadGroup(string $group_name, string $consumer_name, array $streams, array $options = [])
    {
    }
    public function xPending(string $key, string $group_name, array $options = [])
    {
    }
    public function xAck(string $key, string $group_name, array $id)
    {
    }
    public function xClaim(string $key, string $group_name, string $consumer_name, int $min_idle_time, array $id, array $options = [])
    {
    }
    public function xAutoClaim(string $key, string $group_name, string $consumer_name, int $min_idle_time, string $start, array $options = [])
    {
    }
    public function xInfoConsumers(string $key, string $group_name)
    {
    }
    public function xInfoGroups(string $key)
    {
    }
    public function xInfoStream(string $key)
    {
    }
}
