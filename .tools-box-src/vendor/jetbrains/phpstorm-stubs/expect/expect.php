<?php




const EXP_GLOB = 1;




const EXP_EXACT = 2;




const EXP_REGEXP = 3;




const EXP_EOF = -11;




const EXP_TIMEOUT = -2;




const EXP_FULLBUFFER = -5;










function expect_popen(string $command)
{
unset($command);
return false;
}
















































function expect_expectl($expect, array $cases, array &$match = []): int
{
unset($expect, $cases, $match);
return 0;
}
