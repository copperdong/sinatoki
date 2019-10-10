<?php

include("converter.php");

// The script that handles the actual speech synthesis

$input = $argv;

// Model to use
$model = $input[1];
unset($input[0]);
unset($input[1]); // We only need this for the model name

// Get the text we should speak
$text = implode(" ", $input);
speak($text, $model);

function speak($text, $model){
	// Retrieve pronunciation from espeak in ipa format
	$ipa = exec('espeak --ipa=3 -q "'.$text.'"');

	// Process ipa string
	$ignoreChars = ["ˈ", "ˌ", "ː"]; 
	// Ignore these as we don't handle pronunciation yet
	$ipa = str_ireplace($ignoreChars, "", $ipa);
	echo $ipa;
	// Tokenize and convert to gentle
	$ipa = explode(" ", $ipa);
	$soundOrder = [];
	foreach($ipa as $word){
		$tokens = explode("_", $word);
		// Loop through all sounds and match them
		foreach($tokens as $token){
			$soundOrder[] = convertIPAtoGentle($token);
		}
		// Add silence to the end of a word
		// TODO
	}

	$fileOrder = [];
	foreach($soundOrder as $sound){
		$fileOrder[] = $model."/phones/".$sound.".mp3";
	}

	exec("cat ".implode(" ", $fileOrder)." > output.mp3");
}
