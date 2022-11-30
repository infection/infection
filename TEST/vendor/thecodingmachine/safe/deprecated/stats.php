<?php

namespace _HumbugBox9658796bb9f0\Safe;

use _HumbugBox9658796bb9f0\Safe\Exceptions\StatsException;
function stats_covariance(array $a, array $b) : float
{
    \error_clear_last();
    $result = \stats_covariance($a, $b);
    if ($result === \false) {
        throw StatsException::createFromPhpError();
    }
    return $result;
}
function stats_standard_deviation(array $a, bool $sample = \false) : float
{
    \error_clear_last();
    $result = \stats_standard_deviation($a, $sample);
    if ($result === \false) {
        throw StatsException::createFromPhpError();
    }
    return $result;
}
function stats_stat_correlation(array $arr1, array $arr2) : float
{
    \error_clear_last();
    $result = \stats_stat_correlation($arr1, $arr2);
    if ($result === \false) {
        throw StatsException::createFromPhpError();
    }
    return $result;
}
function stats_stat_innerproduct(array $arr1, array $arr2) : float
{
    \error_clear_last();
    $result = \stats_stat_innerproduct($arr1, $arr2);
    if ($result === \false) {
        throw StatsException::createFromPhpError();
    }
    return $result;
}
function stats_variance(array $a, bool $sample = \false) : float
{
    \error_clear_last();
    $result = \stats_variance($a, $sample);
    if ($result === \false) {
        throw StatsException::createFromPhpError();
    }
    return $result;
}
