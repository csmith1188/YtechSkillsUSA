<?php
/*
* Plugin Name: Unlimited Elements Blocks Library
* Plugin URI: https://unlimited-elements.com
* Description: All-in-one addons pack with the best blocks for WordPress Editor, offering 100+ free blocks, templates, and tools to create stunning websites!
* Author: Unlimited Elements
* Version: 2.0.5
* Author URI: https://unlimited-elements.com/about
* Text Domain: unlimited-elements
* Domain Path: /languages
* Requires PHP: 7.4
*
* Tested up to: 6.9
*
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if(!defined("UELM_UNLIMITED_ELEMENTS_INC"))
	define("UELM_UNLIMITED_ELEMENTS_INC", true);

if(!defined("UELM_ENABLE_GUTENBERG_SUPPORT"))
	define("UELM_ENABLE_GUTENBERG_SUPPORT", true);
else{
	if(!defined("UELM_UC_BOTH_VERSIONS_ACTIVE"))
		define("UELM_UC_BOTH_VERSIONS_ACTIVE", true);
} 

if ( ! function_exists( 'uewp_fs' ) ) {
    // Create a helper function for easy SDK access.
    function uewp_fs() {
        global $uewp_fs;

        if ( ! isset( $uewp_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/provider/freemius/start.php';
            $uewp_fs = fs_dynamic_init( array(
                'id'                  => '19772',
                'slug'                => 'unlimited-elements',
                'type'                => 'plugin',
                'public_key'          => 'pk_21742241ea3d26c139002904e1322',
                'is_premium'          => false,
                'premium_suffix'      => 'Pro',
                // If your plugin is a serviceware, set this option to false.
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'unlimitedelements',
                    'contact'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $uewp_fs;
    }

    // Init Freemius.
    uewp_fs();
    // Signal that SDK was initiated.
    do_action( 'uewp_fs_loaded' );
}

	
$uelm_mainFilepath = __FILE__;
$currentFolder = dirname($uelm_mainFilepath);
$pathProvider = $currentFolder."/provider/";



try{
	if(!class_exists("UELM_GlobalsUC")) {
		$pathAltLoader = $pathProvider."provider_alt_loader.php";
		if(file_exists($pathAltLoader)){
			
			require $pathAltLoader;
		
		}else{
			require_once $currentFolder.'/includes.php';
			
			require_once  UELM_GlobalsUC::$pathProvider."core/provider_main_file.php";
		}
		
	}
    

	
}catch(Exception $e){
	$message = $e->getMessage();
	$trace = $e->getTraceAsString();
	
	echo "<br>";
	echo esc_html($message);
	echo "<pre>";
	print_r($trace);
}

