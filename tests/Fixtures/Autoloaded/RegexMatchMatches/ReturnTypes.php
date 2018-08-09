<?php

namespace RegexMatchMatches;

class ReturnTypes
{
    public function foo()
    {
        return preg_match('/a/', 'foobar', $matches);
    }
}
