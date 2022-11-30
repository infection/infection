<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box;

use _HumbugBoxb47773b41c19\Amp\MultiReasonException;
use function _HumbugBoxb47773b41c19\Amp\ParallelFunctions\parallelMap;
use function _HumbugBoxb47773b41c19\Amp\Promise\wait;
use function array_filter;
use function array_flip;
use function array_map;
use function array_unshift;
use BadMethodCallException;
use function chdir;
use Countable;
use function dirname;
use function extension_loaded;
use function file_exists;
use function getcwd;
use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Symbol\SymbolsRegistry;
use function is_object;
use _HumbugBoxb47773b41c19\KevinGH\Box\Compactor\Compactors;
use _HumbugBoxb47773b41c19\KevinGH\Box\Compactor\PhpScoper;
use _HumbugBoxb47773b41c19\KevinGH\Box\Compactor\Placeholder;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\dump_file;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\file_contents;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\make_tmp_dir;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\mkdir;
use function _HumbugBoxb47773b41c19\KevinGH\Box\FileSystem\remove;
use _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper\NullScoper;
use _HumbugBoxb47773b41c19\KevinGH\Box\PhpScoper\Scoper;
use function openssl_pkey_export;
use function openssl_pkey_get_details;
use function openssl_pkey_get_private;
use Phar;
use RecursiveDirectoryIterator;
use RuntimeException;
use SplFileInfo;
use function sprintf;
use _HumbugBoxb47773b41c19\Webmozart\Assert\Assert;
final class Box implements Countable
{
    private Compactors $compactors;
    private Placeholder $placeholderCompactor;
    private MapFile $mapFile;
    private Scoper $scoper;
    private bool $buffering = \false;
    private array $bufferedFiles = [];
    private function __construct(private readonly Phar $phar, private readonly string $pharFilePath)
    {
        $this->compactors = new Compactors();
        $this->placeholderCompactor = new Placeholder([]);
        $this->mapFile = new MapFile(getcwd(), []);
        $this->scoper = new NullScoper();
    }
    public static function create(string $pharFilePath, int $pharFlags = 0, ?string $pharAlias = null) : self
    {
        mkdir(dirname($pharFilePath));
        return new self(new Phar($pharFilePath, $pharFlags, $pharAlias), $pharFilePath);
    }
    public function startBuffering() : void
    {
        Assert::false($this->buffering, 'The buffering must be ended before starting it again');
        $this->buffering = \true;
        $this->phar->startBuffering();
    }
    public function endBuffering(?callable $dumpAutoload) : void
    {
        Assert::true($this->buffering, 'The buffering must be started before ending it');
        $dumpAutoload ??= static fn() => null;
        $cwd = getcwd();
        $tmp = make_tmp_dir('box', self::class);
        chdir($tmp);
        if ([] === $this->bufferedFiles) {
            $this->bufferedFiles = ['.box_empty' => 'A PHAR cannot be empty so Box adds this file to ensure the PHAR is created still.'];
        }
        try {
            foreach ($this->bufferedFiles as $file => $contents) {
                dump_file($file, $contents);
            }
            if (null !== $dumpAutoload) {
                $dumpAutoload($this->scoper->getSymbolsRegistry(), $this->scoper->getPrefix());
            }
            chdir($cwd);
            $this->phar->buildFromDirectory($tmp);
        } finally {
            remove($tmp);
        }
        $this->buffering = \false;
        $this->phar->stopBuffering();
    }
    public function removeComposerArtefacts(string $normalizedVendorDir) : void
    {
        Assert::false($this->buffering, 'The buffering must have ended before removing the Composer artefacts');
        $composerFiles = ['composer.json', 'composer.lock', $normalizedVendorDir . '/composer/installed.json'];
        $this->phar->startBuffering();
        foreach ($composerFiles as $composerFile) {
            $localComposerFile = ($this->mapFile)($composerFile);
            $pharFilePath = sprintf('phar://%s/%s', $this->phar->getPath(), $localComposerFile);
            if (file_exists($pharFilePath)) {
                $this->phar->delete($localComposerFile);
            }
        }
        $this->phar->stopBuffering();
    }
    public function compress(int $compressionAlgorithm) : ?string
    {
        Assert::false($this->buffering, 'Cannot compress files while buffering.');
        Assert::inArray($compressionAlgorithm, get_phar_compression_algorithms());
        $extensionRequired = get_phar_compression_algorithm_extension($compressionAlgorithm);
        if (null !== $extensionRequired && \false === extension_loaded($extensionRequired)) {
            throw new RuntimeException(sprintf('Cannot compress the PHAR with the compression algorithm "%s": the extension "%s" is required but appear to not be loaded', array_flip(get_phar_compression_algorithms())[$compressionAlgorithm], $extensionRequired));
        }
        try {
            if (Phar::NONE === $compressionAlgorithm) {
                $this->getPhar()->decompressFiles();
            } else {
                $this->phar->compressFiles($compressionAlgorithm);
            }
        } catch (BadMethodCallException $exception) {
            $exceptionMessage = 'unable to create temporary file' !== $exception->getMessage() ? 'Could not compress the PHAR: ' . $exception->getMessage() : sprintf('Could not compress the PHAR: the compression requires too many file descriptors to be opened (%s). Check your system limits or install the posix extension to allow Box to automatically configure it during the compression', $this->phar->count());
            throw new RuntimeException($exceptionMessage, $exception->getCode(), $exception);
        }
        return $extensionRequired;
    }
    public function registerCompactors(Compactors $compactors) : void
    {
        $compactorsArray = $compactors->toArray();
        foreach ($compactorsArray as $index => $compactor) {
            if ($compactor instanceof PhpScoper) {
                $this->scoper = $compactor->getScoper();
                continue;
            }
            if ($compactor instanceof Placeholder) {
                unset($compactorsArray[$index]);
            }
        }
        array_unshift($compactorsArray, $this->placeholderCompactor);
        $this->compactors = new Compactors(...$compactorsArray);
    }
    public function registerPlaceholders(array $placeholders) : void
    {
        $message = 'Expected value "%s" to be a scalar or stringable object.';
        foreach ($placeholders as $index => $placeholder) {
            if (is_object($placeholder)) {
                Assert::methodExists($placeholder, '__toString', $message);
                $placeholders[$index] = (string) $placeholder;
                break;
            }
            Assert::scalar($placeholder, $message);
        }
        $this->placeholderCompactor = new Placeholder($placeholders);
        $this->registerCompactors($this->compactors);
    }
    public function registerFileMapping(MapFile $fileMapper) : void
    {
        $this->mapFile = $fileMapper;
    }
    public function registerStub(string $file) : void
    {
        $contents = $this->placeholderCompactor->compact($file, file_contents($file));
        $this->phar->setStub($contents);
    }
    public function addFiles(array $files, bool $binary) : void
    {
        Assert::true($this->buffering, 'Cannot add files if the buffering has not started.');
        $files = array_map('strval', $files);
        if ($binary) {
            foreach ($files as $file) {
                $this->addFile($file, null, \true);
            }
            return;
        }
        foreach ($this->processContents($files) as [$file, $contents]) {
            $this->bufferedFiles[$file] = $contents;
        }
    }
    public function addFile(string $file, ?string $contents = null, bool $binary = \false) : string
    {
        Assert::true($this->buffering, 'Cannot add files if the buffering has not started.');
        if (null === $contents) {
            $contents = file_contents($file);
        }
        $local = ($this->mapFile)($file);
        $this->bufferedFiles[$local] = $binary ? $contents : $this->compactors->compact($local, $contents);
        return $local;
    }
    public function getPhar() : Phar
    {
        return $this->phar;
    }
    public function signUsingFile(string $file, ?string $password = null) : void
    {
        $this->sign(file_contents($file), $password);
    }
    public function sign(string $key, ?string $password) : void
    {
        $pubKey = $this->pharFilePath . '.pubkey';
        Assert::writable(dirname($pubKey));
        Assert::true(extension_loaded('openssl'));
        if (file_exists($pubKey)) {
            Assert::file($pubKey, 'Cannot create public key: %s already exists and is not a file.');
        }
        $resource = openssl_pkey_get_private($key, (string) $password);
        Assert::notSame(\false, $resource, 'Could not retrieve the private key, check that the password is correct.');
        openssl_pkey_export($resource, $private);
        $details = openssl_pkey_get_details($resource);
        $this->phar->setSignatureAlgorithm(Phar::OPENSSL, $private);
        dump_file($pubKey, $details['key']);
    }
    private function processContents(array $files) : array
    {
        $mapFile = $this->mapFile;
        $compactors = $this->compactors;
        $cwd = getcwd();
        $processFile = static function (string $file) use($cwd, $mapFile, $compactors) : array {
            chdir($cwd);
            \_HumbugBoxb47773b41c19\KevinGH\Box\register_aliases();
            if (\true === \_HumbugBoxb47773b41c19\KevinGH\Box\is_parallel_processing_enabled()) {
                \_HumbugBoxb47773b41c19\KevinGH\Box\register_error_handler();
            }
            $contents = file_contents($file);
            $local = $mapFile($file);
            $processedContents = $compactors->compact($local, $contents);
            return [$local, $processedContents, $compactors->getScoperSymbolsRegistry()];
        };
        if ($this->scoper instanceof NullScoper || \false === is_parallel_processing_enabled()) {
            return array_map($processFile, $files);
        }
        $tuples = wait(parallelMap($files, $processFile));
        if ([] === $tuples) {
            return [];
        }
        $filesWithContents = [];
        $symbolRegistries = [];
        foreach ($tuples as [$local, $processedContents, $symbolRegistry]) {
            $filesWithContents[] = [$local, $processedContents];
            $symbolRegistries[] = $symbolRegistry;
        }
        $this->compactors->registerSymbolsRegistry(SymbolsRegistry::createFromRegistries(array_filter($symbolRegistries)));
        return $filesWithContents;
    }
    public function count() : int
    {
        Assert::false($this->buffering, 'Cannot count the number of files in the PHAR when buffering');
        return $this->phar->count();
    }
}
