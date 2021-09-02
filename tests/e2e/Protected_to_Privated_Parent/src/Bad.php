<?php

declare(strict_types=1);


namespace Protected_to_Privated_Parent;

use Protected_to_Privated_Parent\Base;

final class Bad extends Base
{
    protected function getIntInner(): int
    {
        return 2;
    }
}
