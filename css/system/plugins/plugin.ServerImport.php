<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * ServerImportPlugin class
 *
 * @package Cacheer
 **/
class ServerImport extends Plugins
{
		
	function pre_process($css)
	{		
		while (preg_match_all('#@server\s+import\s+url\(([^\)]+)+\);#i', $css, $matches))
		{
			$unique = array_unique($matches[1]);
			
			foreach($unique as $i => $include)
			{
				$include = preg_replace('#^("|\')|("|\')$#', '', $include);
				if(is_dir($include))
				{
					$include_css .= load($include);
					$css = str_replace($matches[0][$i], $include_css, $css);
				}
				else
				{
					// import each file once, only import css
					if (check_type($include, array('css')))
					{	
						$include_css = load(CSSPATH. "/" .$include);
						$css = str_replace($matches[0][$i], $include_css, $css);
					}
				}
				
				// Remove any left over @server-imports
				$css = str_replace($matches[0][$i], '', $css);
			}
		}
		
		// Condense it all now so that there is less to process
		$css = trim(preg_replace('#/\*[^*]*\*+([^/*][^*]*\*+)*/#', '', $css)); // comments
		$css = trim(preg_replace('/(\s|\t)+\:/', ':', $css)); // space after properties
		$css = preg_replace('#\s+(\{|\})#', "$1", $css); // before
		$css = preg_replace('#(\{|\}|:|,|;)\s+#', "$1", $css); // after
		return $css;
	}
}