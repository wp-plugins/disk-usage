<?php
/*

HTML Treemap Implementation
by Geoff Gaudreault
http://www.neurofuzzy.net

This is provided AS-IS with no warranty express or implied.
Use this code, or any code that I write, at your own risk.

Good luck, starfighter!

*/

 
//
//	Function: render_treemap
//	Recursive function that returns an HTML treemap based on an list of items
//
//	Parameters:
//	$theArray - treemapped items; associative array where key is the item name and value is the quantity
//	$width - the width of the treemap
//	$height - the height of the treemap
//	$depth - the current recursion depth, starts at 0
//	$orientation - 0 starts dividing vertically, 1 starts dividing horizontally.  This basically swaps the aspect ratio of the cells
function render_treemap ($theArray, $width, $height, $depth = 0, $orientation = 0) {
	
	// base url of links
	global $baseurl;
	
	// CELL COLORING (optional)
	// ------------------------
	//
	// secondary associative array where key is the item name and the value is the string date of the item.
	// this is used to alter the item's color based on it's age
	global $taggedArray;

	// the age of the newest item
	global $towhen;
	// the amount of time dilation.  This parameter alters time, speeds up the harvest, and teleports you off this rock.
	global $timesquash;
	// ------------------------
	
	
	// if starting, start with the opening treemap tag
	if ($depth == 0) {
	
		$html = '<div class="treemap" style="width: '.$width.'px; height: '.$height.'px;">';
	
	} else {
	
		$html = "";
		
	}
	
	
	// continue to chunk this array in halves until you are left with chunks of 1 item.
	// a chunk of 1 item is the cell
	if (count($theArray) > 1) {
	
		$splitArray = array_chunk($theArray,ceil(count($theArray) / 2),true);
		
		$a = $splitArray[0];
		$b = $splitArray[1];
		
		$apercent = array_sum($a) / array_sum($theArray);
		$bpercent = 1 - $apercent;
		
		// swap division horizontal/vertical depending on orientation
		if ($depth % 2 == $orientation) {
	
			$awidth = ceil($width * $apercent);
			$bwidth = $width - $awidth;
			
			$aheight = $height;
			$bheight = $height;
			
		} else {

			$aheight = ceil($height * $apercent);
			$bheight = $height - $aheight;
			
			$awidth = $width;
			$bwidth = $width;
			
		}

		$astyle = "width: ".$awidth."px; height: ".$aheight."px;";
		$bstyle = "width: ".$bwidth."px; height: ".$bheight."px;";
		
		$html .= "<div class=\"node\" style=\"$astyle\">";
		
		// recurse on child a
		$html .= render_treemap($a, $awidth, $aheight, $depth + 1);
		
		$html .= "</div>";
		
		// recurse on child b
		$html .= "<div class=\"node\" style=\"$bstyle\">";
		
		$html .= render_treemap($b, $bwidth, $bheight, $depth + 1);
		
		$html .= "</div>";
	
	} else {
	
		// make cell
		foreach ( $theArray as $tag => $pop ) {

			$urltag = strtolower(str_replace(" ","-",trim($tag)));

			if (strpos($urltag,"-")) {
			
				$classtext = " proper";
				
			} else {
			
				$classtext = "";
				
			}
			
			// file type coloring
			$ftypes  = array(
			"html"=>array("html","htm","shtml"),
			"txt"=>array("txt","conf","bat","sh","js","bak","doc","log","sfc","cfg","htaccess"),
			"exe"=>array("sh","install","bat","cmd"),
			"ini"=>array("ini","inf"),
			"code"=>array("php","phtml","php3","php4","inc","tcl","h","c","cpp","py","cgi","pl"),
			"img"=>array("gif","png","jpeg","jfif","jpg","jpe","bmp","ico","tif","tiff","avi","mpg","mpeg"),
			"sql"=>array("sql"),
			"phpsess"=>array("sess"),
			"download"=>array("exe","com","pif","src","lnk","zip","rar","gz","tar","tar.gz","mp3","wma","ace","pdf")
			);
			$colors = array(
			"html"=>"#cccccc",
			"txt"=>	"#DA59EF",
			"exe"=>	"#cccccc",
			"ini"=>	"#cccccc",
			"code"=>"#EFB859",
			"img"=>"#A7EF59",
			"sql"=>"#59EFC2",
			"phpsess"=>"#cccccc",
			"download"=>"#DF6237"
			);
			
			$ex = explode(".", $tag);
			$extension = $ex[1];

			foreach ($ftypes as $type => $ext) {
				if (in_array($extension,$ext)) {
					$the_type = $type; break;
				}
			}
			foreach($colors as $type => $color) {
				if($type == $the_type) {
					$styletext = " background-color: ".$color; break;
				}
			}
			
			// change text size depending on size of cell
			$textsize = max(11,floor(($width - 16) / max(8,strlen($tag))));
			$textsize = max(11,min($textsize, $height - 8));
			
			$styletext = "style=\" font-size: ".$textsize."px; $styletext;\"";
			
			// make html
			if ($pop > 1000000) {
				$linktext = dprx_du_format_size($pop,1)."<br />".$tag;
			} elseif ($pop > 150000) {
				$linktext = dprx_du_format_size($pop,1);
			} else {
				$linktext = "&nbsp;";
			}
			$html .= "<a class=\"textnode$classtext\"$styletext href=\"".get_bloginfo("wpurl").$urltag."\" title=\"$tag\"><img src=\"".$baseurl."chrome/spacer.gif\" height=\"100%\" width=\"1\" border=\"0\" alt=\"\" />".$linktext."</a>";
			
		}
	
	}
	
	// close treemap
	if ($depth == 0) {
	
		$html .= '</div>';
	
	}
	
	return $html;

}
?>