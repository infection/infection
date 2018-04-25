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
    const ENV_PHPRC = 'PHPRC';

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
            $this->setEnvironment();

            return $this->tmpIniPath;
        }

        throw new IOException('Can not create temporary php config with disabled xdebug.');
    }

    private function createTemporaryDirectory()
    {
        do {
            if (!$tempname = tempnam($this->tempDir, 'infection')) {
                return false;
            }

            unlink($tempname);

            // avoid race condition where someone else creates the very same directory
            if (!mkdir($tempname, 0700)) {
                continue;
            }

            return $tempname;
        } while (false);
    }

    /**
     * @param string[] $originalIniPaths
     *
     * @return bool
     */
    private function writeTempIni(array $originalIniPaths): bool
    {
        if (!$tempdir = $this->createTemporaryDirectory()) {
            return false;
        }

        // for PHPRC to work the file must be named exactly php.ini
        $this->tmpIniPath = "$tempdir/php.ini";

        if (!touch($this->tmpIniPath)) {
            // out of inodes? let's not make it worse
            rmdir($tempdir);

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

        return (bool) @file_put_contents($this->tmpIniPath, $content);
    }

    private function setEnvironment()
    {
        // Set all child processes to use our php.ini
        putenv(self::ENV_PHPRC . '=' . dirname($this->tmpIniPath));
        // We need to ignore all additional .ini files (our php.ini has everything)
        putenv(self::ENV_PHP_INI_SCAN_DIR . '=');

        putenv(self::ENV_TEMP_PHP_CONFIG_PATH . '=' . $this->tmpIniPath);
    }
}
