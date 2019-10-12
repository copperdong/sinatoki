<?php

namespace Synthi;

/**
 * Converting IPA symbols to gentle/kaldi phones
 */
class Converter {

	public static function convertIPAtoGentle($ipa){
		// Many replacements and matches are still wrong. They need to be adjusted.
		$replace = [
			"ð" => "th",
			"ə" => "er",
			"k" => "k",
			"w" => "w",
			"ɪ" => "ih",
			"b" => "b",
			"ɹ" => "r",
			"aʊ" => "aw",
			"n" => "n",
			"f" => "f",
			"ɒ" => "ao",
			"s" => "s",
			"dʒ" => "jh",
			"ʌ" => "ah",
			"m" => "m",
			"p" => "p",
			"v" => "v",
			"l" => "l",
			"eɪ" => "ey",
			"z" => "z",
			"ɡ" => "g",
			"ɜ" => "er",
			"h" => "hh",
			"əʊ" => "ow",
			"d" => "d",
			"ŋ" => "ng",
			"t" => "t",
			"θ" => "th",
			"aɪ" => "ay",
			"ɐ" => "ah",
			"u" => "uh",
			"ɛ" => "eh",
			"a" => "ae",
			"j" => "y",
			// Wrong phoneme, but resembles
			"i" => "iy",
			// Incomplete - requires + t
			"tʃ" => "sh",
			"ɔ" => "ow",
			// Incomplete - requires + e
			"əl" => "l",
			// Wrong phoneme, but resembles
			"eə" => "eh",
			"ʃ" => "sh",
			"ʊ" => "uh",
			// Incomplete - requires + e
			"aɪə" => "ey",
			"ɔɪ" => "oy",
			"ɑ" => "aa",
			// Incomplete - requires + aa
			"iə" => "iy",
			// Incomplete - requires + a
			"ʊə" => "uh"
		];

		// Map every single ipa sound to a phoneme
		return $replace[$ipa];
	}

}

