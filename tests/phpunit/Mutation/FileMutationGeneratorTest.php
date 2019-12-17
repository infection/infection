<?php

declare(strict_types=1);

namespace Infection\Tests\Mutation;

use Generator;
use Infection\Console\InfectionContainer;
use Infection\EventDispatcher\EventDispatcherInterface;
use Infection\Events\MutableFileProcessed;
use Infection\Events\MutationGeneratingFinished;
use Infection\Events\MutationGeneratingStarted;
use Infection\Exception\InvalidMutatorException;
use Infection\FileSystem\SourceFileCollector;
use Infection\Mutation;
use Infection\Mutation\FileMutationGenerator;
use Infection\Mutation\FileParser;
use Infection\Mutation\MutationGenerator;
use Infection\Mutation\NodeTraverserFactory;
use Infection\Mutation\PriorityNodeTraverser;
use Infection\Mutator\Arithmetic\Decrement;
use Infection\Mutator\Arithmetic\Plus;
use Infection\Mutator\Boolean\TrueValue;
use Infection\Mutator\FunctionSignature\PublicVisibility;
use Infection\Mutator\Number\DecrementInteger;
use Infection\Mutator\Util\MutatorConfig;
use Infection\TestFramework\Coverage\LineCodeCoverage;
use Infection\Tests\Fixtures\Files\Mutation\OneFile\OneFile;
use Infection\Tests\Fixtures\PhpParser\FakeNode;
use Infection\Tests\Fixtures\PhpParser\FakeVisitor;
use Infection\Visitor\MutationsCollectorVisitor;
use Infection\WrongMutator\ErrorMutator;
use InvalidArgumentException;
use PhpParser\NodeTraverserInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Symfony\Component\Finder\SplFileInfo;
use function current;
use function func_get_args;
use function Safe\sprintf;

final class FileMutationGeneratorTest extends TestCase
{
    private const FIXTURES_DIR = __DIR__ . '/../Fixtures/Files';
    /**
     * @var FileParser|MockObject
     */
    private $fileParserMock;

    /**
     * @var NodeTraverserFactory|MockObject
     */
    private $traverserFactoryMock;

    /**
     * @var FileMutationGenerator|MockObject
     */
    private $mutationGenerator;

    public function setUp(): void
    {
        $this->fileParserMock = $this->createMock(FileParser::class);
        $this->traverserFactoryMock = $this->createMock(NodeTraverserFactory::class);

        $this->mutationGenerator = new FileMutationGenerator(
            $this->fileParserMock,
            $this->traverserFactoryMock
        );
    }

    public function test_it_generates_mutations_for_a_given_file(): void
    {
        $codeCoverageMock = $this->createMock(LineCodeCoverage::class);

        $container = InfectionContainer::create();

        /** @var FileParser $fileParser */
        $fileParser = $container[FileParser::class];

        /** @var NodeTraverserFactory $traverserFactory */
        $traverserFactory = $container[NodeTraverserFactory::class];

        $mutationGenerator = new FileMutationGenerator(
            $fileParser,
            $traverserFactory
        );

        $mutations = $mutationGenerator->generate(
            new SplFileInfo(self::FIXTURES_DIR . '/Mutation/OneFile/OneFile.php', '', ''),
            false,
            $codeCoverageMock,
            [new Plus(new MutatorConfig([]))],
            []
        );

        foreach ($mutations as $mutation) {
            $this->assertInstanceOf(Mutation::class, $mutation);
        }

        $this->assertCount(1, $mutations);
        $this->assertArrayHasKey(0, $mutations);

        /** @var Mutation $mutation */
        $mutation = current($mutations);

        $this->assertInstanceOf(Plus::class, $mutation->getMutator());
    }

