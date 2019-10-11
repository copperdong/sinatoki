<?php

namespace Synthi;

/**
 * Slice up the given audio file based on a time-aligned transcript outputted by the gentle forced aligner
 */
class Slicer {

	public $alignedTranscript;
	public $baseAudioFilename;
	public $modelName;

	public $sliceSize = 3;	
	public $verbosity = 10;

	public function __construct($alignedTranscript, $baseAudioFilename, $modelName){
		// Filename of the transcript
		$this->alignedTranscript = $alignedTranscript;
		// Filename of the base audio file
		$this->baseAudioFilename = $baseAudioFilename;
		// Folder of the model to be generated
		$this->modelName = $modelName;
	}

	/**
	 * Generates necessary file structure for a voice model
	 */
	private function generateModelFileStructure(){
		@mkdir($this->modelName);
		@mkdir($this->modelName."/words");
		@mkdir($this->modelName."/phones");
		@mkdir($this->modelName."/syllables");
	}

	/**
	 * Generate the voice model
	 */
	public function createModel(){
		$this->generateModelFileStructure();
		// Loop through the words (we don't need the rest atm) to figure out stuff
		foreach($this->alignedTranscript["words"] as $index => $word){
			// Tell the user about the progress, but not all the time
			if($index % $this->verbosity === 0){
				echo "Processing word ".$index." of ".count($this->alignedTranscript["words"])."\n";
			}	
			// Ignore the word if matching didn't work
			if($word["case"] === "success"){
				// Extract all phonemes from the file
				$this->extractPhones($word);
			}
		}
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
			$this->sliceAudiofile($startTime, $endTime, $this->modelName."/phones/".$phoneme.".mp3");
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
					$allString .= explode("_", $futurePhoneme["phone"])[0]."_"; // just the first element.
					$this->sliceAudiofile($startTime, $endTime, $this->modelName."/syllables/".$allString.".mp3");
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

