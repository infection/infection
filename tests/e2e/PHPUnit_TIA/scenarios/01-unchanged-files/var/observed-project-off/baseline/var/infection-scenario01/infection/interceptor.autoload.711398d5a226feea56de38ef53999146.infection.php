<?php

if (function_exists('proc_nice')) {
    proc_nice(1);
}

require_once '/Users/tfidry/Project/Humbug/infection/vendor/infection/include-interceptor/src/IncludeInterceptor.php';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/observed-project-off/baseline/src/Calculator.php', '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/observed-project-off/baseline/var/infection-scenario01/infection/mutant.711398d5a226feea56de38ef53999146.infection.php');
IncludeInterceptor::enable();
require_once '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/observed-project-off/baseline/bootstrap.php';
