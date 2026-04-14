<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorAddonType_Elementor extends UELM_UniteCreatorAddonType{
	
	
	/**
	 * init the addon type
	 */
	protected function init(){
		
		parent::init();
		
		$isGutenbergPlugin = UELM_GlobalsUnlimitedElements::$isGutenbergOnly;
		
		$this->typeName = UELM_GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR;
		
		if($isGutenbergPlugin == true)
			$this->typeNameCorrection = "gutenberg";
		
		$this->isBasicType = false;
		
		$this->allowWebCatalog = true;
		$this->allowManagerWebCatalog = true;
		$this->catalogKey = "addons";
		$this->arrCatalogExcludeCats = array("basic");
		
		//can't translate here - before init
		
		$this->textPlural = "Widgets";
		$this->textSingle = "Widget";
		$this->textShowType = "Elementor Widget";
		
		if($isGutenbergPlugin == true){
			$this->textPlural = "Blocks";
			$this->textSingle = "Block";
			$this->textShowType = "Gutenberg Blocks";
		}
		
		$this->browser_textBuy = "Go Pro";
		$this->browser_textHoverPro = "Upgrade to PRO version <br> to use this widget";
		$this->browser_urlPreview = "https://unlimited-elements.com/widget-preview/?widget=[name]";
		
		$urlLicense = "https://unlimited-elements.com/pricing/";
				
		$urlBuyInsidePlugin = admin_url("admin.php?".UELM_GlobalsUnlimitedElements::SLUG_BUY_BROWSER);
				
		$this->browser_urlBuyPro = $urlBuyInsidePlugin;
		
		$responseAssets = UELM_UniteProviderFunctionsUC::setAssetsPath("ac_assets", true);
		
		$this->pathAssets = UELM_UniteFunctionsUC::getVal($responseAssets, "path_assets");
		$this->urlAssets = UELM_UniteFunctionsUC::getVal($responseAssets, "url_assets");
		
		$this->addonView_urlBack = UELM_HelperUC::getViewUrl(UELM_GlobalsUnlimitedElements::VIEW_ADDONS_ELEMENTOR);
		$this->addonView_showSmallIconOption = false;		
	}
	
	
}
