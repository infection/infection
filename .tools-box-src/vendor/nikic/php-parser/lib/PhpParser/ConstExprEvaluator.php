<?php

namespace _HumbugBoxb47773b41c19\PhpParser;

use function array_merge;
use _HumbugBoxb47773b41c19\PhpParser\Node\Expr;
use _HumbugBoxb47773b41c19\PhpParser\Node\Scalar;
class ConstExprEvaluator
{
    private $fallbackEvaluator;
    public function __construct(callable $fallbackEvaluator = null)
    {
        $this->fallbackEvaluator = $fallbackEvaluator ?? function (Expr $expr) {
            throw new ConstExprEvaluationException("Expression of type {$expr->getType()} cannot be evaluated");
        };
    }
    public function evaluateSilently(Expr $expr)
    {
        \set_error_handler(function ($num, $str, $file, $line) {
            throw new \ErrorException($str, 0, $num, $file, $line);
        });
        try {
            return $this->evaluate($expr);
        } catch (\Throwable $e) {
            if (!$e instanceof ConstExprEvaluationException) {
                $e = new ConstExprEvaluationException("An error occurred during constant expression evaluation", 0, $e);
            }
            throw $e;
        } finally {
            \restore_error_handler();
        }
    }
    public function evaluateDirectly(Expr $expr)
    {
        return $this->evaluate($expr);
    }
    private function evaluate(Expr $expr)
    {
        if ($expr instanceof Scalar\LNumber || $expr instanceof Scalar\DNumber || $expr instanceof Scalar\String_) {
            return $expr->value;
        }
        if ($expr instanceof Expr\Array_) {
            return $this->evaluateArray($expr);
        }
        if ($expr instanceof Expr\UnaryPlus) {
            return +$this->evaluate($expr->expr);
        }
        if ($expr instanceof Expr\UnaryMinus) {
            return -$this->evaluate($expr->expr);
        }
        if ($expr instanceof Expr\BooleanNot) {
            return !$this->evaluate($expr->expr);
        }
        if ($expr instanceof Expr\BitwiseNot) {
            return ~$this->evaluate($expr->expr);
        }
        if ($expr instanceof Expr\BinaryOp) {
            return $this->evaluateBinaryOp($expr);
        }
        if ($expr instanceof Expr\Ternary) {
            return $this->evaluateTernary($expr);
        }
        if ($expr instanceof Expr\ArrayDimFetch && null !== $expr->dim) {
            return $this->evaluate($expr->var)[$this->evaluate($expr->dim)];
        }
        if ($expr instanceof Expr\ConstFetch) {
            return $this->evaluateConstFetch($expr);
        }
        return ($this->fallbackEvaluator)($expr);
    }
    private function evaluateArray(Expr\Array_ $expr)
    {
        $array = [];
        foreach ($expr->items as $item) {
            if (null !== $item->key) {
                $array[$this->evaluate($item->key)] = $this->evaluate($item->value);
            } elseif ($item->unpack) {
                $array = array_merge($array, $this->evaluate($item->value));
            } else {
                $array[] = $this->evaluate($item->value);
            }
        }
        return $array;
    }
    private function evaluateTernary(Expr\Ternary $expr)
    {
        if (null === $expr->if) {
            return $this->evaluate($expr->cond) ?: $this->evaluate($expr->else);
        }
        return $this->evaluate($expr->cond) ? $this->evaluate($expr->if) : $this->evaluate($expr->else);
    }
    private function evaluateBinaryOp(Expr\BinaryOp $expr)
    {
        if ($expr instanceof Expr\BinaryOp\Coalesce && $expr->left instanceof Expr\ArrayDimFetch) {
            return $this->evaluate($expr->left->var)[$this->evaluate($expr->left->dim)] ?? $this->evaluate($expr->right);
        }
        $l = $expr->left;
        $r = $expr->right;
        switch ($expr->getOperatorSigil()) {
            case '&':
                return $this->evaluate($l) & $this->evaluate($r);
            case '|':
                return $this->evaluate($l) | $this->evaluate($r);
            case '^':
                return $this->evaluate($l) ^ $this->evaluate($r);
            case '&&':
                return $this->evaluate($l) && $this->evaluate($r);
            case '||':
                return $this->evaluate($l) || $this->evaluate($r);
            case '??':
                return $this->evaluate($l) ?? $this->evaluate($r);
            case '.':
                return $this->evaluate($l) . $this->evaluate($r);
            case '/':
                return $this->evaluate($l) / $this->evaluate($r);
            case '==':
                return $this->evaluate($l) == $this->evaluate($r);
            case '>':
                return $this->evaluate($l) > $this->evaluate($r);
            case '>=':
                return $this->evaluate($l) >= $this->evaluate($r);
            case '===':
                return $this->evaluate($l) === $this->evaluate($r);
            case 'and':
                return $this->evaluate($l) and $this->evaluate($r);
            case 'or':
                return $this->evaluate($l) or $this->evaluate($r);
            case 'xor':
                return $this->evaluate($l) xor $this->evaluate($r);
            case '-':
                return $this->evaluate($l) - $this->evaluate($r);
            case '%':
                return $this->evaluate($l) % $this->evaluate($r);
            case '*':
                return $this->evaluate($l) * $this->evaluate($r);
            case '!=':
                return $this->evaluate($l) != $this->evaluate($r);
            case '!==':
                return $this->evaluate($l) !== $this->evaluate($r);
            case '+':
                return $this->evaluate($l) + $this->evaluate($r);
            case '**':
                return $this->evaluate($l) ** $this->evaluate($r);
            case '<<':
                return $this->evaluate($l) << $this->evaluate($r);
            case '>>':
                return $this->evaluate($l) >> $this->evaluate($r);
            case '<':
                return $this->evaluate($l) < $this->evaluate($r);
            case '<=':
                return $this->evaluate($l) <= $this->evaluate($r);
            case '<=>':
                return $this->evaluate($l) <=> $this->evaluate($r);
        }
        throw new \Exception('Should not happen');
    }
    private function evaluateConstFetch(Expr\ConstFetch $expr)
    {
        $name = $expr->name->toLowerString();
        switch ($name) {
            case 'null':
                return null;
            case 'false':
                return \false;
            case 'true':
                return \true;
        }
        return ($this->fallbackEvaluator)($expr);
    }
}
