<?php
/******************************************************************************
 Prevent direct access
 ******************************************************************************/
if (!defined('CSS_CACHEER')) { header('Location:/'); }

$plugin_class = 'Grid';

class Grid extends CacheerPlugin
{
	
	function pre_process($css)
	{	
		global $grid;
		
		// Create a new GridCSS object and put the css into it
		$grid = new GridCSS($css);
				
		// If there are settings, keep going
		if($grid->getSettings() != FALSE)
		{			
			// Generate the grid.png
			$grid -> generateGridImage($css);
			
			// Replace the grid() variables
			$css = $grid -> replaceGridVariables($css);
		}

		return $css;
	}
	
	function process($css)
	{
		global $grid;
		
		// If there are settings, keep going
		if($grid->getSettings() != FALSE)
		{
			// Create the layouts xml for use with the tests
			$grid -> generateLayoutXML($css);
		
			// Replace the columns:; properties
			$css = $grid -> replaceColumns($css);
		}
		
		return $css;
	}
	
	function post_process($css)
	{
		global $grid,$options;
			
		// If there are settings, keep going
		if($grid->getSettings() != FALSE)
		{
			if($options['grid_layout']['create_grid_css'] == TRUE)
			{
				// Generate the grid.css
				$css = $grid -> generateGrid($css);
			}
		
			// Remove the settings
			$css = $grid -> removeSettings($css);
		}

		return $css;
	}
}


class GridCSS
{
	var $settings = array();
	
	function __construct($css)
	{
		$this->GridCSS($css);
	}
	
	function GridCSS($css)
	{	
		global $settings;
		
		// Make sure there are settings, if so, grab them
		if (preg_match_all('/@grid.*?\}/sx', $css, $match)) 
		{						
			$settings['columncount'] 	= 	$this -> getParam('column-count', $match[0][0]);
			$settings['columnwidth']	= 	$this -> getParam('column-width', $match[0][0]);
			$settings['gutterwidth']	= 	$this -> getParam('gutter-width', $match[0][0]);
			$settings['baseline']		=	$this -> getParam('baseline', $match[0][0]);
						
			// Check whether we should use the column width or calculate it from the grid width
			if ($settings['columnwidth'] == "") 
			{
				$settings['gridwidth']	= $this -> getParam('grid-width', $match[0][0]);
				$settings['columnwidth'] = $this -> getColumnWidth();
			}
			else 
			{
				$settings['columnwidth'] = $settings['columnwidth'] + $settings['gutterwidth'];
				$settings['gridwidth'] = ($settings['columnwidth'] * $settings['columncount']) - $settings['gutterwidth'];
			}	
			
			// If theres no format specified, go with 'newline'
			if ($settings['format'] == "") 
			{
				$settings['format'] = "newline";
			}		
		}
		
		else
		{
			$settings = FALSE;
		}
		
		return $settings;
		
	}
	
	public function getSettings()
	{
		global $settings;
		return $settings;
	}
	
