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

namespace Infection\CodeSamples;

class VariableFunctionNames
{
    public function variableFunctionNames(): void
    {
        $a = 'fake_function';
        $b = 'somethign';

        $a('do the thing');

        'a string can be a function as well'();
        ('it can also be in between brackets')();

        //In assignments
        $a = $$b();
        $b = $a();
        $c = 'function'();

        //Outside of assignments
        $$b();
        $a();
        'function'();

        // As static method calls
        $a::$b();
        $$a::$b();
        $a::$$b();
        $$a::$$b();
        'Class'::$a();
        //'Class'::'function'(); is invalid syntax

        // As method calls
        $a->$b();
        $$a->$b();
        $a->$$b();
        $$a->$$b();
        'Class'->$c();
        //'Class'->'function'(); is invalid syntax

        // As static method calls in assignments
        $a = $a::$b();
        $b = $$a::$b();
        $a = $a::$$b();
        $b = $$a::$$b();
        $c = 'Class'::$c();

        // As method calls in assignments
        $a = $a->$b();
        $b = $$a->$b();
        $a = $a->$$b();
        $b = $$a->$$b();
        $c = 'Class'->$c();

        //With an array
        $$a = $a[$b->$a($a->$$$b::$a)];

        // With comparisons
        if ($$a() < 3 || $$a() < 3 || 'func'() > 3) {
        }

        if ($b() > 12 || $b() > 12 || 'func'() > 3) {
        }

        if ($$a() === 12 || $$a() === 12 || 'func'() === 3) {
        }

        if ($b() === 12 || $b() === 12 || 'func'() === 12) {
        }
        // With 0
        if ($$b() < 0 || $$b > 0 || 'func'() === 0) {
        }

        if ($a() > 0 || $b() > 0 || 'func'() === 0) {
        }

        if ($$a() === 0 || $$b() === 0 || 'func'() === 0) {
        }

        if ($a() === 0 || $b() === 0 || 'func'() === 0) {
        }
        // With 1
        if ($$b() < 1 || $$b > 1 || 'func'() === 1) {
        }

        if ($a() > 1 || $b() > 1 || 'func'() === 1) {
        }

        if ($$a() === 1 || $$b() === 1 || 'func'() === 1) {
        }

        if ($a() === 1 || $b() === 1 || 'func'() === 1) {
        }
    }
}
