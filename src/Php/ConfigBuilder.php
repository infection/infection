<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Php;

use Symfony\Component\Filesystem\Exception\IOException;

final class ConfigBuilder
{
    const ENV_TEMP_PHP_CONFIG_PATH = 'INFECTION_TEMP_PHP_CONFIG_PATH';
    const ENV_PHP_INI_SCAN_DIR = 'PHP_INI_SCAN_DIR';

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var string
     */
    private $tmpIniPath;

    public function __construct(string $tempDir)
    {
        $this->tempDir = $tempDir;
    }

    /**
     * @return string|null config path
     *
     * @throws \Exception
     */
    public function build()
    {
        $tmpIniPath = (string) getenv(self::ENV_TEMP_PHP_CONFIG_PATH);

        if (!empty($tmpIniPath) && file_exists($tmpIniPath)) {
            return $tmpIniPath;
        }

        $iniPaths = PhpIniHelper::get();

        if ($this->writeTempIni($iniPaths)) {
            $additional = count($iniPaths) > 1;

            $this->setEnvironment($additional);

            return $this->tmpIniPath;
        }

        throw new IOException('Can not create temporary php config with disabled xdebug.');
    }

    /**
     * @param string[] $originalIniPaths
     *
     * @return bool
     */
    private function writeTempIni(array $originalIniPaths): bool
    {
        if (!($this->tmpIniPath = tempnam($this->tempDir, 'infection'))) {
            return false;
        }

        // $originalIniPaths is either empty or has at least one element
        if (empty($originalIniPaths[0])) {
            array_shift($originalIniPaths);
        }

        $content = '';
        $regex = '/^\s*(zend_extension\s*=.*xdebug.*)$/mi';

        foreach ($originalIniPaths as $iniPath) {
            $content .= preg_replace($regex, ';$1', file_get_contents($iniPath)) . PHP_EOL;
        }

        $loaded = ini_get_all(null, false);
        $config = parse_ini_string($content);

        $content .= $this->mergeLoadedConfig($loaded, $config);
        // Work-around for https://bugs.php.net/bug.php?id=75932
        $content .= 'opcache.enable_cli=0' . PHP_EOL;

        return (bool) @file_put_contents($this->tmpIniPath, $content);
    }

    private function setEnvironment(bool $additional): bool
    {
        if ($additional && !putenv(self::ENV_PHP_INI_SCAN_DIR . '=')) {
            return false;
        }

        return putenv(self::ENV_TEMP_PHP_CONFIG_PATH . '=' . $this->tmpIniPath);
    }

    /**
     * Returns default or changed settings for the tmp ini
     *
     * Ini settings can be passed on the command line using the -d option. To
     * preserve these, all loaded settings that are either not present or
     * different from those in the ini files are added at the end of the tmp ini.
     *
     * @param array $loadedConfig All current ini settings
     * @param array $iniConfig Settings from user ini files
     *
     * @return string
     *
     * @see https://github.com/composer/xdebug-handler/blob/master/src/XdebugHandler.php
     *
     * @license MIT composer/xdebug-handler
     */
    private function mergeLoadedConfig(array $loadedConfig, array $iniConfig)
    {
        $content = '';
        foreach ($loadedConfig as $name => $value) {
            // Values will either be null, string or array (HHVM only)
            if (!is_string($value) || strpos($name, 'xdebug') === 0) {
                continue;
            }
            if (!isset($iniConfig[$name]) || $iniConfig[$name] !== $value) {
                // Based on main -d option handling in php-src/sapi/cli/php_cli.c
                if ($value && !ctype_alnum($value)) {
                    $value = '"' . str_replace('"', '\\"', $value) . '"';
                }
                $content .= $name . '=' . $value . PHP_EOL;
            }
        }

        return $content;
    }
}
