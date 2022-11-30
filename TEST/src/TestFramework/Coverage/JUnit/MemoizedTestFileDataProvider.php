<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\Infection\TestFramework\Coverage\JUnit;

use function array_key_exists;
final class MemoizedTestFileDataProvider implements TestFileDataProvider
{
    private array $cache = [];
    public function __construct(private TestFileDataProvider $provider)
    {
    }
    public function getTestFileInfo(string $fullyQualifiedClassName) : TestFileTimeData
    {
        if (!array_key_exists($fullyQualifiedClassName, $this->cache)) {
            $this->cache[$fullyQualifiedClassName] = $this->provider->getTestFileInfo($fullyQualifiedClassName);
        }
        return $this->cache[$fullyQualifiedClassName];
    }
}
