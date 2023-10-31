<?php

error_reporting(E_ALL);

ini_set('memory_limit', '-1');

// Convert HOCR XHTML

//----------------------------------------------------------------------------------------
function extract_box($text)
{
	$bbox = array(0,0,0,0);
	
	if (preg_match('/bbox (\d+) (\d+) (\d+) (\d+)/', $text, $m))
	{
		$bbox = array(
			(Double)$m[1], 
			(Double)$m[2],
			(Double)$m[3],
			(Double)$m[4]
			);
	}

	return $bbox;
}

//----------------------------------------------------------------------------------------
function parse_hocr($filename)
{
	$pages_tokens = array();
	
	$image_counter = 0;
	
	$xml = file_get_contents($filename);
				
	$dom = new DOMDocument;
	$dom->loadXML($xml);
	$xpath = new DOMXPath($dom);

	$xpath->registerNamespace('xhtml', 'http://www.w3.org/1999/xhtml');

	$ocr_pages = $xpath->query ('//xhtml:div[@class="ocr_page"]');
	foreach($ocr_pages as $ocr_page)
	{		
		$page = new stdclass;
		
		$page->words = array();
		$page->bbox = array();
		$page->block_ids = array();
		$page->line_ids = array();
		$page->labels = array();
		
		$word_counter = 0;
		$line_counter = 0;
		$block_counter = 0;
			
		// coordinates and other attributes 
		if ($ocr_page->hasAttributes()) 
		{ 
			$attributes = array();
			$attrs = $ocr_page->attributes; 
		
			foreach ($attrs as $i => $attr)
			{
				$attributes[$attr->name] = $attr->value; 
			}
		}
			
		$bbox = extract_box($attributes['title']);
		$page->width = $bbox[2] - $bbox[0];
		$page->height= $bbox[3] - $bbox[1];
	
		// images (these may be simply page numbers that haven't been recognised)
		foreach($xpath->query ('xhtml:div[@class="ocr_photo"]', $ocr_page) as $ocr_photo)
		{
			if ($ocr_photo->hasAttributes()) 
			{ 
				$attributes = array();
				$attrs = $ocr_photo->attributes; 

				foreach ($attrs as $i => $attr)
				{
					$attributes[$attr->name] = $attr->value; 
				}
			} 			
			$block = new stdclass;
			$block->type = 'image';
			$block->bbox = extract_box($attributes['title']);
			
			$block->href = 'image-' . $image_counter . '.jpeg';
			$image_counter++;

			$page->blocks[$block_counter] = $block;	
			$block_counter++;			
		}
	
		// text
		$ocr_careas = $xpath->query ('xhtml:div[@class="ocr_carea"]', $ocr_page);
		foreach($ocr_careas as $ocr_carea)
		{
			$ocr_pars = $xpath->query ('xhtml:p[@class="ocr_par"]', $ocr_carea);
			foreach($ocr_pars as $ocr_par)
			{		
				$block = new stdclass;
				$block->type = 'text';
				
				// coordinates
				if ($ocr_par->hasAttributes()) 
				{ 
					$attributes = array();
					$attrs = $ocr_par->attributes; 

					foreach ($attrs as $i => $attr)
					{
						$attributes[$attr->name] = $attr->value; 
					}
				}
								
				$block->bbox =  extract_box($attributes['title']);
			
			
				// hOCR can flag captions
				$lines = $xpath->query ('xhtml:span[@class="ocr_line" or "ocr_caption"]', $ocr_par);
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
					
					$line_bbox = extract_box($attributes['title']);
							
					$words = $xpath->query ('xhtml:span[@class="ocrx_word"]', $line_tag);
					foreach($words as $word)
					{			
						if ($word->hasAttributes()) 
						{ 
							$attributes = array();
							$attrs = $word->attributes; 
		
							foreach ($attrs as $i => $attr)
							{
								$attributes[$attr->name] = $attr->value; 
							}
						}
						
						if (isset($word->firstChild->nodeValue))
						{
							$text 				= $word->firstChild->nodeValue;
					
							$page->words[] 		= $text;
							
							$bbox 				= extract_box($attributes['title']);
							$bbox[1] 			= $line_bbox[1];
							$bbox[3] 			= $line_bbox[3];
							
							$page->bbox[] 		= $bbox;					
							$page->block_ids[] 	= $block_counter;
							$page->line_ids[] 	= $line_counter;
							$page->labels[]		= null;
						}										
					}	
					
					$line_counter++;
				}
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
	echo "Usage: hocr.php <basedir>\n";
	exit(1);
}
else
{
	$basedir = $argv[1];
}

$filename = $basedir . '.html';

if (!file_exists($basedir))
{
    $oldumask = umask(0); 
    mkdir($basedir, 0777);
    umask($oldumask);
}

$pages_tokens = parse_hocr($filename);

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

$images_done = false;

if (!$images_done)
{
	// jbig2 of IA jp2 images
	$png_prefix = str_replace('_hocr', '_png', $basedir);

	if (file_exists($png_prefix))
	{
		$images_done = true;

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
			
					$png_filename = $png_prefix . '/page-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '.png';

					$image_filename  = $basedir . '/' . $block->href;

					$command = 'convert -extract ' . $fragment . ' ' . $png_filename . ' ' . $image_filename;
					echo $command . "\n";
			
					system($command);		
		
				}
			}
		}
	}
}


if (!$images_done)
{
	// Internet Archive
	$jp2_prefix = str_replace('_hocr', '_jp2', $basedir);
	$archive = str_replace('_hocr', '', $basedir);

	if (file_exists($jp2_prefix))
	{
		$images_done = true;

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
	}
}

if (!$images_done)
{
	// OCR from PDF
	$tiff_prefix = str_replace('_hocr', '_tiff', $basedir);

	if (file_exists($tiff_prefix))
	{
		$images_done = true;
	
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
			
					$tiff_filename = $tiff_prefix . '/page-' . str_pad($i, 3, '0', STR_PAD_LEFT) . '.tiff';

					$image_filename  = $basedir . '/' . $block->href;

					$command = 'convert -extract ' . $fragment . ' ' . $tiff_filename . ' ' . $image_filename;
					echo $command . "\n";
			
					system($command);		
		
				}
			}
		}
	}
}




?>
