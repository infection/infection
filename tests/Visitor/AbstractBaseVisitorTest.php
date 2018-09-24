<?php
/**
 * Copyright © 2017-2018 Maks Rafalko
 *
 * License: https://opensource.org/licenses/BSD-3-Clause New BSD License
 */

declare(strict_types=1);

namespace Infection\Tests\Visitor;

use PhpParser\Lexer;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
abstract class AbstractBaseVisitorTest extends TestCase
{
    protected function getNodes(string $code): array
    {
        $lexer = new Lexer\Emulative();
        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7, $lexer);

        return $parser->parse($code);
    }

    protected function getFileContent(string $file): string
    {
        return file_get_contents(sprintf(__DIR__ . '/../Fixtures/Autoloaded/%s', $file));
    }
}
