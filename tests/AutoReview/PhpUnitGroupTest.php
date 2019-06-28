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

namespace Infection\Tests\AutoReview;

use Generator;
use function implode;
use Infection\Tests\AutoReview\PhpDoc\ClassParser;
use PHPUnit\Framework\TestCase;
use function sprintf;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @internal
 *
 * @coversNothing
 *
 * @group autoReview
 */
final class PhpUnitGroupTest extends TestCase
{
    private const AUTO_REVIEW_DIR = __DIR__ . '/../AutoReview';

    /**
     * @dataProvider autoReviewFilesProvider
     */
    public function test_all_auto_review_tests_are_properly_tagged_in_the_auto_review_group(
        string $filePath,
        string $fileContent
    ): void {
        $tags = ClassParser::parseFilePhpDoc($fileContent);

        $tagStrings = [];

        foreach ($tags as $tag => $value) {
            if ('@group' === $tag && 'autoReview' === $value) {
                $this->assertTrue(true);

                return;
            }

            $tagStrings[] = trim(sprintf('%s %s', $tag, $value));
        }

        $this->fail(sprintf(
            'Expected file "%s" to have the tag "@group autoReview". Tags found: [%s]',
            $filePath,
            implode('"", "', $tagStrings)
        ));
    }

    public function autoReviewFilesProvider(): Generator
    {
        /** @var Finder&SplFileInfo[] $finder */
        $finder = Finder::create()->files()->in(self::AUTO_REVIEW_DIR);

        foreach ($finder as $fileInfo) {
            yield [
                // TODO: it might be worth to make the path canonical and relative to the project
                // directory.
                // Not done as the time of writing as implies importing webmozart/path-util for this
                // single scenario which is not worth it
                $fileInfo->getPathname(),
                $fileInfo->getContents(),
            ];
        }
    }
}
