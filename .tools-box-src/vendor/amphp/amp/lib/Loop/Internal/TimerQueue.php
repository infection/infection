<?php

namespace _HumbugBoxb47773b41c19\Amp\Loop\Internal;

use _HumbugBoxb47773b41c19\Amp\Loop\Watcher;
final class TimerQueue
{
    private $data = [];
    private $pointers = [];
    private function heapifyUp(int $node)
    {
        $entry = $this->data[$node];
        while ($node !== 0 && $entry->expiration < $this->data[$parent = $node - 1 >> 1]->expiration) {
            $this->swap($node, $parent);
            $node = $parent;
        }
    }
    private function heapifyDown(int $node)
    {
        $length = \count($this->data);
        while (($child = ($node << 1) + 1) < $length) {
            if ($this->data[$child]->expiration < $this->data[$node]->expiration && ($child + 1 >= $length || $this->data[$child]->expiration < $this->data[$child + 1]->expiration)) {
                $swap = $child;
            } elseif ($child + 1 < $length && $this->data[$child + 1]->expiration < $this->data[$node]->expiration) {
                $swap = $child + 1;
            } else {
                break;
            }
            $this->swap($node, $swap);
            $node = $swap;
        }
    }
    private function swap(int $left, int $right)
    {
        $temp = $this->data[$left];
        $this->data[$left] = $this->data[$right];
        $this->pointers[$this->data[$right]->id] = $left;
        $this->data[$right] = $temp;
        $this->pointers[$temp->id] = $right;
    }
    /**
    @psalm-param
    */
    public function insert(Watcher $watcher)
    {
        \assert($watcher->expiration !== null);
        \assert(!isset($this->pointers[$watcher->id]));
        $node = \count($this->data);
        $this->data[$node] = $watcher;
        $this->pointers[$watcher->id] = $node;
        $this->heapifyUp($node);
    }
    /**
    @psalm-param
    */
    public function remove(Watcher $watcher)
    {
        $id = $watcher->id;
        if (!isset($this->pointers[$id])) {
            return;
        }
        $this->removeAndRebuild($this->pointers[$id]);
    }
    /**
    @psalm-return
    */
    public function extract(int $now)
    {
        if (empty($this->data)) {
            return null;
        }
        $watcher = $this->data[0];
        if ($watcher->expiration > $now) {
            return null;
        }
        $this->removeAndRebuild(0);
        return $watcher;
    }
    public function peek()
    {
        return isset($this->data[0]) ? $this->data[0]->expiration : null;
    }
    private function removeAndRebuild(int $node)
    {
        $length = \count($this->data) - 1;
        $id = $this->data[$node]->id;
        $left = $this->data[$node] = $this->data[$length];
        $this->pointers[$left->id] = $node;
        unset($this->data[$length], $this->pointers[$id]);
        if ($node < $length) {
            $parent = $node - 1 >> 1;
            if ($parent >= 0 && $this->data[$node]->expiration < $this->data[$parent]->expiration) {
                $this->heapifyUp($node);
            } else {
                $this->heapifyDown($node);
            }
        }
    }
}
