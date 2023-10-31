<?php


//----------------------------------------------------------------------------------------
function do_ocr($jp2dir, $language = 'eng', $image_format = 'png')
{
	$basename = str_replace('_jp2', '', $jp2dir);

	echo $basename . "\n";

	$dir = $basename . '_' . $image_format;

	if (!file_exists($dir))
	{
		$oldumask = umask(0); 
		mkdir($dir, 0777);
		umask($oldumask);
	}
	
	echo "JP2 to images...\n";
	
	
	$files = scandir($jp2dir);
	
	foreach ($files as $image_file)
	{
		if (preg_match('/_(\d+).jp2$/', $image_file, $m))
		{
			$page_num = $m[1];
			
			$source_file = $jp2dir . '/' . $image_file;
			$target_file = $dir . '/' . 'page-' . str_pad($page_num, 3, '0', STR_PAD_LEFT) . '.' . $image_format;
						
			if (!file_exists($target_file))
			{
				$options = array(
					"-s", // use text region
					"-p", // PDF ready
					"-O " .  $target_file, // output
					$source_file
				);
				
				$command =  'jbig2 ' . join(" ", $options);
				echo $command . "\n";
				system($command);
			}
		}	
	}

	// OCR	
	echo "OCR on each image...\n";
	
	$files = scandir($dir);

	foreach ($files as $image_filename)
	{
		if (preg_match('/page-(\d+)/', $image_filename, $m))
		{
			$command = "tesseract -l $language $dir/$image_filename $dir/page-" . $m[1] . " hocr";
			echo $command . "\n";
			system($command);
		}

	}

	// Merge
	echo "Merge hOCR...\n";
	
	$files = scandir($dir);

	$html_start = '<?xml version="1.0" encoding="UTF-8"?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
		"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	 <head>
	  <title></title>
	  <meta http-equiv="Content-Type" content="text/html;charset=utf-8"/>
	  <meta name=\'ocr-system\' content=\'tesseract 5.3.2\' />
	  <meta name=\'ocr-capabilities\' content=\'ocr_page ocr_carea ocr_par ocr_line ocrx_word ocrp_wconf\'/>
	 </head>
	 <body>
	';

	$html_end = ' </body>
	</html>';

	$html = '';

	foreach ($files as $hocr_filename)
	{
		if (preg_match('/hocr/', $hocr_filename, $m))
		{
			$h = file_get_contents($dir . '/' . $hocr_filename);
		
			$h = preg_replace('/\R/u', '', $h);
		
			$h = preg_replace('/^.*<body>/m', '', $h);
			$h = preg_replace('/<\/body>.*$/m', '', $h);
		
			$h .= "\n";
		
			$html .= $h;

		}
	}

	$html = $html_start . $html . $html_end;
	file_put_contents($basename . '_hocr.html', $html);
}

//----------------------------------------------------------------------------------------


$filename = '';
if ($argc < 2)
{
	echo "Usage: ocr-jp2.php <jp2 directory>\n";
	exit(1);
}
else
{
	$filename = $argv[1];
}

$language = 'eng';
//$language = 'eng+jpn';

do_ocr($filename, $language);


?>
