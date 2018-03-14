<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator;

use Infection\Mutator\MutatorProfile;
use PHPUnit\Framework\TestCase;

class MutatorProfileTest extends TestCase
{
    public function test_all_mutators_have_the_correct_name_in_the_full_mutator_list()
    {
        foreach (MutatorProfile::FULL_MUTATOR_LIST as $name => $class) {
            $this->assertSame(
                $name,
                $class::getName(),
                sprintf(
                    'Invalid name "%s" provided for the class "%s", expected "%s" as key',
                    $name,
                    $class,
                    $class::getName()
                )
            );
        }
    }
}
