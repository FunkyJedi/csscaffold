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
		if($grid->isSettings() === TRUE)
		{
		
			// Generate the grid.css
			$grid -> generateGrid($css);
			
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
		if($grid->isSettings() === TRUE)
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
		global $grid;
		
		// If there are settings, keep going
		if($grid->isSettings() === TRUE)
		{
			// Remove the settings
			$css = $grid -> removeSettings($css);
		}

		return $css;
	}
}


class GridCSS
{
	function GridCSS($css)
	{	
		global $settings;
		
		// Make sure there are settings, if so, grab them
		if (preg_match_all('/@grid.*?\}/sx', $css, $match)) 
		{			
					
			//$settings['format']			= 	$this -> getParam('format', $css);			/* newline or inline */
			$settings['format']			= "inline";
			
			$settings['columncount'] 	= 	$this -> getParam('column-count', $match[0][0]);
			$settings['columnwidth']	= 	$this -> getParam('column-width', $match[0][0]);
			$settings['gutterwidth']	= 	$this -> getParam('gutter-width', $match[0][0]);
			$settings['baseline']		=	$this -> getParam('baseline', $match[0][0]);
			
			//$settings['keep-settings']	=	$this -> getParam('keep-settings', $css);	/* yes or no */
			$settings['keep-settings']	= "no";
			
			//$settings['generate-path']	=	$this -> getParam('generate-path', $css); MAKE SURE IT HAS A GENERATE PATH
			
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
		
	}
	
	public function isSettings()
	{
		global $settings;
		
		if($settings === FALSE)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}
	
	public function generateGrid($css)
	{
		global $settings;
		global $generated_dir;
		
		// Make the .columns-x classes
		for ($i=1; $i < $settings['columncount'] + 1; $i++) { 
			$w = $settings['columnwidth'] * $i - $settings['gutterwidth'];\
			$s .= "  .columns-$i \t{ width:".$w."px; }\n";
		}
	
		
		// Add an extra line to clean it up
		$s .= "\n";
		
		// Make the .push classes
		for ($i=1; $i < $settings['columncount']; $i++) { 
			$w = $settings['columnwidth'] * $i;
			$s .= "  .push-$i \t{ margin-left: ".$w."px; }\n";
			$pushselectors .= ".push-$i,";
		}
		$s .= $pushselectors . "{ float:right; position:relative; }\n\n";
		
		// Add an extra line to clean it up
		$s .= "\n";
		
		
		// Make the .pull classes
		for ($i=1; $i < $settings['columncount']; $i++) { 
			$w = $settings['columnwidth'] * $i;
			$s .= "  .pull-$i \t{ margin-right:".$w."px; }\n";
			$pullselectors .= ".pull-$i,";
		}
		$s .= $pullselectors . "{ float:left; position:relative; }\n\n";
		
		// Make the .baseline-x classes
		for ($i=1; $i < 51; $i++) { 
			$h = $settings['baseline'] * $i;
			$s .= "  .baseline-$i \t{ height:".$h."px; }\n";
		}
		
		// Make the .baseline-pull-x class
		for ($i=1; $i < 51; $i++) { 
			$h = $settings['baseline'] * $i;
			$s .= "  .baseline-pull-$i \t{ margin-top:-".$h."px; }\n";
		}
		
		// Make the .baseline-push-x classes
		for ($i=1; $i < 51; $i++) { 
			$h = $settings['baseline'] * $i;
			$s .= "  .baseline-push-$i \t{ margin-bottom:-".$h."px; }\n";
		}
		
		// Make the .append classes
		for ($i=1; $i < $settings['columncount']; $i++) { 
			$w = $settings['columnwidth'] * $i;
			$s .= "  .append-$i \t{ padding-right:".$w."px; }\n";
		}
		
		// Make the .prepend classes
		for ($i=1; $i < $settings['columncount']; $i++) { 
			$w = $settings['columnwidth'] * $i;
			$s .= "  .prepend-$i \t{ padding-left:".$w."px; }\n";
		}
		
		// Open the file relative to /css/
		$file = fopen($generated_dir."/grid.css", "w") or die("Can't open the grid.css file");
		
		// Write the string to the file
		chmod($file, 777);
		fwrite($file, $s);
		fclose($file);
	}
	
	public function generateGridImage($css)
	{
		global $settings;
		
		$image = ImageCreate($settings['columnwidth'], $settings['baseline']);
		
		$colorWhite	= ImageColorAllocate($image, 255, 255, 255);
		$colorGrey		= ImageColorAllocate($image, 200, 200, 200);
		$colorBlue		= ImageColorAllocate($image, 240, 240, 255);
		
		Imagefilledrectangle($image, 0, 0, ($settings['columnwidth'] - $settings['gutterwidth']), ($settings['baseline'] - 1), $colorBlue);
		Imagefilledrectangle($image, ($settings['columnwidth'] - $settings['gutterwidth'] + 1), 0, $settings['columnwidth'], ($settings['baseline'] - 1), $colorWhite);
	
		imageline($image, 0, ($settings['baseline'] - 1 ), $settings['columnwidth'], ($settings['baseline'] - 1 ), $colorGrey);
		
	    ImagePNG($image,"../css/assets/backgrounds/grid.png") or die("Can't save the grid.png file");
	    ImageDestroy($image);
	}
	
	public function generateLayoutXML($css)
	{
		global $settings;
		
		$list = "<layouts>\n";
		$layoutnames = array();

		if(preg_match_all('/\.layout\-(\w*)/',$css,$matches))
		{
			foreach($matches[1] as $match)
			{
				array_push($layoutnames, $match);
			}					
		}
		
		$layouts = array_unique($layoutnames);
		
		foreach($layouts as $layout)
		{
			$node = "<layout>".$layout."</layout>\n";
			$list .= $node;
		}
		
		$list .= "\n</layouts>";
		
		// Open the file
		$file = fopen("assets/snippets/layouts.xml", "w") or die("Can't open the xml file");
		
		// Write the string to the file
		chmod($file, 777);
		fwrite($file, $list);
		fclose($file);
		
	}
	
	public function buildGrid($css) 
	{	
		global $settings;
		
		$css = $this -> replaceGridVariables($css);
		$css = $this -> replaceColumns($css);

		return $css;
	}
	 
	public function freezeGrid()
	{
		// Remove the @grid settings 
		$css = preg_replace('/\/\*.+\@grid.+?\*\//s','',$css);

		//Remove all the grid(col) variable stores
		while (preg_match_all('/\/\*.?grid\(\d+col\).?\*\//', $css, $match))
		{
			foreach ($match[0] as $key => $properties)
			{
				$css = str_replace($match[0][0], '', $css);
			}
		}

		//Remove all the grid(gutter) variable stores
		$css = preg_replace('/\/\*.?grid\(gut\).?\*\//', '', $css);

		// Removes all the /*grid(cols:x;)*/ 
		while (preg_match_all('/\/\*.?grid.?\(.?cols.?\:.?\d+.?\;.?\).?\*\//', $css, $match))
		{
			foreach ($match[0] as $key => $properties)
			{
				$css = str_replace($match[0][0], '', $css);
			}
		}

		while (preg_match_all('/\/\*.?grid.?\(.?end.?\).?\*\//', $css, $match))
		{
			foreach ($match[0] as $key => $properties)
			{
				$css = str_replace($match[0][0], '', $css);
			}
		}

		return $css;
	}
	
	public function restoreGrid()
	{
		global $settings;
		
		//Replace grid(xcol)
		while (preg_match_all('/\d*px\/\*grid\(\d+\)\*\//', $css, $match))
		{
			foreach ($match[0] as $key => $properties)
			{
				$s = $match[0][0];  // 23px/*grid(1)*/
				
				// Get the original variable
				preg_match('/grid.+\)/', $s, $var); 
				$original = $var[0]; // grid(1)
				$original = str_replace(')', 'col)',$original); 
				
				// Get rid of the variable
				// $s = preg_replace('/\/\*.+\*\//','',$s);
				
				$css = str_replace($match[0][0], $original, $css);

			}
			
		}
		
		//Restore grid(gutter)
		$css = preg_replace('/'.$settings['gutterwidth'].'px.?\/\*.?grid\(gut\).?\*\//', 'grid(gutter)', $css);
		
		// Restore columns:x;
		while (preg_match_all('/\/\*grid\(cols\:\d+\;\)\*\/.+?\/\*grid\(end\)\*\//s', $css, $match))
		{
			foreach ($match[0] as $key => $properties)
			{
				$s = $match[0][0];
				$s = str_replace("\n","",$s);
				
				// Get the original variable
				preg_match('/cols:\d+\;/', $s, $var);
				$original = $var[0];
				$original = str_replace('cols','columns',$original);// 'columns' was causing issues for some reason
				
				$css = str_replace($match[0][0], $original, $css);
			}
			
		}
		
		return $css;
	}

	public function replaceColumns($css)
	{
		global $settings;
		
		// We'll loop through each of the columns properties by looking for each columns:x; property.
		// This means we'll only loop through $columnscount number of times which could be better
		// or worse depending on how many columns properties there are in your css
		
		for ($i=1; $i <= $settings['columncount']; $i++) { 
		
			// Matches all selectors (just the properties) which have a columns property
			while (preg_match_all('/\{([^\}]*(columns\:\s*('.$i.'!?)\s*\;).*?)\}/sx', $css, $match)) {
			
				// For each of the selectors with columns properties...
				foreach ($match[0] as $key => $properties)
				{
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
			
					// Send the properties through the functions to get the padding and border from them  
					$padding 	= $this -> getPadding($properties);
					$border 	= $this -> getBorder($properties);
			
					// Add it all together to get extra width
					$extrawidth = $padding + $border;
			
					// Calculate the width of the column with adjustments for padding and border
					$width = (($settings['columnwidth']*$i)-$settings['gutterwidth'])-$extrawidth;
					
					// Create the properties
					$styles = "width:" . $width . "px;";
					
					if ($showproperties) 
					{
						$styles .= "display:inline;float:left;overflow:hidden;";
						
						if ($numberofcolumns <= $settings['columncount'])
						{
							$styles .= "margin-right:" . $settings['gutterwidth'] . "px;";
						}
					}

					// Apply some formatting and add variable comments
					if ($settings['keep-settings'] == "yes") {
						$styles = "/*grid(cols:".$numberofcolumns.";)*/". $styles . "/*grid(end)*/";
					}

					if ($settings['format'] == "newline")
					{
						$styles = str_replace("*/", "*/\n\t", $styles);
						$styles = str_replace(";", ";\n\t", $styles);
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
				
				if($settings['keep-settings'] == "yes"){
					$colw   = $colw.'/*grid('.$number.')*/';
				}
				
				$css = str_replace($matches[0][$key],$colw,$css);
			}
		}
		
		// Replace grid(max)
		$maxw = ($settings['columncount'] * $settings['columnwidth']) - $settings['gutterwidth'] .'px';
		$css = str_replace('grid(max)',$maxw,$css);
		
		// Replace grid(baseline)
		
		$bl = $settings['baseline'].'px';
		
		if($settings['keep-settings'] == "yes")
		{
			$bl  = $bl.'/*grid(base)*/';
		}
			
		$css = str_replace('grid(baseline)', $bl, $css);
		
		// Replace grid(gutter)
		
		$gutter = $settings['gutterwidth'].'px';
		
		if ($settings['keep-settings'] == "yes") {
			$gutter = $gutter.'/*grid(gut)*/';
		}
		
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