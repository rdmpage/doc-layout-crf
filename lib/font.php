<?php

//----------------------------------------------------------------------------------------
// Cluster font sizes
// 
// We find peaks in the distribution of sizes using persistent topology, see
// https://www.sthu.org/blog/13-perstopology-peakdetection/index.html
// and https://doi.org/10.1007/978-3-658-32182-6_13
// $sizes is an array of the form size => frequency
// Problem is that this method doesn't always cluster all sizes, if we have lots of
// rare sizes these never get clustered :( so we have to fill these in
function cluster_sizes($sizes, $num_clusters = 3)
{
	$debug = false;
	//$debug = true;

	// CSS names for fonts	
	$font_size_names = array(
		-5 => 'xx-small',
		
		-4 => 'xx-small',
		-3 => 'xx-small',
		-2 => 'x-small',
		-1 => 'small',
		 0 => 'medium',
		 1 => 'large',
		 2 => 'x-large',
		 3 => 'xx-large',
		 4 => 'xx-large',
		 
		 5 => 'xx-large',
		  
	);	
	
	// reverse sort by frequency
	arsort($sizes);

	// how many different sizes do we have?
	$num_sizes 		= count($sizes);
	
	if ($num_sizes <= $num_clusters)
	{
		// Small number of fonts, typical of born-digital PDFs
		$modal_size = array_keys($sizes)[0]; // most common font size
		
		// get list of the fonts in order of font size
		$fs = array_keys($sizes);
		sort($fs);
		
		// which row in this list has the modal font size?
		$mode = array_search($modal_size, $fs);
		
		// get font size names for font sizes
		$map = array();
		foreach ($fs as $order => $font_size)
		{
			$name_key = $order - $mode;
			$map[$font_size] = $font_size_names[$name_key];
		}
	}
	else
	{
		// We have too many sizes of fonts so we need to cluster them
	
		$peaks 			= array(); 	// store peaks	
		$orig_peaks		= array(); 	// store peaks before any merging changes them	
		$index_to_peak 	= array();	// 0-n index of sizes mapped to peak

		// birth and death of peaks
		$born = array();
		$died = array();
		
		// get list of sizes in order of frequency
		$sorted_sizes 	= array_keys($sizes);			
	
		// find peaks
		// we go through list of sizes in descending order of their frequency
		$index_left = $index_right = 0;
		foreach ($sorted_sizes as $index => $size)
		{
			// echo "index=>size $index => $size\n";

			$left  = $index > 0 				&& isset($index_to_peak[$size - 1]);
			$right = $index < $num_sizes - 1 	&& isset($index_to_peak[$size + 1]);
		
			if ($left)
			{
				$index_left = $index_to_peak[$size-1];
			}
			if ($right)
			{
				$index_right = $index_to_peak[$size+1];
			}
	
			// new peak
			if (!$left && !$right)
			{
				if ($debug) { echo "New peak\n"; }
		
				$peaks[] = array();
		
				$index_to_peak[$size] = count($peaks) - 1;
				$peaks[$index_to_peak[$size]][] = $index;
					
				$born[$index_to_peak[$size]] = $sizes[$size];
				$died[$index_to_peak[$size]] = 0;
			}
	
			// Merge to left peak
			if ($left && !$right)
			{
				if ($debug) { echo "Add to left peak\n"; }
		
				$index_to_peak[$size] = $index_left;
				$peaks[$index_left][] = $index;
			}
	
			// Merge to right peak
			if (!$left && $right)
			{
				if ($debug) { echo "Add to right peak\n"; }
	
				$index_to_peak[$size] = $index_right;
				$peaks[$index_right][] = $index;
			}
	
			// Merge left and right peaks
			// We store the original peaks so that we don't lose them
			if ($left && $right)
			{
				if ($debug) { echo "Merge\n"; }
		
				if (!isset($orig_peaks[$index_left]))
				{
					$orig_peaks[$index_left] = $peaks[$index_left];
				}
				if (!isset($orig_peaks[$index_right]))
				{
					$orig_peaks[$index_right] = $peaks[$index_right];
				}
				
				if ($peaks[$index_left][0] < $peaks[$index_right][0])
				{
					// merge right with left
					if ($debug) { echo " r $index_right with l $index_left \n"; }
			
					// frequency value at which right peak "dies"			
					$died[$index_right] = $sizes[$size];
			
					foreach ($peaks[$index_right] as $x)
					{
						$peaks[$index_left][] = $x;			
						$index_to_peak[$sorted_sizes[$x]] = $index_left;
					}
				}
				else
				{
					// merge left with right
					if ($debug) { echo " l $index_left with r $index_right \n"; }
			
					// frequency value at which left peak "dies"	
					$died[$index_left] = $sizes[$size];

					foreach ($peaks[$index_left] as $x)
					{
						$peaks[$index_right][] = $x;			
						$index_to_peak[$sorted_sizes[$x]] = $index_right;
					}
		
				}
			}	
		}

		// Add any peaks that haven't ever been merged
		foreach ($peaks as $index => $p)
		{
			if (!isset($orig_peaks[$index]))
			{
				$orig_peaks[$index] = $p;
			}
		}	
	
		ksort($orig_peaks);
	
		// OK, so we now have a set of peaks in the distribution of font sizes
	
		if ($debug)
		{
			print_r($orig_peaks);
			print_r($died);
			print_r($born);
		}

		// Persistence is how long a peak survives, and gives us a way to rank peaks
		// by importance
		$persistence = array();
		foreach ($born as $index => $birth)
		{
			$persistence[$index] = $birth - $died[$index];
		}
	
		if ($debug)
		{
			echo "Persistence\n";
			print_r($persistence);
		}
	
		// sort persistence
		arsort($persistence);
	
		if ($debug)
		{
			print_r($persistence);
		}
	
		// We will keep only the set of num_cluster largest peaks
		$num_clusters = min($num_clusters, count($persistence));
		$peak_list = array_slice($persistence, 0, $num_clusters, true);
	
		//print_r($peak_list);
	
		// map fonts to relative sizes
		
		// For each value of a font size we map it to a font size name

		// Set default font size name
		$map = array();
		foreach($sorted_sizes as $order => $size)
		{
			$map[$size] = 'unknown';
		}
	
		// To get peak size order get the first font size in the peak
		$peak_size_order = array();
		foreach ($peak_list as $peak_number => $depth)
		{
			$peak_size_order[$peak_number] = $sorted_sizes[$orig_peaks[$peak_number][0]];
		}
		// sort peaks by their font size
		asort($peak_size_order);
	
		if ($debug) { print_r($peak_size_order); }
	
		// get list of the peaks in order of font size
		$po = array_keys($peak_size_order);
	
		if ($debug)
		{
			echo "po\n";
			print_r($po);
		}
	
		$peak_names = array();
	
		// the modal font size is peak "0" and this is the "normal" sized font
		$mode = array_search(0, $po);
	
		for ($i = 0; $i < $num_clusters; $i++)
		{
			$peak_names[$po[$i]] = $i - $mode; // fonts other than the mode are either smaller or larger than the mode
		}
	
		if ($debug)
		{
			echo "peak_names\n";
			print_r($peak_names);
		}
	
		// Set CSS font size name for font size clusters
		foreach ($peak_list as $peak_number => $depth)
		{
			foreach ($orig_peaks[$peak_number] as $order)
			{
				$index = $peak_names[$peak_number];
				$index = min(5, $index);
				$index = max(-5, $index);
			
				$map[$sorted_sizes[$order]] = $font_size_names[$index];
			}
		}
		
	}
	
	// Sort for visual display
	ksort($map);
	
	// Fill in any gaps
	$extra_name = 'xx-small'; // dummy value in case first font size has no name
	
	$previous_size = 0;
	foreach ($map as $size => $name)
	{
		if ($name == 'unknown')
		{
			if ($previous_size == 0)
			{
				$map[$size] = $extra_name;
			}
			else
			{
				// give this size the name of the font below it in the list
				$map[$size] = $map[$previous_size];
			}
		}
		$previous_size = $size;
	}	
	
	// return the map, this is what we will use for font size lookup
	return $map;
}

