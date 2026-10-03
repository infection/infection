<?php

if (function_exists('proc_nice')) {
    proc_nice(1);
}

require_once '/Users/tfidry/Project/Humbug/infection/vendor/infection/include-interceptor/src/IncludeInterceptor.php';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/observed-project-on/project/src/Calculator.php', '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/observed-project-on/project/var/infection-scenario01/infection/mutant.552e20946d4de20e43c313f90fabb58f.infection.php');
IncludeInterceptor::enable();
require_once '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/observed-project-on/project/bootstrap.php';
