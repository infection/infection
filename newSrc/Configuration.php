<?php

namespace newSrc;

use SplFileInfo;

interface Configuration
{
    public function shouldSkipInitialTests(string $frameworkName): bool;

    /**
     * @return iterable<SplFileInfo>
     */
    public function getSourceFiles(): iterable;
}
