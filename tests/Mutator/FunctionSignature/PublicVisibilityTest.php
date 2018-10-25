<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Mutator\FunctionSignature;

use Infection\Tests\Mutator\AbstractMutatorTestCase;

/**
 * @internal
 */
final class PublicVisibilityTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider blacklistedProvider
     */
    public function test_it_does_not_modify_blacklisted_functions(string $functionName, string $args = '', string $modifier = ''): void
    {
        $code = $this->getFileContent("pv-{$functionName}.php");

        $this->doTest($code);
    }

    public function blacklistedProvider(): array
    {
        return [
            ['__construct'],
            ['__invoke'],
            ['__call', '$n, $v'],
            ['__callStatic', '$n, $v', 'static '],
            ['__get', '$n'],
            ['__set', '$n, $v'],
            ['__isset', '$n'],
            ['__unset', '$n'],
            ['__toString'],
            ['__debugInfo'],
        ];
    }

    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates public to protected' => [
            $this->getFileContent('pv-one-class.php'),
            [
                $this->getFileContent('pv-one-class-m0.php'),
            ],
        ];

        yield 'It does not mutate final flag' => [
            $this->getFileContent('pv-final.php'),
            [
                $this->getFileContent('pv-final-m0.php'),
            ],
        ];

        yield 'It mutates non abstract public to protected in an abstract class' => [
            $this->getFileContent('pv-non-abstract-in-abstract-class.php'),
            [
                $this->getFileContent('pv-non-abstract-in-abstract-class-m0.php'),
            ],
        ];

        yield 'It does not mutate static flag' => [
            $this->getFileContent('pv-static.php'),
            [
                $this->getFileContent('pv-static-m0.php'),
            ],
        ];

        yield 'It replaces visibility if not set' => [
            $this->getFileContent('pv-not-set.php'),
            [
                $this->getFileContent('pv-not-set-m0.php'),
            ],
        ];

        yield 'It does not mutate an interface' => [
            $this->getFileContent('pv-interface.php'),
        ];

        yield 'It does not mutate an abstract function' => [
            $this->getFileContent('pv-abstract.php'),
        ];

        yield 'It does not mutate if interface has same public method' => [
            $this->getFileContent('pv-same-method-interface.php'),
        ];

        yield 'It does not mutate if any of interfaces has same public method' => [
            $this->getFileContent('pv-same-method-any-interface.php'),
        ];

        yield 'It does not mutate if parent abstract has same public method' => [
            $this->getFileContent('pv-same-method-abstract.php'),
        ];

        yield 'It does not mutate if parent class has same public method' => [
            $this->getFileContent('pv-same-method-parent.php'),
            [
                $this->getFileContent('pv-same-method-parent-m0.php'),
            ],
        ];

        yield 'it does not mutate if grandparent class has same public method' => [
            $this->getFileContent('pv-same-method-grandparent.php'),
            <<<'PHP'
<?php

namespace SameGrandParent;

class GrandParent
{
    protected function foo()
    {
    }
}
class SameParent extends GrandParent
{
}
class Child extends SameParent
{
    public function foo()
    {
    }
}
PHP
            ,
        ];

        yield 'it does mutate non-inherited methods' => [
            $this->getFileContent('pv-non-same-method-parent.php'),
            <<<'PHP'
<?php

namespace NonSameAbstract;

abstract class NonSameAbstract
{
    public abstract function foo();
}
class Child extends NonSameAbstract
{
    public function foo()
    {
    }
    protected function bar()
    {
    }
}
PHP
        ];

        yield 'it does not mutate an anonymous class because reflection is not avalable' => [
            <<<'PHP'
<?php

function something()
{
    return new class() {
        public function anything()
        {
            return null;
        }
    };
}
PHP
        ];
    }

    private function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/PublicVisibility/%s', $file));
    }
}
