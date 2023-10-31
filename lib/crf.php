<?php

// seems tio be a bug, can't we get all the info we need from the CRF outut and the tokebns files?
// to see bug try predict.php JJSE_19_hocr

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
	
	$row_counter = 0;

	$n = count($rows);
	for ($i = 0; $i < $n; $i++)
	{
		$row = $rows[$i];
		
		if (trim($row) != '')
		{
	
			$values = preg_split('/\s+/', $row);
			$tag = array_pop($values);
	
			$page_number = $doc->feature_row_to_page[$row_counter];
			if (!isset($labels[$page_number]))
			{
				$labels[$page_number] = array();
			}
	
			foreach ($doc->feature_row_to_words[$row_counter] as $word_id)
			{
				$labels[$page_number][$word_id] = $tag;
			}
			
			$row_counter++;
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
