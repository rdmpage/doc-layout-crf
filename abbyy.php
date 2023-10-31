<?php

error_reporting(E_ALL);

ini_set('memory_limit', '-1');

// Convert ABBYY XML to VILA

require_once(dirname(__FILE__) . '/lib/spatial.php');

//----------------------------------------------------------------------------------------
function parse_abbyy($filename)
{
	$pages_tokens = array();

	$image_counter = 0;
	
	$xml = file_get_contents($filename);
				
	$dom = new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);
	
	$buffer = substr($xml, 0, 1024);
	
	if (preg_match('/"(http:\/\/www.abbyy.com\/FineReader_xml\/FineReader\d+-schema-v1.xml)"/', $buffer, $m))
	{
		$xpath->registerNamespace('abbyy', $m[1]);
	}

	$pages = $xpath->query ('//abbyy:page');
	foreach($pages as $xml_page)
	{
		// page level
		$page = new stdclass;	
		
		$page->words = array();
		$page->bbox = array();
		$page->block_ids = array();
		$page->line_ids = array();
		$page->labels = array();
		
		$page->blocks = array();
		
		$word_counter = 0;
		$line_counter = 0;
		$block_counter = 0;	

		// coordinates
		if ($xml_page->hasAttributes()) 
		{ 
			$attributes = array();
			$attrs = $xml_page->attributes; 
		
			foreach ($attrs as $i => $attr)
			{
				$attributes[$attr->name] = $attr->value; 
			}
		}
		
		/*
		$source_id = basename($filename, ".xml");
		$source_id = preg_replace('/_abbyy/', '', $source_id);
		$source_id = str_replace(' ', '', $source_id);
		
		// Construct image URL
		$page->imageUrl = 'https://archive.org/download/' . $source_id . '/page/n' . $page_count . '.jpg';			
		//print_r($attributes);
		*/
			
		$page->width = (Integer)$attributes['width'];
		$page->height = (Integer)$attributes['height'];	
	
		//$page->dpi = $attributes['resolution'];	
	
		$blocks = $xpath->query ('abbyy:block', $xml_page);
		foreach($blocks as $block_tag)
		{		
			$block_type = 'text';
	
			// attributes
			if ($block_tag->hasAttributes()) 
			{ 
				$attributes = array();
				$attrs = $block_tag->attributes; 
		
				foreach ($attrs as $i => $attr)
				{
					$attributes[$attr->name] = $attr->value; 
				}
			}

			// what type of block?
			switch ($attributes['blockType'])
			{
				case 'Picture':
					$block_type = 'image';
					break;
		
				case 'Table':
					$block_type = 'table';
					break;		
					
				case 'Separator':	
					$block_type = 'separator';
					break;					
		
				case 'Text':
				default:
					$block_type = 'text';
					break;
			}
		
			// images
			if ($block_type == 'image')
			{			
				$block = new stdclass;
				$block->bbox = array(
					(Double)$attributes['l'], 
					(Double)$attributes['t'],
					(Double)$attributes['r'],
					(Double)$attributes['b']
					);
					
				$block->type = $block_type;			
			
				$block->href = 'image-' . $image_counter . '.jpeg';
				$image_counter++;
				
				// There are some odd things that happen in ABBYY that we need to deal with
		
				// There may be an image almost the size of the page, this is the scan of the 
				// whole page and we don't want that.
				
				//to do
				$width = $block->bbox[2] - $block->bbox[0];
				$height = $block->bbox[3] - $block->bbox[1];
			
				//echo '[' . $width . ',' . $height . ']' . "\n";
				//echo '[' . $page->width . ',' . $page->height . ']' . "\n";
			
				$ratio = ($width * $height) / ($page->width * $page->height);
			
				//echo $ratio . "\n";
			
				if ($ratio < 0.9)
				{
					$page->blocks[$block_counter] = $block;
					$block_counter++;
				}								
			}

			if ($block_type == 'text')
			{		
				// Option 1: ABBYY blocks are paragraphs
				$pars = $xpath->query ('abbyy:text/abbyy:par', $block_tag);
				foreach ($pars as $par)
				{		
					$block = new stdclass;
					$block->type = 'text';
					$block->bbox = array($page->width, $page->height,0,0);
			
					// Get lines of text
					$lines = $xpath->query ('abbyy:line', $par);
							
					foreach($lines as $line_tag)
					{			
						// coordinates
						if ($line_tag->hasAttributes()) 
						{ 
							$attributes = array();
							$attrs = $line_tag->attributes; 
		
							foreach ($attrs as $i => $attr)
							{
								$attributes[$attr->name] = $attr->value; 
							}
						}
						
						$line_bbox = array(
							(Double)$attributes['l'], 
							(Double)$attributes['t'],
							(Double)$attributes['r'],
							(Double)$attributes['b']
							);
							
						$block->bbox[0] = min($block->bbox[0], $line_bbox[0]);
						$block->bbox[1] = min($block->bbox[1], $line_bbox[1]);
						$block->bbox[2] = max($block->bbox[2], $line_bbox[2]);
						$block->bbox[3] = max($block->bbox[3], $line_bbox[3]);
												
						$formattings = $xpath->query ('abbyy:formatting', $line_tag);
						foreach($formattings as $formatting)
						{
							if ($formatting->hasAttributes()) 
							{ 
								$attributes = array();
								$attrs = $formatting->attributes; 
			
								foreach ($attrs as $i => $attr)
								{
									$attributes[$attr->name] = $attr->value; 
								}
							}
				
							//$bold 		= isset($attributes['bold']);
							//$italic 		= isset($attributes['italic']);
							//$font_size 	= $attributes['fs'];
							//$font_name 	= $attributes['ff'];
							
							// $line->font_size = round($font_size);
					
							// pts to pixels (?)
							//$font_size *= $page->dpi / 72; 
				
							$nc = $xpath->query ('abbyy:charParams', $formatting);
					
							$token_bbox = array($page->width, $page->height, 0, 0);
							$word = array();
					
							foreach($nc as $n)
							{
								// coordinates
								if ($n->hasAttributes()) 
								{ 
									$attributes = array();
									$attrs = $n->attributes; 
			
									foreach ($attrs as $i => $attr)
									{
										$attributes[$attr->name] = $attr->value; 
									}
								}
						
								if (0)
								{
									// take coordinates for this character 
									$char_box = array(
										(Double)$attributes['l'], 
										(Double)$attributes['t'],
										(Double)$attributes['r'],
										(Double)$attributes['b']
										);										
								}
								else
								{
									// use line top and bottom to ensure smooth display of text
										
									$char_box = array(
										(Double)$attributes['l'], 
										$line_bbox[1],
										(Double)$attributes['r'],
										$line_bbox[3],
										);											
								}
								
								$char = $n->firstChild->nodeValue;		
								
								if ($char == ' ')
								{
									if (count($word) > 0)
									{
										// emit
										//echo join('', $word) . "\n";
										
										$text = join('', $word);
										
										$page->words[] 		= $text;
										$page->bbox[] 		= $token_bbox;						
										$page->block_ids[] 	= $block_counter;
										$page->line_ids[] 	= $line_counter;
										$page->labels[]		= null;
										
									}
									// initialise
									$word = array();
									$token_bbox = array($page->width, $page->height, 0, 0);
								}
								else
								{
									$word[] = $char;

									$token_bbox[0] = min($token_bbox[0], $char_box[0]);
									$token_bbox[1] = min($token_bbox[1], $char_box[1]);
									$token_bbox[2] = max($token_bbox[2], $char_box[2]);
									$token_bbox[3] = max($token_bbox[3], $char_box[3]);
								}
							}
			
							if (count($word) > 0)
							{
								// emit
								//echo join('', $word) . "\n";
								
								$text = join('', $word);
								
								$page->words[] 		= $text;
								$page->bbox[] 		= $token_bbox;						
								$page->block_ids[] 	= $block_counter;
								$page->line_ids[] 	= $line_counter;
								$page->labels[]		= null;
							}
							
						}
						
						//$line->text = join(' ', $line->text_strings);
						//unset($line->text_strings);
			
						//$b->lines[] = $line;
						
						$line_counter++;
					}
			
					$page->blocks[$block_counter] = $block;		
					$block_counter++;				
				}
			}
		}	
		$pages_tokens[] = $page;				
	}

	return $pages_tokens;
}

