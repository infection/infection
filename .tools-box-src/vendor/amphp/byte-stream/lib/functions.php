<?php

namespace _HumbugBoxb47773b41c19\Amp\ByteStream;

use _HumbugBoxb47773b41c19\Amp\Iterator;
use _HumbugBoxb47773b41c19\Amp\Loop;
use _HumbugBoxb47773b41c19\Amp\Producer;
use _HumbugBoxb47773b41c19\Amp\Promise;
use function _HumbugBoxb47773b41c19\Amp\call;
if (\strlen('…') !== 3) {
    throw new \Error('The mbstring.func_overload ini setting is enabled. It must be disabled to use the stream package.');
}
if (!\defined('STDOUT')) {
    \define('STDOUT', \fopen('php://stdout', 'w'));
}
if (!\defined('STDERR')) {
    \define('STDERR', \fopen('php://stderr', 'w'));
}
function pipe(InputStream $source, OutputStream $destination) : Promise
{
    return call(function () use($source, $destination) : \Generator {
        $written = 0;
        while (($chunk = (yield $source->read())) !== null) {
            $written += \strlen($chunk);
            $writePromise = $destination->write($chunk);
            $chunk = null;
            (yield $writePromise);
        }
        return $written;
    });
}
function buffer(InputStream $source) : Promise
{
    return call(function () use($source) : \Generator {
        $buffer = "";
        while (($chunk = (yield $source->read())) !== null) {
            $buffer .= $chunk;
            $chunk = null;
        }
        return $buffer;
    });
}
function getInputBufferStream() : ResourceInputStream
{
    static $key = InputStream::class . '\\input';
    $stream = Loop::getState($key);
    if (!$stream) {
        $stream = new ResourceInputStream(\fopen('php://input', 'rb'));
        Loop::setState($key, $stream);
    }
    return $stream;
}
function getOutputBufferStream() : ResourceOutputStream
{
    static $key = OutputStream::class . '\\output';
    $stream = Loop::getState($key);
    if (!$stream) {
        $stream = new ResourceOutputStream(\fopen('php://output', 'wb'));
        Loop::setState($key, $stream);
    }
    return $stream;
}
function getStdin() : ResourceInputStream
{
    static $key = InputStream::class . '\\stdin';
    $stream = Loop::getState($key);
    if (!$stream) {
        $stream = new ResourceInputStream(\STDIN);
        Loop::setState($key, $stream);
    }
    return $stream;
}
function getStdout() : ResourceOutputStream
{
    static $key = OutputStream::class . '\\stdout';
    $stream = Loop::getState($key);
    if (!$stream) {
        $stream = new ResourceOutputStream(\STDOUT);
        Loop::setState($key, $stream);
    }
    return $stream;
}
function getStderr() : ResourceOutputStream
{
    static $key = OutputStream::class . '\\stderr';
    $stream = Loop::getState($key);
    if (!$stream) {
        $stream = new ResourceOutputStream(\STDERR);
        Loop::setState($key, $stream);
    }
    return $stream;
}
function parseLineDelimitedJson(InputStream $stream, bool $assoc = \false, int $depth = 512, int $options = 0) : Iterator
{
    return new Producer(static function (callable $emit) use($stream, $assoc, $depth, $options) {
        $reader = new LineReader($stream);
        while (null !== ($line = (yield $reader->readLine()))) {
            $line = \trim($line);
            if ($line === '') {
                continue;
            }
            /**
            @noinspection */
            $data = \json_decode($line, $assoc, $depth, $options);
            /**
            @noinspection */
            $error = \json_last_error();
            /**
            @noinspection */
            if ($error !== \JSON_ERROR_NONE) {
                /**
                @noinspection */
                throw new StreamException('Failed to parse JSON: ' . \json_last_error_msg(), $error);
            }
            (yield $emit($data));
        }
    });
}