	public function generateGrid($css)
	{
		global $settings,$path,$options;
		
		$s = $pushselectors = $pullselectors = "";
		
		if($options['grid_layout']['columns-x'] == TRUE)
		{
			// Make the .columns-x classes
			for ($i=1; $i < $settings['columncount'] + 1; $i++) { 
				$w = $settings['columnwidth'] * $i - $settings['gutterwidth'];
				$s .= ".columns-$i{ width:".$w."px; }";
			}
		}
	
		if($options['grid_layout']['push'] == TRUE)
		{
			// Make the .push classes
			for ($i=1; $i < $settings['columncount']; $i++) { 
				$w = $settings['columnwidth'] * $i;
				$s .= ".push-$i{ margin-left: ".$w."px; }";
				$pushselectors .= ".push-$i,";
			}
			$s .= substr_replace($pushselectors,"",-1) . "{ float:right; position:relative; }";
		}
		
		if($options['grid_layout']['pull'] == TRUE)
		{
			// Make the .pull classes
			for ($i=1; $i < $settings['columncount']; $i++) { 
				$w = $settings['columnwidth'] * $i;
				$s .= ".pull-$i{ margin-right:".$w."px; }";
				$pullselectors .= ".pull-$i,";
			}
			$s .= substr_replace($pullselectors,"",-1) . "{ float:left; position:relative; }";
		}
		
		if($options['grid_layout']['baseline-x'] == TRUE)
		{
			// Make the .baseline-x classes
			for ($i=1; $i < 51; $i++) { 
				$h = $settings['baseline'] * $i;
				$s .= ".baseline-$i{ height:".$h."px; }";
			}
		}
		
		if($options['grid_layout']['baseline-pull-x'] == TRUE)
		{
			// Make the .baseline-pull-x class
			for ($i=1; $i < 51; $i++) { 
				$h = $settings['baseline'] * $i;
				$s .= ".baseline-pull-$i{ margin-top:-".$h."px; }";
			}
		}
		
		if($options['grid_layout']['baseline-push-x'] == TRUE)
		{
			// Make the .baseline-push-x classes
			for ($i=1; $i < 51; $i++) { 
				$h = $settings['baseline'] * $i;
				$s .= ".baseline-push-$i{ margin-bottom:-".$h."px; }";
			}
		}
		
		if($options['grid_layout']['append'] == TRUE)
		{
			// Make the .append classes
			for ($i=1; $i < $settings['columncount']; $i++) { 
				$w = $settings['columnwidth'] * $i;
				$s .= ".append-$i{ padding-right:".$w."px; }";
			}
		}
		
		if($options['grid_layout']['prepend'] == TRUE)
		{
			// Make the .prepend classes
			for ($i=1; $i < $settings['columncount']; $i++) { 
				$w = $settings['columnwidth'] * $i;
				$s .= ".prepend-$i{ padding-left:".$w."px; }";
			}
		}
		
		$css = $css . $s;
		
		return $css;
	}
	
	public function generateGridImage($css)
	{
		global $settings, $path;
		
		$image = ImageCreate($settings['columnwidth'], $settings['baseline']);
		
		$colorWhite		= ImageColorAllocate($image, 255, 255, 255);
		$colorGrey		= ImageColorAllocate($image, 200, 200, 200);
		$colorBlue		= ImageColorAllocate($image, 240, 240, 255);
		
		Imagefilledrectangle($image, 0, 0, ($settings['columnwidth'] - $settings['gutterwidth']), ($settings['baseline'] - 1), $colorBlue);
		Imagefilledrectangle($image, ($settings['columnwidth'] - $settings['gutterwidth'] + 1), 0, $settings['columnwidth'], ($settings['baseline'] - 1), $colorWhite);
	
		imageline($image, 0, ($settings['baseline'] - 1 ), $settings['columnwidth'], ($settings['baseline'] - 1 ), $colorGrey);
		
	    ImagePNG($image, $path['backgrounds'] . "/grid.png") or die("Can't save the grid.png file");
	    ImageDestroy($image);
	}
	
	public function generateLayoutXML($css)
	{
		global $settings, $path;
		
		$list = "<layouts>\n";
		$layoutnames = array();

		if(preg_match_all('/\.layout\-([a-zA-Z0-9\-]*)/',$css,$matches))
		{
			foreach($matches[1] as $match)
			{
				array_push($layoutnames, $match);
			}					
		}
		
		$layouts = array_unique($layoutnames);
		
		foreach($layouts as $layout)
		{
			$node = "<layout>layout-".$layout."</layout>\n";
			$list .= $node;
		}
		
		$list .= "\n</layouts>";
		$list = "<?xml version=\"1.0\" ?>\n" . $list; 
				
		// Open the file
		$file = fopen($path['xml'] . "/layouts.xml", "w") or die("Can't open the xml file");
		
		// Write the string to the file
		fwrite($file, $list);
		//chmod($file, 777);
		fclose($file);
		
	}
	
