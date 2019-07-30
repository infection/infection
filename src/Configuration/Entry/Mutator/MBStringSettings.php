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

final class MBStringSettings
{
    private $mbChr;
    private $mbOrd;
    private $mbParseStr;
    private $mbSendMail;
    private $mbStrcut;
    private $mbStripos;
    private $mbStristr;
    private $mbStrlen;
    private $mbStrpos;
    private $mbStrrchr;
    private $mbStrripos;
    private $mbStrrpos;
    private $mbStrstr;
    private $mbStrtolower;
    private $mbStrtoupper;
    private $mbSubstrCount;
    private $mbSubstr;
    private $mbConvertCase;

    public function __construct(
        bool $mbChr,
        bool $mbOrd,
        bool $mbParseStr,
        bool $mbSendMail,
        bool $mbStrcut,
        bool $mbStripos,
        bool $mbStristr,
        bool $mbStrlen,
        bool $mbStrpos,
        bool $mbStrrchr,
        bool $mbStrripos,
        bool $mbStrrpos,
        bool $mbStrstr,
        bool $mbStrtolower,
        bool $mbStrtoupper,
        bool $mbSubstrCount,
        bool $mbSubstr,
        bool $mbConvertCase
    ) {
        $this->mbChr = $mbChr;
        $this->mbOrd = $mbOrd;
        $this->mbParseStr = $mbParseStr;
        $this->mbSendMail = $mbSendMail;
        $this->mbStrcut = $mbStrcut;
        $this->mbStripos = $mbStripos;
        $this->mbStristr = $mbStristr;
        $this->mbStrlen = $mbStrlen;
        $this->mbStrpos = $mbStrpos;
        $this->mbStrrchr = $mbStrrchr;
        $this->mbStrripos = $mbStrripos;
        $this->mbStrrpos = $mbStrrpos;
        $this->mbStrstr = $mbStrstr;
        $this->mbStrtolower = $mbStrtolower;
        $this->mbStrtoupper = $mbStrtoupper;
        $this->mbSubstrCount = $mbSubstrCount;
        $this->mbSubstr = $mbSubstr;
        $this->mbConvertCase = $mbConvertCase;
    }

    public function isMbChr(): bool
    {
        return $this->mbChr;
    }

    public function isMbOrd(): bool
    {
        return $this->mbOrd;
    }

    public function isMbParseStr(): bool
    {
        return $this->mbParseStr;
    }

    public function isMbSendMail(): bool
    {
        return $this->mbSendMail;
    }

    public function isMbStrcut(): bool
    {
        return $this->mbStrcut;
    }

    public function isMbStripos(): bool
    {
        return $this->mbStripos;
    }

    public function isMbStristr(): bool
    {
        return $this->mbStristr;
    }

    public function isMbStrlen(): bool
    {
        return $this->mbStrlen;
    }

    public function isMbStrpos(): bool
    {
        return $this->mbStrpos;
    }

    public function isMbStrrchr(): bool
    {
        return $this->mbStrrchr;
    }

    public function isMbStrripos(): bool
    {
        return $this->mbStrripos;
    }

    public function isMbStrrpos(): bool
    {
        return $this->mbStrrpos;
    }

    public function isMbStrstr(): bool
    {
        return $this->mbStrstr;
    }

    public function isMbStrtolower(): bool
    {
        return $this->mbStrtolower;
    }

    public function isMbStrtoupper(): bool
    {
        return $this->mbStrtoupper;
    }

    public function isMbSubstrCount(): bool
    {
        return $this->mbSubstrCount;
    }

    public function isMbSubstr(): bool
    {
        return $this->mbSubstr;
    }

    public function isMbConvertCase(): bool
    {
        return $this->mbConvertCase;
    }
}
