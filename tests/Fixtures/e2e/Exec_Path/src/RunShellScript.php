<?php

namespace Namespace_;

class RunShellScript
{
    public function hello(): string
    {
    	exec('test.bash', $output, $returnVal);

		if ($returnVal > 0) {
			throw new \RuntimeException("Failed to run a program");
		}

		return $output[0];
    }
}
