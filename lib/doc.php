<?php

error_reporting(E_ALL);

ini_set('memory_limit', '-1');

require_once(dirname(__FILE__) . '/block.php');
require_once(dirname(__FILE__) . '/font.php');
require_once(dirname(__FILE__) . '/spatial.php');
require_once(dirname(__FILE__) . '/utils.php');

require_once(dirname(__FILE__) . '/dict.php');

// Load dictionary

$dict_filename = dirname(__FILE__) . '/parsCitDict.txt';

$dict = load_dictionary($dict_filename);


//----------------------------------------------------------------------------------------
// features

// global flags for features

$use_dictionary = false;
$use_grid = true;

$feature_templates = array();

//----------------------------------------------------------------------------------------
// grid (experimental)

// two lines above and below
$feature_templates['x'] =
'UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]';

$feature_templates['y'] =
'UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]';

$feature_templates['w'] =
'UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]';

$feature_templates['h'] =
'UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]';


//----------------------------------------------------------------------------------------

$feature_templates['repetitivePattern'] = 	
'UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]
UCOUNT:%x[3,FID]';

$feature_templates['string'] = 	
'UCOUNT:%x[-4,FID]
UCOUNT:%x[-3,FID]
UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]
UCOUNT:%x[3,FID]
UCOUNT:%x[4,FID]
UCOUNT:%x[5,FID]';

$feature_templates['secondString'] = 	
'UCOUNT:%x[-4,FID]
UCOUNT:%x[-3,FID]
UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]
UCOUNT:%x[3,FID]
UCOUNT:%x[4,FID]';

$feature_templates['lowercase'] = 	
'UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]';

// just look at this token
$feature_templates['prefix1'] = 'UCOUNT:%x[0,FID]';
$feature_templates['prefix2'] = 'UCOUNT:%x[0,FID]';
$feature_templates['prefix3'] = 'UCOUNT:%x[0,FID]';
$feature_templates['prefix4'] = 'UCOUNT:%x[0,FID]';

