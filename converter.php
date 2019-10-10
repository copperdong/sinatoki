<?php

// This script maps espeak's IPA output to gentle/kaldi phones

function convertIPAtoGentle($espeak){
	$replace = [
		"ð" => "th_E",
		"ə" => "ah_B",
		"k" => "k_E",
		"w" => "w_I",
		"ɪ" => "ih_E",
		"b" => "b_B",
		"ɹ" => "r_B",
		"aʊ" => "aw_I",
		"n" => "n_E",
		"f" => "f_B",
		"ɒ" => "ao_B",
		"s" => "s_E",
		"dʒ" => "jh_I",
		"ʌ" => "ah_B",
		"m" => "m_I",
		"p" => "p_B",
		"v" => "v_I",
		"l" => "l_B",
		"eɪ" => "ey_I",
		"z" => "z_E",
		"ɡ" => "g_B"
	];

	// Map every single ipa sound to a phoneme
	$output = [];
	foreach($espeak as $value){
		$output[] = $replace[$value];
	}

	return $output;
}
