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

namespace Infection\Benchmark\Tracing;

use RuntimeException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;
use function file_exists;
use function is_dir;
use function mkdir;
use function sprintf;

require_once __DIR__ . '/../../../vendor/autoload.php';

const NUM_CLASSES_ARG = 'num-classes';
const NUM_METHODS_ARG = 'num-methods';
const OUTPUT_DIR_ARG = 'output-dir';
const CLEAN_OPT = 'clean';

$input = new ArgvInput(
    null,
    new InputDefinition([
        new InputArgument(
            NUM_CLASSES_ARG,
            InputArgument::OPTIONAL,
            'Number of classes to generate',
            100,
        ),
        new InputArgument(
            NUM_METHODS_ARG,
            InputArgument::OPTIONAL,
            'Average number of methods per class',
            10,
        ),
        new InputArgument(
            OUTPUT_DIR_ARG,
            InputArgument::OPTIONAL,
            'Output directory for generated code',
            __DIR__ . '/generated-benchmark',
        ),
        new InputOption(
            CLEAN_OPT,
            'c',
            InputOption::VALUE_NONE,
            'Clean output directory before generating',
        ),
    ]),
);
$output = new ConsoleOutput();
$io = new SymfonyStyle($input, $output);

$numClasses = (int) $input->getArgument(NUM_CLASSES_ARG);
$numMethods = (int) $input->getArgument(NUM_METHODS_ARG);
$outputDir = (string) $input->getArgument(OUTPUT_DIR_ARG);
$clean = $input->getOption(CLEAN_OPT);

$io->title('Infection Benchmark Code Generator');
$io->section('Configuration');
$io->listing([
    sprintf('Classes: %d', $numClasses),
    sprintf('Methods per class: ~%d', $numMethods),
    sprintf('Output directory: %s', $outputDir),
]);

// Clean output directory if requested
if ($clean && file_exists($outputDir)) {
    $io->section('Cleaning output directory');

    $process = new \Symfony\Component\Process\Process(['rm', '-rf', $outputDir]);
    $process->run();

    if (!$process->isSuccessful()) {
        throw new RuntimeException('Failed to clean output directory: ' . $process->getErrorOutput());
    }

    $io->success('Output directory cleaned');
}

// Create directories
$srcDir = $outputDir . '/src';
$testDir = $outputDir . '/tests';

if (!is_dir($srcDir) && !mkdir($srcDir, 0755, true) && !is_dir($srcDir)) {
    throw new RuntimeException(sprintf('Directory "%s" was not created', $srcDir));
}

if (!is_dir($testDir) && !mkdir($testDir, 0755, true) && !is_dir($testDir)) {
    throw new RuntimeException(sprintf('Directory "%s" was not created', $testDir));
}

$io->section('Generating classes and tests');
$io->progressStart($numClasses);

$classGenerator = new ClassGenerator();
$testGenerator = new TestGenerator();

for ($i = 0; $i < $numClasses; $i++) {
    // Generate random method count with some variance
    $methodCount = max(3, $numMethods + random_int(-3, 3));

    // Generate class
    $className = sprintf('BenchmarkClass%04d', $i);
    $classContent = $classGenerator->generate($className, $methodCount);
    $classFile = sprintf('%s/%s.php', $srcDir, $className);
    file_put_contents($classFile, $classContent);

    // Generate test
    $testContent = $testGenerator->generate($className, $methodCount);
    $testFile = sprintf('%s/%sTest.php', $testDir, $className);
    file_put_contents($testFile, $testContent);

    $io->progressAdvance();
}

$io->progressFinish();

// Generate composer.json
$io->section('Generating composer.json');
$composerContent = <<<JSON
{
    "name": "infection/benchmark-generated",
    "description": "Generated codebase for Infection benchmarking",
    "type": "library",
    "require": {
        "php": "^8.1"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0"
    },
    "autoload": {
        "psr-4": {
            "Infection\\\\BenchmarkGenerated\\\\": "src/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Infection\\\\BenchmarkGenerated\\\\Tests\\\\": "tests/"
        }
    }
}
JSON;

file_put_contents($outputDir . '/composer.json', $composerContent);
$io->success('composer.json created');

// Generate phpunit.xml
$io->section('Generating phpunit.xml');
$phpunitContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         colors="true">
    <testsuites>
        <testsuite name="Benchmark Generated Test Suite">
            <directory>tests</directory>
        </testsuite>
    </testsuites>
    <coverage>
        <report>
            <xml outputDirectory="coverage/xml"/>
            <html outputDirectory="coverage/html"/>
        </report>
    </coverage>
    <source>
        <include>
            <directory suffix=".php">src</directory>
        </include>
    </source>
</phpunit>
XML;

file_put_contents($outputDir . '/phpunit.xml', $phpunitContent);
$io->success('phpunit.xml created');

// Generate infection.json5
$io->section('Generating infection.json5');
$infectionContent = <<<JSON
{
    "source": {
        "directories": [
            "src"
        ]
    },
    "logs": {
        "text": "infection.log"
    },
    "mutators": {
        "@default": true
    }
}
JSON;

