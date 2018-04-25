<?php

namespace Namespace_;

class SourceClass
{
    public function count(): int
    {
        $result = [];

        do {
            $result[] = new \SplFixedArray(1<<22);
        } while (false);

        return count($result);
    }
}
