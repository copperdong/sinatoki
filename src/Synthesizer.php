<?php

namespace Synthi;

use Synthi\Converter;

class Synthesizer {

	public $model;
	public $accuracy = 3;

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
			// Do not add the same sound multiple times!
			if(!in_array($soundIndex, $ignoreIndexes)){
				// Fallback to the isolated phone if our processing doesn't work
				$save = $this->model."/phones/".$sound.".mp3";
				// Look into the future, see if there are more sounds present
				$currentPhoneString = $sound;
				for($i = 1; $i < $this->accuracy; $i++){
					$futureIndex = $soundIndex + $i;
					if(!empty($soundOrder[$futureIndex])){
						// Sound is present. Check whether we already know that phoneme context
						$futureSound = $soundOrder[$futureIndex];
						if(file_exists($this->model."/syllables/".$currentPhoneString."_".$futureSound."_.mp3")){
							// Checkmate!
							$currentPhoneString .= "_".$futureSound;
							// Don't add the same sound twice
							$ignoreIndexes[] = $futureIndex;
						}
					}
				}

				// Save what we know is best
				echo "Saving ".$currentPhoneString."\n";
				if($currentPhoneString === $sound){
					$fileOrder[] = $this->model."/phones/".$sound.".mp3";
				} else {
					$fileOrder[] = $this->model."/syllables/".$currentPhoneString."_.mp3";
				}
			}	
		}

		exec("cat ".implode(" ", $fileOrder)." > ".$this->outputFilename.".mp3");
	}

}
