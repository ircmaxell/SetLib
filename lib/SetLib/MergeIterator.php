<?php

namespace SetLib;

class MergeIterator implements \Iterator {

    protected $left;
    protected $right;
    protected $compare;
    protected $current;
    protected $valid = false;
    protected $key = 0;

    public function __construct($left, $right, $compare) {
        $this->left = set($left)->getIterator();
        $this->right = set($right)->getIterator();
        $this->compare = $compare;
        $this->rewind();
    }

    public function next() {
        $this->valid = true;
        $cmp = $this->compare;
        if ($this->left->valid() && $this->right->valid()) {
            if (0 < $cmp($this->left->current(), $this->right->current())) {
                $this->current = $this->right->current();
                $this->right->next();
            } else {
                $this->current = $this->left->current();
                $this->left->next();
            }
        } elseif ($this->left->valid()) {
            $this->current = $this->left->current();
            $this->left->next();
        } elseif ($this->right->valid()) {
            $this->current = $this->right->current();
            $this->right->next();
        } else {
            $this->current = null;
            $this->valid = false;
        }
        $this->key++;
    }

    public function valid() {
        return $this->valid;
    }

    public function current() {
        return $this->current;
    }
    
    public function key() {
        return $this->key;
    }

    public function rewind() {
        $this->left->rewind();
        $this->right->rewind();
        $this->next();
        $this->key = 0;
    }

}