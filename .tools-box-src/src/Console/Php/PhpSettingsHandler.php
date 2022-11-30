<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Console\Php;

use _HumbugBoxb47773b41c19\Composer\XdebugHandler\XdebugHandler;
use function getenv;
use function ini_get;
use function ini_set;
use const _HumbugBoxb47773b41c19\KevinGH\Box\BOX_MEMORY_LIMIT;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\append_to_file;
use function _HumbugBoxb47773b41c19\KevinGH\Box\format_size;
use function _HumbugBoxb47773b41c19\KevinGH\Box\memory_to_bytes;
use const PHP_EOL;
use _HumbugBoxb47773b41c19\Psr\Log\LoggerInterface;
use function sprintf;
use function trim;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class PhpSettingsHandler extends XdebugHandler
{
    private LoggerInterface $logger;
    private bool $pharReadonly;
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct('box');
        $this->setPersistent();
        $this->setLogger($logger);
        $this->logger = $logger;
        $this->pharReadonly = '1' === ini_get('phar.readonly');
    }
    public function check() : void
    {
        $this->bumpMemoryLimit();
        parent::check();
    }
    protected function requiresRestart(bool $default) : bool
    {
        if ($this->pharReadonly) {
            $this->logger->debug('phar.readonly is enabled');
            return \true;
        }
        $this->logger->debug('phar.readonly is disabled');
        return parent::requiresRestart($default);
    }
    protected function restart(array $command) : void
    {
        $this->disablePharReadonly();
        parent::restart($command);
    }
    private function disablePharReadonly() : void
    {
        if (ini_get('phar.readonly')) {
            Assert::notNull($this->tmpIni);
            append_to_file($this->tmpIni, 'phar.readonly=0' . PHP_EOL);
            $this->logger->debug('Configured `phar.readonly=0`');
        }
    }
    private function bumpMemoryLimit() : void
    {
        $userDefinedMemoryLimit = self::getUserDefinedMemoryLimit();
        $memoryLimit = trim(ini_get('memory_limit'));
        $memoryLimitInBytes = '-1' === $memoryLimit ? -1 : memory_to_bytes($memoryLimit);
        $bumpMemoryLimit = null === $userDefinedMemoryLimit && -1 !== $memoryLimitInBytes && $memoryLimitInBytes < 1024 * 1024 * 512;
        $setUserDefinedMemoryLimit = null !== $userDefinedMemoryLimit && $memoryLimitInBytes !== $userDefinedMemoryLimit;
        if ($bumpMemoryLimit && \false === $setUserDefinedMemoryLimit) {
            ini_set('memory_limit', '512M');
            $this->logger->debug(sprintf('Changed the memory limit from "%s" to "%s"', format_size($memoryLimitInBytes, 0), '512M'));
        } elseif ($setUserDefinedMemoryLimit) {
            ini_set('memory_limit', (string) $userDefinedMemoryLimit);
            $this->logger->debug(sprintf('Changed the memory limit from "%s" to %s="%s"', format_size($memoryLimitInBytes, 0), BOX_MEMORY_LIMIT, format_size($userDefinedMemoryLimit, 0)));
        } else {
            $this->logger->debug(sprintf('Current memory limit: "%s"', format_size($memoryLimitInBytes, 0)));
        }
    }
    private static function getUserDefinedMemoryLimit() : ?int
    {
        $memoryLimit = getenv(BOX_MEMORY_LIMIT);
        if (\false === $memoryLimit) {
            $memoryLimitInBytes = null;
        } elseif ('-1' === $memoryLimit) {
            $memoryLimitInBytes = -1;
        } else {
            $memoryLimitInBytes = memory_to_bytes($memoryLimit);
        }
        return $memoryLimitInBytes;
    }
}
