<?php

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
        if ($$a() < 3 || 3 > $$a() || 3 < 'func'()) {}
        if ($b() > 12 || 12 < $b() || 'func'() > 3) {}
        if ($$a() == 12 || 12 == $$a() || 'func'() == 3) {}
        if ($b() === 12 || 12 === $b() || 'func'() === 12) {}
        // With 0
        if ($$b() < 0 || $$b > 0 || 'func'() == 0) {}
        if ($a() > 0 || 0 < $b() || 0 == 'func'()) {}
        if ($$a() == 0 || 0 == $$b() || 'func'() === 0 ) {}
        if ($a() === 0 || 0 === $b() || 0 === 'func'()) {}
        // With 1
        if ($$b() < 1 || $$b > 1 || 'func'() == 1) {}
        if ($a() > 1 || 1 < $b() || 1 == 'func'()) {}
        if ($$a() == 1 || 1 == $$b() || 'func'() === 1) {}
        if ($a() === 1 || 1 === $b() || 1 === 'func'()) {}

    }
}
