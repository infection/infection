<?php

declare (strict_types=1);
namespace _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;

use _HumbugBox9658796bb9f0\PhpParser\Node\Stmt;
class Use_ extends Stmt
{
    const TYPE_UNKNOWN = 0;
    const TYPE_NORMAL = 1;
    const TYPE_FUNCTION = 2;
    const TYPE_CONSTANT = 3;
    public $type;
    public $uses;
    public function __construct(array $uses, int $type = self::TYPE_NORMAL, array $attributes = [])
    {
        $this->attributes = $attributes;
        $this->type = $type;
        $this->uses = $uses;
    }
    public function getSubNodeNames() : array
    {
        return ['type', 'uses'];
    }
    public function getType() : string
    {
        return 'Stmt_Use';
    }
}
