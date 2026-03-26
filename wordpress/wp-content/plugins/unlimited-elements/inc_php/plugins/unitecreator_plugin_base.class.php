<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * plugin base
 *
 */
class UELM_UniteCreatorPluginBase extends UELM_UniteCreatorFilters{
	
	protected $urlPlugin;
	protected $pathPlugin;
	
	private $isRegistered = false;
	private $objPlugins;
	
	
	/**
	 * set path and url plugin
	 */
	private function initPaths($pathPlugin){
		
		if(empty($pathPlugin))
			return(false);
			
		$this->pathPlugin = $pathPlugin;
		$this->urlPlugin = UELM_HelperUC::pathToFullUrl($this->pathPlugin);
		
		//autoregister views path
		$pathViews = $this->pathPlugin."views/";
		if(is_dir($pathViews))
			$this->registerAdminViewPath($pathViews);
		
	}
	
	
	/**
	 * constructor
	 */
	public function __construct($pathPlugin = null){
		
		$this->initPaths($pathPlugin);
		
		$this->objPlugins = new UELM_UniteCreatorPlugins();
		
		//actions
		UELM_UniteProviderFunctionsUC::addAction(UELM_GlobalsProviderUC::ACTION_RUN_ADMIN, array($this,"runAdmin"));
		UELM_UniteProviderFunctionsUC::addAction(UELM_GlobalsProviderUC::ACTION_RUN_FRONT, array($this,"runFront"));
		
	}
	
	
	/**
	 * validate that the plugin is registered
	 */
	protected function validateRegistered(){
		
		if($this->isRegistered == false)
			UELM_UniteFunctionsUC::throwError("The plugin is not registered");
	}
	
	
	/**
	 * register the plugin
	 */
	protected function register($name, $title, $version, $description, $params){
		
		$this->objPlugins->registerPlugin($name, $title, $version, $description, $params);
	}
	
	
	/**
	 * add action
	 */
	protected function addAction($tag, $function_to_add, $priority = 10, $accepted_args = 1){
		UELM_UniteProviderFunctionsUC::addAction($tag, array($this,$function_to_add),$priority, $accepted_args);
	}
	
	
	/**
	 * add filter
	 */
	protected function addFilter($tag, $function_to_add, $priority = 10, $accepted_args = 1){
		UELM_UniteProviderFunctionsUC::addFilter($tag, array($this,$function_to_add), $priority, $accepted_args);
	}
	
	
	/**
	 * register view alias
	 */
	public function registerViewAlias($alias, $view){
		
		UELM_GlobalsUC::$arrViewAliases[$alias] = $view;
		
	}
	
		
	/**
	 * register admin view path
	 */
	public function registerAdminViewPath($path){
		
		UELM_GlobalsUC::$arrAdminViewPaths[] = $path;
		
	}
	
	/**
	 * register ajax action function
	 * the callback should be: onStandAloneAjaxAction($found, $action, $data)
	 * should be called from admin class
	 */
	public function registerAdminAjaxActionFunction($func){
    	
		UELM_UniteProviderFunctionsUC::addFilter(UELM_UniteCreatorFilters::FILTER_ADMIN_AJAX_ACTION, $func, 10, 3);
		
	}
	
	
	/**
	 * register dataset type
	 */
	protected function registerDatasetType($type, $title, $arrQueries){
		
		$objDataset = new UELM_UniteCreatorDataset();
		$objDataset->registerDataset($type, $title, $arrQueries);
	}
	
	
	/**
	 * register custom addon type
	 */
	public function registerAddonType($typeName, $objAddonType){
		
		if(isset(UELM_UniteCreatorAddonType::$arrTypesCache[$typeName]))
			UELM_UniteFunctionsUC::throwError("Addon type alrady exists: $typeName");
			
		
		UELM_UniteCreatorAddonType::$arrTypesCache[$typeName] = $objAddonType;
	}
	
	/**
	 * layout output mode
	 */
	public function registerLayoutOutputMode($mode, UELM_UniteCreatorLayoutOutputConfigBase $objConfig){
		
		if(isset(UELM_UniteCreatorLayoutOutput::$arrOutputModes[$mode]))
			UELM_UniteFunctionsUC::throwError("Layout output mode already exists: ".$mode);
		
		UELM_UniteCreatorLayoutOutput::$arrOutputModes[$mode] = $objConfig;
		
		
	}

	/**
	 * run admin, function for override
	 */
	public function runAdmin(){
		
	}
	
	/**
	 * run front, function for override
	 */
	public function runFront(){
		
	}
	
	
}