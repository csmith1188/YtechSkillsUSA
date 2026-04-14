<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorAddonType_BGAddon extends UELM_UniteCreatorAddonType{
	
	/**
	 * init the addon type
	 */
	protected function init(){
		 
		$this->typeName = UELM_GlobalsUC::ADDON_TYPE_BGADDON;
		$this->textSingle = __("Background Widget", "unlimited-elements");
		$this->textPlural = __("Background Widgets", "unlimited-elements");
		$this->textShowType = $this->textSingle;
		$this->titlePrefix = $this->textSingle." - ";
		$this->isBasicType = false;
		$this->allowWebCatalog = true;
		$this->allowManagerWebCatalog = true;
		$this->catalogKey = $this->typeName;
		$this->allowNoCategory = false;
		$this->defaultCatTitle = "Main";

		$this->browser_textBuy = esc_html__("Go Pro", "unlimited-elements");
		$this->browser_textHoverPro = __("Upgrade to PRO version <br> to use this widget", "unlimited-elements");
		$this->browser_urlPreview = "https://unlimited-elements.com/widget-preview/?widget=[name]";
		
		$urlLicense = UELM_GlobalsUnlimitedElements::LINK_BUY;
		
		$this->browser_urlBuyPro = $urlLicense;
		
		$responseAssets = UELM_UniteProviderFunctionsUC::setAssetsPath("ac_assets", true);
		
		$this->pathAssets = $responseAssets["path_assets"];
		$this->urlAssets = $responseAssets["url_assets"];
		
		$this->addonView_urlBack = UELM_HelperUC::getViewUrl(UELM_GlobalsUnlimitedElements::VIEW_BACKGROUNDS);
		$this->addonView_showSmallIconOption = false;		
		$this->isBackground = true;
		
	}
	
	
}
