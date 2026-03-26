<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_UniteCreatorTestAddonView{
	
	protected $showToolbar = true;
	protected $showHeader = true;
	protected $addon;
	protected $addonID;
	protected $isPreviewMode;	
	protected $isTestData1;
	protected $textSingle, $textPlural;
	
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$this->putHtml();
	}
	
	
	/**
	 * get header text
	 */
	protected function getHeader(){
		
		$addonTitle = $this->addon->getTitle();
		
		$headerTitle = esc_html__("Test ","unlimited-elements").$this->textSingle;
		$headerTitle .= " - ".$addonTitle;
		
		return($headerTitle);
	}
	
	
	/**
	 * put header html
	 */
	protected function putHeaderHtml(){
		
		$headerTitle = $this->getHeader();
		require UELM_HelperUC::getPathTemplate("header");
		
	}
	
	/**
	 * init by addon type
	 */
	private function initByAddonType($objType){
		
		$this->textSingle = $objType->textSingle;
		$this->textPlural = $objType->textPlural;
		
	}
	
	
	/**
	 * init by addon
	 */
	private function initByAddon($addonID){
		
		if(empty($addonID))
			UELM_UniteFunctionsUC::throwError("Addon ID not given");
		
		$this->addonID = $addonID;
		
		$addon = new UELM_UniteCreatorAddon();
		$addon->initByID($addonID);
		
		$this->addon = $addon;
		
		$objType = $addon->getObjAddonType();
		
		$this->initByAddonType($objType);
		
	}
	
	
	/**
	 * put html
	 */
	private function putHtml(){
		
		//UELM_HelperHtmlUC::putAddonTypesBrowserDialogs();
		
		$addonID = UELM_UniteFunctionsUC::getGetVar("id","",UELM_UniteFunctionsUC::SANITIZE_ID);
		
		$this->initByAddon($addonID);
		
		$objAddons = new UELM_UniteCreatorAddons();
		
		$isNeedHelperEditor = $objAddons->isHelperEditorNeeded($this->addon);
		
		
		$addonTitle = $this->addon->getTitle();
		
		$addonType = $this->addon->getType();
		$objAddonType = $this->addon->getObjAddonType();
		
		$urlEditAddon = UELM_HelperUC::getViewUrl_EditAddon($addonID);
		
		$urlTestWithData = UELM_HelperUC::getViewUrl_TestAddon($addonID, "loaddata=test");
		
		//init addon config
		$addonConfig = new UELM_UniteCreatorAddonConfig();
		$addonConfig->setStartAddon($this->addon);
		
		$this->isTestData1 = $this->addon->isTestDataExists(1);
		
		//get addon data
		$addonData = null;
		$isLoadData = UELM_UniteFunctionsUC::getGetVar("loaddata","",UELM_UniteFunctionsUC::SANITIZE_NOTHING);
		
		if($isLoadData == "test" && $this->isTestData1 == true)
			$addon->setValuesFromTestData(1);
		
		$isPreviewMode = UELM_UniteFunctionsUC::getGetVar("preview","",UELM_UniteFunctionsUC::SANITIZE_KEY);
		$isPreviewMode = UELM_UniteFunctionsUC::strToBool($isPreviewMode);
		
		$addonConfig->startWithPreview($isPreviewMode);
		
		$this->isPreviewMode = $isPreviewMode;
		
		require UELM_HelperUC::getPathTemplate("test_addon");
				
	}
	
	
}


$pathProviderAddon = UELM_GlobalsUC::$pathProvider."views/test_addon.php";

if(file_exists($pathProviderAddon) == true){
	require_once $pathProviderAddon;
	new UELM_UniteCreatorTestAddonViewProvider();
}
else{
	new UELM_UniteCreatorTestAddonView();
}
