<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Console;

use _HumbugBoxb47773b41c19\Fidry\Console\Application\Application;
use _HumbugBoxb47773b41c19\Fidry\Console\Input\IO;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Autoload\ScoperAutoloadGenerator;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Configuration\Configuration;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Scoper;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\ScoperFactory;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Throwable\Exception\ParsingException;
use _HumbugBoxb47773b41c19\Symfony\Component\Filesystem\Filesystem;
use Throwable;
use function array_column;
use function array_keys;
use function array_map;
use function count;
use function _HumbugBoxb47773b41c19\Humbug\PhpScoper\get_common_path;
use function preg_match as native_preg_match;
use function _HumbugBoxb47773b41c19\Safe\file_get_contents;
use function _HumbugBoxb47773b41c19\Safe\fileperms;
use function sprintf;
use function str_replace;
use function strlen;
use function usort;
use const DIRECTORY_SEPARATOR;
final class ConsoleScoper
{
    private const VENDOR_DIR_PATTERN = '~((?:.*)\\' . DIRECTORY_SEPARATOR . 'vendor)\\' . DIRECTORY_SEPARATOR . '.*~';
    public function __construct(private readonly Filesystem $fileSystem, private readonly Application $application, private readonly ScoperFactory $scoperFactory)
    {
    }
    public function scope(IO $io, Configuration $config, array $paths, string $outputDir, bool $stopOnFailure) : void
    {
        $logger = new ScoperLogger($this->application, $io);
        $logger->outputScopingStart($config->getPrefix(), $paths);
        try {
            $this->scopeFiles($config, $outputDir, $stopOnFailure, $logger);
        } catch (Throwable $throwable) {
            $this->fileSystem->remove($outputDir);
            $logger->outputScopingEndWithFailure();
            throw $throwable;
        }
        $logger->outputScopingEnd();
    }
    private function scopeFiles(Configuration $config, string $outputDir, bool $stopOnFailure, ScoperLogger $logger) : void
    {
        $this->fileSystem->mkdir($outputDir);
        [$files, $excludedFilesWithContents] = self::getFiles($config, $outputDir);
        $logger->outputFileCount(count($files));
        $symbolsRegistry = new SymbolsRegistry();
        $scoper = $this->scoperFactory->createScoper($config, $symbolsRegistry);
        foreach ($files as [$inputFilePath, $inputContents, $outputFilePath]) {
            $this->scopeFile($scoper, $inputFilePath, $inputContents, $outputFilePath, $stopOnFailure, $logger);
        }
        foreach ($excludedFilesWithContents as $excludedFileWithContent) {
            $this->dumpFileWithPermissions(...$excludedFileWithContent);
        }
        $vendorDir = self::findVendorDir([...array_column($files, 2), ...array_column($excludedFilesWithContents, 2)]);
        if (null !== $vendorDir) {
            $autoload = (new ScoperAutoloadGenerator($symbolsRegistry))->dump();
            $this->fileSystem->dumpFile($vendorDir . DIRECTORY_SEPARATOR . 'scoper-autoload.php', $autoload);
        }
    }
    private function dumpFileWithPermissions(string $inputFilePath, string $inputContents, string $outputFilePath) : void
    {
        $this->fileSystem->dumpFile($outputFilePath, $inputContents);
        $originalFilePermissions = fileperms($inputFilePath) & 0777;
        if ($originalFilePermissions !== 420) {
            $this->fileSystem->chmod($outputFilePath, $originalFilePermissions);
        }
    }
    private static function getFiles(Configuration $config, string $outputDir) : array
    {
        $filesWithContent = $config->getFilesWithContents();
        $excludedFilesWithContents = $config->getExcludedFilesWithContents();
        $commonPath = get_common_path([...array_keys($filesWithContent), ...array_keys($excludedFilesWithContents)]);
        $mapFiles = static fn(array $inputFileTuple) => [$inputFileTuple[0], $inputFileTuple[1], $outputDir . str_replace($commonPath, '', $inputFileTuple[0])];
        return [array_map($mapFiles, $filesWithContent), array_map($mapFiles, $excludedFilesWithContents)];
    }
    private static function findVendorDir(array $outputFilePaths) : ?string
    {
        $vendorDirsAsKeys = [];
        foreach ($outputFilePaths as $filePath) {
            if (native_preg_match(self::VENDOR_DIR_PATTERN, $filePath, $matches)) {
                $vendorDirsAsKeys[$matches[1]] = \true;
            }
        }
        $vendorDirs = array_keys($vendorDirsAsKeys);
        usort($vendorDirs, static fn($a, $b) => strlen((string) $a) <=> strlen((string) $b));
        return 0 === count($vendorDirs) ? null : (string) $vendorDirs[0];
    }
    private function scopeFile(Scoper $scoper, string $inputFilePath, string $inputContents, string $outputFilePath, bool $stopOnFailure, ScoperLogger $logger) : void
    {
        try {
            $scoppedContent = $scoper->scope($inputFilePath, $inputContents);
        } catch (Throwable $throwable) {
            $exception = new ParsingException(sprintf('Could not parse the file "%s".', $inputFilePath), 0, $throwable);
            if ($stopOnFailure) {
                throw $exception;
            }
            $logger->outputWarnOfFailure($inputFilePath, $exception);
            $scoppedContent = file_get_contents($inputFilePath);
        }
        $this->dumpFileWithPermissions($inputFilePath, $scoppedContent, $outputFilePath);
        if (!isset($exception)) {
            $logger->outputSuccess($inputFilePath);
        }
    }
}
