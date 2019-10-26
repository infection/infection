<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Configuration\Entry\Mutator;

/**
 * @internal
 */
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
