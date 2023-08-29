<?php

// Get key details from a PDF using PdfParser library

require_once (dirname(__FILE__) . '/vendor/autoload.php');

// Parse PDF file and build necessary objects.
$parser = new \Smalot\PdfParser\Parser();


$filename = 'ttr-8-2.pdf'; 
$filename = 'researchgate/PulawskietalPCASser4v62withIDXrevisedversionupdatedIDXattachedLRVS.pdf';
//$filename = 'scans figs not extracted/McGuire-AME102.pdf';

$pdf = $parser->parseFile($filename);

$topics = array('text', 'metadata', 'fonts', 'coords');
$topics = array( 'details');
$topics = array( 'metadata');
$topics = array( 'media');
//$topics = array( 'fonts');
//$topics = array( 'trailer');


//----------------------------------------------------------------------------------------
// text
if (in_array('text', $topics))
{
	echo "\Text\n";
	echo   "--------\n";
	$text = $pdf->getText();
	echo $text;
}

//----------------------------------------------------------------------------------------
if (in_array('details', $topics))
{
	echo "\nDetails\n";
	echo   "--------\n";
	$metaData = $pdf->getDetails();
	print_r($metaData);
}

//----------------------------------------------------------------------------------------
// seems to crash on some chinese fonts
if (in_array('fonts', $topics))
{
	echo "\nFonts\n";
	echo   "-----\n";
	$fonts = $pdf->getFonts();
	//print_r(array_keys($fonts));

	foreach ($fonts as $id => $font)
	{
		//print_r($font->getDetails());
		echo $font->getDetails()['Name'] . "\n";	
	}
}

//$o = $pdf->getObjects();
//print_r(array_keys($o));

/*
$o = $pdf->getObjectById('26_0');
print_r($o);
*/


//----------------------------------------------------------------------------------------
if (in_array('media', $topics))
{
	echo "\nPage media boxes\n";
	echo   "----------------\n";
	$pages = $pdf->getPages();
	foreach ($pages as $page)
	{
		echo "\nPage\n";
		echo "\n----\n";
		
		$details = $page->getDetails();
	
		$mediaBox = $details['MediaBox'];
	
		echo "MediaBox\n";
		print_r($mediaBox);
		
		$xobjects = $page->getXObjects();
		foreach( $xobjects  as $object ) 
		{
			if ($object && method_exists($object, 'getDetails'))
			{
				print_r($object->getDetails());
			}
		}	
	}
}

//----------------------------------------------------------------------------------------
if (in_array('numbering', $topics))
{
	echo "\nPage labels\n";
	echo   "-----------\n";
	$catalogues = $pdf->getObjectsByType('Catalog');
	if ($catalogues)
	{
		$catalogue = reset($catalogues);
		$object = $catalogue->get('PageLabels');
	
		if ($object && method_exists($object, 'getDetails'))
		{
			print_r($object->getDetails());
		}
	}
}

//----------------------------------------------------------------------------------------
if (in_array('metadata', $topics))
{
	echo "\nMetadata\n";
	echo   "--------\n";

	$metadata = $pdf->getObjectsByType('Metadata');
	//dump_r($metadata, false, true, 2);
	if ($metadata)
	{
		foreach ($metadata as $m)
		{
			//$d = $m->getDetails();
		
			$xml = $m->getContent();
			//dump_r($d, false, true, 2);
		
			echo $xml; // XML
		
		}
	}
}

//----------------------------------------------------------------------------------------
if (in_array('trailer', $topics))
{
	echo "\nID\n";
	echo   "--\n";

	$trailer = $pdf->getTrailer();

	//echo gettype($trailer) . "\n";

	//dump_r($trailer, false, true, 3);

	if ($trailer->has('Id')) 
	{
		/** @var PDFObject $info */
		$id = $trailer->get('Id');
	
		//dump_r($id, false, true, 4);
	
		$elements = $id->getContent()->getElements();
	
		$ID = array();
		foreach ($elements as $element)
		{
			$ID[] = $element->getContent();
		}
	
		echo "[" . join(",", $ID) . "]\n";
		echo "\n";
	}
}

//----------------------------------------------------------------------------------------
if (in_array('coords', $topics))
{
	$data = $pdf->getPages()[0]->getDataTm();
	print_r($data);
}

?>
