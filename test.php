<?php

require_once "vendor/autoload.php";

use SetLib\Set;
$primitives = SetLib\getFunctionalPrimitives();
extract($primitives);

var_dump(
    implode(" ", $zip(["Tcwci", "haoat", "inrn?", "stk "])->map($partial('implode', ""))->map('trim')->toArray())
);


$set = new Set([1, 5, 2, 7, 3, 9, 10, 4]);
var_dump($set->sort()->toArray());
