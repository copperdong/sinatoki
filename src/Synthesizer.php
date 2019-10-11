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
				$sound = Converter::convertIPAtoGentle($token);
				// Make sure it is an actual sound
				if(!empty($sound)){
					$soundOrder[] = $sound;
				}
			}
			// TODO: Adjust silence length so it best fits the speaker model
			$soundOrder[] = "silence";
		}

		$fileOrder = [];
		$ignoreIndexes = [];
		foreach($soundOrder as $soundIndex => $sound){
			// Add silence
			if($sound === "silence"){
				$fileOrder[] = "silence.mp3";
			}
			// Do not add the same sound multiple times!
			elseif(!in_array($soundIndex, $ignoreIndexes)){
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
						} else {
							break;
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

		$this->produceSpeech($fileOrder);
	}

	/**
	* Use ffmpeg to create the final speech file
	*/
	private function produceSpeech($fileOrder){
		// Assembly of a new ffmpeg command
		$command = "ffmpeg -f mp3 -i ";
		// Add all the mp3s to the command
		$command .= implode(" -f mp3 -i ", $fileOrder);

		// Tell it more about the files
		$command .= " -filter_complex '";
		for($i = 0; $i < count($fileOrder); $i++){
			$command .= "[".$i.":0]";
		}

		// Export options
		$command .= "concat=n=".count($fileOrder).":v=0:a=1[out]' -map [out] -y -loglevel warning ".$this->outputFilename.".mp3";

		// Run it
		exec($command);
	}

}
