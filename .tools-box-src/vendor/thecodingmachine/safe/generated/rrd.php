<?php

namespace _HumbugBoxb47773b41c19\Safe;

use _HumbugBoxb47773b41c19\Safe\Exceptions\RrdException;
function rrd_create(string $filename, array $options) : void
{
    \error_clear_last();
    $safeResult = \rrd_create($filename, $options);
    if ($safeResult === \false) {
        throw RrdException::createFromPhpError();
    }
}
function rrd_first(string $file, int $raaindex = 0) : int
{
    \error_clear_last();
    $safeResult = \rrd_first($file, $raaindex);
    if ($safeResult === \false) {
        throw RrdException::createFromPhpError();
    }
    return $safeResult;
}
function rrd_graph(string $filename, array $options) : array
{
    \error_clear_last();
    $safeResult = \rrd_graph($filename, $options);
    if ($safeResult === \false) {
        throw RrdException::createFromPhpError();
    }
    return $safeResult;
}
function rrd_info(string $filename) : array
{
    \error_clear_last();
    $safeResult = \rrd_info($filename);
    if ($safeResult === \false) {
        throw RrdException::createFromPhpError();
    }
    return $safeResult;
}
function rrd_lastupdate(string $filename) : array
{
    \error_clear_last();
    $safeResult = \rrd_lastupdate($filename);
    if ($safeResult === \false) {
        throw RrdException::createFromPhpError();
    }
    return $safeResult;
}
function rrd_restore(string $xml_file, string $rrd_file, array $options = null) : void
{
    \error_clear_last();
    if ($options !== null) {
        $safeResult = \rrd_restore($xml_file, $rrd_file, $options);
    } else {
        $safeResult = \rrd_restore($xml_file, $rrd_file);
    }
    if ($safeResult === \false) {
        throw RrdException::createFromPhpError();
    }
}
function rrd_tune(string $filename, array $options) : void
{
    \error_clear_last();
    $safeResult = \rrd_tune($filename, $options);
    if ($safeResult === \false) {
        throw RrdException::createFromPhpError();
    }
}
function rrd_update(string $filename, array $options) : void
{
    \error_clear_last();
    $safeResult = \rrd_update($filename, $options);
    if ($safeResult === \false) {
        throw RrdException::createFromPhpError();
    }
}
function rrd_xport(array $options) : array
{
    \error_clear_last();
    $safeResult = \rrd_xport($options);
    if ($safeResult === \false) {
        throw RrdException::createFromPhpError();
    }
    return $safeResult;
}
