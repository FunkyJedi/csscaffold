<?php

// All paths are relative to the css directory above your system folder

$system_dir 		= "system"; 				// Name of the system directory relative to your css folder
$ext_dir 			= "extensions/";			// Path to the extensions folder
$generated_dir 		= "frameworks/";			// Path of generated css files to your css folder
$xml_dir 			= "system/"; 				// XML Directory
$css_plugin_dir 	= 'plugins/'; 				// CSS plugins directory
$font_dir 			= "assets/fonts/"; 			// Font Directory 
$bg_dir				= "assets/backgrounds/";	// CSS background images directory
$ir_path 			= "assets/image-replacement/";

// Generate the header at the top of the CSS - useful for debugging plugins
$show_header 		= true;

// CSSTidy Options
$tidy_options = array(
	
		'preserve_css' 				=> false,
		'sort_selectors' 			=> false,
		'sort_properties' 			=> true,
		'merge_selectors' 			=> 2,
		'optimise_shorthands'		=> 1,
		'compress_colors' 			=> true,
		'compress_font-weight' 		=> false,
		'lowercase_s' 				=> true,
		'case_properties' 			=> 1,
		'remove_bslash' 			=> false,
		'remove_last_;' 			=> true,
		'discard_invalid_properties' => false,
		'css_level' 				=> 'CSS2.1',
		'timestamp' 				=> false,
		'template'					=> 'highest_compression'
	
);

// Control the order the plugins are loaded. Comment out plugins you don't want to load.
$plugin_order = array(
	
		'ServerImportPlugin',
		'Browsers'	,
		'Append',
		'BasedOnPlugin',
		'NestedSelectorsPlugin',
		'ConstantsPlugin',
		'Math',
		'Grid',
		'ImageReplacement',
		'Classes',
		'Base64Plugin',
		'CSSTidyPlugin',
		'CSS3Helper',
		'CondenserPlugin',
		//'PrettyPlugin'
);

?>