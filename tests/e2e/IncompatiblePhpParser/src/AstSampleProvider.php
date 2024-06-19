<?php

namespace e2ePhpParserVersion;

use PhpParser\ParserFactory;

class AstSampleProvider
{
    public static function provideSample(): array
    {
        $code = <<<'CODE'
        <?php

        function test($foo)
        {
            var_dump($foo);
        }
        CODE;

        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        return $parser->parse($code);
    }
}
