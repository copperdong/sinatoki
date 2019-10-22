<?php

namespace Sinatoki;

/**
 * Slice up the given audio file based on a time-aligned transcript outputted by the gentle forced aligner
 */
class Slicer {

	public $alignedTranscript;
	public $baseAudioFilename;
	public $modelName;

	public $sliceSize = 3;

	// Save the structure
	private $phoneMap = [
		"words" => [],
		"slices" => [],
		"phones" => []
	];

	public function __construct($alignedTranscript, $baseAudioFilename, $modelName){
		// Filename of the transcript
		$this->alignedTranscript = $alignedTranscript;
		// Filename of the base audio file
		$this->baseAudioFilename = $baseAudioFilename;
		// Folder of the model to be generated
		$this->modelName = $modelName;
	}

	/**
	 * Generate the voice model
	 */
	public function createModel(){
		// Loop through the words (we don't need the rest atm) to figure out stuff
		foreach($this->alignedTranscript["words"] as $index => $word){
			// Ignore the word if matching didn't work
			if($word["case"] === "success"){
				// Extract all phonemes from the file
				$this->extractPhones($word);
			}
		}

		$this->savePhoneMap();
		$this->exportVoiceData();
	}

	/**
	 *  Extract all the relevant slices from the audio file
	 */
	private function extractPhones($word){
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
			$phoneId = md5($startTime.$endTime.$phoneme);
			$this->phoneMap["phones"][$phoneme][] = 
			[
				"startTime" => $startTime,
				"endTime" => $endTime,
				"file" => $this->modelName."/phones/".$phoneId.".mp3",
				// Save additional information for better synthesis
				"context" => 
				[
					// The word this phoneme has been used in
					"word" => $word["word"],
					// The position at which the phoneme occured
					"phonPos" => $index,
					// The amount of total phonemes of the word
					"phonAmt" => count($word["phones"])
				]
			];

			// Now, extract the context of the phoneme along with it
			// Start working from current phoneme, and then look into the future
			// Save all phonemes that are part of context to string for file naming
			$allString = $phoneme."_";
			for($i = 1; $i < $this->sliceSize; $i++){
				// Check whether there is a phoneme in the future we can use
				if(!empty($word["phones"][$index + $i])){
					$futurePhoneme = $word["phones"][$index + $i];

					// Phone exists, slice new times
					$endTime += $futurePhoneme["duration"];
					$allString .= explode("_", $futurePhoneme["phone"])[0]."_";

					// just the first element.
					$sliceId = md5($startTime.$endTime.$allString);
					$this->phoneMap["slices"][$allString][] = 
					[
						"startTime" => $startTime,
						"endTime" => $endTime,
						"file" => $this->modelName."/slices/".$sliceId.".mp3",
						"context" => 
						[
							"word" => strtolower($word["word"])
						]
					];
				}
			}
		}

		// Also save the word as a whole, individually
		if(strlen($word["word"]) > 1){
			$wordId = md5($word["start"].$word["end"].$word["word"]);
			$this->phoneMap["words"][$word["word"]][] = 
				[
					"startTime" => $word["start"],
					"endTime" => $word["end"],
					"file" => $this->modelName."/words/".$wordId.".mp3",
					"context" => []
				];
		}
	}

	// Save the calculated phone map to disk
	public function savePhoneMap(){
		echo "Writing phone map...\n";
		@mkdir($this->modelName);
		return file_put_contents($this->modelName."/voice.json", json_encode($this->phoneMap, JSON_PRETTY_PRINT));
	}

	// Extract all relevant phoneme slices from audio
	public function exportVoiceData(){
		// Loop through all
		foreach($this->phoneMap as $index => $data){
			// Create a directory if it's not already present
			echo "Exporting ".count($data)." of ".$index."\n";
			@mkdir($this->modelName."/".$index);

			// Go through each sound and export every single one
			foreach($data as $sound => $sounds){
				echo "Slicing ".count($sounds)." of ".$sound."\n";
				// Now finally export all sounds of the same type
				foreach($sounds as $soundData){
					$this->sliceAudiofile($soundData["startTime"], $soundData["endTime"], $soundData["file"]);
				}
			}
		}
	}

	// Assemble an ffmpeg command for slicing everything up
	private function sliceAudiofile($start, $end, $outputFilename){
		// Do not do the same job twice (takes so much time!)
		// TODO: This will check will eventually have to move somewhere else
		if(!file_exists($outputFilename)){
			// Tell ffmpeg what file to read from
			$command = "ffmpeg -i ".$this->baseAudioFilename;
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

}

