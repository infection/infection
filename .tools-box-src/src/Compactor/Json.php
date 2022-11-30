<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\KevinGH\Box\Compactor;

use function json_decode;
use function json_encode;
use const JSON_ERROR_NONE;
use function json_last_error;
use const JSON_THROW_ON_ERROR;
final class Json extends FileExtensionCompactor
{
    public function __construct(array $extensions = ['json', 'lock'])
    {
        parent::__construct($extensions);
    }
    protected function compactContent(string $contents) : string
    {
        $decodedContents = json_decode($contents, \false);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return $contents;
        }
        return json_encode($decodedContents, JSON_THROW_ON_ERROR);
    }
}
\class_alias('_HumbugBoxb47773b41c19\\KevinGH\\Box\\Compactor\\Json', 'KevinGH\\Box\\Compactor\\Json', \false);
