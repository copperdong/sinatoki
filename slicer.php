<?php

// This is the script used to slice up the given audio file based on a time-aligned transcript outputted by the gentle forced aligner

// Filename of the transcript
$alignedTranscriptFilename = $argv[1];
// Filename of the base audio file
$baseAudioFilename = $argv[2];
// Folder of the model to be generated
$modelFolder = $argv[3];

// How big should the slices be, at maximum?
define("MAX_SLICE_SIZE", 3);

// Generate necessary file structure
@mkdir($modelFolder);
@mkdir($modelFolder."words");
@mkdir($modelFolder."phones");
@mkdir($modelFolder."syllables");

// Parse the json data
$data = json_decode(file_get_contents($alignedTranscriptFilename));

// Loop through the words (we don't need the rest atm) to figure out stuff
foreach($data["words"] as $index => $word){
	// Ignore the word if the matching didn't work
	if($word["case"] === "success"){
		// Extract all phonemes from the file
		extractPhones($word, $baseAudioFilename, $modelFolder);
	}
}

// Extract all the relevant slices from the audio file using ffmpeg
function extractPhones($word, $baseAudioFilename, $model){
	// Get all possible slices in all possible sizes
	for($i = 0; $i < MAX_SLICE_SIZE, $i++){
		
	}
}
