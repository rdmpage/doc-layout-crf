<?php

// Convert PDFALTO XML to VILA
// Add block details and images to basic training data

//----------------------------------------------------------------------------------------
function parse_xml($filename)
{
	$pages_tokens = array();
	
	$xml = file_get_contents($filename);
	
	
	$dom = new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);
	
	$xpath->registerNamespace('alto', "http://www.loc.gov/standards/alto/ns-v3#");
				
	$pages = $xpath->query ('//alto:Layout/alto:Page');
	foreach($pages as $xml_page)
	{
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
	
		$page->width = (Integer)round($attributes['WIDTH']);
		$page->height = (Integer)round($attributes['HEIGHT']);	
		
		foreach ($xpath->query ('alto:PrintSpace', $xml_page) as $print_space)
		{
			$images = $xpath->query ('alto:Illustration', $print_space);
			foreach($images as $image)
			{
				// coordinates
				if ($image->hasAttributes()) 
				{ 
					$attributes = array();
					$attrs = $image->attributes; 
			
					foreach ($attrs as $i => $attr)
					{
						$attributes[$attr->name] = $attr->value; 
					}
				}
			
				// SVG and PNG need to be treated differently			
				$bbox = array(
					(Double)$attributes['HPOS'], 
					(Double)$attributes['VPOS'],
					(Double)($attributes['HPOS'] + $attributes['WIDTH']),
					(Double)($attributes['VPOS'] + $attributes['HEIGHT'])					
				);
			
				if (preg_match('/\.png/', $attributes['FILEID']))
				{
					$block = new stdclass;
					$block->bbox = $bbox;
					$block->href = '../' . $attributes['FILEID'];
					$block->type = 'image';

					$page->blocks[$block_counter] = $block;
					$block_counter++;			
				}
			
				// SVG is different, drawn on whole? page, also need to get bounding boxes, etc.
				if (preg_match('/\.svg/', $attributes['FILEID']))
				{
					//$block->bbox = array(0,0,$page->width, $page->height);
					//$block->href = '../' . $attributes['FILEID'];
					//$block->type = 'image';
				}
			
			}
		
			// text from born native PDF ---------------------------------------------------------
	
			// Get blocks 
			$blocks = $xpath->query ('alto:TextBlock', $print_space);
			foreach($blocks as $block_tag)
			{		
				$block = new stdclass;
				$block->type = 'text';
				//$block->bbox = array($page->width, $page->height,0,0);
	
				// coordinates
				if ($block_tag->hasAttributes()) 
				{ 
					$attributes = array();
					$attrs = $block_tag->attributes; 
	
					foreach ($attrs as $i => $attr)
					{
						$attributes[$attr->name] = $attr->value; 
					}
				}
			
				$block->bbox = array(
					(Double)$attributes['HPOS'], 
					(Double)$attributes['VPOS'],
					(Double)($attributes['HPOS'] + $attributes['WIDTH']),
					(Double)($attributes['VPOS'] + $attributes['HEIGHT'])					
				);
			
			
				
				// Get lines of text
				$lines = $xpath->query ('alto:TextLine', $block_tag);
		
				foreach($lines as $line_tag)
				{
					$nc = $xpath->query ('alto:String', $line_tag);				
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
					
						$bbox = array(
							(Double)$attributes['HPOS'], 
							(Double)$attributes['VPOS'],
							(Double)($attributes['HPOS'] + $attributes['WIDTH']),
							(Double)($attributes['VPOS'] + $attributes['HEIGHT'])					
						);
					
						/*
						$block->bbox[0] = min($block->bbox[0], $bbox[0]);
						$block->bbox[1] = min($block->bbox[1], $bbox[1]);
						$block->bbox[2] = max($block->bbox[2], $bbox[2]);
						$block->bbox[3] = max($block->bbox[3], $bbox[3]);
						*/
					
						if ($attributes['CONTENT'] != "")
						{
							$text 				= $attributes['CONTENT'];
						
							$page->words[] 		= $text;
							$page->bbox[] 		= $bbox;						
							$page->block_ids[] 	= $block_counter;
							$page->line_ids[] 	= $line_counter;
							$page->labels[]		= null;
						}										
					}
				
					$line_counter++;
				}	
			
				/*
				// sanity check
				if ($block->bbox[0] == $page->width)
				{
					$block->bbox = array(0,0,0,0);
				}
				*/
			
				$page->blocks[$block_counter] = $block;		
				$block_counter++;
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
	echo "Usage: pdfaltoxml.php <basedir>\n";
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

$pages_tokens = parse_xml($filename);

foreach ($pages_tokens as $page_number => $page)
{
	if (count($page->words) > 0)
	{
		$filename = 'tokens' . str_pad($page_number, 3, '0', STR_PAD_LEFT) . '.json';
		file_put_contents($basedir . '/' . $filename, json_encode($page, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
	}
}

?>
