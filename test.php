<?php

require_once "vendor/autoload.php";

use SetLib\Set;
$primitives = SetLib\getFunctionalPrimitives();
extract($primitives);

var_dump(
    implode(" ", $zip(["Tcwci", "haoat", "inrn?", "stk "])->map($partial('implode', ""))->map('trim')->toArray())
);
