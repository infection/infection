<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Composer;

use _HumbugBoxb47773b41c19\Humbug\PhpScoper\Scoper\Scoper;
use InvalidArgumentException;
use stdClass;
use function gettype;
use function preg_match as native_preg_match;
use function _HumbugBoxb47773b41c19\Safe\json_decode;
use function _HumbugBoxb47773b41c19\Safe\json_encode;
use function sprintf;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
final class JsonFileScoper implements Scoper
{
    public function __construct(private readonly Scoper $decoratedScoper, private readonly AutoloadPrefixer $autoloadPrefixer)
    {
    }
    public function scope(string $filePath, string $contents) : string
    {
        if (1 !== native_preg_match('/composer\\.json$/', $filePath)) {
            return $this->decoratedScoper->scope($filePath, $contents);
        }
        $decodedJson = self::decodeContents($contents);
        $decodedJson = $this->autoloadPrefixer->prefixPackageAutoloadStatements($decodedJson);
        return json_encode($decodedJson, JSON_PRETTY_PRINT);
    }
    private static function decodeContents(string $contents) : stdClass
    {
        $decodedJson = json_decode($contents, \false, 512, JSON_THROW_ON_ERROR);
        if ($decodedJson instanceof stdClass) {
            return $decodedJson;
        }
        throw new InvalidArgumentException(sprintf('Expected the decoded JSON to be an stdClass instance, got "%s" instead', gettype($decodedJson)));
    }
}
