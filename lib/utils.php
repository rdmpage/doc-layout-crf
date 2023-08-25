<?php

//----------------------------------------------------------------------------------------
/*

Based on Klampfl et al.'s modification of Lin's method.

Klampfl S, Granitzer M, Jack K, Kern R (2014) Unsupervised document structure analysis 
of digital scientific articles. Int J Digit Libr 14(3):83–99. 
https://doi.org/10.1007/s00799-014-0115-1

Lin X (2003) Header and footer extraction by page association. In: Document Recognition 
and Retrieval X. SPIE, pp 164–171 https://doi.org/10.1117/12.472833


*/

//----------------------------------------------------------------------------------------
function base_sim($text0, $text1, $debug = false)
{	
	$text0 = strtolower($text0);
	$text1 = strtolower($text1);

	$text0 = preg_replace('/\d/', '@', $text0);
	$text1 = preg_replace('/\d/', '@', $text1);
	
	$text0 = mb_substr($text0, 0, 200);
	$text1 = mb_substr($text1, 0, 200);
	
	if ($debug)
	{
		echo "|$text0|$text1|\n";
	}
	
	$max_len = max(mb_strlen($text0), mb_strlen($text1));
	
	return 1.0 - levenshtein($text0, $text1) / $max_len;
}

//----------------------------------------------------------------------------------------
function geom_sim($rect0, $rect1, $debug = false)
{
	$overlap = $rect0->getOverlap($rect1);
	
	$overlap_area = 0;
	if ($overlap)
	{
		$overlap_area = $overlap->getArea();
	}
	
	$max_area = max($rect0->getArea(), $rect1->getArea());
	
	$similarity = $overlap_area / $max_area;
	
	if ($debug)
	{
		echo "overlap $overlap_area $max_area \n";
	}
	
	return $similarity;
}



?>