file_put_contents($outputDir . '/infection.json5', $infectionContent);
$io->success('infection.json5 created');

$io->section('Summary');
$io->success(sprintf(
    'Generated %d classes and %d tests in %s',
    $numClasses,
    $numClasses,
    $outputDir
));

$io->note([
    'Next steps:',
    sprintf('1. cd %s', $outputDir),
    '2. composer install',
    '3. vendor/bin/phpunit --coverage-xml=coverage/xml',
]);

/**
 * Generates synthetic PHP classes for benchmarking
 */
final class ClassGenerator
{
    private const METHOD_TYPES = [
        'calculator' => 'Calculate %s',
        'processor' => 'Process %s',
        'validator' => 'Validate %s',
        'transformer' => 'Transform %s',
        'analyzer' => 'Analyze %s',
    ];

    private const RETURN_TYPES = ['int', 'string', 'bool', 'array', 'float'];

    private const PARAM_NAMES = ['value', 'data', 'input', 'item', 'element', 'record'];

    public function generate(string $className, int $methodCount): string
    {
        $methods = [];

        for ($i = 0; $i < $methodCount; $i++) {
            $methods[] = $this->generateMethod($i);
        }

        $methodsCode = implode("\n\n", $methods);

        return <<<PHP
<?php

declare(strict_types=1);

namespace Infection\\BenchmarkGenerated;

/**
 * Auto-generated class for Infection benchmarking
 */
final class {$className}
{
{$methodsCode}
}

PHP;
    }

    private function generateMethod(int $index): string
    {
        $methodType = array_rand(self::METHOD_TYPES);
        $methodName = $methodType . ucfirst(self::PARAM_NAMES[array_rand(self::PARAM_NAMES)]) . $index;
        $returnType = self::RETURN_TYPES[array_rand(self::RETURN_TYPES)];
        $paramName = self::PARAM_NAMES[array_rand(self::PARAM_NAMES)];
        $paramType = self::RETURN_TYPES[array_rand(self::RETURN_TYPES)];

        $body = $this->generateMethodBody($returnType, $paramName, $paramType);

        return <<<PHP
    /**
     * {$this->generateDocComment($methodType, $index)}
     */
    public function {$methodName}({$paramType} \${$paramName}): {$returnType}
    {
{$body}
    }
PHP;
    }

    private function generateDocComment(string $methodType, int $index): string
    {
        return sprintf(self::METHOD_TYPES[$methodType], 'value ' . $index);
    }

    private function generateMethodBody(string $returnType, string $paramName, string $paramType): string
    {
        return match ($returnType) {
            'int' => $this->generateIntBody($paramName, $paramType),
            'string' => $this->generateStringBody($paramName, $paramType),
            'bool' => $this->generateBoolBody($paramName, $paramType),
            'array' => $this->generateArrayBody($paramName, $paramType),
            'float' => $this->generateFloatBody($paramName, $paramType),
            default => '        return null;',
        };
    }

    private function generateIntBody(string $paramName, string $paramType): string
    {
        if ($paramType === 'int') {
            return <<<PHP
        if (\${$paramName} < 0) {
            return 0;
        }

        if (\${$paramName} > 100) {
            return 100;
        }

        return \${$paramName} * 2;
PHP;
        }

        return '        return 42;';
    }

    private function generateStringBody(string $paramName, string $paramType): string
    {
        if ($paramType === 'string') {
            return <<<PHP
        if (\${$paramName} === '') {
            return 'empty';
        }

        if (strlen(\${$paramName}) > 10) {
            return substr(\${$paramName}, 0, 10);
        }

        return strtoupper(\${$paramName});
PHP;
        }

        return "        return 'result';";
    }

    private function generateBoolBody(string $paramName, string $paramType): string
    {
        if ($paramType === 'int') {
            return "        return \${$paramName} > 0;";
        }

        if ($paramType === 'string') {
            return "        return \${$paramName} !== '';";
        }

        if ($paramType === 'bool') {
            return "        return !\${$paramName};";
        }

        return '        return true;';
    }

    private function generateArrayBody(string $paramName, string $paramType): string
    {
        if ($paramType === 'array') {
            return <<<PHP
        if (\${$paramName} === []) {
            return ['empty'];
        }

        \$result = [];
        foreach (\${$paramName} as \$key => \$value) {
            \$result[\$key] = \$value;
        }

        return \$result;
PHP;
        }

        return "        return [\${$paramName}];";
    }

    private function generateFloatBody(string $paramName, string $paramType): string
    {
        if ($paramType === 'int' || $paramType === 'float') {
            return <<<PHP
        if (\${$paramName} < 0.0) {
            return 0.0;
        }

        return \${$paramName} * 1.5;
PHP;
        }

        return '        return 3.14;';
    }
}

/**
 * Generates PHPUnit tests for synthetic classes
 */
final class TestGenerator
{
    private const METHOD_TYPES = [
        'calculator',
        'processor',
        'validator',
        'transformer',
        'analyzer',
    ];

