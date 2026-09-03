<?php

if (function_exists('proc_nice')) {
    proc_nice(1);
}

require_once '/Users/tfidry/Project/Humbug/infection/vendor/infection/include-interceptor/src/IncludeInterceptor.php';

use Infection\StreamWrapper\IncludeInterceptor;

IncludeInterceptor::intercept('/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TestSuite_Bootstrap/src/Calculator.php', '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TestSuite_Bootstrap/var/infection-tmp/infection/mutant.b1062079895565f755701cd5f6262023.infection.php');
IncludeInterceptor::enable();
require_once '/Users/tfidry/Project/Humbug/infection/tests/e2e/PHPUnit_TestSuite_Bootstrap/vendor/autoload.php';
