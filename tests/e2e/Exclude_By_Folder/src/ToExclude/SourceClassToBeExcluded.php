<?php

namespace Exclude_By_Folder\ToExclude;

class SourceClassToBeExcluded
{
    public function hello(): string
    {
        $a = 1 + 2;
        return 'hello';
    }
}
