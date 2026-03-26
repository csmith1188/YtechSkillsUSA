<?php
/**
 * @package Unlimited Elements
* @author unlimited-elements.com
* @copyright (C) 2012 Unite CMS, All Rights Reserved.
* @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
* */

if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteProviderFrontUC{
	
	private $t;
	const ACTION_FOOTER_SCRIPTS = "wp_print_footer_scripts";
	const ACTION_AFTER_SETUP_THEME = "after_setup_theme";
	const DEBUG_PAGE_QUERIES = false;
	
	
	/**
	 *
	 * add some wordpress action
	 */
	protected function addAction($action,$eventFunction,$priority=10){
		
		add_action( $action, array($this, $eventFunction), $priority );
	}
		
	/**
	 * add filter
	 */
	protected function addFilter($tag, $func, $priority = 10, $accepted_args = 1){
		
		add_filter($tag, array($this, $func), $priority, $accepted_args);
	}

	
	/**
	 * on script tag output. modify the output by options
	 */
	public function onScriptTagOutput($tag, $handle, $src){
		
		if(isset(UELM_GlobalsProviderUC::$arrJSHandlesModules[$handle])){
			
			
			//modify tag, change to module if needed
						
			$search = "type='text/javascript'";
			$replace = "type='module'";
			
			$tag = str_replace($search, $replace, $tag);
			
			$pos = strpos($tag, "type='module'");
			
			if($pos === false)
				$tag = str_replace('<script',"<script type='module'", $tag);
		}
				
		return($tag);
	}
	
	/**
	 * on template include
	 */
	public function onTemplateInclude($template){
		
		if(is_singular() == false)
			return($template);
		
		$renderTemplateID = UELM_UniteFunctionsUC::getGetVar("ucrendertemplate","",UELM_UniteFunctionsUC::SANITIZE_ID);
		
		if(empty($renderTemplateID))
			return($template);
					
		if(defined("ELEMENTOR_PATH") == false)
			return($template);
		
		$pathTemplate = UELM_HelperProviderCoreUC_EL::$pathCore."template.php";
		
		return($pathTemplate);
	}
	
	/**
	 * disable some services according to settings
	 */
	private function checkDisableServices(){

		//disable short pixel on render template
		$renderTemplateID = UELM_UniteFunctionsUC::getGetVar("ucrendertemplate","",UELM_UniteFunctionsUC::SANITIZE_ID);

		if(empty($renderTemplateID))
			return(false);
		
			
		//disable short pixel
					
		if(defined("SHORTPIXEL_AI_VERSION")){
			
			$isMultiple = UELM_UniteFunctionsUC::getGetVar("multiple","",UELM_UniteFunctionsUC::SANITIZE_TEXT_FIELD);
			$isMultiple = UELM_UniteFunctionsUC::strToBool($isMultiple);
			
			if(!defined("UELM_DONOTCDN") && $isMultiple == false)
				define("UELM_DONOTCDN",true);
		}
		
		//disable doubly
		
		if(!defined("UELM_DISABLE_DOUBLY"))		
			define("UELM_DISABLE_DOUBLY", true);
	}
	
	/**
	 * show debug post data if available
	 */
	public function onFooterDebugPostData(){
		
		$showMetaFields = UELM_HelperUC::hasPermissionsFromQuery("ucpostmetadebug");
		
		if(empty($showMetaFields))
			return(false);
		
		$isSingle = is_singular();
		
		if($isSingle == true){
			
			UELM_HelperProviderUC::showCurrentPostObjectDebug();
				
			UELM_HelperProviderUC::showCurrentPostMetaDebug();
			
			UELM_HelperProviderUC::showCurrentPostTermsDebug();

			return(false);
		}

		//if not single - show the main query
			
		UELM_HelperProviderUC::showLastQuery();
		
		
		
	}
	
	
	/**
	 * on plugins loaded
	 */
	public function onPluginsLoaded(){
		
		$this->checkDisableServices();
		
	}
	
	/**
	 * check schema
	 */
	public function onFooterCheckShowSchema(){
				
		$objSchema = new UELM_UniteCreatorSchema();
		$objSchema->showAddonSchema();
		
	}
	
	/**
	 *
	 * the constructor
	 */
	public function __construct(){
		
		$this->t = $this;
		
		do_action("uelm_addon_library_before_front_init");
		
		UELM_HelperProviderUC::globalInit();
	   	
		$this->addAction(self::ACTION_FOOTER_SCRIPTS, "onPrintFooterStyles", 1);
		$this->addAction(self::ACTION_FOOTER_SCRIPTS, "onPrintFooterScripts");
		
		$this->addAction( 'wp_head', 'onPrintHeadStyles' );
		
		//modify output <script> tag, add module to it
		$this->addFilter("script_loader_tag", 'onScriptTagOutput',10,3);
		
		//set elementor canvas accorging "GET" variable
		$this->addFilter("template_include", "onTemplateInclude",12);	//after elementor and woo
		
		$this->addAction( 'plugins_loaded', 'onPluginsLoaded' );
		
		$this->addAction( 'wp_footer', 'onFooterDebugPostData' );
		
		$this->addAction( 'wp_footer', 'onFooterCheckShowSchema' );
		
		UELM_UniteFunctionsWPUC::onFrontInit();
				
	}
	
	
	/**
	 * on print head styles
	 */
	public function onPrintHeadStyles(){
	    
	  UELM_HelperProviderUC::outputCustomStyles();	  
	}
	
	/**
	 * show queries debug
	 */
	private function showDebugQueries(){
		
		UELM_HelperProviderUC::printDebugQueries(true);
		
	}
	
	/**
	 * print footer scripts
	 */
	public function onPrintFooterStyles(){
		
		if(self::DEBUG_PAGE_QUERIES == true)
			$this->showDebugQueries();
		
		UELM_HelperProviderUC::onPrintFooterScripts(true, "css");
		
	}

	/**
	 * print footer scripts
	 */
	public function onPrintFooterScripts(){
		
		UELM_HelperProviderUC::onPrintFooterScripts(true, "js");
		
	}
	
	
		
}

?>