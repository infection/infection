<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\phpDocumentor\Reflection\DocBlock\Tags;

use _HumbugBoxb47773b41c19\phpDocumentor\Reflection\Type;
use function in_array;
use function strlen;
use function substr;
use function trim;
abstract class TagWithType extends BaseTag
{
    protected $type;
    public function getType() : ?Type
    {
        return $this->type;
    }
    protected static function extractTypeFromBody(string $body) : array
    {
        $type = '';
        $nestingLevel = 0;
        for ($i = 0, $iMax = strlen($body); $i < $iMax; $i++) {
            $character = $body[$i];
            if ($nestingLevel === 0 && trim($character) === '') {
                break;
            }
            $type .= $character;
            if (in_array($character, ['<', '(', '[', '{'])) {
                $nestingLevel++;
                continue;
            }
            if (in_array($character, ['>', ')', ']', '}'])) {
                $nestingLevel--;
                continue;
            }
        }
        $description = trim(substr($body, strlen($type)));
        return [$type, $description];
    }
}
