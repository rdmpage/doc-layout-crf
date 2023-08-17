<?php

// Use labels from  CRF output to generate labels files
// This can be used to process predictions, or if we have manually edited the CRF output
// to create training data

function crf_to_labels($doc, $basedir)
{
	$output_filename = $basedir . '.out';	
		
	// load predictions and convert to label files
	$output = file_get_contents($output_filename);
	$output = trim($output);
	
	$rows = explode("\n", $output);

	// print_r($rows);

	$labels = array();

	$n = count($rows);
	for ($i = 0; $i < $n; $i++)
	{
		$row = $rows[$i];
	
		$values = preg_split('/\s+/', $row);
		$tag = array_pop($values);
	
		$page_number = $doc->feature_row_to_page[$i];
		if (!isset($labels[$page_number]))
		{
			$labels[$page_number] = array();
		}
	
		foreach ($doc->feature_row_to_words[$i] as $word_id)
		{
			$labels[$page_number][$word_id] = $tag;
		}

	}
	
	// print_r($labels);
	
	foreach ($labels as $page_number => $tags)
	{
		$label_filename = $basedir . '/labels' . str_pad($page_number, '3', '0', STR_PAD_LEFT) . '.json';	
		file_put_contents($label_filename, json_encode($tags));
	}
	
}

?>
