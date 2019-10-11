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
define("ANNOUNCE_WORKING_STEPS", 10);

// Generate necessary file structure
@mkdir($modelFolder);
@mkdir($modelFolder."/words");
@mkdir($modelFolder."/phones");
@mkdir($modelFolder."/syllables");

// Parse the json data
$data = json_decode(file_get_contents($alignedTranscriptFilename), true);

// Loop through the words (we don't need the rest atm) to figure out stuff
foreach($data["words"] as $index => $word){
	// Tell the user about the progress, but not all the time
	if($index % ANNOUNCE_WORKING_STEPS === 0){
		echo "Processing word ".$index." of ".count($data["words"])."\n";
	}	
	// Ignore the word if matching didn't work
	if($word["case"] === "success"){
		// Extract all phonemes from the file
		extractPhones($word, $baseAudioFilename, $modelFolder);
	}
}

// Extract all the relevant slices from the audio file using ffmpeg
function extractPhones($word, $baseAudioFilename, $model){
	// Get all possible slices in every possible size
	foreach($word["phones"] as $index => $phone){
		$phoneme = explode("_", $phone["phone"])[0];
		// Get the beginning of the phone
		if($index === 0){
			// first phoneme is also the beginning of a word
			$startTime = $word["start"];
		} else {
			// Loop through all to find out the end of the last phone
			$startTime = $word["start"];
			foreach($word["phones"] as $count => $priorPhone){
				if($count < $index){
					$startTime += $priorPhone["duration"];
				} else {
					break;
				}	
			}
		}
		$endTime = $startTime + $phone["duration"];
		// Extract just the isolated phoneme right now
		sliceAudiofile($startTime, $endTime, $baseAudioFilename, $model."/phones/".$phoneme.".mp3");
		// Now, extract the context of the phoneme along with it
		// Start working from current phoneme, and then look into the future
		// Save all phonemes that are part of context to string for file naming
		$allString = $phoneme."_";
		for($i = 1; $i < MAX_SLICE_SIZE; $i++){
			// Check whether there is a phoneme in the future we can use
			if(!empty($word["phones"][$index + $i])){
				$futurePhoneme = $word["phones"][$index + $i];
				// Phone exists, slice new times
				$endTime += $futurePhoneme["duration"];
				$allString .= explode("_", $futurePhoneme["phone"])[0]."_"; // just the first element.
				sliceAudiofile($startTime, $endTime, $baseAudioFilename, $model."/syllables/".$allString.".mp3");
			}
		}
	}	
}

// Assemble an ffmpeg command for slicing everything up
function sliceAudiofile($start, $end, $inputFilename, $outputFilename){
	// Do not do the same job twice (takes so much time!)
	// TODO: This will check will eventually have to move somewhere else
	if(!file_exists($outputFilename)){
		// Tell ffmpeg what file to read from
		$command = "ffmpeg -i ".$inputFilename;
		// We're working with mp3 files and our slice begins here
		$command .= " -vn -acodec mp3 -ss ".$start;
		// And the slice ends there
		$command .= " -to ".$end;
		// Save it to disk using the given filename and blatantly disagree with everything
		// Also, we don't really need the output from ffmpeg, so we just silence it
		$command .= " -n -loglevel -8 ".$outputFilename;
		
		// Run the ffmpeg command
		exec($command);
	}	
}
