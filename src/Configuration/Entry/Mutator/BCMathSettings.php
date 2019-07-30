<?php

declare(strict_types=1);

namespace Infection\Configuration\Entry\Mutator;

final class BCMathSettings
{
    private $bcadd;
    private $bccomp;
    private $bcdiv;
    private $bcmod;
    private $bcmul;
    private $bcpow;
    private $bcsub;
    private $bcsqrt;
    private $bcpowmod;

    public function __construct(
        bool $bcadd,
        bool $bccomp,
        bool $bcdiv,
        bool $bcmod,
        bool $bcmul,
        bool $bcpow,
        bool $bcsub,
        bool $bcsqrt,
        bool $bcpowmod
    ) {
        $this->bcadd = $bcadd;
        $this->bccomp = $bccomp;
        $this->bcdiv = $bcdiv;
        $this->bcmod = $bcmod;
        $this->bcmul = $bcmul;
        $this->bcpow = $bcpow;
        $this->bcsub = $bcsub;
        $this->bcsqrt = $bcsqrt;
        $this->bcpowmod = $bcpowmod;
    }

    public function isBcadd(): bool
    {
        return $this->bcadd;
    }

    public function isBccomp(): bool
    {
        return $this->bccomp;
    }

    public function isBcdiv(): bool
    {
        return $this->bcdiv;
    }

    public function isBcmod(): bool
    {
        return $this->bcmod;
    }

    public function isBcmul(): bool
    {
        return $this->bcmul;
    }

    public function isBcpow(): bool
    {
        return $this->bcpow;
    }

    public function isBcsub(): bool
    {
        return $this->bcsub;
    }

    public function isBcsqrt(): bool
    {
        return $this->bcsqrt;
    }

    public function isBcpowmod(): bool
    {
        return $this->bcpowmod;
    }
}