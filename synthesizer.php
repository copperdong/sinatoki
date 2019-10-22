<?php

include("vendor/autoload.php");

use Sinatoki\Synthesizer;

// The script that handles the actual speech synthesis

$input = $argv;

// Model to use
$model = $input[1];
unset($input[0]);
unset($input[1]); // We only need this for the model name

// Get the text we should speak
$text = implode(" ", $input);

// Create the synthesizer
$synthesizer = new Synthesizer($model);
$synthesizer->speak($text);

