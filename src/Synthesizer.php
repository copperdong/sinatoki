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
		foreach($soundOrder as $sound){
			$fileOrder[] = $this->model."/phones/".$sound.".mp3";
		}

		exec("cat ".implode(" ", $fileOrder)." > ".$this->outputFilename.".mp3");
	}

}
