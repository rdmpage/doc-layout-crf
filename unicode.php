<?php

// exploring code points

$chars = array(
	'en' => 'A',
	'zu' => '河',
	'de' => 'ö',
	'ru' => 'Р',
	'ja' => 'ク',
	'ru' => 'Ж',
	'ko' => '가',
	'fr' => 'é',
	'no' => 'ø',
	'?'	=> 'æ',
	'?' => '♂',

);

foreach ($chars as $language => $char)
{
	echo $language . ' ' . $char  . ' ' . IntlChar::getBlockCode($char) . "\n";
}

?>
