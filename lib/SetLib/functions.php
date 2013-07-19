<?php

namespace SetLib;

function Set($value) {
    if ($value instanceof Set) {
        return $value;
    }
    return new Set($value);
}

function getFunctionalPrimitives() {
    static $primitives = array();
    if (empty($primitives)) {
        $primitives = array(
            'complement' => function($a) {
                return function() use ($a) {
                    return !call_user_func_array($a, func_get_args());
                };
            },
            'compose' => function($a, $b) {
                return function() use ($a, $b) {
                    return $a(call_user_func_array($b, func_get_args()));
                };
            },
            'conjoin' => function($a) {
                $callbacks = Set(func_get_args());
                return function() use ($callbacks) {
                    $args = func_get_args();
                    return $callbacks->every(function($cb) use ($args) {
                        return call_user_func_array($cb, $args);
                    });
                };
            },
            'disjoin' => function($a) {
                $callbacks = Set(func_get_args());
                return function() use ($callbacks) {
                    $args = func_get_args();
                    return $callbacks->some(function($cb) use ($args) {
                        return call_user_func_array($cb, $args);
                    });
                };
            },
            'filter' => function($iterable, $cb) {
                return Set($iterable)->filter($cb);
            },
            'first' => function($cb) {
                return function($arg) use ($cb) {
                    return $cb($arg);
                };
            },
            'head' => function($iterable) {
                $it = Set($iterable)->getIterator();
                $it->rewind();
                return $it->valid() ? $it->current() : null;
            },
            'map' => function($iterable, $cb) {
                return Set($iterable)->map($cb);
            },
            'memoize' => function($cb) {
                $cache = array();
                return function() use ($cb, &$cache) {
                    $key = serialize(func_get_args());
                    if (!isset($cache[$key])) {
                        $cache[$key] = call_user_func_array($cb, func_get_args());
                    }
                    return $cache[$key];
                };
            },
            'operator' => function($operator) {
                if (!in_array($operator, [
                    '*', '/', '%', '+', '-', '.', '<<', '>>', '<', '<=', '>', '>=',
                    '==', '!=', '===', '!==', '&', '^', '|', '&&', '||', 'instanceof',
                ])) {
                    throw new \InvalidArgumentException('Unknown Operator Provided');
                }
                return create_function('$a,$b', 'return ($a ' . $operator . ' $b);');
            },
            'partial' => function($a, $arg) {
                return function() use ($a, $arg) {
                    return call_user_func_array($a, Set([$arg])->append(func_get_args())->toArray());
                };
            },
            'partialLeft' => function($a, $arg) {
                return function() use ($a, $arg) {
                    return call_user_func_array($a, Set(func_get_args())->append([$arg])->toArray());
                };
            },
            'pattern' => function($from, $to) {
                $from = Set($from);
                $to = Set($to)->getIterator();
                return function($value) use ($from, $to) {
                    $to->rewind();
                    foreach ($from as $pattern) {
                        if ($pattern($value)) {
                            return call_user_func($to->current(), $value);
                        }
                        $to->next();
                    }
                    throw new \RuntimeException('Incomplete Pattern Detected');
                };
            },
            'range' => function($from, $to, $step = 1) {
                if (($from < $to XOR $step > 0)) {
                    $step = -1 * $step;
                }
                return Set(new RewindableGenerator(function() use ($from, $to, $step) {
                    for ($i = $from; $i < $to; $i += $step) {
                        yield $i;
                    }
                }));
            },
            'reduce' => function($iterable, $cb, $initial = null) {
                return Set($iterable)->reduce($cb, $initial);
            },
            'self' => function($a) {
                return $a;
            },
            'static' => function($value) {
                return function() use ($value) {
                    return $value;
                };
            },
            'tail' => function($iterable) {
                return Set($iterable)->limit(1);
            },
            'void' => function() {},
            'zip' => function($iterable) {
                return Set($iterable)->zip();
            }
        );
        $primitives['operator'] = $primitives['memoize']($primitives['operator']);
    }
    return $primitives;
}
