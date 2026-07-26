<?php

declare(strict_types=1);

namespace Infection\Tests\Fixtures\Console;

use function class_alias;
use Composer\InstalledVersions;
use function version_compare;

if (version_compare((string)InstalledVersions::getPrettyVersion('symfony/console'), 'v6.0', '<')) {
    class_alias(FakeOutputSymfony5::class, \Infection\Tests\Fixtures\Console\FakeOutput::class);
} elseif (version_compare((string)InstalledVersions::getPrettyVersion('symfony/console'), 'v7.0', '<')) {
    class_alias(FakeOutputSymfony6::class, \Infection\Tests\Fixtures\Console\FakeOutput::class);
} else {
   class_alias(FakeOutputSymfony7::class, \Infection\Tests\Fixtures\Console\FakeOutput::class);
}
