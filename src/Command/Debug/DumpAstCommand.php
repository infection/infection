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

use Infection\Command\BaseCommand;
use Infection\Command\Option\ConfigurationOption;
use Infection\Console\IO;
use Infection\Container\Container;
use Infection\FileSystem\FileSystem;
use Infection\Logger\Console\ConsoleLogger;
use Infection\PhpParser\Visitor\AddIdToTraversedNodesVisitor\AddIdToTraversedNodesVisitor;
use Infection\PhpParser\Visitor\LabelMutationCandidatesVisitor;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use SplFileObject;
use function sprintf;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Filesystem\Path;
use function trim;
use Webmozart\Assert\Assert;

/**
 * @internal
 */
final class DumpAstCommand extends BaseCommand
{
    private const FILE_PATH_ARGUMENT = 'file';

    private const SHOW_ATTRIBUTES = 'show-attributes';

    public function __construct(
        private readonly FileSystem $fileSystem,
    ) {
        parent::__construct('debug:dump-ast');
    }

    protected function configure(): void
    {
        $this->setDescription('Dumps the AST of a given file.');
        $this->setHelp(
            <<<HELP
                This command will parse the file as infection would to generate an enriched AST. It
                will then dump it.

                The dumped format is what is used by Infection for its tests.
                HELP,
        );

        $this->addArgument(
            self::FILE_PATH_ARGUMENT,
            InputArgument::REQUIRED,
            'Path to the file to parse.',
        );
        $this->addOption(
            self::SHOW_ATTRIBUTES,
            null,
            InputOption::VALUE_NONE,
            'Show all the attributes',
        );

        ConfigurationOption::addOption($this);
    }

    protected function executeCommand(IO $io): bool
    {
        $file = $this->getFile($io);
        $shouldShowAttributes = self::shouldShowAttributes($io);
        $configFile = ConfigurationOption::get($io);
        $logger = new ConsoleLogger($io);
        self::configureFormatter($io);

        $container = $this
            ->getApplication()
            ->getContainer()
            ->withValues(
                logger: $logger,
                output: $io->getOutput(),
                configFile: $configFile,
            );

        $nodes = $this->createAst($container, $file);

        $io->write(
            $container->getNodeDumper()->dump(
                $nodes,
                dumpOtherAttributes: $shouldShowAttributes,
                decorateNodes: $io->isDecorated(),
            ),
        );

        return true;
    }

    /**
     * @return Node[]
     */
    private function createAst(
        Container $container,
        SplFileObject $file,
    ): array {
        $traverserFactory = $container->getNodeTraverserFactory();

        // This code is akin to EnrichmentTraverseIntegrationTest.
        // It is quite close to the one from FileMutationGenerator, but with a
        // few differences for debugging/testing purposes.
        [$initialStatements] = $container->getFileParser()->parse($file);

        self::addIdsToNodes($initialStatements);

        $traverserFactory
            ->createEnrichmentTraverser()
            ->traverse($initialStatements);

        return $traverserFactory
            ->createMutationTraverser(
                new LabelMutationCandidatesVisitor(),
            )
            ->traverse($initialStatements);
    }

    private function getFile(IO $io): SplFileObject
    {
        $path = trim((string) $io->getInput()->getArgument(self::FILE_PATH_ARGUMENT));
        Assert::stringNotEmpty(
            $path,
            sprintf(
                'Expected the argument "%s" to be a file path. Got "%s".',
                self::FILE_PATH_ARGUMENT,
                $path,
            ),
        );

        $canonicalPath = Path::canonicalize($path);

        Assert::true(
            $this->fileSystem->isReadableFile($canonicalPath),
            sprintf(
                'Expected "%s" to be a readable file path.',
                $canonicalPath,
            ),
        );

        return new SplFileObject($canonicalPath);
    }

    private static function shouldShowAttributes(IO $io): bool
    {
        return (bool) $io->getInput()->getOption(self::SHOW_ATTRIBUTES);
    }

    /**
     * @param Node[] $nodes
     */
    private static function addIdsToNodes(array $nodes): void
    {
        (new NodeTraverser(new AddIdToTraversedNodesVisitor()))->traverse($nodes);
    }

    private static function configureFormatter(IO $io): void
    {
        $formatter = $io->getFormatter();

        $formatter->setStyle(
            'eligible',
            new OutputFormatterStyle(background: 'green'),
        );
        $formatter->setStyle(
            'mutation-candidate',
            new OutputFormatterStyle(background: 'red'),
        );
    }
}
