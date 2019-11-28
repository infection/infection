<?php

declare(strict_types=1);


namespace Codeception_Basic\Inner;


class InnerSourceClass
{
    public function sub(float $a, float $b): float
    {
        return $a - $b;
    }
}
