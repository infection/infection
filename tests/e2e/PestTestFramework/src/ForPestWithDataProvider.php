<?php

declare(strict_types=1);


namespace PestTestFramework;


final class ForPestWithDataProvider
{
    public function div(float $a, float $b): float
    {
        return $a / $b;
    }
}
