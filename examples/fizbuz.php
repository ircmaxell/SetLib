<?php

require_once __DIR__ . "/../vendor/autoload.php";

use SetLib\Set;
$primitives = SetLib\getFunctionalPrimitives();
extract($primitives);

$mod3 = $first($compose(
    $partial($operator('==='), 0),
    $partialLeft($operator('%'), 3)
));
$mod5 = $first($compose(
    $partial($operator('==='), 0),
    $partialLeft($operator('%'), 5)
));

$grammar = $pattern([
    $conjoin($mod3, $mod5),
    $mod5,
    $mod3,
    $static(true),
], [
    $static('fizbuz'),
    $static('buz'),
    $static('fiz'),
    $self
]);

$results = $range(1, 100)->map($grammar)->toArray();


var_dump($results);
