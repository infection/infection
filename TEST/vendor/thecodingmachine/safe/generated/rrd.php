<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\RrdException;
function rrd_create(string $filename, array $options) : void
{
    \error_clear_last();
    $result = \rrd_create($filename, $options);
    if ($result === \false) {
        throw RrdException::createFromPhpError();
    }
}
function rrd_first(string $file, int $raaindex = 0) : int
{
    \error_clear_last();
    $result = \rrd_first($file, $raaindex);
    if ($result === \false) {
        throw RrdException::createFromPhpError();
    }
    return $result;
}
function rrd_graph(string $filename, array $options) : array
{
    \error_clear_last();
    $result = \rrd_graph($filename, $options);
    if ($result === \false) {
        throw RrdException::createFromPhpError();
    }
    return $result;
}
function rrd_info(string $filename) : array
{
    \error_clear_last();
    $result = \rrd_info($filename);
    if ($result === \false) {
        throw RrdException::createFromPhpError();
    }
    return $result;
}
function rrd_lastupdate(string $filename) : array
{
    \error_clear_last();
    $result = \rrd_lastupdate($filename);
    if ($result === \false) {
        throw RrdException::createFromPhpError();
    }
    return $result;
}
function rrd_restore(string $xml_file, string $rrd_file, array $options = null) : void
{
    \error_clear_last();
    if ($options !== null) {
        $result = \rrd_restore($xml_file, $rrd_file, $options);
    } else {
        $result = \rrd_restore($xml_file, $rrd_file);
    }
    if ($result === \false) {
        throw RrdException::createFromPhpError();
    }
}
function rrd_tune(string $filename, array $options) : void
{
    \error_clear_last();
    $result = \rrd_tune($filename, $options);
    if ($result === \false) {
        throw RrdException::createFromPhpError();
    }
}
function rrd_update(string $filename, array $options) : void
{
    \error_clear_last();
    $result = \rrd_update($filename, $options);
    if ($result === \false) {
        throw RrdException::createFromPhpError();
    }
}
function rrd_xport(array $options) : array
{
    \error_clear_last();
    $result = \rrd_xport($options);
    if ($result === \false) {
        throw RrdException::createFromPhpError();
    }
    return $result;
}
