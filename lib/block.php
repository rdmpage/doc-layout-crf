<?php



require_once(dirname(__FILE__) . '/spatial.php');

global $svg; // debugging

//----------------------------------------------------------------------------------------
// Relationship between two rects
function relationship($rect1, $rect2)
{
	$relations = array();
	
	if ($rect1->intersectsRect($rect2))
	{
		$relations[] = 'intersect';
		
		$overlap = $rect1->getOverlap($rect2);
		
		if ($overlap)
		{		
			if ($overlap->getArea() == $rect2->GetArea())
			{
				$relations[] = "inclusion";
			}
		
			if ($overlap->getArea() == $rect1->GetArea())
			{
				$relations[] = "is included";
			}
		}
		else
		{
			// $overlap should by definition not be empty
			echo "Badness\n";			
		}
		
	}

	return $relations;
}


//----------------------------------------------------------------------------------------
// Classify relationship in terms of relative position
// left = touches left margin
// right = touches right margin
// top = touches top
// bottom = touches bottom
// centred = centred
// width = percent width
function grid($bounding_rect, $otherRect, $slop = 0)
{
	$features = array(
		'top' => false,
		'bottom' => false,
		'left' => false,
		'right' => false,
		'centered' => false,
		'width' => 0		
	);
	
	// make local copy
	$rect = new Rectangle(
		$otherRect->x,
		$otherRect->y,
		$otherRect->w,
		$otherRect->h		
		);
	
	$rect->inflate($slop, $slop);
	
	$left_side = new Line(
		$bounding_rect->x,
		$bounding_rect->y,
		$bounding_rect->x,
		$bounding_rect->y + $bounding_rect->h	
	);

	$right_side = new Line(
		$bounding_rect->x + $bounding_rect->w,
		$bounding_rect->y,
		$bounding_rect->x + $bounding_rect->w,
		$bounding_rect->y + $bounding_rect->h	
	);

	$top_side = new Line(
		$bounding_rect->x,
		$bounding_rect->y,
		$bounding_rect->x + $bounding_rect->w,
		$bounding_rect->y	
	);

	$bottom_side = new Line(
		$bounding_rect->x,
		$bounding_rect->y + $bounding_rect->h,
		$bounding_rect->x + $bounding_rect->w,
		$bounding_rect->y + $bounding_rect->h	
	);	
	
	if ($rect->intersectsLine($left_side))
	{
		$features['left'] = true;
	}
	
	if ($rect->intersectsLine($right_side))
	{
		$features['right'] = true;
	}

	if ($rect->intersectsLine($top_side))
	{
		$features['top'] = true;
	}

	if ($rect->intersectsLine($bottom_side))
	{
		$features['bottom'] = true;
	}
	
	if (is_centred_within($bounding_rect, $otherRect, $slop))
	{
		$features['centered'] = true;
		
		// sanity check, if we are centred and aligned with only one margin then
		// we must also be aligned with the other, but might miss
		// this due to minor errors in alignment
		
		if (in_array('left', $features) && !in_array('right', $features))
		{
			$features['right'] = true;
		}

		if (in_array('right', $features) && !in_array('left', $features))
		{
			$features['left'] = true;
		}		
		
		// justified 
		if (in_array('left', $features) && in_array('right', $features))
		{
			//$terms[] = 'stretch';
		}
	}
	
	$width = round(100 * $otherRect->w / $bounding_rect->w, 0);
	$features['width'] = $width;
	
	if (0)
	{
		echo "\n\n" . $rect->id . "\n";
		print_r($features);
	}
	
	return $features;
	

}

//----------------------------------------------------------------------------------------
// make a vertical bar connecting two points, assumes that $pt1 and $pt2 are 
// vertically aligned +/- $slop otherwise returns null
function verticalBar($pt1, $pt2, $slop = 10)
{
	$vertical = null;
	
	if (abs($pt1->x - $pt2->x) <= $slop)
	{
		$vertical = new Rectangle();
		$vertical->createFromPoints($pt1, $pt2);
		$vertical->name = "vertical";
		$vertical->inflate($slop, $slop);
	}

	return $vertical;
}

//----------------------------------------------------------------------------------------
// make a horizontal bar connecting two points, assumes that $pt1 and $pt2 are 
// horizontally aligned +/- $slop otherwise returns null
function horizontalBar($pt1, $pt2, $slop = 10)
{
	$horizontal = null;
	
	if (abs($pt1->y - $pt2->y) <= $slop)
	{
		$horizontal = new Rectangle();
		$horizontal->createFromPoints($pt1, $pt2);
		$horizontal->name = "horizontal";
		$horizontal->inflate($slop, $slop);
	}

	return $horizontal;
}