//----------------------------------------------------------------------------------------
// Try and classify font size
// For any size that we haven't classified:
// if it is in the range of classified sizes, it is called "medium"
// if it is smaller than any size, it gets the smallest size
// if it is larger, it gets the largest size
function font_classify($font_map, $font_size)
{
	$font_size_names = array(
		-5 => 'xx-small',
		
		-4 => 'xx-small',
		-3 => 'xx-small',
		-2 => 'x-small',
		-1 => 'small',
		 0 => 'medium',
		 1 => 'large',
		 2 => 'x-large',
		 3 => 'xx-large',
		 4 => 'xx-large',
		 
		 5 => 'xx-large',
		  
	);	
	
	$names_to_index = array();
	foreach ($font_size_names as $k => $v)
	{
		$names_to_index[$v] = $k;
	}
	
	$font_size_name = 'medium'; // assume default
	
	if (isset($font_map[$font_size]))
	{
		$font_size_name = $font_map[$font_size];
	}
	else
	{
		$index = 0;
		
		// is it outside bounds?
		$doc_sizes = array_keys($font_map);
		$range = array($doc_sizes[0], $doc_sizes[count($doc_sizes) - 1]);
		
		//print_r($range);
		
		if ($font_size > $range[1])
		{
			$index = max(4, $names_to_index[$font_map[$range[1]]]);
		}
		if ($font_size < $range[0])
		{
			$index = min(-4, $names_to_index[$font_map[$range[0]]]);
		}
		
		$font_size_name = $font_size_names[$index];
		
	}
	
	return $font_size_name;
}

?>
