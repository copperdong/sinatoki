<?php

include("vendor/autoload.php");

error_reporting(E_ALL);

use Synthi\Slicer;

/*
 * 1: gentle output, time aligned phonetics
 * 2: base audio file the output matches
 * 3: name of the model to generate
 */

$data = json_decode(file_get_contents($argv[1]), true);

$slicer = new Slicer($data, $argv[2], $argv[3]);
$slicer->createModel();
