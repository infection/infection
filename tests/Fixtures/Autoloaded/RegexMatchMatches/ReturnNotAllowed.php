<?php

namespace RegexMatchMatches;

class ReturnNotAllowed
{
    public function foo() : int
    {
        return preg_match('/a/', 'foobar', $matches);
    }
}
