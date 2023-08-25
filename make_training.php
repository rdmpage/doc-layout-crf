<?php

// Given a list of documents that have labels, output CRF frmat data to use for training

error_reporting(E_ALL);

ini_set('memory_limit', '-1');

require_once(dirname(__FILE__) . '/lib/doc.php');
require_once(dirname(__FILE__) . '/lib/font.php');
require_once(dirname(__FILE__) . '/lib/spatial.php');
require_once(dirname(__FILE__) . '/lib/utils.php');

require_once(dirname(__FILE__) . '/lib/crf.php');


$basedir = 'training';

$dirs = array
(
// Zootaxa
'harmer2008',
'zt03796p593',
'zt01991p027',

// Phytokeys
'PK-184-067_article-71045_en_1',

// Acta Zool. Acad. Sci. Hung.
'7459',
'ActaZH_2017_Vol_63_1_71',
'ActaZH_2017_Vol_63_4_429',

// Zoologische Mededelingen
'ZM1989063006',
'ZM82-02_Bruggen',

// Shilap
'71-82', // has issues with text overlapping figures (figure labelling)
);

// make sure we start with a fesh template
$template_filename = 'rod.template';

if (file_exists($template_filename))
{
	unlink($template_filename);
}

foreach ($dirs as $dirname)
{
	if (preg_match('/^\w/', $dirname))
	{
		// To match labels we need to generate the features

		// load pages into document
		$doc = doc_load($basedir . '/' . $dirname);

		// get font info
		doc_fonts($doc);

		// feature engineering, outputs 
		doc_decorations($doc);
		$crf_data = doc_do_pages($doc, true);

		// OK, we now have a doc that has the features, read CRF output and apply labels
		echo $crf_data . "\n";
	}
	
}

?>