//----------------------------------------------------------------------------------------
// True if $rect_below is below $rect_above and both are centred with respect to each other.
// Note that this does not mean that there aren't any other rects between $rect_above and
// rect_below
function is_centred_below($rect_above, $rect_below, $slop = 10)
{
	global $svg;
	
	$below = true;
	
	if ($below)
	{
		if ($rect_above->intersectsRect($rect_below))
		{
			$below = false; // one can't be below another if they intersect
		}
	}
	
	if ($below)
	{
		$centre_above = $rect_above->getCentre();
		$centre_below = $rect_below->getCentre();
		
		$svg .= $centre_above->toSvg();		
		$svg .= $centre_below->toSvg();	
		
		print_r($centre_above);
		print_r($centre_below);
		
		$pt1 = $centre_above;
		$pt2 = new Point($centre_above->x, $centre_below->y);
		
		$svg .= $pt1->toSvg();		
		$svg .= $pt2->toSvg();	
							
		$vertical = verticalBar($pt1, $pt2, $slop = 10);
		
		if ($vertical)
		{				
			$svg .= $vertical->toSvg();		
			$below = $vertical->ptInRect($centre_below);
		}
	}

	return $below;

}

//----------------------------------------------------------------------------------------
// Get bar splitting page vertically 
function vertical_fold($bounding_rect, $slop)
{
	$vertical = null;
	
	$tl = $bounding_rect->getTopLeft();
	$br = $bounding_rect->getBottomRight();
	
	$pt1 = new Point($tl->x + ($br->x - $tl->x)/2, $tl->y);
	$pt2 = new Point($tl->x + ($br->x - $tl->x)/2, $br->y);
	
	$vertical = verticalBar($pt1, $pt2, $slop);

	return $vertical;
}

//----------------------------------------------------------------------------------------
// Get bar splitting page horizontally 
function horizontal_fold($bounding_rect, $slop)
{
	$horizontal = null;
	
	$tl = $bounding_rect->getTopLeft();
	$br = $bounding_rect->getBottomRight();
	
	$pt1 = new Point($tl->x, $tl->y + ($br->y - $tl->y)/2);
	$pt2 = new Point($br->x, $tl->y + ($br->y - $tl->y)/2);
	
	$horizontal = horizontalBar($pt1, $pt2, 10);

	return $horizontal;
}

//----------------------------------------------------------------------------------------
// True if $rect is centred within another rect
function is_centred_within($bounding_rect, $rect, $slop = 10)
{
	global $svg;
	
	$centred = true;
	
	if ($centred)
	{
		$relations = relationship($bounding_rect, $rect);
		if (!in_array('inclusion', $relations))
		{
			$centred = false; // smaller rect must be included within larger rect
		}
	}
	
	if ($centred)
	{	
		$centre = $rect->getCentre();
		
		$svg .= $centre->toSvg();	
		
		$vertical = vertical_fold($bounding_rect, $slop);	
		$svg .= $vertical->toSvg();	
		
		$centred = $vertical->ptInRect($centre);				
	}

	return $centred;
}

//----------------------------------------------------------------------------------------
// trivial test of below, is rect_above lower margin above top of rect_below? 
function isBelow($rect_above, $rect_below)
{
	$below = true;
	
	if ($below)
	{
		if ($rect_above->intersectsRect($rect_below))
		{
			$below = false; // one can't be below another if they intersect
		}
	}
	
	if ($below)
	{
		$below = ($rect_above->y + $rect_above->h) < $rect_below->y;
	}

	return $below;
}


//----------------------------------------------------------------------------------------
// Distance between centres of two rects
function centroid_distance($rect1, $rect2)
{
	global $svg;
	
	$d = 0;	
	
	$centre1 = $rect1->getCentre();
	$centre2 = $rect2->getCentre();
	
	$line = new Line($centre1->x, $centre1->y, $centre2->x, $centre2->y);
	
	$svg .= $line->toSvg();	
	
	$d = $line->getLength();

	return $d;
}



//----------------------------------------------------------------------------------------
  // need to be able to decide if > column and assign blocks to colums
  
  // need to be able to determine text alignment within a rect
  
  // need to determine if one block is below another (e.g. caption below figure)

// need inetrsection with media vertical line to check for columns

// block outputput should be for each it's relativ

// need to detemine charactesitcis of text in block, i.e. what sort of alignment