//----------------------------------------------------------------------------------------


$filename = '';
if ($argc < 2)
{
	echo "Usage: abbyy.php <basedir>\n";
	exit(1);
}
else
{
	$basedir = $argv[1];
}

$filename = $basedir . '.xml';

if (!file_exists($basedir))
{
    $oldumask = umask(0); 
    mkdir($basedir, 0777);
    umask($oldumask);
}

$pages_tokens = parse_abbyy($filename);

//print_r($pages_tokens);

foreach ($pages_tokens as $page_number => $page)
{
	if (count($page->words) > 0)
	{
		$filename = 'tokens' . str_pad($page_number, 3, '0', STR_PAD_LEFT) . '.json';
		file_put_contents($basedir . '/' . $filename, json_encode($page, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}
}


// extract images

$jp2_prefix = str_replace('_abbyy', '_jp2', $basedir);
$archive = str_replace('_abbyy', '', $basedir);

$n = count($pages_tokens);
for ($i = 0; $i < $n; $i++)
{
	foreach ($pages_tokens[$i]->blocks as $block)
	{
		if ($block->type == 'image')
		{
			$fragment = 
				($block->bbox[2] - $block->bbox[0]) 
				. 'x' 
				. ($block->bbox[3] - $block->bbox[1]) 				
				. '+'
				. $block->bbox[0]
				. '+'
				. $block->bbox[1];
			
			$jp2_filename = $jp2_prefix . '/' . $archive . '_' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.jp2';

			$image_filename  = $basedir . '/' . $block->href;

			$command = 'convert -extract ' . $fragment . ' ' . $jp2_filename . ' ' . $image_filename;
			echo $command . "\n";
			
			system($command);		
		
		}
	}
}

?>
