<?php

namespace SetLib;

class RewindableGenerator implements \Iterator {

    protected $callback;
    protected $generator;

    public function __construct($callback, array $args = array()) {
        $this->callback = function() use ($callback, $args) {
            return call_user_func_array($callback, $args);
        };
        $this->rewind();
    }

    public function next() {
        return $this->generator->next();
    }

    public function valid() {
        return $this->generator->valid();
    }

    public function current() {
        return $this->generator->current();
    }
    
    public function key() {
        return $this->generator->key();
    }

    public function send($value = null) {
        return $this->generator->send($value);
    }

    public function rewind() {
        $cb = $this->callback;
        $this->generator = $cb();
    }

    public function __call($method, array $args = array()) {
        $obj = $this;
        if ($method === 'throw') {
            $obj = $this->generator;
        }
        return call_user_func_array([$obj, $method], $args);
    }
}