    /**
     * @dataProvider parsedFilesProvider
     */
    public function test_it_attempts_to_generate_mutations_for_the_file_if_covered_or_not_only_covered_code(
        SplFileInfo $fileInfo,
        bool $onlyCovered,
        LineCodeCoverage $codeCoverage,
        string $expectedFilePath
    ): void
    {
        $extraVisitors = [2 => new FakeVisitor()];

        $this->fileParserMock
            ->expects($this->once())
            ->method('parse')
            ->with($expectedFilePath)
            ->willReturn($initialStatements = [
                new FakeNode(),
                new FakeNode(),
            ])
        ;

        $traverserMock = $this->createMock(PriorityNodeTraverser::class);
        $traverserMock
            ->expects($this->once())
            ->method('traverse')
            ->willReturn($initialStatements)
        ;

        $this->traverserFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $passedExtraNodeVisitors) use ($extraVisitors) {
                $this->assertSame([$passedExtraNodeVisitors], func_get_args());

                $this->assertArrayHasKey(10, $passedExtraNodeVisitors);
                $this->assertInstanceOf(MutationsCollectorVisitor::class, $passedExtraNodeVisitors[10]);

                unset($passedExtraNodeVisitors[10]);

                $this->assertSame($extraVisitors, $passedExtraNodeVisitors);

                return true;
            }))
            ->willReturn($traverserMock)
        ;

        $mutationGenerator = new FileMutationGenerator(
            $this->fileParserMock,
            $this->traverserFactoryMock
        );

        $mutations = $mutationGenerator->generate(
            $fileInfo,
            $onlyCovered,
            $codeCoverage,
            [new Plus(new MutatorConfig([]))],
            $extraVisitors
        );

        $this->assertSame([], $mutations);
    }

    /**
     * @dataProvider skippedFilesProvider
     */
    public function test_it_skips_the_mutation_generation_if_checks_only_covered_code_and_the_file_has_no_tests(
        SplFileInfo $fileInfo,
        string $expectedFilePath
    ): void
    {
        $this->fileParserMock
            ->expects($this->never())
            ->method('parse')
        ;

        $this->traverserFactoryMock
            ->expects($this->never())
            ->method('create')
        ;

        $mutationGenerator = new FileMutationGenerator(
            $this->fileParserMock,
            $this->traverserFactoryMock
        );

        $mutations = $mutationGenerator->generate(
            $fileInfo,
            true,
            $this->createCodeCoverageMock(
                $expectedFilePath,
                false
            ),
            [new Plus(new MutatorConfig([]))],
            []
        );

        $this->assertSame([], $mutations);
    }

    public function test_it_cannot_generate_mutations_if_a_visitor_is_already_registered_instead_of_the_mutation_collector_visitor(): void
    {
        $fileInfo = new SplFileInfo('/path/to/file', 'relativePath', 'relativePathName');

        $extraVisitors = [10 => new FakeVisitor()];

        $fileParserMock = $this->createMock(FileParser::class);
        $fileParserMock
            ->expects($this->never())
            ->method('parse')
        ;

        $traverserFactoryMock = $this->createMock(NodeTraverserFactory::class);
        $traverserFactoryMock
            ->expects($this->never())
            ->method('create')
        ;

        $mutationGenerator = new FileMutationGenerator(
            $fileParserMock,
            $traverserFactoryMock
        );

        try {
            $mutationGenerator->generate(
                $fileInfo,
                false,
                $this->createMock(LineCodeCoverage::class),
                [new Plus(new MutatorConfig([]))],
                $extraVisitors
            );

            $this->fail('Expected an exception to be thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertSame(
                sprintf(
                    'Did not expect to find a visitor for the priority "10". Found "%s". '
                    .'Please free that priority as it is reserved for "%s".',
                    FakeVisitor::class,
                    MutationsCollectorVisitor::class
                ),
                $exception->getMessage()
            );
        }
    }

    public function parsedFilesProvider(): Generator
    {
        foreach ($this->provideBoolean() as $hasTests) {
            $title = sprintf(
                'path - only covered: false - has tests: %s',
                $hasTests ? 'true' : 'false'
            );

            yield $title => [
                new SplFileInfo('/path/to/file', 'relativePath', 'relativePathName'),
                false,
                $this->createCodeCoverageMock(
                    '/path/to/file',
                    true
                ),
                '/path/to/file',
            ];
        }

        foreach ($this->provideBoolean() as $hasTests) {
            $title = sprintf(
                'real path - only covered: false - has tests: %s',
                $hasTests ? 'true' : 'false'
            );

            yield $title => [
                new SplFileInfo(__FILE__, 'relativePath', 'relativePathName'),
                false,
                $this->createCodeCoverageMock(
                    __FILE__,
                    true
                ),
                __FILE__,
            ];
        }

        yield 'path - only covered: true - has tests: %s' => [
            new SplFileInfo('/path/to/file', 'relativePath', 'relativePathName'),
            true,
            $this->createCodeCoverageMock(
                '/path/to/file',
                true
            ),
            '/path/to/file',
        ];

        yield 'real path - only covered: true - has tests: %s' => [
            new SplFileInfo(__FILE__, 'relativePath', 'relativePathName'),
            true,
            $this->createCodeCoverageMock(
                __FILE__,
                true
            ),
            __FILE__,
        ];
    }

    public function skippedFilesProvider(): Generator
    {
        yield 'path - only covered: true - has tests: %s' => [
            new SplFileInfo('/path/to/file', 'relativePath', 'relativePathName'),
            '/path/to/file',
        ];

        yield 'real path - only covered: true - has tests: %s' => [
            new SplFileInfo(__FILE__, 'relativePath', 'relativePathName'),
            __FILE__,
        ];
    }

    public function provideBoolean(): Generator
    {
        yield from [true, false];
    }

    private function createCodeCoverageMock(string $expectedPath, bool $tests): LineCodeCoverage
    {
        $codeCoverageMock = $this->createMock(LineCodeCoverage::class);
        $codeCoverageMock
            ->method('hasTests')
            ->with($expectedPath)
            ->willReturn($tests)
        ;

        return $codeCoverageMock;
    }
}
