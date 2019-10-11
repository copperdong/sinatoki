<?php

namespace Synthi;

use Synthi\Converter;

class Synthesizer {

	public $model;

	public $outputFilename = "output";

	public function __construct($model){
		$this->model = $model;
	}

	/**
	 * Produce speech
	 */
	public function speak($text){
		// Retrieve pronunciation from espeak in ipa format
		$ipa = exec('espeak --ipa=3 -q "'.$text.'"');

		// Process ipa string
		$ignoreChars = ["ˈ", "ˌ", "ː"]; 
		// Ignore these as we don't handle emphasis yet
		$ipa = str_ireplace($ignoreChars, "", $ipa);
		echo $ipa;
		// Tokenize and convert to gentle
		$ipa = explode(" ", $ipa);
		$soundOrder = [];
		foreach($ipa as $word){
			$tokens = explode("_", $word);
			// Loop through all sounds and match them
			foreach($tokens as $token){
				$soundOrder[] = Converter::convertIPAtoGentle($token);
			}
			// Add silence to the end of a word
			// TODO
		}

		$fileOrder = [];
		$ignoreIndexes = [];
		foreach($soundOrder as $soundIndex => $sound){
			if(!in_array($soundIndex, $ignoreIndexes)){
				// Look into the future, see if there are more sounds present
				$futureIndex = $soundIndex + 1;
				if(!empty($soundOrder[$futureIndex])){
					// Sound is present. Check whether we already know that phoneme context
					$futureSound = $soundOrder[$futureIndex];
					if(file_exists($this->model."/syllables/".$sound."_".$futureSound."_.mp3")){
						// Checkmate!
						$fileOrder[] = $this->model."/syllables/".$sound."_".$futureSound."_.mp3";
						// Don't add the same sound twice
						$ignoreIndexes[] = $futureIndex;
					} else {
						// Only save the single phone
						$fileOrder[] = $this->model."/phones/".$sound.".mp3";
					}
				} else {
					$fileOrder[] = $this->model."/phones/".$sound.".mp3";
				}
			}	
		}

		exec("cat ".implode(" ", $fileOrder)." > ".$this->outputFilename.".mp3");
	}

}
