<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console\Command;

use const DATE_ATOM;
use DateTimeImmutable;
use DateTimeZone;
use function function_exists;
use function get_loaded_extensions;
use function implode;
use _HumbugBoxb47773b41c19\KevinGH\Box\Configuration\Configuration;
use function _HumbugBoxb47773b41c19\KevinGH\Box\get_box_version;
use _HumbugBoxb47773b41c19\KevinGH\Box\NotInstantiable;
use const PHP_OS;
use function php_uname;
use const PHP_VERSION;
final class ConfigurationExporter
{
    use NotInstantiable;
    public static function export(Configuration $config) : string
    {
        $date = (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(DATE_ATOM);
        $file = $config->getConfigurationFile() ?? 'No config file';
        $phpVersion = PHP_VERSION;
        $phpExtensions = implode(',', get_loaded_extensions());
        $os = function_exists('php_uname') ? PHP_OS . ' / ' . php_uname('r') : 'Unknown OS';
        $command = implode(' ', $GLOBALS['argv']);
        $boxVersion = get_box_version();
        $header = <<<EOF
//
// Processed content of the configuration file "{$file}" dumped for debugging purposes
//
// PHP Version: {$phpVersion}
// PHP extensions: {$phpExtensions}
// OS: {$os}
// Command: {$command}
// Box: {$boxVersion}
// Time: {$date}
//


EOF;
        return $header . $config->export();
    }
}
