<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator;

use Infection\Mutator\Util\MutatorProfile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

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

    public function test_all_mutators_are_part_of_the_full_mutators_list()
    {
        /** @var Finder $files */
        $files = Finder::create()
            ->name('*.php')
            ->in('src/Mutator')
            ->exclude('Util')
            ->files();

        foreach ($files as $file) {
            $class = substr($file->getFilename(), 0, -4);

            $this->assertArrayHasKey(
                $class,
                MutatorProfile::FULL_MUTATOR_LIST,
                sprintf(
                    'The mutator "%s" located in "%s" has not been added to the FULL_MUTATOR_LIST in the Mutator Profile class' .
                    'Please add it to ensure it can be used.',
                    $class,
                    $file->getPath()
                )
            );
        }
    }
}