	public function buildGrid($css) 
	{	
		global $settings;
		
		$css = $this -> replaceGridVariables($css);
		$css = $this -> replaceColumns($css);

		return $css;
	}
	 
	
	public function replaceColumns($css)
	{
		global $settings, $flags;
				
		// We'll loop through each of the columns properties by looking for each columns:x; property.
		// This means we'll only loop through $columnscount number of times which could be better
		// or worse depending on how many columns properties there are in your css
		
		for ($i=1; $i <= $settings['columncount']; $i++) { 
		
			// Matches all selectors (just the properties) which have a columns property
			while (preg_match_all('/\{([^\}]*(columns\:\s*('.$i.'!?)\s*\;).*?)\}/sx', $css, $match)) {
			
				// For each of the selectors with columns properties...
				foreach ($match[0] as $key => $properties)
				{
					$styles = "";
					
					$properties 		= $match[1][0]; // First match is all the properties				
					$columnsproperty 	= $match[2][0]; // Second match is just the columns property
					$numberofcolumns	= $match[3][0]; // Third match is just number of columns
					
					
					// If there is an ! after the column number, we don't want the properties included.
					if (substr($numberofcolumns, -1) == "!") {
						$showproperties = false;
					}
					else {
						$showproperties = true;
					}
			
					// Calculate the width of the column
					$width = (($settings['columnwidth']*$i)-$settings['gutterwidth']);
										
					// Send the properties through the functions to get the padding and border from them  
					$padding 	= $this -> getPadding($properties);
					$border 	= $this -> getBorder($properties);
					
					// Only factor in padding and border if it the selector has them
					if ($padding > 0 || $border > 0)
					{				
						// If the browser doesn't support box-sizing, minus the padding and border
						if
						(
							isset($flags['IE6']) ||
							isset($flags['IE7'])
						)
						{
							// Calculate the width of the column with adjustments for padding and border
							$width = $width - ($padding + $border);
						}
						else
						{
							// Add box sizing for the browsers that support it. (Everything greater than IE7)
							$styles .= "box-sizing:border-box;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;";
						}
					}
										
					// Create the width property
					$styles .= "width:" . $width . "px;";
					
					if ($showproperties) 
					{
						$styles .= "float:left;"; 
						
						if($this->flags['IE6'] === true)
						{
							$styles .= "display:inline;overflow:hidden;";
						}
						
						if ($numberofcolumns < $settings['columncount'])
						{
							$styles .= "margin-right:" . $settings['gutterwidth'] . "px;";
						}
					}
					
					// Insert into property string
					$newproperties = str_replace($columnsproperty, $styles, $properties);

					// Insert this new string into CSS string
					$css = str_replace($properties, $newproperties, $css);
				
				}
			}
		}
		return $css;
	}
	
	private function getParam($name, $gridsettings)
	{		
		// Make the settings regex-friendly
		$name = str_replace('-','\-', $name);
		
		if (preg_match_all('/'.$name.'\:.+?\;/x', $gridsettings, $matches))
		{
			// Strip the name and leave the value so the value can be anything
			$result = preg_replace('/'.$name.'|\:|\;| /', '', $matches[0][0]);
			
			// Remove quotes
			$result = preg_replace('/\'|\"/', '', $result);
			
			return $result;
		}
	}
	
