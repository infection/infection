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
final class ProtectedVisibilityTest extends AbstractMutatorTestCase
{
    /**
     * @dataProvider provideMutationCases
     */
    public function test_mutator($input, $expected = null): void
    {
        $this->doTest($input, $expected);
    }

    public function provideMutationCases(): \Generator
    {
        yield 'It mutates protected to private' => [
            $this->getFileContent('pv-one-class.php'), [
                $this->getFileContent('pv-one-class-m0.php'),
            ],
        ];

        yield 'It does not mutate final flag' => [
            $this->getFileContent('pv-final.php'), [
                $this->getFileContent('pv-final-m0.php'),
            ],
        ];

        yield 'It does not mutate abstract protected to private' => [
            $this->getFileContent('pv-abstract.php'),
        ];

        yield 'It does mutate not abstract protected to private in an abstract class' => [
            $this->getFileContent('pv-abstract-class-protected-method.php'), [
                $this->getFileContent('pv-abstract-class-protected-method-m0.php'),
            ],
        ];

        yield 'It does not mutate static flag' => [
            $this->getFileContent('pv-static.php'), [
                $this->getFileContent('pv-static-m0.php'),
            ],
        ];

        yield 'It does not mutate if parent abstract has same protected method' => [
            $this->getFileContent('pv-same-method-abstract.php'),
        ];

        yield 'It does not mutate if parent class has same protected method' => [
            $this->getFileContent('pv-same-method-parent.php'), [
                $this->getFileContent('pv-same-method-parent-m0.php'),
            ],
        ];

        yield 'It does not mutate if grand parent class has same protected method' => [
            $this->getFileContent('pv-same-method-grandparent.php'), [
                $this->getFileContent('pv-same-method-grandparent-m0.php'),
            ],
        ];

        yield 'it does mutate non-inherited methods' => [
            $this->getFileContent('pv-non-same-method-parent.php'), [
                $this->getFileContent('pv-non-same-method-parent-m0.php'),
            ],
        ];

        yield 'it does not mutate an anonymous class because reflection is not avalable' => [
            <<<'PHP'
<?php

function something()
{
    return new class() {
        protected function anything()
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
        return file_get_contents(sprintf(__DIR__ . '/../../Fixtures/Autoloaded/ProtectedVisibility/%s', $file));
    }
}
