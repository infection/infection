<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box;

use function array_key_exists;
use function bin2hex;
use function class_alias;
use function class_exists;
use Closure;
use _HumbugBoxb47773b41c19\Composer\InstalledVersions;
use function constant;
use function define;
use function defined;
use ErrorException;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use function floor;
use function function_exists;
use function is_float;
use function is_int;
use _HumbugBoxb47773b41c19\KevinGH\Box\Console\Php\PhpSettingsHandler;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\copy;
use function log;
use function number_format;
use const PATHINFO_EXTENSION;
use Phar;
use function posix_getrlimit;
use const POSIX_RLIMIT_INFINITY;
use const POSIX_RLIMIT_NOFILE;
use function posix_setrlimit;
use function random_bytes;
use function sprintf;
use function str_replace;
use function strlen;
use function strtolower;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Helper\Helper;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Logger\ConsoleLogger;
use _HumbugBoxb47773b41c19\Symfony\Component\Console\Output\OutputInterface;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
function get_box_version() : string
{
    foreach ([__DIR__ . '/../vendor/composer/InstalledVersions.php', __DIR__ . '/../../../composer/InstalledVersions.php'] as $file) {
        if (\file_exists($file)) {
            require_once $file;
            break;
        }
    }
    $prettyVersion = InstalledVersions::getPrettyVersion('humbug/box');
    $commitHash = InstalledVersions::getReference('humbug/box');
    if (null === $commitHash) {
        return $prettyVersion;
    }
    return $prettyVersion . '@' . \substr($commitHash, 0, 7);
}
function get_phar_compression_algorithms() : array
{
    static $algorithms = ['GZ' => Phar::GZ, 'BZ2' => Phar::BZ2, 'NONE' => Phar::NONE];
    return $algorithms;
}
function get_phar_compression_algorithm_extension(int $algorithm) : ?string
{
    static $extensions = [Phar::GZ => 'zlib', Phar::BZ2 => 'bz2', Phar::NONE => null];
    Assert::true(array_key_exists($algorithm, $extensions), sprintf('Unknown compression algorithm code "%d"', $algorithm));
    return $extensions[$algorithm];
}
function get_phar_signing_algorithms() : array
{
    static $algorithms = ['MD5' => Phar::MD5, 'SHA1' => Phar::SHA1, 'SHA256' => Phar::SHA256, 'SHA512' => Phar::SHA512, 'OPENSSL' => Phar::OPENSSL];
    return $algorithms;
}
function format_size(float|int $size, int $decimals = 2) : string
{
    Assert::true(is_int($size) || is_float($size));
    if (-1 === $size) {
        return '-1';
    }
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $power = $size > 0 ? (int) floor(log($size, 1024)) : 0;
    return sprintf('%s%s', number_format($size / 1024 ** $power, $decimals), $units[$power]);
}
function memory_to_bytes(string $value) : float|int
{
    $unit = strtolower($value[strlen($value) - 1]);
    $bytes = (int) $value;
    switch ($unit) {
        case 'g':
            $bytes *= 1024;
        case 'm':
            $bytes *= 1024;
        case 'k':
            $bytes *= 1024;
    }
    return $bytes;
}
function format_time(float $secs) : string
{
    return str_replace(' ', '', Helper::formatTime($secs));
}
function register_aliases() : void
{
    if (\false === class_exists(\Isolated\Symfony\Component\Finder\Finder::class)) {
        class_alias(\_HumbugBoxb47773b41c19\Symfony\Component\Finder\Finder::class, \Isolated\Symfony\Component\Finder\Finder::class);
    }
}
function disable_parallel_processing() : void
{
    if (\false === defined(_NO_PARALLEL_PROCESSING)) {
        define(_NO_PARALLEL_PROCESSING, \true);
    }
}
function is_parallel_processing_enabled() : bool
{
    return \false === defined(_NO_PARALLEL_PROCESSING) || \false === constant(_NO_PARALLEL_PROCESSING);
}
function unique_id(string $prefix) : string
{
    return $prefix . bin2hex(random_bytes(6));
}
function create_temporary_phar(string $file) : string
{
    $tmpFile = \sys_get_temp_dir() . '/' . unique_id('') . \basename($file);
    if ('' === \pathinfo($file, PATHINFO_EXTENSION)) {
        $tmpFile .= '.phar';
    }
    copy($file, $tmpFile, \true);
    return $tmpFile;
}
function check_php_settings(IO $io) : void
{
    (new PhpSettingsHandler(new ConsoleLogger($io->getOutput())))->check();
}
function noop() : Closure
{
    return static function () : void {
    };
}
function register_error_handler() : void
{
    \set_error_handler(static function (int $code, string $message, string $file = '', int $line = -1) : void {
        if (\error_reporting() & $code) {
            throw new ErrorException($message, 0, $code, $file, $line);
        }
    });
}
function bump_open_file_descriptor_limit(int $count, IO $io) : Closure
{
    $count += 128;
    if (\false === function_exists('posix_getrlimit') || \false === function_exists('posix_setrlimit')) {
        $io->writeln('<info>[debug] Could not check the maximum number of open file descriptors: the functions "posix_getrlimit()" and ' . '"posix_setrlimit" could not be found.</info>', OutputInterface::VERBOSITY_DEBUG);
        return static function () : void {
        };
    }
    $softLimit = posix_getrlimit()['soft openfiles'];
    $hardLimit = posix_getrlimit()['hard openfiles'];
    if ($softLimit >= $count) {
        return static function () : void {
        };
    }
    $io->writeln(sprintf('<info>[debug] Increased the maximum number of open file descriptors from ("%s", "%s") to ("%s", "%s")' . '</info>', $softLimit, $hardLimit, $count, 'unlimited'), OutputInterface::VERBOSITY_DEBUG);
    posix_setrlimit(POSIX_RLIMIT_NOFILE, $count, 'unlimited' === $hardLimit ? POSIX_RLIMIT_INFINITY : $hardLimit);
    return static function () use($io, $softLimit, $hardLimit) : void {
        if (function_exists('posix_setrlimit') && isset($softLimit, $hardLimit)) {
            posix_setrlimit(POSIX_RLIMIT_NOFILE, $softLimit, 'unlimited' === $hardLimit ? POSIX_RLIMIT_INFINITY : $hardLimit);
            $io->writeln('<info>[debug] Restored the maximum number of open file descriptors</info>', OutputInterface::VERBOSITY_DEBUG);
        }
    };
}
