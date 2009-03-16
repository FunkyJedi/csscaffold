<?php if (!defined('CSS_CACHEER')) { header('Location:/'); }

/*
If you're looking for the configuration for the plugins, check
the config/plugins folder
*/

/******************************************************************************
 Path Settings
 ******************************************************************************/
 
/**
 * CSS DIRECTORY
 *
 * REQUIRED. URL path to you css directory. No trailing slash! eg. /themes/css
 *
 * @var string
 **/
$config['css_dir'] = "";


/**
 * CSS SERVER PATH
 *
 * REQUIRED. Server path to your css directory either absolute, or relative
 * to your system folder. No trailing slash!
 *
 * @var string
 **/
$config['css_server_path'] = "..";
	
	
/**
 * SYSTEM FOLDER PATH
 *
 * Leave this BLANK unless you would like to set something other 
 * than the default system/cache/ folder.  Use a full server path with trailing slash.
 * If you change this setting, you'll probably need to change the cache path below.
 * Remember to check your .htaccess file in your CSS directory also.
 *
 * @var string
 **/
$config['system_dir'] = "";


/**
 * CACHE DIRECTORY PATH
 *
 * Leave this BLANK unless you would like to set something other 
 * than the default system/cache/ folder.  Use a full server path with trailing slash.
 *
 * @var string
 **/
$config['cache_dir'] = "";


/**
 * ASSET FOLDER PATH
 *
 * The name of your asset folder relative to your css directory. 
 * Use full server path if its out of the css directory with a trailing slash.
 *
 * @var string
 **/
$config['assets_dir'] = "assets";
	

/******************************************************************************
 System Configuration
 ******************************************************************************/
	
/**
 * CACHE LOCK
 *
 * If you lock the cache, it will never recache your css
 *
 * @var boolean
 **/	
$config['cache_lock'] = FALSE;
	
	
/**
 * SECRET WORD
 *
 * The secret word used in a url param to override settings
 *
 * @var string
 **/
$config['secret_word'] = "password";
	

/**
 * SHOW CSS HEADER INFORMATION
 *
 * Output information at the top of your cached file for debugging
 *
 * @var boolean
 **/
$config['show_header'] = TRUE;
	

/**
 * CREATE SIZE REPORT
 *
 * Creates a size report inside your cache folder
 *
 * @var boolean
 **/
$config['create_report'] = TRUE;
