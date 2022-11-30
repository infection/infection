<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Composer\XdebugHandler;

use _HumbugBox9658796bb9f0\Composer\Pcre\Preg;
use _HumbugBox9658796bb9f0\Psr\Log\LoggerInterface;
/**
@phpstan-import-type
*/
class XdebugHandler
{
    const SUFFIX_ALLOW = '_ALLOW_XDEBUG';
    const SUFFIX_INIS = '_ORIGINAL_INIS';
    const RESTART_ID = 'internal';
    const RESTART_SETTINGS = 'XDEBUG_HANDLER_SETTINGS';
    const DEBUG = 'XDEBUG_HANDLER_DEBUG';
    protected $tmpIni;
    private static $inRestart;
    private static $name;
    private static $skipped;
    private static $xdebugActive;
    private $cli;
    private $debug;
    private $envAllowXdebug;
    private $envOriginalInis;
    private $loaded;
    private $mode;
    private $persistent;
    private $script;
    private $statusWriter;
    public function __construct(string $envPrefix)
    {
        if ($envPrefix === '') {
            throw new \RuntimeException('Invalid constructor parameter');
        }
        self::$name = \strtoupper($envPrefix);
        $this->envAllowXdebug = self::$name . self::SUFFIX_ALLOW;
        $this->envOriginalInis = self::$name . self::SUFFIX_INIS;
        if (\extension_loaded('xdebug')) {
            $version = \phpversion('xdebug');
            $this->loaded = $version !== \false ? $version : 'unknown';
            $this->mode = $this->getXdebugMode($this->loaded);
        }
        self::$xdebugActive = $this->loaded !== null && $this->mode !== 'off';
        self::$inRestart = \false;
        if ($this->cli = \PHP_SAPI === 'cli') {
            $this->debug = (string) \getenv(self::DEBUG);
        }
        $this->statusWriter = new Status($this->envAllowXdebug, (bool) $this->debug);
    }
    public function setLogger(LoggerInterface $logger) : self
    {
        $this->statusWriter->setLogger($logger);
        return $this;
    }
    public function setMainScript(string $script) : self
    {
        $this->script = $script;
        return $this;
    }
    public function setPersistent() : self
    {
        $this->persistent = \true;
        return $this;
    }
    public function check() : void
    {
        $this->notify(Status::CHECK, $this->loaded . '|' . $this->mode);
        $envArgs = \explode('|', (string) \getenv($this->envAllowXdebug));
        if (!(bool) $envArgs[0] && $this->requiresRestart(self::$xdebugActive)) {
            $this->notify(Status::RESTART);
            if ($this->prepareRestart()) {
                $command = $this->getCommand();
                $this->restart($command);
            }
            return;
        }
        if (self::RESTART_ID === $envArgs[0] && \count($envArgs) === 5) {
            $this->notify(Status::RESTARTED);
            Process::setEnv($this->envAllowXdebug);
            self::$inRestart = \true;
            if ($this->loaded === null) {
                self::$skipped = $envArgs[1];
            }
            $this->tryEnableSignals();
            $this->setEnvRestartSettings($envArgs);
            return;
        }
        $this->notify(Status::NORESTART);
        $settings = self::getRestartSettings();
        if ($settings !== null) {
            $this->syncSettings($settings);
        }
    }
    public static function getAllIniFiles() : array
    {
        if (self::$name !== null) {
            $env = \getenv(self::$name . self::SUFFIX_INIS);
            if (\false !== $env) {
                return \explode(\PATH_SEPARATOR, $env);
            }
        }
        $paths = [(string) \php_ini_loaded_file()];
        $scanned = \php_ini_scanned_files();
        if ($scanned !== \false) {
            $paths = \array_merge($paths, \array_map('trim', \explode(',', $scanned)));
        }
        return $paths;
    }
    /**
    @phpstan-return
    */
    public static function getRestartSettings() : ?array
    {
        $envArgs = \explode('|', (string) \getenv(self::RESTART_SETTINGS));
        if (\count($envArgs) !== 6 || !self::$inRestart && \php_ini_loaded_file() !== $envArgs[0]) {
            return null;
        }
        return ['tmpIni' => $envArgs[0], 'scannedInis' => (bool) $envArgs[1], 'scanDir' => '*' === $envArgs[2] ? \false : $envArgs[2], 'phprc' => '*' === $envArgs[3] ? \false : $envArgs[3], 'inis' => \explode(\PATH_SEPARATOR, $envArgs[4]), 'skipped' => $envArgs[5]];
    }
    public static function getSkippedVersion() : string
    {
        return (string) self::$skipped;
    }
    public static function isXdebugActive() : bool
    {
        return self::$xdebugActive;
    }
    protected function requiresRestart(bool $default) : bool
    {
        return $default;
    }
    protected function restart(array $command) : void
    {
        $this->doRestart($command);
    }
    /**
    @phpstan-return
    */
    private function doRestart(array $command) : void
    {
        $this->tryEnableSignals();
        $this->notify(Status::RESTARTING, \implode(' ', $command));
        if (\PHP_VERSION_ID >= 70400) {
            $cmd = $command;
        } else {
            $cmd = Process::escapeShellCommand($command);
            if (\defined('PHP_WINDOWS_VERSION_BUILD')) {
                $cmd = '"' . $cmd . '"';
            }
        }
        $process = \proc_open($cmd, [], $pipes);
        if (\is_resource($process)) {
            $exitCode = \proc_close($process);
        }
        if (!isset($exitCode)) {
            $this->notify(Status::ERROR, 'Unable to restart process');
            $exitCode = -1;
        } else {
            $this->notify(Status::INFO, 'Restarted process exited ' . $exitCode);
        }
        if ($this->debug === '2') {
            $this->notify(Status::INFO, 'Temp ini saved: ' . $this->tmpIni);
        } else {
            @\unlink((string) $this->tmpIni);
        }
        exit($exitCode);
    }
    private function prepareRestart() : bool
    {
        $error = null;
        $iniFiles = self::getAllIniFiles();
        $scannedInis = \count($iniFiles) > 1;
        $tmpDir = \sys_get_temp_dir();
        if (!$this->cli) {
            $error = 'Unsupported SAPI: ' . \PHP_SAPI;
        } elseif (!$this->checkConfiguration($info)) {
            $error = $info;
        } elseif (!$this->checkMainScript()) {
            $error = 'Unable to access main script: ' . $this->script;
        } elseif (!$this->writeTmpIni($iniFiles, $tmpDir, $error)) {
            $error = $error !== null ? $error : 'Unable to create temp ini file at: ' . $tmpDir;
        } elseif (!$this->setEnvironment($scannedInis, $iniFiles)) {
            $error = 'Unable to set environment variables';
        }
        if ($error !== null) {
            $this->notify(Status::ERROR, $error);
        }
        return $error === null;
    }
    private function writeTmpIni(array $iniFiles, string $tmpDir, ?string &$error) : bool
    {
        if (($tmpfile = @\tempnam($tmpDir, '')) === \false) {
            return \false;
        }
        $this->tmpIni = $tmpfile;
        if ($iniFiles[0] === '') {
            \array_shift($iniFiles);
        }
        $content = '';
        $sectionRegex = '/^\\s*\\[(?:PATH|HOST)\\s*=/mi';
        $xdebugRegex = '/^\\s*(zend_extension\\s*=.*xdebug.*)$/mi';
        foreach ($iniFiles as $file) {
            if (($data = @\file_get_contents($file)) === \false) {
                $error = 'Unable to read ini: ' . $file;
                return \false;
            }
            if (Preg::isMatchWithOffsets($sectionRegex, $data, $matches, \PREG_OFFSET_CAPTURE)) {
                $data = \substr($data, 0, $matches[0][1]);
            }
            $content .= Preg::replace($xdebugRegex, ';$1', $data) . \PHP_EOL;
        }
        $config = \parse_ini_string($content);
        $loaded = \ini_get_all(null, \false);
        if (\false === $config || \false === $loaded) {
            $error = 'Unable to parse ini data';
            return \false;
        }
        $content .= $this->mergeLoadedConfig($loaded, $config);
        $content .= 'opcache.enable_cli=0' . \PHP_EOL;
        return (bool) @\file_put_contents($this->tmpIni, $content);
    }
    private function getCommand() : array
    {
        $php = [\PHP_BINARY];
        $args = \array_slice($_SERVER['argv'], 1);
        if (!$this->persistent) {
            \array_push($php, '-n', '-c', $this->tmpIni);
        }
        return \array_merge($php, [$this->script], $args);
    }
    private function setEnvironment(bool $scannedInis, array $iniFiles) : bool
    {
        $scanDir = \getenv('PHP_INI_SCAN_DIR');
        $phprc = \getenv('PHPRC');
        if (!\putenv($this->envOriginalInis . '=' . \implode(\PATH_SEPARATOR, $iniFiles))) {
            return \false;
        }
        if ($this->persistent) {
            if (!\putenv('PHP_INI_SCAN_DIR=') || !\putenv('PHPRC=' . $this->tmpIni)) {
                return \false;
            }
        }
        $envArgs = [self::RESTART_ID, $this->loaded, (int) $scannedInis, \false === $scanDir ? '*' : $scanDir, \false === $phprc ? '*' : $phprc];
        return \putenv($this->envAllowXdebug . '=' . \implode('|', $envArgs));
    }
    private function notify(string $op, ?string $data = null) : void
    {
        $this->statusWriter->report($op, $data);
    }
    private function mergeLoadedConfig(array $loadedConfig, array $iniConfig) : string
    {
        $content = '';
        foreach ($loadedConfig as $name => $value) {
            if (!\is_string($value) || \strpos($name, 'xdebug') === 0 || $name === 'apc.mmap_file_mask') {
                continue;
            }
            if (!isset($iniConfig[$name]) || $iniConfig[$name] !== $value) {
                $content .= $name . '="' . \addcslashes($value, '\\"') . '"' . \PHP_EOL;
            }
        }
        return $content;
    }
    private function checkMainScript() : bool
    {
        if ($this->script !== null) {
            return \file_exists($this->script) || '--' === $this->script;
        }
        if (\file_exists($this->script = $_SERVER['argv'][0])) {
            return \true;
        }
        $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
        $main = \end($trace);
        if ($main !== \false && isset($main['file'])) {
            return \file_exists($this->script = $main['file']);
        }
        return \false;
    }
    private function setEnvRestartSettings(array $envArgs) : void
    {
        $settings = [\php_ini_loaded_file(), $envArgs[2], $envArgs[3], $envArgs[4], \getenv($this->envOriginalInis), self::$skipped];
        Process::setEnv(self::RESTART_SETTINGS, \implode('|', $settings));
    }
    /**
    @phpstan-param
    */
    private function syncSettings(array $settings) : void
    {
        if (\false === \getenv($this->envOriginalInis)) {
            Process::setEnv($this->envOriginalInis, \implode(\PATH_SEPARATOR, $settings['inis']));
        }
        self::$skipped = $settings['skipped'];
        $this->notify(Status::INFO, 'Process called with existing restart settings');
    }
    private function checkConfiguration(?string &$info) : bool
    {
        if (!\function_exists('proc_open')) {
            $info = 'proc_open function is disabled';
            return \false;
        }
        if (\extension_loaded('uopz') && !(bool) \ini_get('uopz.disable')) {
            if (\function_exists('uopz_allow_exit')) {
                @\uopz_allow_exit(\true);
            } else {
                $info = 'uopz extension is not compatible';
                return \false;
            }
        }
        if (\defined('PHP_WINDOWS_VERSION_BUILD') && \PHP_VERSION_ID < 70400) {
            $workingDir = \getcwd();
            if ($workingDir === \false) {
                $info = 'unable to determine working directory';
                return \false;
            }
            if (0 === \strpos($workingDir, '\\\\')) {
                $info = 'cmd.exe does not support UNC paths: ' . $workingDir;
                return \false;
            }
        }
        return \true;
    }
    private function tryEnableSignals() : void
    {
        if (\function_exists('pcntl_async_signals') && \function_exists('pcntl_signal')) {
            \pcntl_async_signals(\true);
            $message = 'Async signals enabled';
            if (!self::$inRestart) {
                \pcntl_signal(\SIGINT, \SIG_IGN);
            } elseif (\is_int(\pcntl_signal_get_handler(\SIGINT))) {
                \pcntl_signal(\SIGINT, \SIG_DFL);
            }
        }
        if (!self::$inRestart && \function_exists('sapi_windows_set_ctrl_handler')) {
            \sapi_windows_set_ctrl_handler(function ($evt) {
            });
        }
    }
    private function getXdebugMode(string $version) : ?string
    {
        if (\version_compare($version, '3.1', '>=')) {
            $modes = \xdebug_info('mode');
            return \count($modes) === 0 ? 'off' : \implode(',', $modes);
        }
        $iniMode = \ini_get('xdebug.mode');
        if ($iniMode === \false) {
            return null;
        }
        $envMode = (string) \getenv('XDEBUG_MODE');
        if ($envMode !== '') {
            $mode = $envMode;
        } else {
            $mode = $iniMode !== '' ? $iniMode : 'off';
        }
        if (Preg::isMatch('/^,+$/', \str_replace(' ', '', $mode))) {
            $mode = 'off';
        }
        return $mode;
    }
}
