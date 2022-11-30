<?php

declare (strict_types=1);
namespace _HumbugBoxb47773b41c19\PhpParser;

class NodeTraverser implements NodeTraverserInterface
{
    const DONT_TRAVERSE_CHILDREN = 1;
    const STOP_TRAVERSAL = 2;
    const REMOVE_NODE = 3;
    const DONT_TRAVERSE_CURRENT_AND_CHILDREN = 4;
    protected $visitors = [];
    protected $stopTraversal;
    public function __construct()
    {
    }
    public function addVisitor(NodeVisitor $visitor)
    {
        $this->visitors[] = $visitor;
    }
    public function removeVisitor(NodeVisitor $visitor)
    {
        foreach ($this->visitors as $index => $storedVisitor) {
            if ($storedVisitor === $visitor) {
                unset($this->visitors[$index]);
                break;
            }
        }
    }
    public function traverse(array $nodes) : array
    {
        $this->stopTraversal = \false;
        foreach ($this->visitors as $visitor) {
            if (null !== ($return = $visitor->beforeTraverse($nodes))) {
                $nodes = $return;
            }
        }
        $nodes = $this->traverseArray($nodes);
        foreach ($this->visitors as $visitor) {
            if (null !== ($return = $visitor->afterTraverse($nodes))) {
                $nodes = $return;
            }
        }
        return $nodes;
    }
    protected function traverseNode(Node $node) : Node
    {
        foreach ($node->getSubNodeNames() as $name) {
            $subNode =& $node->{$name};
            if (\is_array($subNode)) {
                $subNode = $this->traverseArray($subNode);
                if ($this->stopTraversal) {
                    break;
                }
            } elseif ($subNode instanceof Node) {
                $traverseChildren = \true;
                $breakVisitorIndex = null;
                foreach ($this->visitors as $visitorIndex => $visitor) {
                    $return = $visitor->enterNode($subNode);
                    if (null !== $return) {
                        if ($return instanceof Node) {
                            $this->ensureReplacementReasonable($subNode, $return);
                            $subNode = $return;
                        } elseif (self::DONT_TRAVERSE_CHILDREN === $return) {
                            $traverseChildren = \false;
                        } elseif (self::DONT_TRAVERSE_CURRENT_AND_CHILDREN === $return) {
                            $traverseChildren = \false;
                            $breakVisitorIndex = $visitorIndex;
                            break;
                        } elseif (self::STOP_TRAVERSAL === $return) {
                            $this->stopTraversal = \true;
                            break 2;
                        } else {
                            throw new \LogicException('enterNode() returned invalid value of type ' . \gettype($return));
                        }
                    }
                }
                if ($traverseChildren) {
                    $subNode = $this->traverseNode($subNode);
                    if ($this->stopTraversal) {
                        break;
                    }
                }
                foreach ($this->visitors as $visitorIndex => $visitor) {
                    $return = $visitor->leaveNode($subNode);
                    if (null !== $return) {
                        if ($return instanceof Node) {
                            $this->ensureReplacementReasonable($subNode, $return);
                            $subNode = $return;
                        } elseif (self::STOP_TRAVERSAL === $return) {
                            $this->stopTraversal = \true;
                            break 2;
                        } elseif (\is_array($return)) {
                            throw new \LogicException('leaveNode() may only return an array ' . 'if the parent structure is an array');
                        } else {
                            throw new \LogicException('leaveNode() returned invalid value of type ' . \gettype($return));
                        }
                    }
                    if ($breakVisitorIndex === $visitorIndex) {
                        break;
                    }
                }
            }
        }
        return $node;
    }
    protected function traverseArray(array $nodes) : array
    {
        $doNodes = [];
        foreach ($nodes as $i => &$node) {
            if ($node instanceof Node) {
                $traverseChildren = \true;
                $breakVisitorIndex = null;
                foreach ($this->visitors as $visitorIndex => $visitor) {
                    $return = $visitor->enterNode($node);
                    if (null !== $return) {
                        if ($return instanceof Node) {
                            $this->ensureReplacementReasonable($node, $return);
                            $node = $return;
                        } elseif (self::DONT_TRAVERSE_CHILDREN === $return) {
                            $traverseChildren = \false;
                        } elseif (self::DONT_TRAVERSE_CURRENT_AND_CHILDREN === $return) {
                            $traverseChildren = \false;
                            $breakVisitorIndex = $visitorIndex;
                            break;
                        } elseif (self::STOP_TRAVERSAL === $return) {
                            $this->stopTraversal = \true;
                            break 2;
                        } else {
                            throw new \LogicException('enterNode() returned invalid value of type ' . \gettype($return));
                        }
                    }
                }
                if ($traverseChildren) {
                    $node = $this->traverseNode($node);
                    if ($this->stopTraversal) {
                        break;
                    }
                }
                foreach ($this->visitors as $visitorIndex => $visitor) {
                    $return = $visitor->leaveNode($node);
                    if (null !== $return) {
                        if ($return instanceof Node) {
                            $this->ensureReplacementReasonable($node, $return);
                            $node = $return;
                        } elseif (\is_array($return)) {
                            $doNodes[] = [$i, $return];
                            break;
                        } elseif (self::REMOVE_NODE === $return) {
                            $doNodes[] = [$i, []];
                            break;
                        } elseif (self::STOP_TRAVERSAL === $return) {
                            $this->stopTraversal = \true;
                            break 2;
                        } elseif (\false === $return) {
                            throw new \LogicException('bool(false) return from leaveNode() no longer supported. ' . 'Return NodeTraverser::REMOVE_NODE instead');
                        } else {
                            throw new \LogicException('leaveNode() returned invalid value of type ' . \gettype($return));
                        }
                    }
                    if ($breakVisitorIndex === $visitorIndex) {
                        break;
                    }
                }
            } elseif (\is_array($node)) {
                throw new \LogicException('Invalid node structure: Contains nested arrays');
            }
        }
        if (!empty($doNodes)) {
            while (list($i, $replace) = \array_pop($doNodes)) {
                \array_splice($nodes, $i, 1, $replace);
            }
        }
        return $nodes;
    }
    private function ensureReplacementReasonable($old, $new)
    {
        if ($old instanceof Node\Stmt && $new instanceof Node\Expr) {
            throw new \LogicException("Trying to replace statement ({$old->getType()}) " . "with expression ({$new->getType()}). Are you missing a " . "Stmt_Expression wrapper?");
        }
        if ($old instanceof Node\Expr && $new instanceof Node\Stmt) {
            throw new \LogicException("Trying to replace expression ({$old->getType()}) " . "with statement ({$new->getType()})");
        }
    }
}
