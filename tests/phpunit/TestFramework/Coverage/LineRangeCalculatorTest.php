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

namespace Infection\Tests\TestFramework\Coverage;

use Infection\Container;
use Infection\TestFramework\Coverage\LineRangeCalculator;
use Infection\Visitor\ParentConnectorVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PHPUnit\Framework\TestCase;

final class LineRangeCalculatorTest extends TestCase
{
    /**
     * @see https://github.com/infection/infection/issues/815
     */
    public function test_it_can_find_the_outer_most_array(): void
    {
        $parser = Container::create()->getParser();
        $code = <<<'PHP'
<?php

declare(strict_types=1);

namespace App\Config;

use Monolog\Logger;

class ProdConfig extends AbstractConfig
{
    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        $cacheDir = $this->getCacheDir();
        $logDir = $this->getLogDir();

        return [// line 19 of code snippet
            'cors' => [
                'allow-origin' => [],
                'allow-methods' => ['DELETE', 'GET', 'POST', 'PUT'],
                'allow-headers' => [
                    'Accept',
                    'Content-Type',
                ],
                'allow-credentials' => false,
                'expose-headers' => [],
                'max-age' => $findMe,
            ],
            'debug' => false,
            'doctrine.dbal.db.options' => [
                'configuration' => [
                    'cache.result' => ['type' => 'apcu'],
                ],
                'connection' => [
                    'driver' => 'pdo_pgsql',
                    'charset' => 'utf8',
                    'user' => getenv('DATABASE_USER'),
                    'password' => getenv('DATABASE_PASS'),
                    'host' => getenv('DATABASE_HOST'),
                    'port' => getenv('DATABASE_PORT'),
                    'dbname' => getenv('DATABASE_NAME'),
                ],
            ],
            'doctrine.orm.em.options' => [
                'cache.hydration' => ['type' => 'apcu'],
                'cache.metadata' => ['type' => 'apcu'],
                'cache.query' => ['type' => 'apcu'],
                'proxies.dir' => $cacheDir.'/doctrine/proxies',
            ],
            'monolog' => [
                'name' => 'petstore',
                'path' => $logDir.'/application.log',
                'level' => Logger::NOTICE,
            ],
            'routerCacheFile' => $cacheDir.'/routes.php',
        ]; // line 58 of code snippet
    }

    public function getEnv(): string
    {
        return 'prod';
    }
}

PHP;

        $nodes = $parser->parse($code);
        $traverser = new NodeTraverser();
        $spy = $this->spyTraverser();
        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor($spy);
        $traverser->traverse($nodes);
        $range = $spy->range;
        $this->assertSame(range(19, 58), $range);
    }

    public function test_it_calculates_range_outside_of_array(): void
    {
        $parser = Container::create()->getParser();
        $code = <<<'PHP'
<?php

function foo(): void
{
    (static function() {
        $a = $findMe;
    })();
}

PHP;

        $nodes = $parser->parse($code);
        $traverser = new NodeTraverser();
        $spy = $this->spyTraverser();
        $traverser->addVisitor(new ParentConnectorVisitor());
        $traverser->addVisitor($spy);
        $traverser->traverse($nodes);
        $range = $spy->range;
        $this->assertSame([6], $range);
    }

    private function spyTraverser()
    {
        return new class() extends NodeVisitorAbstract {
            /**
             * @var array<int>
             */
            public $range = [];

            public function leaveNode(Node $node): void
            {
                if ($node instanceof Node\Expr\Variable && $node->name === 'findMe') {
                    $lineRange = new LineRangeCalculator();
                    $this->range = $lineRange->getNodeRange($node)->range;
                }
            }
        };
    }
}
