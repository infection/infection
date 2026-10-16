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

namespace Infection\Command\Debug;

use function array_values;
use Infection\Command\BaseCommand;
use Infection\Console\IO;
use Infection\FileSystem\FileSystem;
use Infection\PhpParser\UnparsableFile;
use Infection\PhpParser\Visitor\FullyQualifiedClassNameManipulator;
use Infection\PhpParser\Visitor\NameResolverFactory;
use function ksort;
use PhpParser\Node;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use SplFileInfo;
use function sprintf;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Filesystem\Path;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class ListSymbolsCommand extends BaseCommand
{
    private const string NAME = 'debug:list-symbols';

    private const string FILE_PATH_ARGUMENT = 'file';

    public function __construct(
        private readonly FileSystem $fileSystem,
    ) {
        parent::__construct(self::NAME);
    }

    protected function configure(): void
    {
        $this->setDescription('Lists the symbols declared in a given file.');
        $this->setHelp(
            <<<'HELP'
                Lists named classes, interfaces, traits, enums, and their directly declared methods
                in source order. Each line contains a qualified name without a leading backslash,
                such as App\Example or App\Example::method.

                Anonymous classes, inherited methods, trait-imported methods, functions, properties,
                and constants are omitted. A file with no matching declarations produces no output.

                The file is parsed without executing it or autoloading its declarations. No project
                configuration, test coverage, or mutation eligibility is required. A listed symbol
                does not necessarily contain mutation candidates. Diagnostics are written to stderr.

                Usage: infection debug:list-symbols path/to/File.php
                HELP,
        );
        $this->addArgument(
            self::FILE_PATH_ARGUMENT,
            InputArgument::REQUIRED,
            'Path to the readable PHP file to inspect.',
        );
    }

    /**
     * @throws UnparsableFile
     */
    protected function executeCommand(IO $io): bool
    {
        $file = $this->getFile($io);
        [$nodes] = $this->getApplication()->getContainer()->getFileParser()->parse($file);

        $nodes = (new NodeTraverser(
            NameResolverFactory::create(),
        ))->traverse($nodes);

        $io->writeln(
            self::getSymbols($nodes),
        );

        return true;
    }

    private function getFile(IO $io): SplFileInfo
    {
        $path = trim(
            (string) $io->getInput()->getArgument(self::FILE_PATH_ARGUMENT),
        );

        Assert::stringNotEmpty(
            $path,
            'Expected the argument "file" to be a non-empty file path.',
        );

        $canonicalPath = Path::canonicalize($path);

        Assert::true(
            $this->fileSystem->isReadableFile($canonicalPath),
            sprintf(
                'Expected "%s" to be a readable file path.',
                $path,
            ),
        );

        return new SplFileInfo($canonicalPath);
    }

    /**
     * @param Node[] $nodes
     *
     * @return list<string>
     */
    private static function getSymbols(array $nodes): array
    {
        $symbols = [];
        $classes = (new NodeFinder())->findInstanceOf(
            $nodes,
            ClassLike::class,
        );

        foreach ($classes as $class) {
            $name = FullyQualifiedClassNameManipulator::getFqcn($class);

            if ($name === null) {
                continue;
            }

            $symbols[$class->getStartTokenPos()] = $name->toString();

            foreach ($class->getMethods() as $method) {
                $symbols[$method->getStartTokenPos()] = sprintf(
                    '%s::%s',
                    $name->toString(),
                    $method->name->toString(),
                );
            }
        }

        // Nested declarations may occur between two methods of their enclosing class.
        ksort($symbols);

        return array_values($symbols);
    }
}
