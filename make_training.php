<?php

// Given a list of documents that have labels, output CRF frmat data to use for training

error_reporting(E_ALL);

ini_set('memory_limit', '-1');

require_once(dirname(__FILE__) . '/lib/doc.php');
require_once(dirname(__FILE__) . '/lib/font.php');
require_once(dirname(__FILE__) . '/lib/spatial.php');
require_once(dirname(__FILE__) . '/lib/utils.php');

require_once(dirname(__FILE__) . '/lib/crf.php');


$sources = array
(
'harmer2008',
'zt03796p593',
'zt01991p027',
);

// make sure we start with a fesh template
$template_filename = 'rod.template';

if (file_exists($template_filename))
{
	unlink($template_filename);
}

foreach ($sources as $basedir)
{
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
	
	echo "\n";
	
}

?>
