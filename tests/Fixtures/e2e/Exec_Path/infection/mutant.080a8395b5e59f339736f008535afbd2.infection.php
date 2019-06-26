<?php

namespace ExecPath;

class RunShellScript
{
    public function hello() : string
    {
        
        if ($returnVal > 0) {
            throw new \RuntimeException("Failed to run a program");
        }
        return $output[0];
    }
}