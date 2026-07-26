<?php

namespace StyleTag;

class SourceClass
{
    public function hello(): string
    {
        preg_match('/(?:.*controller\\\|.*controllers\\\)([\w\\\]+)controller$/iU', '', $m);

        return 'hello';
    }
}
