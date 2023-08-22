<?php

// Gneertae training data (i.e., labelled)

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

// load pages into document
$doc = doc_load($basedir);

// get font info
doc_fonts($doc);

// feature engineering, outputs 
doc_decorations($doc);

$crf_data = doc_do_pages($doc);

$data_filename = $basedir . '.data';
file_put_contents($data_filename, $crf_data);

$output_filename = $basedir . '.out';	

$command = 'crf_test -m rod.model ' . $data_filename . ' > ' . $output_filename;

system($command);

crf_to_labels($doc, $basedir);

?>
