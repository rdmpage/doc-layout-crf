<?php

// Parse VILA-style format and output HTML to view. Tokens and blocks are unclassified

error_reporting(E_ALL);

require_once(dirname(__FILE__) . '/lib/spatial.php');

$basedir = '';
if ($argc < 2)
{
	echo "Usage: predict.php <basedir>\n";
	exit(1);
}
else
{
	$basedir = $argv[1];
}

$files = scandir($basedir);
foreach ($files as $filename)
{
	if (preg_match('/tokens(?<pagenum>\d+)\.json/', $filename, $m))
	{
		$tokens_filename = $basedir . '/' . $filename;
	
		$tokens_json = file_get_contents($tokens_filename);		
		$tokens = json_decode($tokens_json);
		
		$scale = 600 / $tokens->width;

		if (0)
		{
			// raw words
			$html = '';
		
			$html .=  '<html>';
			$html .=  '<body>';
	
			$html .=  '<div style="position:relative;width:' . ($scale * $tokens->width) . 'px;height:' . ($scale * $tokens->height)  . 'px;border:1px solid rgb(228,228,228);margin:10px;">';		
						
	
			foreach ($tokens->words as $word_id => $text)
			{
				$left 	= $scale * $tokens->bbox[$word_id][0];
				$top 	= $scale * $tokens->bbox[$word_id][1];
				$width 	= $scale * ($tokens->bbox[$word_id][2] - $tokens->bbox[$word_id][0]);
				$height = $scale * ($tokens->bbox[$word_id][3] - $tokens->bbox[$word_id][1]);
			
				$html .=  '<div style="background:rgb(221,227,221); opacity:1.0;position:absolute;left:' . $left . 'px;'
					. 'top:' . $top . 'px;'
					. 'width:' . $width . 'px;'
					. 'height:' . $height . 'px;'						
					. '"></div>';
		
			}
	
			$html .=  '</div>';
			$html .=  '</body>';			
			$html .=  '</html>';	
			
			$html_filename = str_replace('.json', '.html', $tokens_filename);
		
			file_put_contents($html_filename , $html);

		}
		
		// known blocks
		if (1)
		{
			$html = '';
		
			$html .=  '<html>';
			$html .=  '<body>';
	
			$html .=  '<div style="position:relative;width:' . ($scale * $tokens->width) . 'px;height:' . ($scale * $tokens->height)  . 'px;border:1px solid rgb(228,228,228);margin:10px;">';		

			foreach ($tokens->blocks as $block_id => $block)
			{
			
				$left 	= $scale * $block->bbox[0];
				$top 	= $scale * $block->bbox[1];
				$width 	= $scale * ($block->bbox[2] - $block->bbox[0]);
				$height = $scale * ($block->bbox[3] - $block->bbox[1]);

				$html .=  '<div style="background:rgb(221,227,221); opacity:1.0;position:absolute;left:' . $left . 'px;'
					. 'top:' . $top . 'px;'
					. 'width:' . $width . 'px;'
					. 'height:' . $height . 'px;'						
					. '">';
					
				if ($block->type == 'image')
				{
					$html .= '<img src="../' . $block->href . '" width="' . $width . '">';
				}
					
				$html .= '</div>';
			
			}
			
			$html .=  '</div>';
			$html .=  '</body>';			
			$html .=  '</html>';	
			
			$html_filename = str_replace('.json', '-blocks.html', $tokens_filename);
		
			file_put_contents($html_filename , $html);			
		}
		
		/*
		if (1)
		{
			// find blocks from words
	
			$blocks = array();
	
			foreach ($tokens->block_ids as $word_id => $block_id)
			{
				if (!isset($blocks[$block_id]))
				{
					$blocks[$block_id] = new BBox(0, 0, 0, 0);
				}
		
				$bbox = new BBox(
					$tokens->bbox[$word_id][0], 
					$tokens->bbox[$word_id][1],
					$tokens->bbox[$word_id][2],
					$tokens->bbox[$word_id][3]			
					);
		
				$blocks[$block_id]->merge($bbox);	
			}
	
			$scale = 600 / $tokens->width;
			$html = '';
		
			$html .=  '<html>';
			$html .=  '<body>';
	
			$html .=  '<div style="position:relative;width:' . ($scale * $tokens->width) . 'px;height:' . ($scale * $tokens->height)  . 'px;border:1px solid rgb(228,228,228);margin:10px;">';		
	
	
			foreach ($blocks as $block)
			{
				$bbox = $block->toArray();
		
				$left 	= $scale * $bbox[0];
				$top 	= $scale * $bbox[1];
				$width 	= $scale * ($bbox[2] - $bbox[0]);
				$height = $scale * ($bbox[3] - $bbox[1]);
			
				$html .=  '<div style="background:rgb(221,227,221); opacity:1.0;position:absolute;left:' . $left . 'px;'
					. 'top:' . $top . 'px;'
					. 'width:' . $width . 'px;'
					. 'height:' . $height . 'px;'						
					. '"></div>';
			}
		
			$html .=  '</div>';
			$html .=  '</body>';			
			$html .=  '</html>';	

			$html_filename = str_replace('.json', '-blocks.html', $tokens_filename);
		
			file_put_contents($html_filename , $html);
		}
		*/
	
	}
}

?>
