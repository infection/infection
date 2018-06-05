<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Console;

use Composer\Autoload\ClassLoader;
use Infection\Command\ConfigureCommand;
use Infection\Console\Application;
use Infection\Console\InfectionContainer;
use Infection\Finder\ComposerExecutableFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;

/**
 * @internal
 */
final class E2ETest extends TestCase
{
    const EXPECT_ERROR = 1;
    const EXPECT_SUCCESS = 0;

    private $cwd;

    /**
     * @var \Composer\Autoload\ClassLoader|null
     */
    private $previousLoader;

    protected function setUp()
    {
        // Without overcommit this test fails with `proc_open(): fork failed - Cannot allocate memory`
        if (strpos(PHP_OS, 'Linux') === 0 &&
            is_readable('/proc/sys/vm/overcommit_memory') &&
            file_get_contents('/proc/sys/vm/overcommit_memory') == 2) {
            $this->markTestSkipped('This test needs copious amounts of virtual memory. It will fail unless it is allowed to overcommit memory.');
        }

        // E2E tests usually require to chdir to their location
        // Hence we would need to go back to this dir
        $this->cwd = getcwd();
    }

    /**
     * Longest test: runs under about 160-200 sec
     *
     * To be run with:
     *
     * php -dmemory_limit=128M vendor/bin/phpunit --group=large
     *
     * @group e2e
     * @large
     */
    public function test_it_runs_on_itself()
    {
        if (ini_get('memory_limit') === '-1') {
            $this->markTestSkipped(implode("\n", [
                'Refusing to run Infection on itself with no memory limit set: it is dangerous.',
                'To run this test with a memory limit set please use:',
                'php -dmemory_limit=128M vendor/bin/phpunit --group=large',
            ]));
        }

        $output = $this->runInfection(self::EXPECT_SUCCESS, [
            '--test-framework-options="--exclude-group=e2e"',
        ]);

        $this->assertRegExp('/\d+ mutations were generated/', $output);
        $this->assertRegExp('/\d{2,} mutants were killed/', $output);
    }

    /**
     * @group e2e
     */
    public function test_it_runs_configure_command_if_no_configuration()
    {
        chdir('tests/Fixtures/e2e/Unconfigured/');

        $output = $this->runInfection(self::EXPECT_ERROR);

        $this->assertContains(ConfigureCommand::NONINTERACTIVE_MODE_ERROR, $output);
    }

    /**
     * @dataProvider e2eTestSuiteDataProvider
     * @group e2e
     */
    public function test_it_runs_an_e2e_test_with_success(string $fullPath)
    {
        if ('\\' == \DIRECTORY_SEPARATOR && 'Config_Framework' == basename((string) $fullPath)) {
            $this->markTestIncomplete("Ignoring this test on Windows because it's a know bug");
            // Development tracked at https://github.com/infection/infection/issues/377
        }

        $this->runOnE2EFixture($fullPath);
    }

    public function e2eTestSuiteDataProvider(): \Generator
    {
        $directories = Finder::create()
            ->depth('== 0')
            ->in(__DIR__ . '/../Fixtures/e2e/')
            ->directories();

        foreach ($directories as $dirName) {
            if (file_exists($dirName . '/run_tests.bash')) {
                // skipping non-standard tests
                // specifically Memory_Limit - it is very slow to fail
                continue;
            }

            yield basename((string) $dirName) => [$dirName];
        }
    }

    protected function tearDown()
    {
        if ($this->previousLoader) {
            $this->previousLoader->unregister();
        }

        chdir($this->cwd);
    }

