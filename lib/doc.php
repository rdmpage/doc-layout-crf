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

$feature_templates = array();

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

// simple presence/absence
$feature_templates['email'] = 'UCOUNT:%x[0,FID]';
$feature_templates['http'] = 'UCOUNT:%x[0,FID]';
$feature_templates['doi'] = 'UCOUNT:%x[0,FID]';

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

$feature_templates['nomenclature'] = 'UCOUNT:%x[0,FID]';

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
			
			$doc->pages[] = $page;

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
	$doc->font_map = cluster_sizes($sizes, 5);

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

	$page_counter = 0;
	
	$decorations = array('header', 'footer');	
	$header_candidates = array();
	$footer_candidates = array();
	
	foreach ($doc->pages as $page)
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
					$header_candidates[$page_counter] = $candidate;
				}
				else
				{
					$footer_candidates[$page_counter] = $candidate;				
				}
			
			}			
		
		}
		
		$page_counter++;
		
	}
	
	if ($debug)
	{
		print_r($header_candidates);
		print_r($footer_candidates);
	}	
	
	$page_window = 3;
	$threshold = 0.5;

	$doc->decoration_blocks = array(); 
	
	for ($j = 0; $j < $page_counter; $j++)
	{
		$win_start = max(0, $j - $page_window);
		$win_end = min($j + $page_window, $page_counter - 1);
	
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
					if (($k != $j) && $candidates[$k])
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
	
	$template = '';
	
	$crf_data = '';
	
	$doc->feature_row_to_page = array();
	$doc->feature_row_to_words = array();

	$page_counter = 0;
	
	foreach ($doc->pages as $page)
	{
		$block_features = array();
		
	
		$bounding_rect = page_bounding_rect($page);
		$block_rects   = page_block_rects($page);
		
		// Get basic style for each block based on position w.r.t. bounding rectangle
		// to do: handle multiple columns	
		$slop = $doc->modal_font_size; // allow for some margin of error	
			
		foreach ($block_rects as $block_id => $r)
		{		
			$grid = grid($bounding_rect, $r, $slop);
			
			if ($debug)
			{
				echo "\nGrid alignment\n";
				print_r($grid);
			}

			// block-level features			
			$block_features[$block_id] = array();
			
			// defaults
			$block_features[$block_id]['repetitivePattern'] = 0;
			
			/*
			$block_features[$block_id]['width'] = $grid['width'];
			$positions = array('left', 'top', 'bottom', 'right', 'centered');
			foreach ($positions as $pos)
			{
				$block_features[$block_id][$pos] = $grid[$pos];
			}
			*/
		}	
		
		foreach ($doc->decoration_blocks[$page_counter] as $decoration_block_id)
		{
			$block_features[$decoration_block_id]['repetitivePattern'] = 1;
		}
			
		if ($debug)
		{
			print_r($block_features);
		}
		
		//--------------------------------------------------------------------------------
		// Get number of lines in each block, also store text in each line		
		
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
		// enforce same height across tokens in line)
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
		}	
		
		//--------------------------------------------------------------------------------
		// how much of line is occupied by tokens?
		// sparse lines (widely separated tokens) would be typical of tabkles, for example
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
				$doc->feature_row_to_page[$n] = $page_counter;
				
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
			
				// Initialise line features
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
				
				// lowercase
				$wordLCNP = mb_strtolower($string);
				$line_features[$line_id]['lowercase'] = $wordLCNP;
					
				// atomise the first token				
				$chars = mb_str_split($string);
			
				$line_features[$line_id]['prefix1']  = $chars[0]; // 2 = first char
				$line_features[$line_id]['prefix2']  = join("", array_slice($chars, 0, 2)); // 3 = first 2 chars
				$line_features[$line_id]['prefix3']  = join("", array_slice($chars, 0, 3)); // 4 = first 3 chars
				$line_features[$line_id]['prefix4']  = join("", array_slice($chars, 0, 4)); // 5 = first 4 chars

				// first token
				$wordNP = $string;
				$wordNP = preg_replace('/[^\\p{L}|\d]/u', '', $wordNP);
				if (preg_match('/^\s*$/u', $wordNP))
				{
					$wordNP = "EMPTY";
				}
				
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
				
				// DOI
				$line_features[$line_id]['doi'] = 0;
				if (preg_match("/(doi.org|doi:)/", $line_text))
				{
					$line_features[$line_id]['doi'] = 1;
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
				
				// page numbers?
				if (preg_match('/([0-9]\s*[\-|—|–]\s*[0-9])|(\d+\s*pp\.)/u', $line_text))
				{
					$line_features[$line_id]['possiblePage'] = 1;
				}
				else
				{
					$line_features[$line_id]['possiblePage'] = 0;				
				}
				
				// line ends in a number
				$line_features[$line_id]['lineEndDigit'] = 0;
				if (preg_match('/\d+$/', $line_text))
				{
					$line_features[$line_id]['lineEndDigit'] = 1;
				}
				
				//------------------------------------------------------------------------
				// domain specific	
				
				
				// nomenclature annotations (where can we get a list)?
				if (preg_match('/(new\s+(combination|family|genus|species))|((comb|fam|gen|sp)\.\s+n(ov)?\.)/i', $line_text))
				{
					$line_features[$line_id]['nomenclature'] = 1;
				}
				else
				{
					$line_features[$line_id]['nomenclature'] = 0;				
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
				
				
				//$line_features[$line_id]['publisherName'] 	= $publisherName;	// seems garbarge
				//$line_features[$line_id]['placeName'] 		= $placeName;	
				//$line_features[$line_id]['monthName'] 		= $monthName;	
				//$line_features[$line_id]['lastName'] 		= $lastName;	
				//$line_features[$line_id]['femaleName'] 		= $femaleName;	
				//$line_features[$line_id]['maleName'] 		= $maleName;	


				//------------------------------------------------------------------------
				// structural features of the line
				
				// to do: line density
								
				// line width
				$line_features[$line_id]['lineWidth'] = $line_widths[$line_id];	
				
				// line indent (think about how to handle centred text)
				$line_features[$line_id]['alignmentStatus'] = 'ALIGNEDLEFT';			
				if ($line_indents[$line_id] == 1)
				{
					$line_features[$line_id]['alignmentStatus'] = 'INDENT';		
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
				
				$fontSize = $block_features[$block_id]['fontSize'];
				$fontSize = strtoupper(str_replace('-', '', $fontSize));
				
				$line_features[$line_id]['fontSize'] = $fontSize;		
				$line_features[$line_id]['repetitivePattern'] = $block_features[$block_id]['repetitivePattern'];		
				
				
				//------------------------------------------------------------------------
				// features of page this line belongs too
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
		
		
		$page_counter++;
	}

	return $crf_data;	
}



?>
