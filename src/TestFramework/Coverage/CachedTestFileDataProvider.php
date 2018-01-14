<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\TestFramework\Coverage;

class CachedTestFileDataProvider implements TestFileDataProvider
{
    /**
     * @var TestFileDataProvider
     */
    private $testFileDataProvider;

    /**
     * @var array
     */
    private $testFileInfoCache = [];

    public function __construct(TestFileDataProvider $testFileDataProvider)
    {
        $this->testFileDataProvider = $testFileDataProvider;
    }

    public function getTestFileInfo(string $fullyQualifiedClassName): array
    {
        if (array_key_exists($fullyQualifiedClassName, $this->testFileInfoCache)) {
            return $this->testFileInfoCache[$fullyQualifiedClassName];
        }

        return $this->testFileInfoCache[$fullyQualifiedClassName] = $this->testFileDataProvider->getTestFileInfo(
            $fullyQualifiedClassName
        );
    }
}
