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

namespace Infection\Tests\Visitor;

use Infection\Container;
use PhpParser\Node;
use PhpParser\NodeDumper;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\Parser;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\PrettyPrinterAbstract;
use PHPUnit\Framework\TestCase;
use function Safe\file_get_contents;
use function Safe\sprintf;

abstract class BaseVisitorTest extends TestCase
{
    /**
     * @var Parser|null
     */
    private static $parser;

    /**
     * @var NodeDumper|null
     */
    private static $dumper;

    /**
     * @var PrettyPrinterAbstract|null
     */
    private static $printer;

    /**
     * @return Node[]
     */
    final protected function parseCode(string $code): array
    {
        return (array) $this->getParser()->parse($code);
    }

    /**
     * @param Node[] $nodes
     */
    final protected function dumpNodes(array $nodes): string
    {
        return $this->getDumper()->dump($nodes);
    }

    /**
     * @param Node[] $nodes
     * @param NodeVisitor[] $visitors
     *
     * @return Node[]
     */
    final protected function traverse(array $nodes, array $visitors): array
    {
        $traverser = new NodeTraverser();

        foreach ($visitors as $visitor) {
            $traverser->addVisitor($visitor);
        }

        return $traverser->traverse($nodes);
    }

    final protected function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../Fixtures/Autoloaded/%s', $file));
    }

    /**
     * @param Node[] $nodes
     */
    final protected function print(array $nodes): string
    {
        return $this->getPrinter()->prettyPrintFile($nodes);
    }

    private function getParser(): Parser
    {
        if (self::$parser === null) {
            self::$parser = Container::create()->getParser();
        }

        return self::$parser;
    }

    private function getDumper(): NodeDumper
    {
        if (self::$dumper === null) {
            self::$dumper = new NodeDumper();
        }

        return self::$dumper;
    }

    private function getPrinter(): PrettyPrinterAbstract
    {
        if (self::$printer === null) {
            self::$printer = new Standard();
        }

        return self::$printer;
    }
}
