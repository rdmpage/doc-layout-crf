<?php

// Create features and add existingm labels

error_reporting(E_ALL);

ini_set('memory_limit', '-1');

require_once(dirname(__FILE__) . '/lib/doc.php');
require_once(dirname(__FILE__) . '/lib/font.php');
require_once(dirname(__FILE__) . '/lib/spatial.php');
require_once(dirname(__FILE__) . '/lib/utils.php');

require_once(dirname(__FILE__) . '/lib/crf.php');

$basedir = '';
if ($argc < 2)
{
	echo "Usage: predict.php <basedir>\n";
	exit(1);
}
else
{
	$basedir = $argv[1];
}

// To match labels we need to generate the features

// load pages into document
$doc = doc_load($basedir);

// get font info
doc_fonts($doc);

// feature engineering, outputs 
doc_decorations($doc);
$crf_data = doc_do_pages($doc, true);

// OK, we now have a doc that has the features, read CRF output and apply labels

echo $crf_data;

?>
