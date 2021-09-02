<?php

declare(strict_types=1);


namespace Protected_to_Privated_Parent;


abstract class Base
{
    public function getIntOuter(): int
    {
        return $this->getIntInner();
    }

    protected function getIntInner(): int
    {
        return 1;
    }
}
