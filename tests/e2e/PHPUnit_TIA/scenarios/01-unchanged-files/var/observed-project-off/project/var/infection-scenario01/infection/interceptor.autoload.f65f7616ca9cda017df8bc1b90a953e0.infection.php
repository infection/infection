<?php

if (function_exists('proc_nice')) {
    proc_nice(1);
}

require_once '/Users/tfidry/Project/Humbug/infection/vendor/infection/include-interceptor/src/IncludeInterceptor.php';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/observed-project-off/project/src/Calculator.php', '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/observed-project-off/project/var/infection-scenario01/infection/mutant.f65f7616ca9cda017df8bc1b90a953e0.infection.php');
IncludeInterceptor::enable();
require_once '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TIA/scenarios/01-unchanged-files/var/observed-project-off/project/bootstrap.php';
