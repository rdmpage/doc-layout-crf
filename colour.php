<?php

// Parse VILA-style format and output HTML, coloured by labels, so we can view results

error_reporting(E_ALL);

$basedir = '';
if ($argc < 2)
{
	echo "Usage: colour.php <basedir>\n";
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
		$page_num = $m['pagenum'];
	
		$tokens_filename = $basedir . '/' . $filename;
	
		$tokens_json = file_get_contents($tokens_filename);		
		$tokens = json_decode($tokens_json);
		
		// Do we have labels (e.g., from a model or as training data)
		$label_filename = $basedir . '/labels' . $page_num . '.json';

		if (file_exists($label_filename))
		{
			$labels_json = file_get_contents($label_filename);		
			$labels = json_decode($labels_json);
		}
		else
		{
			echo "Error: no labels files found\n";
			exit();
		}

		$scale = 600 / $tokens->width;

		if (1)
		{
			// raw words
			$html = '';
		
			$html .=  '<html>';
			$html .=  '<head>';
			$html .=  '<style>';
			$html .=  'body { padding:1em;background:rgb(228,228,228);}';
			$html .=  '/* https://docs.google.com/document/d/1frGmzYOHnVRWAwTOuuPfc3KVAwu-XKdkFSbpLfy78RI/edit#heading=h.iz6rewv3v747 */';
			$html .=  '.Paragraph { background:rgb(221,227,221); opacity:1.0; }';
			$html .=  '.Header { background:rgb(234,183,172); opacity:1.0; }';
			$html .=  '.Footer { background:rgb(234,183,172); opacity:1.0; }';
			$html .=  '.Footnote { background:rgb(234,183,172); opacity:1.0; }';
			$html .=  '.Figure { background:rgb(233,222,241); opacity:1.0; }';
			$html .=  '.Caption { background:rgb(234,183,172); opacity:1.0; }';

			$html .=  '.Title { background:rgb(158,216,190); opacity:1.0; }';
			$html .=  '.Author { background:rgb(197,219,136); opacity:1.0; }';
			$html .=  '.Abstract { background:rgb(187,218,177); opacity:1.0; }';
			$html .=  '.Keywords { background:rgb(160,230,164); opacity:1.0; }';
			$html .=  '.Section { background:rgb(244,219,144); opacity:1.0; }';
			$html .=  '.List { background:rgb(215,233,248); opacity:1.0; }';
			$html .=  '.Bibliography { background:rgb(200,203,146); opacity:1.0; }';
			$html .=  '.Table { background:rgb(197,195,229); opacity:1.0; }';
			
			// "semantic"
			$html .=  '.Affiliation { background:rgb(89,157,173); opacity:1.0; }';			
			$html .=  '.Email { background:rgb(99,93,136); opacity:1.0; }';			
			$html .=  '.Name { background:rgb(223,159,98); opacity:1.0; }';			
			$html .=  '.Venue { background:rgb(60,85,95); opacity:1.0; }';			

			$html .=  '</style>';
			$html .=  '</head>';
			$html .=  '<body>';
			
			
	
			$html .=  '<div style="position:relative;width:' . ($scale * $tokens->width) . 'px;height:' . ($scale * $tokens->height)  . 'px;border:1px solid rgb(228,228,228);background:white;margin:10px;">';		
			
			if (1)
			{
				foreach ($tokens->blocks as $block_id => $block)
				{			
					$left 	= $scale * $block->bbox[0];
					$top 	= $scale * $block->bbox[1];
					$width 	= $scale * ($block->bbox[2] - $block->bbox[0]);
					$height = $scale * ($block->bbox[3] - $block->bbox[1]);
					
					if ($block->type == 'image')
					{
						$html .=  '<div title="block_' . $block_id . '" style="background:rgba(255,0,0,0.2); border:1px solid rgb(228,228,228); position:absolute;left:' . $left . 'px;'
							. 'top:' . $top . 'px;'
							. 'width:' . $width . 'px;'
							. 'height:' . $height . 'px;'						
							. '">';

						if (isset($block->href))
						{
							$html .= '<img src="' . $block->href . '" width="' . $width . '">';
						}
					}
					else
					{
						$html .=  '<div title="block_' . $block_id . '" style="background:rgba(255,255,0,0.2); border:1px solid rgb(228,228,228); position:absolute;left:' . $left . 'px;'
							. 'top:' . $top . 'px;'
							. 'width:' . $width . 'px;'
							. 'height:' . $height . 'px;'						
							. '">';
					}
					
					$html .= '</div>';			
				}
			}
			
			
			
			foreach ($labels as $word_id => $label)
			{
				$left 	= $scale * $tokens->bbox[$word_id][0];
				$top 	= $scale * $tokens->bbox[$word_id][1];
				$width 	= $scale * ($tokens->bbox[$word_id][2] - $tokens->bbox[$word_id][0]);
				$height = $scale * ($tokens->bbox[$word_id][3] - $tokens->bbox[$word_id][1]);
			
				// $tokens->words[$word_id]
			
				$html .=  '<div title="' . $label . '" class="' . $label . '" style="position:absolute;left:' . $left . 'px;'
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
	
	
	}
}

?>