    private const PARAM_NAMES = ['value', 'data', 'input', 'item', 'element', 'record'];

    public function generate(string $className, int $methodCount): string
    {
        $testMethods = [];

        // Generate test methods for each method in the class
        for ($i = 0; $i < $methodCount; $i++) {
            $testMethods[] = $this->generateTestMethod($i);
        }

        $testMethodsCode = implode("\n\n", $testMethods);

        return <<<PHP
<?php

declare(strict_types=1);

namespace Infection\\BenchmarkGenerated\\Tests;

use Infection\\BenchmarkGenerated\\{$className};
use PHPUnit\\Framework\\TestCase;

/**
 * Auto-generated test for Infection benchmarking
 */
final class {$className}Test extends TestCase
{
{$testMethodsCode}
}

PHP;
    }

    private function generateTestMethod(int $index): string
    {
        // Replicate the method name generation logic from ClassGenerator
        $methodType = self::METHOD_TYPES[$index % count(self::METHOD_TYPES)];
        $paramName = self::PARAM_NAMES[$index % count(self::PARAM_NAMES)];
        $methodName = $methodType . ucfirst($paramName) . $index;

        // Determine expected return type based on method generation pattern
        $returnTypes = ['int', 'string', 'bool', 'array', 'float'];
        $returnType = $returnTypes[$index % count($returnTypes)];

        return $this->generateTestMethodBody($methodName, $returnType, $index);
    }

    private function generateTestMethodBody(string $methodName, string $returnType, int $index): string
    {
        return match ($returnType) {
            'int' => $this->generateIntTest($methodName, $index),
            'string' => $this->generateStringTest($methodName, $index),
            'bool' => $this->generateBoolTest($methodName, $index),
            'array' => $this->generateArrayTest($methodName, $index),
            'float' => $this->generateFloatTest($methodName, $index),
            default => '',
        };
    }

    private function generateIntTest(string $methodName, int $index): string
    {
        return <<<PHP
    public function testMethod{$index}WithInt(): void
    {
        \$className = str_replace('Test', '', static::class);
        \$instance = new \$className();

        // Test boundary values to maximize coverage
        \$result1 = \$instance->{$methodName}(-10);
        \$this->assertIsInt(\$result1);
        \$this->assertSame(0, \$result1);

        \$result2 = \$instance->{$methodName}(50);
        \$this->assertIsInt(\$result2);
        \$this->assertSame(100, \$result2);

        \$result3 = \$instance->{$methodName}(150);
        \$this->assertIsInt(\$result3);
        \$this->assertSame(100, \$result3);
    }
PHP;
    }

    private function generateStringTest(string $methodName, int $index): string
    {
        return <<<PHP
    public function testMethod{$index}WithString(): void
    {
        \$className = str_replace('Test', '', static::class);
        \$instance = new \$className();

        // Test different string cases for coverage
        \$result1 = \$instance->{$methodName}('');
        \$this->assertIsString(\$result1);
        \$this->assertSame('empty', \$result1);

        \$result2 = \$instance->{$methodName}('short');
        \$this->assertIsString(\$result2);
        \$this->assertSame('SHORT', \$result2);

        \$result3 = \$instance->{$methodName}('verylongstring');
        \$this->assertIsString(\$result3);
        \$this->assertSame('verylongst', \$result3);
    }
PHP;
    }

    private function generateBoolTest(string $methodName, int $index): string
    {
        return <<<PHP
    public function testMethod{$index}WithBool(): void
    {
        \$className = str_replace('Test', '', static::class);
        \$instance = new \$className();

        // Test boolean logic branches
        \$result1 = \$instance->{$methodName}(true);
        \$this->assertIsBool(\$result1);

        \$result2 = \$instance->{$methodName}(false);
        \$this->assertIsBool(\$result2);
    }
PHP;
    }

    private function generateArrayTest(string $methodName, int $index): string
    {
        return <<<PHP
    public function testMethod{$index}WithArray(): void
    {
        \$className = str_replace('Test', '', static::class);
        \$instance = new \$className();

        // Test array handling branches
        \$result1 = \$instance->{$methodName}([]);
        \$this->assertIsArray(\$result1);
        \$this->assertSame(['empty'], \$result1);

        \$result2 = \$instance->{$methodName}(['a' => 1, 'b' => 2]);
        \$this->assertIsArray(\$result2);
        \$this->assertCount(2, \$result2);
    }
PHP;
    }

    private function generateFloatTest(string $methodName, int $index): string
    {
        return <<<PHP
    public function testMethod{$index}WithFloat(): void
    {
        \$className = str_replace('Test', '', static::class);
        \$instance = new \$className();

        // Test float calculation branches
        \$result1 = \$instance->{$methodName}(-5.5);
        \$this->assertIsFloat(\$result1);
        \$this->assertSame(0.0, \$result1);

        \$result2 = \$instance->{$methodName}(10.0);
        \$this->assertIsFloat(\$result2);
        \$this->assertSame(15.0, \$result2);
    }
PHP;
    }
}
