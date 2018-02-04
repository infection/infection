<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */
declare(strict_types=1);

namespace Infection\Php;

class XdebugHandler
{
    const ENV_DISABLE_XDEBUG = 'INFECTION_DISABLE_XDEBUG';

    const RESTART_HANDLE = 'internal';

    /**
     * @var ConfigBuilder
     */
    private $configBuilder;

    /**
     * @var bool
     */
    private $isLoaded;

    /**
     * @var string
     */
    private $envScanDir;

    /**
     * @var string
     */
    private $tmpIniPath;

    public function __construct(ConfigBuilder $configBuilder)
    {
        $this->configBuilder = $configBuilder;

        $this->isLoaded = extension_loaded('xdebug');
        $this->envScanDir = (string) getenv(ConfigBuilder::ENV_PHP_INI_SCAN_DIR);
    }

    public function check()
    {
        $args = explode('|', (string) getenv(self::ENV_DISABLE_XDEBUG));
        if ($this->needsRestart($args[0])) {
            if ($this->prepareRestart()) {
                $this->restart($this->getCommand());
            }
        }

        if (self::RESTART_HANDLE === $args[0]) {
            putenv(self::ENV_DISABLE_XDEBUG);

            if (false !== $this->envScanDir) {
                // $args[1] contains the original value
                if (isset($args[1])) {
                    putenv(ConfigBuilder::ENV_PHP_INI_SCAN_DIR . '=' . $args[1]);
                } else {
                    putenv(ConfigBuilder::ENV_PHP_INI_SCAN_DIR);
                }
            }
        }
    }

    private function needsRestart(string $allow): bool
    {
        if (PHP_SAPI !== 'cli' || \defined('PHP_BINARY') === false) {
            return false;
        }

        return $this->isLoaded && '' === $allow;
    }

    private function prepareRestart(): bool
    {
        if ($this->tmpIniPath = $this->configBuilder->build()) {
            return $this->setEnvironment();
        }

        return false;
    }

    /**
     * Returns true if the restart environment variables were set
     *
     * @return bool
     */
    private function setEnvironment(): bool
    {
        return putenv(self::ENV_DISABLE_XDEBUG . '=' . self::RESTART_HANDLE);
    }

    /**
     * Returns the restart command line
     *
     * @return string
     */
    private function getCommand(): string
    {
        $params = array_merge(
            [PHP_BINARY, '-c', $this->tmpIniPath],
            $this->getScriptArgs($_SERVER['argv'])
        );

        return implode(' ', array_map([$this, 'escape'], $params));
    }

    /**
     * Returns the restart script arguments, adding --ansi if required
     *
     * If we are a terminal with color support we must ensure that the --ansi
     * option is set, because the restarted output is piped.
     *
     * @param array $args The argv array
     *
     * @return array
     */
    private function getScriptArgs(array $args): array
    {
        if (\in_array('--no-ansi', $args, true) || \in_array('--ansi', $args, true)) {
            return $args;
        }

        $offset = \count($args) > 1 ? 2 : 1;
        array_splice($args, $offset, 0, '--ansi');

        return $args;
    }

    /**
     * Escapes a string to be used as a shell argument.
     *
     * From https://github.com/johnstevenson/winbox-args
     * MIT Licensed (c) John Stevenson <john-stevenson@blueyonder.co.uk>
     *
     * @param string $arg  The argument to be escaped
     * @param bool   $meta Additionally escape cmd.exe meta characters
     *
     * @return string The escaped argument
     */
    private function escape(string $arg, bool $meta = true): string
    {
        if (!defined('PHP_WINDOWS_VERSION_BUILD')) {
            return escapeshellarg($arg);
        }

        $quote = strpbrk($arg, " \t") !== false || $arg === '';
        $arg = preg_replace('/(\\\\*)"/', '$1$1\\"', $arg, -1, $quotesCount);

        if ($meta) {
            $meta = $quotesCount || preg_match('/%[^%]+%/', $arg);

            if (!$meta && !$quote) {
                $quote = strpbrk($arg, '^&|<>()') !== false;
            }
        }

        if ($quote) {
            $arg = preg_replace('/(\\\\*)$/', '$1$1', $arg);
            $arg = '"' . $arg . '"';
        }

        if ($meta) {
            $arg = preg_replace('/(["^&|<>()%])/', '^$1', $arg);
        }

        return $arg;
    }

    /**
     * Executes the restarted command then deletes the tmp ini
     *
     * @param string $command
     */
    protected function restart(string $command)
    {
        passthru($command, $exitCode);

        exit($exitCode);
    }
}
