<?php
/**
 * This code is licensed under the BSD 3-Clause License.
 *
 * Copyright (c) 2017, Maks Rafalko
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * * Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * * Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * * Neither the name of the copyright holder nor the names of its
 *   contributors may be used to endorse or promote products derived from
 *   this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE
 * FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
 * DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY,
 * OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

declare(strict_types=1);

namespace Infection\Tests\Mutation\FileMutationGenerator;

use function current;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\Mutation;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Testing\MutatorName;
use Infection\Testing\SingletonContainer;
use Infection\Tests\TestFramework\Tracing\DummyTracer;
use Infection\Tests\TestingUtility\FileSystem\MockSplFileInfo;
use function iterator_to_array;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('integration')]
#[CoversClass(FileMutationGenerator::class)]
final class FileMutationGeneratorIntegrationTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/Fixtures';

    public function test_it_generates_mutations_for_a_given_file(): void
    {
        $fileInfoMock = new MockSplFileInfo(realPath: self::FIXTURES_DIR . '/TwoAdditions.php');

        $mutators = [new Plus()];

        $mutationGenerator = new FileMutationGenerator(
            SingletonContainer::getContainer()->getFileParser(),
            SingletonContainer::getContainer()->getNodeTraverserFactory(),
            SingletonContainer::getContainer()->getLineRangeCalculator(),
            SingletonContainer::getContainer()->getSourceLineMatcher(),
            new DummyTracer(),
            SingletonContainer::getContainer()->getFileStore(),
        );

        $mutations = $mutationGenerator->generate(
            $fileInfoMock,
            false,
            $mutators,
        );

        $mutations = iterator_to_array($mutations, false);

        $this->assertContainsOnlyInstancesOf(Mutation::class, $mutations);

        $this->assertCount(1, $mutations);
        $this->assertArrayHasKey(0, $mutations);

        /** @var Mutation $mutation */
        $mutation = current($mutations);

        $this->assertSame(
            MutatorName::getName(Plus::class),
            $mutation->getMutatorName(),
        );
    }
}
