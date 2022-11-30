<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\Humbug\PhpScoper\PhpParser\Printer;

use _HumbugBoxb47773b41c19\PhpParser\PrettyPrinterAbstract;
final class StandardPrinter implements Printer
{
    public function __construct(private readonly PrettyPrinterAbstract $decoratedPrinter)
    {
    }
    public function print(array $newStmts, array $oldStmts, array $oldTokens) : string
    {
        return $this->decoratedPrinter->prettyPrintFile($newStmts) . "\n";
    }
}
