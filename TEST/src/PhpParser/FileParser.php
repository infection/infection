<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\PhpParser;

use _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;
use _HumbugBox9658796bb9f0\PhpParser\Parser;
use _HumbugBox9658796bb9f0\Symfony\Component\Finder\SplFileInfo;
use Throwable;
class FileParser
{
    public function __construct(private Parser $parser)
    {
    }
    public function parse(SplFileInfo $fileInfo) : array
    {
        try {
            return $this->parser->parse($fileInfo->getContents()) ?? [];
        } catch (Throwable $throwable) {
            $filePath = $fileInfo->getRealPath() === \false ? $fileInfo->getPathname() : $fileInfo->getRealPath();
            throw UnparsableFile::fromInvalidFile($filePath, $throwable);
        }
    }
}
