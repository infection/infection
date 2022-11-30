<?php

namespace _HumbugBoxb47773b41c19\Symfony\Component\VarDumper\Cloner;

class VarCloner extends AbstractCloner
{
    private static string $gid;
    private static array $arrayCache = [];
    protected function doClone(mixed $var) : array
    {
        $len = 1;
        $pos = 0;
        $refsCounter = 0;
        $queue = [[$var]];
        $hardRefs = [];
        $objRefs = [];
        $objects = [];
        $resRefs = [];
        $values = [];
        $maxItems = $this->maxItems;
        $maxString = $this->maxString;
        $minDepth = $this->minDepth;
        $currentDepth = 0;
        $currentDepthFinalIndex = 0;
        $minimumDepthReached = 0 === $minDepth;
        $cookie = (object) [];
        $a = null;
        $stub = null;
        $gid = self::$gid ??= \md5(\random_bytes(6));
        $arrayStub = new Stub();
        $arrayStub->type = Stub::TYPE_ARRAY;
        $fromObjCast = \false;
        for ($i = 0; $i < $len; ++$i) {
            if ($i > $currentDepthFinalIndex) {
                ++$currentDepth;
                $currentDepthFinalIndex = $len - 1;
                if ($currentDepth >= $minDepth) {
                    $minimumDepthReached = \true;
                }
            }
            $refs = $vals = $queue[$i];
            foreach ($vals as $k => $v) {
                $zvalRef = ($r = \ReflectionReference::fromArrayElement($vals, $k)) ? $r->getId() : null;
                if ($zvalRef) {
                    $vals[$k] =& $stub;
                    unset($stub);
                    if (null !== ($vals[$k] = $hardRefs[$zvalRef] ?? null)) {
                        $v = $vals[$k];
                        if ($v->value instanceof Stub && (Stub::TYPE_OBJECT === $v->value->type || Stub::TYPE_RESOURCE === $v->value->type)) {
                            ++$v->value->refCount;
                        }
                        ++$v->refCount;
                        continue;
                    }
                    $vals[$k] = new Stub();
                    $vals[$k]->value = $v;
                    $vals[$k]->handle = ++$refsCounter;
                    $hardRefs[$zvalRef] = $vals[$k];
                }
                switch (\true) {
                    case null === $v:
                    case \is_bool($v):
                    case \is_int($v):
                    case \is_float($v):
                        continue 2;
                    case \is_string($v):
                        if ('' === $v) {
                            continue 2;
                        }
                        if (!\preg_match('//u', $v)) {
                            $stub = new Stub();
                            $stub->type = Stub::TYPE_STRING;
                            $stub->class = Stub::STRING_BINARY;
                            if (0 <= $maxString && 0 < ($cut = \strlen($v) - $maxString)) {
                                $stub->cut = $cut;
                                $stub->value = \substr($v, 0, -$cut);
                            } else {
                                $stub->value = $v;
                            }
                        } elseif (0 <= $maxString && isset($v[1 + ($maxString >> 2)]) && 0 < ($cut = \mb_strlen($v, 'UTF-8') - $maxString)) {
                            $stub = new Stub();
                            $stub->type = Stub::TYPE_STRING;
                            $stub->class = Stub::STRING_UTF8;
                            $stub->cut = $cut;
                            $stub->value = \mb_substr($v, 0, $maxString, 'UTF-8');
                        } else {
                            continue 2;
                        }
                        $a = null;
                        break;
                    case \is_array($v):
                        if (!$v) {
                            continue 2;
                        }
                        $stub = $arrayStub;
                        $stub->class = \array_is_list($v) ? Stub::ARRAY_INDEXED : Stub::ARRAY_ASSOC;
                        $a = $v;
                        break;
                    case \is_object($v):
                        if (empty($objRefs[$h = \spl_object_id($v)])) {
                            $stub = new Stub();
                            $stub->type = Stub::TYPE_OBJECT;
                            $stub->class = \get_class($v);
                            $stub->value = $v;
                            $stub->handle = $h;
                            $a = $this->castObject($stub, 0 < $i);
                            if ($v !== $stub->value) {
                                if (Stub::TYPE_OBJECT !== $stub->type || null === $stub->value) {
                                    break;
                                }
                                $stub->handle = $h = \spl_object_id($stub->value);
                            }
                            $stub->value = null;
                            if (0 <= $maxItems && $maxItems <= $pos && $minimumDepthReached) {
                                $stub->cut = \count($a);
                                $a = null;
                            }
                        }
                        if (empty($objRefs[$h])) {
                            $objRefs[$h] = $stub;
                            $objects[] = $v;
                        } else {
                            $stub = $objRefs[$h];
                            ++$stub->refCount;
                            $a = null;
                        }
                        break;
                    default:
                        if (empty($resRefs[$h = (int) $v])) {
                            $stub = new Stub();
                            $stub->type = Stub::TYPE_RESOURCE;
                            if ('Unknown' === ($stub->class = @\get_resource_type($v))) {
                                $stub->class = 'Closed';
                            }
                            $stub->value = $v;
                            $stub->handle = $h;
                            $a = $this->castResource($stub, 0 < $i);
                            $stub->value = null;
                            if (0 <= $maxItems && $maxItems <= $pos && $minimumDepthReached) {
                                $stub->cut = \count($a);
                                $a = null;
                            }
                        }
                        if (empty($resRefs[$h])) {
                            $resRefs[$h] = $stub;
                        } else {
                            $stub = $resRefs[$h];
                            ++$stub->refCount;
                            $a = null;
                        }
                        break;
                }
                if ($a) {
                    if (!$minimumDepthReached || 0 > $maxItems) {
                        $queue[$len] = $a;
                        $stub->position = $len++;
                    } elseif ($pos < $maxItems) {
                        if ($maxItems < ($pos += \count($a))) {
                            $a = \array_slice($a, 0, $maxItems - $pos, \true);
                            if ($stub->cut >= 0) {
                                $stub->cut += $pos - $maxItems;
                            }
                        }
                        $queue[$len] = $a;
                        $stub->position = $len++;
                    } elseif ($stub->cut >= 0) {
                        $stub->cut += \count($a);
                        $stub->position = 0;
                    }
                }
                if ($arrayStub === $stub) {
                    if ($arrayStub->cut) {
                        $stub = [$arrayStub->cut, $arrayStub->class => $arrayStub->position];
                        $arrayStub->cut = 0;
                    } elseif (isset(self::$arrayCache[$arrayStub->class][$arrayStub->position])) {
                        $stub = self::$arrayCache[$arrayStub->class][$arrayStub->position];
                    } else {
                        self::$arrayCache[$arrayStub->class][$arrayStub->position] = $stub = [$arrayStub->class => $arrayStub->position];
                    }
                }
                if (!$zvalRef) {
                    $vals[$k] = $stub;
                } else {
                    $hardRefs[$zvalRef]->value = $stub;
                }
            }
            if ($fromObjCast) {
                $fromObjCast = \false;
                $refs = $vals;
                $vals = [];
                $j = -1;
                foreach ($queue[$i] as $k => $v) {
                    foreach ([$k => \true] as $gk => $gv) {
                    }
                    if ($gk !== $k) {
                        $vals = (object) $vals;
                        $vals->{$k} = $refs[++$j];
                        $vals = (array) $vals;
                    } else {
                        $vals[$k] = $refs[++$j];
                    }
                }
            }
            $queue[$i] = $vals;
        }
        foreach ($values as $h => $v) {
            $hardRefs[$h] = $v;
        }
        return $queue;
    }
}