if (0)
{
	$output_filename = '1.svg';

	// minx, miny, maxx, maxy

	$bounding_bbox = new BBox(100, 100, 600, 800); 

	$bounding_rect = $bounding_bbox->toRectangle();

	// lines we test for hitting




	$svg = '<?xml version="1.0" ?>
<svg xmlns:xlink="http://www.w3.org/1999/xlink" 
		xmlns="http://www.w3.org/2000/svg"
		width="1000px" 
		height="1000px" >';	
	$svg .= '<g transform="scale(1)">';
	
	$svg .= $bounding_rect->toSvg();
	
	
	
	// test cases
	if (0)
	{
		$centred_bb = new BBox(200, 200, 500, 250); 
		$centred = $centred_bb->toRectangle();
		$centred->id = 'centre';
	
		$centred->name = "Centred";
	
		$svg .= $centred->toSvg();
	
		features($bounding_rect, $centred);
	
		$relations = relationship($bounding_rect, $centred);	
		print_r($relations);
	}
	
	if (0)
	{

		$left_bb = new BBox(105, 300, 400, 350); 
		
		$left = $left_bb->toRectangle();
		$left->id = 'left text box';
		$left->name = "left";
	
		$svg .= $left->toSvg();
	
		features($bounding_rect, $left, 4);
	
		$relations = relationship($bounding_rect, $left);	
		print_r($relations);
	
	}
	
	if (0)
	{

		$top_left_bb = new BBox(100, 100, 200, 120); 
		$top_left = $top_left_bb->toRectangle();
	
		$top_left->id = 'top left corner';
		$top_left->name = "top left";

	
		$svg .= $top_left->toSvg();
	
		features($bounding_rect, $top_left, 0);
	
		$relations = relationship($bounding_rect, $top_left);	
		print_r($relations);
	
	}
	
	if (0)
	{
		$figure_bb = new BBox(100, 100, 500, 200); 
		$figure = $figure_bb->toRectangle();
		$figure->name = 'figure';
	
		$caption_bb = new BBox(100, 400, 500, 450); 
		$caption = $caption_bb->toRectangle();
		$caption->name = 'caption';
		
		$svg .= $figure->toSvg();
		$svg .= $caption->toSvg();
		
		if (is_centred_below($figure, $caption))
		{
			echo "is below\n";
		}
	}
	
	
	// Is rect centred in another ?
	if (0)
	{
		
		$topbb = new BBox(110, 110, 590, 130); 
		$top = $topbb->toRectangle();
		$top->name = 'one';

			
		$svg .= $top->toSvg();
		
		$centred = is_centred_within($bounding_rect, $top);
		
		if ($centred)
		{
			echo "Centred\n";
		
		}
		else
		{
			echo "Not centred\n";
		}
		
		
		
	}
	

	
	// column detection
	
	if (0)
	{
	
		/*
		$vertical = vertical_fold($bounding_rect, 10);
		if ($vertical)
		{
			$svg .= $vertical->toSvg();
		}

		$horizontal = horizontal_fold($bounding_rect, 10);
		if ($horizontal)
		{
			$svg .= $horizontal->toSvg();
		}
		*/
		
		$topbb = new BBox(110, 110, 590, 130); 
		$top = $topbb->toRectangle();
		$top->name = 'one';

	
		$onebb = new BBox(100, 200, 340, 400); 
		$one = $onebb->toRectangle();
		$one->name = 'one';

		$twobb = new BBox(100, 450, 340, 550); 
		$two = $twobb->toRectangle();
		$two->name = 'two';
		
		$zwobb = new BBox(360, 150, 600, 500); 
		$z = $zwobb->toRectangle();
		$z->name = 'three';		
		
		$svg .= $top->toSvg();
		$svg .= $one->toSvg();
		$svg .= $two->toSvg();
		$svg .= $z->toSvg();
		$svg .= $top->toSvg();
		
	}
	
	if (1)
	{
		$figure_bb = new BBox(100, 100, 500, 200); 
		$figure = $figure_bb->toRectangle();
		$figure->name = 'figure';
	
		$caption_bb = new BBox(100, 400, 500, 450); 
		$caption = $caption_bb->toRectangle();
		$caption->name = 'caption';
		
		$svg .= $figure->toSvg();
		$svg .= $caption->toSvg();
		
		if (isBelow($figure, $caption))
		{
			echo "is below\n";
			echo "d=" . centroid_distance($figure, $caption);
		}
	}
	
	


	$svg .= '</g>';
	$svg .= '</svg>';
	file_put_contents($output_filename ,$svg);

}

?>
