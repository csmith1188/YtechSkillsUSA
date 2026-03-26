<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorSettingsMultisource{
	
	private $settings;
	private $objAddon;
	
	const TYPE_JSONCSV = "json_csv";
	const TYPE_REPEATER = "repeater";
	const TYPE_POSTS = "posts";
	const TYPE_PRODUCTS = "products";
	const TYPE_TERMS = "terms";
	const TYPE_USERS = "users";
	const TYPE_MENU = "menu";
	const TYPE_INSTAGRAM = "instagram";
	const TYPE_GALLERY = "gallery";
	
	
	
	public function __construct(){
		
		//for autocomplete
		$this->objAddon	= new UELM_UniteCreatorAddon();
		
		$this->objAddon = null;
		
	}
	
	
	/**
	 * set the settings
	 */
	public function setSettings(UELM_UniteCreatorSettings $settings){

		$this->settings = $settings;
		$this->objAddon = UELM_GlobalsProviderUC::$activeAddonForSettings;
		
	}
	
	
	/**
	 * add items multisource
	 */
	public function addItemsMultisourceSettings($name, $value, $title, $param){
		
		UELM_UniteFunctionsUC::validateNotEmpty($this->settings, "settings object");

		if(empty($this->objAddon))
			return(false);
		
		//------ items source ------
		
		$arrSource = array();
		
		$arrSource["items"] = __("Items", "unlimited-elements");
		$arrSource["posts_free"] = __("Posts (pro)", "unlimited-elements");
		
		$isWooActive = UELM_UniteCreatorWooIntegrate::isWooActive();
		if($isWooActive == true)
			$arrSource["products_free"] = __("WooCommerce Products (pro)", "unlimited-elements");
		
		$metaRepeaterTitle = __("Meta Field (pro)", "unlimited-elements");
		
		$isAcfExists = UELM_UniteCreatorAcfIntegrate::isAcfActive();
		
		if($isAcfExists == true)
			$metaRepeaterTitle = __("ACF Cutom Field (pro)", "unlimited-elements");
		
		$arrSource["repeater_free"] = $metaRepeaterTitle;
		$arrSource["json_free"] = __("JSON or CSV (pro)", "unlimited-elements");
		$arrSource["gallery_free"] = __("Gallery (pro)", "unlimited-elements");
		$arrSource["terms_free"] = __("Terms (pro)", "unlimited-elements");
		$arrSource["users_free"] = __("Users (pro)", "unlimited-elements");
		$arrSource["menu_free"] = __("Menu (pro)", "unlimited-elements");
		
		$hasInstagram = UELM_HelperProviderCoreUC_EL::isInstagramSetUp();
		
		if($hasInstagram)
			$arrSource["instagram_free"] = __("Instagram (pro)", "unlimited-elements");
		
		
		$arrSource = array_flip($arrSource);

		$params = array();
		$params["origtype"] = UELM_UniteCreatorDialogParam::PARAM_DROPDOWN;
		
		$this->settings->addSelect($name."_source", $arrSource, __("Items Source", "unlimited-elements"), "items", $params);
		
		
		//--------- message ---------- 
		
		$params = array();
		$params["origtype"] = UELM_UniteCreatorDialogParam::PARAM_STATIC_TEXT;
		$params["elementor_condition"] = array($name."_source!"=>"items");
		
		$text = __("The Multi-Source feature exists only in the PRO version. 
		<a href='https://unlimited-elements.com/pricing/' target='_blank'>Upgrade Now</a> 
		<br><br>
		To learn more about Multi-Source <a href='https://unlimited-elements.com/multi-source/' target='_blank' >Click Here</a>", "unlimited-elements");
		
		$this->settings->addStaticText($text, $name."_source_free_text", $params);
		
	}
	
	
}