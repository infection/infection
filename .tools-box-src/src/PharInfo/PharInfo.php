<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\PharInfo;

use function array_flip;
use function _HumbugBoxb47773b41c19\KevinGH\Box\get_phar_compression_algorithms;
use function _HumbugBoxb47773b41c19\KevinGH\Box\unique_id;
use Phar;
use PharData;
use PharFileInfo;
use RecursiveIteratorIterator;
use UnexpectedValueException;
final class PharInfo
{
    private static array $ALGORITHMS;
    private PharData|Phar $phar;
    private ?array $compressionCount = null;
    private ?string $hash = null;
    public function __construct(string $pharFile)
    {
        if (!isset(self::$ALGORITHMS)) {
            self::$ALGORITHMS = array_flip(get_phar_compression_algorithms());
            self::$ALGORITHMS[Phar::NONE] = 'None';
        }
        try {
            $this->phar = new Phar($pharFile);
        } catch (UnexpectedValueException) {
            $this->phar = new PharData($pharFile);
        }
    }
    public function equals(self $pharInfo) : bool
    {
        return $pharInfo->getCompressionCount() === $this->getCompressionCount() && $pharInfo->getNormalizedMetadata() === $this->getNormalizedMetadata();
    }
    public function getCompressionCount() : array
    {
        if (null === $this->compressionCount || $this->hash !== $this->getPharHash()) {
            $this->compressionCount = $this->calculateCompressionCount();
            $this->hash = $this->getPharHash();
        }
        return $this->compressionCount;
    }
    public function getPhar() : Phar|PharData
    {
        return $this->phar;
    }
    public function getRoot() : string
    {
        return 'phar://' . \str_replace('\\', '/', \realpath($this->phar->getPath())) . '/';
    }
    public function getVersion() : string
    {
        return '' !== $this->phar->getVersion() ? $this->phar->getVersion() : 'No information found';
    }
    public function getNormalizedMetadata() : ?string
    {
        $metadata = \var_export($this->phar->getMetadata(), \true);
        return 'NULL' === $metadata ? null : $metadata;
    }
    private function getPharHash() : string
    {
        return $this->phar->getSignature()['hash'] ?? unique_id('');
    }
    private function calculateCompressionCount() : array
    {
        $count = \array_fill_keys(self::$ALGORITHMS, 0);
        if ($this->phar instanceof PharData) {
            $count[self::$ALGORITHMS[$this->phar->isCompressed()]] = 1;
            return $count;
        }
        $countFile = static function (array $count, PharFileInfo $file) : array {
            if (\false === $file->isCompressed()) {
                ++$count['None'];
                return $count;
            }
            foreach (self::$ALGORITHMS as $compressionAlgorithmCode => $compressionAlgorithmName) {
                if ($file->isCompressed($compressionAlgorithmCode)) {
                    ++$count[$compressionAlgorithmName];
                    return $count;
                }
            }
            return $count;
        };
        return \array_reduce(\iterator_to_array(new RecursiveIteratorIterator($this->phar), \true), $countFile, $count);
    }
}