// before and next
$feature_templates['blockStatus'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

// before and next
$feature_templates['blockAlignSelf'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['blockJustifySelf'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['blockImageAdjacent'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

// before and next
$feature_templates['pageStatus'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['punctuationProfile'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['punctuationProfileLength'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

// two lines above and below
$feature_templates['possiblePage'] =
'UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]';

// two lines above and below
$feature_templates['possiblePlates'] =
'UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]';

// simple presence/absence
$feature_templates['email'] = 'UCOUNT:%x[0,FID]';
$feature_templates['http'] = 'UCOUNT:%x[0,FID]';
$feature_templates['urn'] = 'UCOUNT:%x[0,FID]';
$feature_templates['doi'] = 'UCOUNT:%x[0,FID]';
$feature_templates['orcid'] = 'UCOUNT:%x[0,FID]';

// two lines above and below
$feature_templates['year'] = 
'UCOUNT:%x[-2,FID]
UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]
UCOUNT:%x[2,FID]';

$feature_templates['capitalisation'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['digit'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['singleChar'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['lineWidth'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['alignmentStatus'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['fontSize'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['lineEndDigit'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

// gazetter -----------------------------------
$feature_templates['publisherName'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['placeName'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['monthName'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['lastName'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['femaleName'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['maleName'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

//----------------------------------------------------------------------------------------
// Domain specific

$feature_templates['nomenclature'] = 'UCOUNT:%x[0,FID]';

$feature_templates['latlon'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['date'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

$feature_templates['acronym'] =
'UCOUNT:%x[-1,FID]
UCOUNT:%x[0,FID]
UCOUNT:%x[1,FID]';

//----------------------------------------------------------------------------------------
// empty because this is where either training label goes, or where we output prediction
$feature_templates['label'] = "";

//----------------------------------------------------------------------------------------
function create_template($feature_keys)
{
	global $feature_templates;
	
	$template_text = '';
	
	$pattern_count = 0;
	$feature_count = 0;
		
	foreach ($feature_keys as $key)
	{
		$template_text .= "# $key\n";
	
		$text = $feature_templates[$key];
		
		$rows = explode("\n", $text);
		
		//print_r($rows);
		
		foreach ($rows as &$row)
		{
			// id of feature is order it appears in list of features
			$row = str_replace('FID', $feature_count, $row);
			
			// id of row is sequential
			$pattern_hex = strtoupper(sprintf('U%02x', dechex($pattern_count)));
						
			$row = str_replace('UCOUNT', $pattern_hex, $row);
						
			$pattern_count++;
		
		}
		//print_r($rows);
		
		$template_text .= join("\n", $rows) . "\n\n";		
		
		$feature_count++;
	}

	return $template_text;
}


//----------------------------------------------------------------------------------------
// Get lines of text in a given block (terribly inefficient)
function get_lines_in_block($page, $block_id)
{
	$lines = array();

	// get lines of text in block (super inefficient)
	foreach ($page->words as $word_id => $word)
	{
		$line_id = $page->line_ids[$word_id];
		if ($page->block_ids[$word_id] == $block_id)
		{
			if (!isset($lines[$line_id]))
			{
				$lines[$line_id] = array();
			}
			$lines[$line_id][] = $word;
		}
	}
	
	return array_values($lines);
}



//----------------------------------------------------------------------------------------
// Bounding rect of blocks on page
function page_bounding_rect($page)
{
	$content_box = new BBox();		
		
	foreach ($page->blocks as $block_id => $block)
	{
		$block_bbox = new BBox(
			$block->bbox[0],
			$block->bbox[1],
			$block->bbox[2],
			$block->bbox[3],
			);
			
		$content_box->merge($block_bbox);
	}
	$bounding_rect = $content_box->toRectangle();
	
	return $bounding_rect;
}

//----------------------------------------------------------------------------------------
// Get list of blocks on the page
function page_block_rects($page)
{
	$block_rects = array();		
	$rect_counter = 0;	
		
	foreach ($page->blocks as $block_id => $block)
	{
		$block_bbox = new BBox(
			$block->bbox[0],
			$block->bbox[1],
			$block->bbox[2],
			$block->bbox[3],
			);
		
		$rect = $block_bbox->toRectangle();
		$block_rects[$block_id] = $rect;
	}
	
	return $block_rects;
}



//----------------------------------------------------------------------------------------



/*

words
bbox
block_ids
line_ids
labels
blocks
width
height

*/

//----------------------------------------------------------------------------------------
// Load document from token (and possibly label) files
function doc_load($basedir)
{
	$doc = new stdclass;
	$doc->pages = array();
	
	$files = scandir($basedir);
	
	foreach ($files as $filename)
	{
		if (preg_match('/tokens(?<pagenum>\d+)\.json/', $filename, $m))
		{
			$pagenum = $m['pagenum'];
			
			$tokens_filename = $basedir . '/' . $filename;	
			$tokens_json = file_get_contents($tokens_filename);		
			
			$page = json_decode($tokens_json);
			
			// Do we have labels (e.g., from a model or as training data)
			$label_filename = 'labels' . $pagenum . '.json';
			
			if (file_exists($basedir . '/' . $label_filename))
			{				
				$labels_json = file_get_contents($basedir . '/' . $label_filename);		
				$page->labels = json_decode($labels_json);
			}
			
			$pagenum = preg_replace('/^0+/', '', $pagenum);
			
			$doc->pages[$pagenum] = $page;

		}	
	}
	
	return $doc;
}

//----------------------------------------------------------------------------------------
// Get information on font sizes across document, based on size of word bouding boxes. 
// Group these into clusters and name using CSS terms. Get "modal" font size for document,
// which helps us when computing block overlap, etc.
function doc_fonts(&$doc, $debug = false)
{
	$sizes = array();
	
	foreach ($doc->pages as $page)
	{
		// Get information on font sizes			
		$block_token_heights = array();
		foreach ($page->bbox as $word_id => $bbox)
		{
			$block_id = $page->block_ids[$word_id];
			
			if (!isset($block_token_heights[$block_id]))
			{
				$block_token_heights[$block_id] = array();
			}
			
			$block_token_heights[$block_id][] = $bbox[3] - $bbox[1];
			
			// We treat sizes as integers
			$rounded = round($bbox[3] - $bbox[1]);
			if (!isset($sizes[$rounded]))
			{
				$sizes[$rounded] = 0;
			}
			$sizes[$rounded]++;
		}


	}	
	
	if ($debug)
	{
		print_r($sizes);
	}
	
	// Document-level modal font size
	$doc->modal_font_size = 0;
	$max_count = 0;
	foreach ($sizes as $k => $v)
	{
		if ($v > $max_count)
		{
			$doc->modal_font_size = $k;
			$max_count = $v;
		}
	}

	// Convert font sizes to CSS names
	$doc->font_map = cluster_sizes($sizes, 7);

	if ($debug)
	{
		print_r($doc->font_map);
	}

}

//----------------------------------------------------------------------------------------
// Locate potential headers and footers by looking for repeteive blocks at the top and 
// bottom of each page.
function doc_decorations(&$doc, $debug = false)
{
	// array of pages, each listing the block_ids of those blocks we think are decorations
	$doc->decoration_blocks = array();
	
	$decorations = array('header', 'footer');	
	$header_candidates = array();
	$footer_candidates = array();
	
	foreach ($doc->pages as $page_num => $page)
	{
		$bounding_rect = page_bounding_rect($page);
		
		foreach ($decorations as $decoration_type)
		{
			if ($decoration_type == 'header')
			{
				$decoration_rect = new Rectangle($bounding_rect->x, $bounding_rect->y, $bounding_rect->w, $doc->modal_font_size);
			}
			else
			{
				$decoration_rect = new Rectangle($bounding_rect->x, $bounding_rect->y + $bounding_rect->h - $doc->modal_font_size, $bounding_rect->w, $doc->modal_font_size);
			}
		
			$candidate = null;
		
			foreach ($page->blocks as $block_id => $block)
			{
				$block_bbox = new BBox(
					$block->bbox[0],
					$block->bbox[1],
					$block->bbox[2],
					$block->bbox[3],
					);
		
				$rect = $block_bbox->toRectangle();
			
				$overlap = $decoration_rect->getOverlap($rect);
			
				if ($overlap)
				{
					if (!$candidate)
					{
						$candidate = new stdclass;
						$candidate->text = array();
						$candidate->blocks = array();
						$candidate->rect = $overlap;				
					}
					else
					{
						$candidate->rect->merge($overlap);
					}
					
					$text = get_lines_in_block($page, $block_id);
					
					$n = count($text);
					if ($n > 0)
					{
						if ($decoration_type == 'header')
						{
							// first line of block
							$candidate->text = array_merge($candidate->text, $text[0]);
						}
						else
						{
							// last line of block
							$candidate->text = array_merge($candidate->text, $text[$n - 1]);
						
						}					
					}

					// blocks
					$candidate->blocks[] = $block_id;
				}
			
			}
		
			if ($candidate)
			{
				if ($decoration_type == 'header')
				{
					$header_candidates[$page_num] = $candidate;
				}
				else
				{
					$footer_candidates[$page_num] = $candidate;				
				}
			
			}			
		
		}
		
	}
	
	if ($debug)
	{
		print_r($header_candidates);
		print_r($footer_candidates);
	}	
	
	$page_window = 3;
	$threshold = 0.5;

	$doc->decoration_blocks = array(); 
	
	for ($j = 0; $j < $page_num; $j++)
	{
		$win_start = max(0, $j - $page_window);
		$win_end = min($j + $page_window, $page_num - 1);
	
		$doc->decoration_blocks[$j] = array();
	
		foreach ($decorations as $decoration_type)
		{
			$candidates = null;
		
			if ($decoration_type == 'header')
			{
				$candidates = $header_candidates;
			}
			else
			{
				$candidates = $footer_candidates;		
			}
	
			if (isset($candidates[$j]))
			{
				$max_score = 0;
				for ($k = $win_start; $k < $win_end; $k++)
				{			
					if (($k != $j) && isset($candidates[$k]))
					{
						$max_score = 0;
						// only compare if we have text in both
						if (count($candidates[$j]->text) > 0 && count($candidates[$k]->text) > 0)
						{
				
							//echo "[$k $j]\n";
							$score = base_sim(join(' ', $candidates[$j]->text), join(' ', $candidates[$k]->text))
								 * geom_sim($candidates[$j]->rect, $candidates[$k]->rect)
								;

							$max_score = max($score, $max_score);
						}			
					}	
			
					//echo "max score=$max_score\n\n";	
			
					if ($max_score > $threshold)
					{
						foreach ($candidates[$j]->blocks as $block_id)
						{
							if (!in_array($block_id, $doc->decoration_blocks[$j]))
							{
								$doc->decoration_blocks[$j][] = $block_id;
							}
						}
					}
			
				}
			}
		}

	}	
	
	if ($debug)
	{
		print_r($doc->decoration_blocks);
	}	

}

//----------------------------------------------------------------------------------------
// Process pages
function doc_do_pages(&$doc, $output_labels = false, $debug = false)
{
	global $dict;
	global $use_dictionary;
	global $use_grid;
	
	$template = '';
	
	$crf_data = '';
	
	$doc->feature_row_to_page = array();
	$doc->feature_row_to_words = array();
	
	// Get basic style for each block based on position w.r.t. bounding rectangle
	// to do: handle multiple columns	
	$slop = $doc->modal_font_size; // allow for some margin of error	
	
	
	$doc_columns = 1; // defauult
	
	$number_two_columns = 0;
	
	//  does document have two columns?
	foreach ($doc->pages as $page)
	{
		$bounding_rect = page_bounding_rect($page);
		$block_rects   = page_block_rects($page);
		$vertical = vertical_fold($bounding_rect, $slop);
		
		$total_area = 0;
		$column_area = 0;
		foreach ($block_rects as $block_id => $r)
		{		
			$total_area += $r->getArea();
			
			if (!$r->intersectsRect($vertical))
			{
				$column_area += $r->getArea();
			}
		}
		
		if ($column_area / $total_area > 0.5)
		{
			$number_two_columns++;
		}

	}	
	
	if ($number_two_columns / count($doc->pages) > 0.5)
	{
		$doc_columns = 2;
	}
	
	$region_rects = array();
	
	foreach ($doc->pages as $page_num => $page)
	{
		$block_features = array();
		
		$bounding_rect = page_bounding_rect($page);
		$block_rects   = page_block_rects($page);
		
		$page_top_rect = new Rectangle($bounding_rect->x, $bounding_rect->y, $bounding_rect->w, $doc->modal_font_size);							
		$page_bottom_rect = new Rectangle($bounding_rect->x, $bounding_rect->y + $bounding_rect->h - $doc->modal_font_size, $bounding_rect->w, $doc->modal_font_size);		


		// for single column docments all blocks have same parent,
		// but if two columns we have three parents (whole page, and left and right columns)		
		$block_parent = array();
		
		// default parent is 0 (the page)
		foreach ($block_rects as $block_id => $r)
		{		
			$block_parent[$block_id] = 0;
		}
		$region_rects[0] = $bounding_rect;	
		
		// this is where we figure out if we have two colums
		if ($doc_columns == 2)
		{
			$vertical = vertical_fold($bounding_rect, $slop);	

			foreach ($block_rects as $block_id => $r)
			{	
				if ($r->intersectsRect($vertical))
				{
					$block_parent[$block_id] = 0;
				}
				else
				{
					if ($r->x + $r->w < $vertical->x)
					{
						$block_parent[$block_id] = 1;
					}
					else
					{
						$block_parent[$block_id] = 2;
					}
				}
			}
			
			foreach ($block_rects as $block_id => $r)
			{	
				if (!isset($region_rects[$block_parent[$block_id]]))
				{
					$region_rects[$block_parent[$block_id]] = $r;
				}
				else
				{
					$region_rects[$block_parent[$block_id]]->merge($r);
				}			
			}
		}		
		
		foreach ($block_rects as $block_id => $r)
		{		
			// block-level features			
			$block_features[$block_id] = array();
			
			// defaults
			$block_features[$block_id]['repetitivePattern'] = 0;
			
			// get alignment of block w.r.t. to enclsoing rect 						
			$br = $region_rects[$block_parent[$block_id]];
			
			$grid = grid($br, $r, $slop);
			
			if ($debug)
			{
				echo "\nGrid alignment\n";
				print_r($grid);
			}
						
			// block alignment
			$justifyself = 'NORMAL';
			
			if ($grid['centered'])
			{
				$justifyself = 'CENTER';
			
				if ($grid['left'] && $grid['right'])
				{
					$justifyself = 'STRETCH';
				}
			}
			else
			{
				if ($grid['left'])
				{
					$justifyself = 'LEFT';
				}
				elseif ($grid['right'])
				{
					$justifyself = 'RIGHT';
				}				
			}
			$block_features[$block_id]['blockJustifySelf'] = $justifyself;			
			
			// block position w.r.t. page
			$block_features[$block_id]['blockAlignSelf'] = 'NORMAL';
			
			$overlap = $page_top_rect->getOverlap($block_rects[$block_id]);				
			if ($overlap)
			{
				$block_features[$block_id]['blockAlignSelf'] = 'START';
			}
			$overlap = $page_bottom_rect->getOverlap($block_rects[$block_id]);				
			if ($overlap)
			{
				$block_features[$block_id]['blockAlignSelf'] = 'END';
			}						
			
		}	
		
		// repetitive header/footers
		if (isset($doc->decoration_blocks[$page_num]))
		{
			foreach ($doc->decoration_blocks[$page_num] as $decoration_block_id)
			{
				$block_features[$decoration_block_id]['repetitivePattern'] = 1;
			}
		}
			
		if ($debug)
		{
			print_r($block_features);
		}
				
		//--------------------------------------------------------------------------------
		// relationship between text and image blocks
		// by default we just look for text that is below an image
		foreach ($page->blocks as $block_id => $block)
		{
			// clear flag by default
			if (!isset($block_features[$block_id]['blockImageAdjacent']))
			{
				$block_features[$block_id]['blockImageAdjacent'] = 0;
			}
		
			// if block is an image we look for nearest text block below image
			if ($block->type == 'image')
			{
				$min_distance = $page->width; // maximum distance (should really be page diagonal)
				$closest_id = $block_id; // nearest block is itself
				
				$image_rect = $block_rects[$block_id];
								
				foreach ($page->blocks as $other_id => $other_block)
				{
					if ($block_id != $other_id)
					{
						if (isBelow($image_rect, $block_rects[$other_id]))
						{
							$d = centroid_distance($image_rect, $block_rects[$other_id]);
							
							if ($d < $min_distance)
							{
								$min_distance = $d;
								$closest_id = $other_id;
							}
						}					
					}
				}
				
				// do we have a block below the image rect?
				if ($closest_id != $block_id)
				{
					$block_features[$closest_id]['blockImageAdjacent'] = 1;
				}			
			
				/*
				// inflate
				$image_rect = $block_rects[$block_id];
				
				$image_rect->h += 1.5 *$doc->modal_font_size;
				
				// does it overlap with anything?
				foreach ($page->blocks as $other_id => $other_block)
				{
					if ($block_id != $other_id)
					{
						if ($block_rects[$other_id]->getOverlap($image_rect))
						{
							$block_features[$other_id]['blockImageAdjacent'] = 1;
						}
					}
				}
				*/
			}
		}
		
		//--------------------------------------------------------------------------------
		// Get number of lines in each block		
		
		$block_line_count = array();
		$block_lines = array();
		
		$lines_words = array();
		
		// For each token get line_id, get block_id for token, and add line_id 
		// to list for that block. If more than one token per line (i.e., most cases) we
		// will need to filter out duplicates (see below)
		foreach ($page->line_ids as $word_id => $line_id)
		{
			$block_id = $page->block_ids[$word_id];
			$block_lines[$block_id][] = $line_id;
			
			if (!isset($lines_words[$line_id]))
			{
				$lines_words[$line_id] = array();
			}
			$lines_words[$line_id][] = $word_id;
		}
		
		// Collapse into unique values for line_ids, then count. This is the 
		// number of lines in each block.
		foreach ($block_lines as $block_id => $lines)
		{
			$lines = array_values(array_unique($lines));
			$block_lines[$block_id] = $lines;
			$block_line_count[$block_id] = count($lines);
		}
		
		if ($debug)
		{
			echo "\nNumbers of lines per block\n";
			print_r($block_line_count);
		
			echo "\nBlocks and their lines\n";		
			print_r($block_lines);
		}
		
		
		//--------------------------------------------------------------------------------
		// get modal font size in block, just use first word in line (assume we have 
		// enforced the same height across tokens in line)
		foreach ($block_lines as $block_id => $lines)
		{
			$sizes = array();
			foreach ($lines as $line_id)
			{
				// Get first word in block
				$word_id = $lines_words[$line_id][0];
				
				// We treat sizes as integers
				$rounded = round($page->bbox[$word_id][3] - $page->bbox[$word_id][1]);
				if (!isset($sizes[$rounded]))
				{
					$sizes[$rounded] = 0;
				}
				$sizes[$rounded]++;
			}			
			
			$block_modal_font_size = 0;
			$max_count = 0;
			foreach ($sizes as $k => $v)
			{
				if ($v > $max_count)
				{
					$block_modal_font_size = $k;
					$max_count = $v;
				}
			}			
			
			if ($debug)
			{
				echo "Block modal font size=" . $block_modal_font_size . " " . font_classify($doc->font_map, $block_modal_font_size) . "\n";
			}
			
			$fontSize  = font_classify($doc->font_map, $block_modal_font_size);
			$block_features[$block_id]['fontSize'] = $fontSize;
		}
		
		//--------------------------------------------------------------------------------
		// text alignment within block, e.g. indents
		// only look at left margin
		$line_indents = array();
		foreach ($block_lines as $block_id => $lines)
		{
			foreach ($lines as $line_id)
			{
				// Get first word
				$word_id = $lines_words[$line_id][0];
				
				// Only want meaningful indents, i.e. at least a character's width
				$char_width = $doc->modal_font_size * 0.5;
					
				$indent = max(0, $page->bbox[$word_id][0] - $block_rects[$block_id]->x - $char_width);

				// yes/no flag
				if ($indent > 0)
				{
					$indent = 1; 
				}
				$line_indents[$line_id] = $indent;
			}			
		}		
		
		//--------------------------------------------------------------------------------
		// how wide is line relative to block?
		$line_widths = array();
		foreach ($block_lines as $block_id => $lines)
		{
			foreach ($lines as $line_id)
			{
				$minx = $block_rects[$block_id]->x + $block_rects[$block_id]->w;
				$maxx = 0;
				
				foreach ($lines_words[$line_id] as $word_id)
				{
					$minx = min($minx, $page->bbox[$word_id][0]);
					$maxx = max($maxx, $page->bbox[$word_id][2]);
				}	
				
				$line_widths[$line_id] = round(100 * ($maxx - $minx) / $block_rects[$block_id]->w, 0);
			}
			
			if ($block_rects[$block_id]->w == 0)
			{
				echo "Bad block, page $page_num, block $block_id\n";				
				print_r($block_rects[$block_id]);
			}
		}	
		
		//--------------------------------------------------------------------------------
		// how much of line is occupied by tokens?
		// sparse lines (widely separated tokens) would be typical of tables, for example
		$line_density = array();
		foreach ($block_lines as $block_id => $lines)
		{
			foreach ($lines as $line_id)
			{
				$line_area = 0;
				$token_area = 0;
			
				$minx = $block_rects[$block_id]->x + $block_rects[$block_id]->w;
				$maxx = 0;
				
				$miny = $block_rects[$block_id]->y + $block_rects[$block_id]->h;
				$maxy = 0;
				
				foreach ($lines_words[$line_id] as $word_id)
				{
					// we want to get maximum extent of line
					$minx = min($minx, $page->bbox[$word_id][0]);
					$maxx = max($maxx, $page->bbox[$word_id][2]);
	
					$miny = min($miny, $page->bbox[$word_id][1]);
					$maxy = max($maxy, $page->bbox[$word_id][3]);
					
					// get area of token
					$token_area += ($page->bbox[$word_id][2] - $page->bbox[$word_id][0]) 
						* ($page->bbox[$word_id][3] - $page->bbox[$word_id][1]);
				}	
				
				$line_area = ($maxx - $minx) * ($maxy - $miny);
				
				$line_density[$line_id] = round(100 * $token_area / $line_area, 0);
			}
		}
		
		//--------------------------------------------------------------------------------
		// Are all lines within a block centered w.r.t. that block?
		// combine this with block's alignment (CENTER OR JUSTIFY) to determne whether
		// a text element is centred 

		$block_lines_centred = array();
		$line_rects = array();
		
		//echo "\nPage $page_num\n";
		
		foreach ($block_lines as $block_id => $lines)
		{
			//echo "Block $block_id\n";
			
			$block_lines_centred[$block_id] = true;
		
			$enclosing_rect = $block_rects[$block_id];
			
			foreach ($lines as $line_id)
			{
				$line_rect = null;
				
				foreach ($lines_words[$line_id] as $word_id)
				{
					//echo $page->words[$word_id] . ' ';
				
					$token_rect = new Rectangle(
						$page->bbox[$word_id][0],
						$page->bbox[$word_id][1],
						$page->bbox[$word_id][2] - $page->bbox[$word_id][0],
						$page->bbox[$word_id][3] - $page->bbox[$word_id][1],						
						);
						
					if ($line_rect)
					{
						$line_rect->merge($token_rect);
					}
					else
					{
						$line_rect = $token_rect;
					}
				}
				
				//echo "\n";
				
				// test
				if (is_centred_within($enclosing_rect, $line_rect, $doc->modal_font_size))
				{
					//echo join(",", $enclosing_rect->toArray()) . "\n";
					//echo join(",", $line_rect->toArray()) . "\n";				
					//echo "$line_id centerd\n\n";
				}
				else
				{
					$block_lines_centred[$block_id] = false;
				}
				
				$line_rects[$line_id] = $line_rect;								
			}
			
			if ($debug && $block_lines_centred[$block_id])
			{
				echo "Text in block $block_id is centred\n";
			}
		}	
		
		
					
		
		//--------------------------------------------------------------------------------
		// generate features
		
		$page_start = true;
		
		$line_features = array();
		
		foreach ($block_lines as $block_id => $lines)
		{
			$block_start = true;
			
			foreach ($lines as $line_id)
			{
				//------------------------------------------------------------------------
				// keep track of relationshp between pages and tokens so we can 
				// figure out how to apply labels
				
				$n = count($doc->feature_row_to_page);
				$doc->feature_row_to_page[$n] = $page_num;
				
				$doc->feature_row_to_words[$n] = array();		
			
				//------------------------------------------------------------------------
				// get line of text
				$words = array();
				$word_ids = array();
				$line_text = '';				
				foreach ($lines_words[$line_id] as $word_id)
				{
					$words[] = $page->words[$word_id];

					$doc->feature_row_to_words[$n][] = $word_id;
				}
				$line_text = join(' ', $words);
			
				// Initialise list of line features
				$line_features[$line_id] = array();				
			
				//------------------------------------------------------------------------
				// Features of first two words in the line
				// first token
				
				$word_zero_id = $lines_words[$line_id][0];
				
				$string = $page->words[$word_zero_id];				
				$line_features[$line_id]['string'] = $string;

				// second token (if we have one)
				$secondString = $string;				
				if (count($lines_words[$line_id]) > 1)
				{
					$secondString = $page->words[$lines_words[$line_id][1]];
				}
				$line_features[$line_id]['secondString'] = $secondString;
				
				// cleaned first token
				$wordNP = $string;
				$wordNP = preg_replace('/[^\\p{L}|\d]/u', '', $wordNP);
				if (preg_match('/^\s*$/u', $wordNP))
				{
					$wordNP = "EMPTY";
				}								
				// lowercase
				$wordLCNP = mb_strtolower($wordNP);
				$line_features[$line_id]['lowercase'] = $wordLCNP;
					
				// atomise the first token so we can extract characters			
				$chars = mb_str_split($string);
			
				$line_features[$line_id]['prefix1']  = $chars[0]; // 2 = first char
				$line_features[$line_id]['prefix2']  = join("", array_slice($chars, 0, 2)); // 3 = first 2 chars
				$line_features[$line_id]['prefix3']  = join("", array_slice($chars, 0, 3)); // 4 = first 3 chars
				$line_features[$line_id]['prefix4']  = join("", array_slice($chars, 0, 4)); // 5 = first 4 chars
				
				// capitalisation
				$ortho = 'NOCAPS';
				if (preg_match('/^\p{Lu}+$/u', $wordNP))
				{
					$ortho = "ALLCAP";
				} 
				else if (preg_match('/^\p{Lu}\p{L}+/u', $wordNP))
				{
					$ortho = "INITCAP";
				}								
				$line_features[$line_id]['capitalisation'] = $ortho;
				
				// digit
				$ortho = 'NODIGIT';
				if (preg_match('/^[0-9]+$/u', $wordNP))
				{
					$ortho = "ALLDIGIT";
				} 
				else if (preg_match('/[0-9]/u', $wordNP))
				{
					$ortho = "CONTAINDIGIT";
				}								
				$line_features[$line_id]['digit'] = $ortho;
				
				// singleChar
				$line_features[$line_id]['singleChar'] = 0;
				if (mb_strlen($string) == 1)
				{
					$line_features[$line_id]['singleChar'] = 1;
				}
				
				//------------------------------------------------------------------------
				// to do: grid coordinates of line
				if ($use_grid)
				{
					// normalise to 0-100
					$scale_x = 100/$page->width;
					$scale_y = 100/$page->height;
					
					$line_features[$line_id]['x'] = round($line_rects[$line_id]->x * $scale_x, 0);
					$line_features[$line_id]['y'] = round($line_rects[$line_id]->y * $scale_y, 0);
					$line_features[$line_id]['w'] = round($line_rects[$line_id]->w * $scale_x, 0);
					$line_features[$line_id]['h'] = round($line_rects[$line_id]->h * $scale_y, 0);				
				}

				//------------------------------------------------------------------------
				// features of text on lines (may need to move between word and line 
				// while we figure this out)
				
				// email
				$line_features[$line_id]['email'] = 0;
				if (preg_match("/[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+/", $line_text))
				{
					$line_features[$line_id]['email'] = 1;
				}
	
				// URL
				$line_features[$line_id]['http'] = 0;
				if (preg_match("/https?:\/\//", $line_text))
				{
					$line_features[$line_id]['http'] = 1;
				}
				
				// URN
				$line_features[$line_id]['urn'] = 0;
				if (preg_match("/urn:/", $line_text))
				{
					$line_features[$line_id]['urn'] = 1;
				}				
				
				// DOI
				$line_features[$line_id]['doi'] = 0;
				if (preg_match("/(doi.org|doi:)/", $line_text))
				{
					$line_features[$line_id]['doi'] = 1;
				}
				
				// ORCID
				$line_features[$line_id]['orcid'] = 0;
				if (preg_match("/(orcid.org)/", $line_text))
				{
					$line_features[$line_id]['orcid'] = 1;
				}				
				
				// year
				$line_features[$line_id]['year'] = 0;
				if (preg_match("/(17|18|19|20)[0-9][0-9]/", $line_text))
				{
					$line_features[$line_id]['year'] = 1;
				}		
				
				//------------------------------------------------------------------------
				// defintely a line feature	
				
				// punctuation
				$punctuationProfile = preg_replace('/[\w\s]/u', '', $line_text);				
				if ($punctuationProfile != '')
				{				
					$line_features[$line_id]['punctuationProfile'] = $punctuationProfile;
					$line_features[$line_id]['punctuationProfileLength'] = mb_strlen($punctuationProfile);
				}
				else
				{
					$line_features[$line_id]['punctuationProfile'] = 'no';
					$line_features[$line_id]['punctuationProfileLength'] = 0;
				}	
				
				// page numbers (e.g., in a citation)
				if (preg_match('/([0-9]\s*[\-|—|–]\s*[0-9])|(\d+\s*pp\.)/u', $line_text))
				{
					$line_features[$line_id]['possiblePage'] = 'PAGES';
				}
				else
				{
					$line_features[$line_id]['possiblePage'] = 'no';				
				}
				
				// plates
				if (preg_match('/(pls?\.)/iu', $line_text))
				{
					$line_features[$line_id]['possiblePlates'] = 'PLATES';
				}
				else
				{
					$line_features[$line_id]['possiblePlates'] = 'no';				
				}
								
				//------------------------------------------------------------------------
				// domain specific	
								
				// line ends in a number (e.g., keys)
				$line_features[$line_id]['lineEndDigit'] = 0;
				if (preg_match('/\d+$/', $line_text))
				{
					$line_features[$line_id]['lineEndDigit'] = 1;
				}
				
				// latitude longitude
				// here we want just to detect possible latitude and/or longitudes,
				// not parse them, hence we just try and match one hemisphere, not the
				// complete lat/lon pair
								
				$line_features[$line_id]['latlon'] = 'no';

				// N11°23'59"E76°44'06" 
				// N11°23'59"
				// 28°13’49”S
				// 17°23’S
				// 17°45'46"N
				// 17°59'06.60"N
				// 10°12′1′′S
				if (preg_match('/[N|S|W|E]?\d+[°|º]\d+[\'|’|′](\d+(\.\d+)?("|”|′′|’’))?[N|S|W|E]?/u', $line_text))
				{
					$line_features[$line_id]['latlon'] = 'LATLON';
				}
				
				// S15.97188°
				if (preg_match('/[N|S|W|E]\d+\.\d+°/u', $line_text))
				{
					$line_features[$line_id]['latlon'] = 'LATLON';
				}

				// 57o29.00'W				
				if (preg_match('/\d+o\d+(\.\d+)?\'[N|S|W|E]/u', $line_text))
				{
					$line_features[$line_id]['latlon'] = 'LATLON';
				}

				// 9∞ 11.1'S
				// 139°13.8'E
				if (preg_match('/\d+[°|∞]\s*\d+(\.\d+)?\'[N|S|W|E]/u', $line_text))
				{
					$line_features[$line_id]['latlon'] = 'LATLON';
				}				
				
				/*
				$line_features[$line_id]['date'] = 'no';

				if (preg_match('/\d{1,2}[\.|-|-|\s]\s*[ivx]+[\.|-|-|\s]\s*[0-9]{4}/iu', $line_text))
				{
					$line_features[$line_id]['date'] = 'DATE';
				}

				if (preg_match('/\d+\s+[A-Z][a-z]{2}\.?[a-z]*\s+[0-9]{4}/u', $line_text))
				{
					$line_features[$line_id]['date'] = 'DATE';
				}	
				
				// Received: 19.11.2018
				if (preg_match('/\d{1,2}\.\d{1,2}\.[1|2][0-9]{3}/iu', $line_text))
				{
					$line_features[$line_id]['date'] = 'DATE';
				}
				
				*/			
				
				// acronyms (e.g., museum codes)
				$line_features[$line_id]['acronym'] = 0;
				
				if (preg_match('/[a-z]/u', $line_text))
				{
					// line is mixed case (we want to avoid matching things such as 
					// section headings that might be all caps)
					if (preg_match('/[A-Z]{3,}/', $line_text))
					{
						$line_features[$line_id]['acronym'] = 'ACRONYM';
					}
				}

				// nomenclature annotations (where can we get a list)?
				$line_features[$line_id]['nomenclature'] = 'no';	
				if (preg_match('/(new\s+(combination|family|genus|species))|((comb|fam|gen|sp|spec)\.\s+n(ov)?\.)/i', $line_text))
				{
					$line_features[$line_id]['nomenclature'] = 'NOMEN';
				}

				//------------------------------------------------------------------------
				// gazetter (try all tokens in line)
				// may need to be cleverer about this...
				
				$dictStatus = 0;
				foreach ($words as $string)
				{
					$wordNP = $string;
					$wordNP = preg_replace('/[^\\p{L}|\d]/u', '', $wordNP);
					$wordLCNP = mb_strtolower($wordNP);
					
					if (isset($dict[$wordLCNP]))
					{
						$dictStatus |= $dict[$wordLCNP];
					}					
				}
				
				$isInDict = $dictStatus;
				
				if ($dictStatus & 32) { $dictStatus ^ 32; $publisherName = "publisherName"; } else { $publisherName = "no"; }
				if ($dictStatus & 16) { $dictStatus ^ 16; $placeName = "placeName"; } else { $placeName = "no"; }
				if ($dictStatus & 8) { $dictStatus ^ 8; $monthName = "monthName"; } else { $monthName = "no"; }
				if ($dictStatus & 4) { $dictStatus ^ 4; $lastName = "lastName"; } else { $lastName = "no"; }
				if ($dictStatus & 2) { $dictStatus ^ 2; $femaleName = "femaleName"; } else { $femaleName = "no"; }
				if ($dictStatus & 1) { $dictStatus ^ 1; $maleName = "maleName"; } else { $maleName = "no"; }
				
				if ($use_dictionary)
				{
					$line_features[$line_id]['publisherName'] 	= $publisherName;	// seems garbarge
					$line_features[$line_id]['placeName'] 		= $placeName;	
					$line_features[$line_id]['monthName'] 		= $monthName;	
					$line_features[$line_id]['lastName'] 		= $lastName;	
					$line_features[$line_id]['femaleName'] 		= $femaleName;	
					$line_features[$line_id]['maleName'] 		= $maleName;	
				}

				//------------------------------------------------------------------------
				// structural features of the line
				
				// to do: line density
								
				// line width
				$line_features[$line_id]['lineWidth'] = $line_widths[$line_id];	
				
				// by default assume text is left-aligned, possibly with an indent				
				$line_features[$line_id]['alignmentStatus'] = 'ALIGNEDLEFT';			
				if ($line_indents[$line_id] == 1)
				{
					$line_features[$line_id]['alignmentStatus'] = 'INDENT';		
				}
				
				// if block line belongs to is centred or justified, check whether text 
				// is centred
				switch ($block_features[$block_id]['blockJustifySelf'])
				{
					case 'CENTER':
					case 'STRETCH':
						if ($block_lines_centred[$block_id])
						{
							$line_features[$line_id]['alignmentStatus'] = 'CENTER';
						}
						break;
					
					default:
						break;				
				}
												
				//------------------------------------------------------------------------
				// features of block line belongs too
				if ($block_start)
				{
					$line_features[$line_id]['blockStatus'] = 'BLOCKSTART';
				}
				else
				{
					$line_features[$line_id]['blockStatus'] = 'BLOCKIN';				
				}
				
				// Font size
				$fontSize = $block_features[$block_id]['fontSize'];
				$fontSize = strtoupper(str_replace('-', '', $fontSize));
				
				$line_features[$line_id]['fontSize'] = $fontSize;
										
				// Repetitive pattenr across pages (this is how we detect header/footers)
				$line_features[$line_id]['repetitivePattern'] = $block_features[$block_id]['repetitivePattern'];						
				
				// Position of block on page
				$line_features[$line_id]['blockAlignSelf'] = $block_features[$block_id]['blockAlignSelf'];
				$line_features[$line_id]['blockJustifySelf'] = $block_features[$block_id]['blockJustifySelf'];
				
				// Are we adjacent to an image?
				$line_features[$line_id]['blockImageAdjacent'] = $block_features[$block_id]['blockImageAdjacent'];

				//------------------------------------------------------------------------
				// features of page this line belongs too
				
				// this effectively assumes that the order of blocks matches reading order
				if ($page_start)
				{
					$line_features[$line_id]['pageStatus'] = 'PAGESTART';
				}
				else
				{
					$line_features[$line_id]['pageStatus'] = 'PAGEIN';				
				}
				
				//------------------------------------------------------------------------
				// labels (for training)
				if ($output_labels && isset($page->labels[$word_id]))
				{
					$line_features[$line_id]['label'] = $page->labels[$word_id];
				}
								
				$page_start = false;
				$block_start = false;
			}
			
			if ($line_features[$line_id]['blockStatus'] == 'BLOCKIN')
			{
				$line_features[$line_id]['blockStatus'] = 'BLOCKEND';
			}
		}
		
		if ($line_features[$line_id]['pageStatus'] == 'PAGEIN')
		{
			$line_features[$line_id]['pageStatus'] = 'PAGEEND';
		}

		foreach ($line_features as $line_id => $features)
		{	
			// Make sure we have a template file for CRF
			// We generate this from code to keep things consistent
						
			$template_filename = 'rod.template';
			if ($template == '' && !file_exists($template_filename))
			{
				$feature_keys = array_keys($features);
				$template_text = create_template($feature_keys);
				
				file_put_contents($template_filename, $template_text);
			}
			
			// output features for each line
			$crf_data .= join(' ', $features) . "\n";						
		}	
		
		$crf_data .=  "\n";	
		
	}

	return $crf_data;	
}



?>
