<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection;

use InvalidArgumentException;
use function assert;
use function end;
use function explode;
use function is_string;
use function preg_match;
use function sprintf;
use function trim;
/**
@psalm-immutable
*/
final class Fqsen
{
    private $fqsen;
    private $name;
    public function __construct(string $fqsen)
    {
        $matches = [];
        $result = preg_match('/^\\\\([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff\\\\]*)?(?:[:]{2}\\$?([a-zA-Z_\\x7f-\\xff][a-zA-Z0-9_\\x7f-\\xff]*))?(?:\\(\\))?$/', $fqsen, $matches);
        if ($result === 0) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid Fqsen.', $fqsen));
        }
        $this->fqsen = $fqsen;
        if (isset($matches[2])) {
            $this->name = $matches[2];
        } else {
            $matches = explode('\\', $fqsen);
            $name = end($matches);
            assert(is_string($name));
            $this->name = trim($name, '()');
        }
    }
    public function __toString() : string
    {
        return $this->fqsen;
    }
    public function getName() : string
    {
        return $this->name;
    }
}
