<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/**
 * The class name
 * @var string
 */
$plugin_class = 'Browsers';

/**
 * Include the settings
 */
include $config['system_dir'] . '/config/plugins/browsers.config.php';

/**
 * Browsers class
 *
 * @package csscaffold
 **/
class Browsers extends CacheerPlugin
{	
	/**
	 * Construct function
	 *
	 * @return void
	 **/
	function Browsers()
	{
		$ua = parse_user_agent($_SERVER['HTTP_USER_AGENT']);
		
		if($ua['browser'] == 'ie' && $ua['version'] == 7.0)
		{
			$this->flags['IE7'] = true;
		}
		elseif($ua['browser'] == 'ie' && $ua['version'] == 6.0)
		{
			$this->flags['IE7'] = true;
		}
		elseif($ua['browser'] == 'ie' && $ua['version'] == 8.0)
		{
			$this->flags['IE8'] = true;
		}
		
		elseif($ua['browser'] == 'applewebkit' && $ua['version'] >= 525)
		{
			$this->flags['Safari3'] = true;
		}
		elseif($ua['browser'] == 'applewebkit' && $ua['version'] >= 528)
		{
			$this->flags['Safari4'] = true;
		}
		
		elseif($ua['browser'] == 'firefox' && $ua['version'] >= 2)
		{
			$this->flags['Firefox2'] = true;
		}
		elseif($ua['browser'] == 'firefox' && $ua['version'] >= 3)
		{
			$this->flags['Firefox3'] = true;
		}
		
		elseif($ua['browser'] == 'opera')
		{
			$this->flags['Opera'] = true;
		}
		
		else
		{
			$this->flags['UnknownBrowser'] = true;
		}		
	}

	/**
	 * pre_process function
	 *
	 * @return $css
	 **/
	function pre_process($css)
	{
		global $options;		
		
		if (isset($this->flags['IE7']) || isset($this->flags['IE6']))
		{
			$file 		= file_get_contents($options['Browsers']['path'] . "/ie.css");
			$css 		= $css . $file;
	
			return $css;
		}
		elseif (isset($this->flags['Safari3']))
		{
			$file 		= file_get_contents($options['Browsers']['path'] . "/safari.css");		
			$css 		= $css . $file;
			
			return $css;
		}
		elseif (isset($this->flags['Firefox3']))
		{
			$file 		= file_get_contents($options['Browsers']['path'] . "/firefox.css");
			$css 		= $css .$file;
		
			return $css;
		}
		else
		{
			return $css;
		}
	}
	
} // END Browsers

?>