	public function removeSettings($css)
	{
		$css = preg_replace('/\@grid\s*\{.*?\}/sx', '', $css);
		return $css;
	}

	
	public function replaceGridVariables($css) 
	{	
		global $settings;
		
		// Replace grid(xcol)
		if (preg_match_all('/grid\((\d+)?col\)/', $css, $matches))
		{
			foreach ($matches[1] as $key => $number)
			{
				$colw = ($number * $settings['columnwidth']) - $settings['gutterwidth'] .'px';		
				$css = str_replace($matches[0][$key],$colw,$css);
			}
		}
		
		// Replace grid(max)
		$maxw = ($settings['columncount'] * $settings['columnwidth']) - $settings['gutterwidth'] .'px';
		$css = str_replace('grid(max)',$maxw,$css);
		
		// Replace grid(baseline)
		$bl = $settings['baseline'].'px';		
		$css = str_replace('grid(baseline)', $bl, $css);
		
		// Replace grid(gutter)
		$gutter = $settings['gutterwidth'].'px';		
		$css = str_replace('grid(gutter)', $gutter, $css);
		
		// Send it all back
		return $css;
	}
	
	
	private function getColumnWidth() 
	{
		global $settings;
		
		$grossgridwidth		= $settings['gridwidth'] - ($settings['gutterwidth'] * ($settings['columncount']-1)); /* Width without gutters */
		$singlecolumnwidth 	= $grossgridwidth/$settings['columncount'];
		$columnwidth 		= $singlecolumnwidth + $settings['gutterwidth'];

		return $columnwidth;
	}
	
	private function getPadding($properties)
	{
		$padding = $paddingleft = $paddingright = 0;
		
		// Get the padding (in its many different forms)
		// This gets it in shorthand
		if (preg_match_all('/padding\:.+?\;/x', $properties, $matches))
		{

			$padding = str_replace(';','',$matches[0][0]);
			$padding = str_replace('padding:','',$padding);
			$padding = str_replace('px','',$padding);
			$padding = preg_split('/\s/', $padding);
			if (sizeof($padding) == 1)
			{
				$paddingright = $padding[0];
				$paddingleft = $padding[0];
			} 
			elseif (sizeof($padding) == 2 || sizeof($padding) == 3)
			{
				$paddingleft = $padding[1];
				$paddingright = $padding[1];
			}
			elseif (sizeof($padding) == 4)
			{
				$paddingright = $padding[1];
				$paddingleft = $padding[3];
			}
		}
		if (preg_match_all('/padding\-left\:.+?\;/x', $properties, $paddingl))
		{
			$paddingleft =  $paddingl[0][0];
			$paddingleft = str_replace(' ', '', $paddingleft);
			$paddingleft = str_replace('padding-left:', '', $paddingleft);
			$paddingleft = str_replace('px', '', $paddingleft);
			$paddingleft = str_replace(';', '', $paddingleft);

		}
		if (preg_match_all('/padding\-right\:.+?\;/x', $properties, $paddingr))
		{
			$paddingright =  $paddingr[0][0];
			$paddingright = str_replace(' ', '', $paddingright);
			$paddingright = str_replace('padding-right:', '', $paddingright);
			$paddingright = str_replace('px', '', $paddingright);
			$paddingright = str_replace(';', '', $paddingright);
		}

		$padding = $paddingleft + $paddingright;
		return $padding;
		
	}
	
	private function getBorder($properties)
	{		
	
		$border = 0;
		$borderleft = 0;
		$borderright = 0;
				
		if (preg_match_all('/border\:.+?\;/x', $properties, $matches))
		{
			if (preg_match_all('/\d.?px/', $matches[0][0], $match))
			{
				$borderw = $match[0][0];
				$borderw = str_replace('px','',$borderw);
				
				$borderleft = $borderw;
				$borderright = $borderw;
			}
		}	
		if (preg_match_all('/border\-left\:.+?\;/x', $properties, $matches))
		{
			if (preg_match_all('/\d.?px/', $matches[0][0], $match))
			{
				$borderleft = $match[0][0];
				$borderleft = str_replace('px','',$borderleft);
			}
		}
		
		if (preg_match_all('/border\-right\:.+?\;/x', $properties, $matches))
		{
			if (preg_match_all('/\d.?px/', $matches[0][0], $match))
			{
				$borderright = $match[0][0];
				$borderright = str_replace('px','',$borderright);
			}
		}
			
		$border = $borderleft + $borderright;
		return $border;
		
	}

}

?>