    private function runOnE2EFixture($path): string
    {
        $this->assertDirectoryExists($path);
        chdir($path);

        $this->installComposerDeps();
        $output = $this->runInfection(self::EXPECT_SUCCESS);

        $this->assertRegExp('/You are running Infection with \w+ enabled./', $output);
        $this->assertRegExp('/\d+ mutations were generated/', $output);
        $this->assertRegExp('/\d+ mutants were killed/', $output);

        if (isset($_SERVER['GOLDEN'])) {
            copy('infection-log.txt', 'expected-output.txt');
            $this->markTestSkipped('Saved golden output');
        }

        $this->assertFileEquals('expected-output.txt', 'infection-log.txt', sprintf('%s/expected-output.txt is not same as infection-log.txt (if that is OK, run GOLDEN=1 vendor/bin/phpunit)', getcwd()));

        return $output;
    }

    private function installComposerDeps()
    {
        if (!file_exists('composer.json')) {
            // Tests may have no deps to install
            return;
        }

        if (!file_exists('vendor/autoload.php')) {
            // Install deps only if there's none
            $this->assertNotEmpty(getenv('PATH') ?: getenv('Path'), 'E2E tests need a system composer installed, but it could not be found without a PATH set');

            $process = new Process(sprintf('%s %s', (new ComposerExecutableFinder())->find(), 'install'));
            $process->mustRun();
        }

        /*
         * Now we need to handle autoloading. This is important because Infection uses
         * reflection, and reflection should know how to autoload files. But best if
         * we could avoid autoloading non-essential to Infection files, especially
         * those from vendor folder, because they're likely to interfere by breaking
         * other tests and causing a debugging hell. If it breaks, it better be here.
         */

        /*
         * E2E tests are expected to follow PSR-4.
         *
         * We exploit this to autoload only classes belonging to the test,
         * but not to vendored deps (so we don't need them here, but to run
         * a testing tool from an E2E test we need them all the same).
         */

        $loader = new ClassLoader();

        $map = require 'vendor/composer/autoload_psr4.php';

        // $vendorDir is normally defined inside autoload_psr4.php, but PHPStan
        // can't see there, so have to both tell it so, and verify that too
        $vendorDir = $vendorDir ?? null;
        $this->assertNotEmpty($vendorDir, 'Unexpected autoload_psr4.php found: please confirm that all dependencies are installed correctly for this fixture.');

        foreach ($map as $namespace => $paths) {
            foreach ($paths as $path) {
                if (strpos($path, $vendorDir) !== false) {
                    // Skip known dependency from autoloading
                    continue 2;
                }
            }

            $loader->setPsr4($namespace, $paths);
        }

        $loader->register($prepend = false); // Note: not prepending, but appending to our autoloader

        $this->previousLoader = $loader;

        /*
         * Another way to handle this is to append a new autoloader to the stock autoloader.
         *
         * By default this autoloader gets registered prepended to previously defined
         * autoloaders, but we can make our old autoloader to have preference:
         *
         * $this->previousLoader = include 'vendor/autoload.php';
         * $this->previousLoader->unregister();
         * $this->previousLoader->register(false);
         *
         * But this way vendored deps can still stick through, breaking other tests.
         * That could be a major debugging headache. If this test gets broked because
         * of missing deps, it better be this test, not something else.
         *
         * Yet another way is to declare E2E test classes in the autoload-dev in the
         * main composer.json, but that's not only ugly but will also require maintenance.
         */
    }

    private function runInfection(int $expectedExitCode, array $argvExtra = []): string
    {
        if (!extension_loaded('xdebug') && \PHP_SAPI !== 'phpdbg') {
            $this->markTestSkipped("Infection from within PHPUnit won't run without xdebug or phpdbg");
        }

        $container = new InfectionContainer();
        $input = new ArgvInput(array_merge([
            'bin/infection',
            'run',
            '--verbose',
            '--no-interaction',
        ], $argvExtra));

        $output = new BufferedOutput();

        $application = new Application($container);
        $application->setAutoExit(false);
        $exitCode = $application->run($input, $output);

        $outputText = $output->fetch();

        // Leaving window open to negative tests (e.g. where Infection is expected to fail)
        $this->assertSame(
            $expectedExitCode,
            $exitCode,
            'Unexpected exit code. Command output was' . $outputText
        );

        return $outputText;
    }
}
