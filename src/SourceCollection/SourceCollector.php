<?php

namespace Infection\SourceCollection;

use SplFileInfo;

interface SourceCollector
{
    /**
     * @return iterable<SplFileInfo>
     */
    public function collect(): iterable;
}
