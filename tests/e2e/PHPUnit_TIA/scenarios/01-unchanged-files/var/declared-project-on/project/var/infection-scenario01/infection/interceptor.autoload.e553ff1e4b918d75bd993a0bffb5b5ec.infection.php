<?php

if (function_exists('proc_nice')) {
    proc_nice(1);
}

require_once '/Users/tfidry/Project/Humbug/infection/vendor/infection/include-interceptor/src/IncludeInterceptor.php';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/declared-project-on/project/src/Calculator.php', '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/declared-project-on/project/var/infection-scenario01/infection/mutant.e553ff1e4b918d75bd993a0bffb5b5ec.infection.php');
IncludeInterceptor::enable();
require_once '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/declared-project-on/project/bootstrap.php';
