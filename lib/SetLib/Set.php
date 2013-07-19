<?php

namespace SetLib;

class Set implements \IteratorAggregate {
    
    protected $iterator;

    public function __construct($value) {
        if (is_string($value)) {
            $this->iterator = new RewindableGenerator(function() use ($value) {
                $length = strlen($value);
                for ($i = 0; $i < $length; $i++) {
                    yield $value[$i];
                }
            });
        } elseif (is_array($value)) {
            $this->iterator = new \ArrayIterator($value);
        } elseif ($value instanceof \IteratorAggregate) {
            $this->iterator = $value->getIterator();
        } elseif ($value instanceof \Iterator) {
            $this->iterator = $value;
        } else {
            throw new \InvalidArgumentException('Expecting a Traversable item!');
        }
    }

    public function getIterator() {
        return $this->iterator;
    }

    public function toArray() {
        return iterator_to_array($this, false);
    }

    public function every($callback) {
        foreach ($this as $key => $value) {
            if (!call_user_func($callback, $value, $key)) {
                return false;
            }
        }
        return true;
    }

    public function some($callback) {
        foreach ($this as $key => $value) {
            if (call_user_func($callback, $value, $key)) {
                return true;
            }
        }
        return false;
    }

    public function reduce($callback, $initial = null) {
        $useInitial = is_null($initial);
        $result = $initial;
        foreach ($this as $key => $value) {
            if ($useInitial) {
                $useInitial = false;
                $result = $value;
            } else {
                $result = call_user_func($callback, $result, $value, $key);
            }
        }
        return $result;
    }

    public function append($value) {
        $set = Set($value);
        $it = new \AppendIterator();
        $it->append($this->iterator);
        $it->append($set->iterator);
        return Set($it);
    }

    public function cache() {
        return Set(new \CachingIterator($this->iterator));
    }

    public function filter($callback) {
        return Set(new \CallbackFilterIterator($this->iterator, $callback));
    }

    public function match($regexp) {
        return Set(new \RegexIterator($this->iterator, $regexp));
    }

    public function limit($offset = 0, $count = -1) {
        return Set(new \LimitIterator($this->iterator, $offset, $count));
    }

    public function map($callback) {
        return Set(new RewindableGenerator(function () use ($callback) {
            foreach ($this->iterator as $key => $value) {
                yield $key => $callback($value);
            }
        }));
    }

    public function zip() {
        $set = $this->map('SetLib\Set')->map(function($subset) { return $subset->getIterator(); })->toArray();
        return Set(new RewindableGenerator(function() use ($set) {
            do {
                $sub = array();
                $valid = 0;
                foreach ($set as $subset) {
                    if ($subset->valid()) {
                        $sub[] = $subset->current();
                        $subset->next();
                        $valid++;
                    } else {
                        $sub[] = null;
                    }
                }
                if ($valid > 0) {
                    yield $sub;
                }
            } while ($valid > 0);
        }));
    }

}