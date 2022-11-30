<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19;

use _HumbugBoxb47773b41c19\PhpParser\NodeDumper;
use _HumbugBoxb47773b41c19\PhpParser\ParserFactory;
require_once __DIR__ . '/vendor/autoload.php';
$code = <<<'CODE'
<?php

namespace _HumbugBoxb47773b41c19;

function test(int|float $foo)
{
    \var_dump($foo);
}
CODE;
$parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);
$ast = $parser->parse($code);
$dumper = new NodeDumper();
echo $dumper->dump($ast) . "\n";
