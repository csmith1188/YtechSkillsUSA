<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


require UELM_HelperUC::getPathViewObject("settings_view.class");

class UELM_UniteCreatorViewElementorSettings extends UELM_UniteCreatorSettingsView{


	/**
	 * modify custom settings - function for override
	 */
	protected function modifyCustomSettings($objSettings){

		$objSettings = UELM_HelperProviderUC::modifyGeneralSettings_memoryLimit($objSettings);

		if(UELM_GlobalsUnlimitedElements::$enableGoogleAPI == false){
			
			$objSettings->hideSetting("google_connect_heading");
			$objSettings->hideSetting("google_connect_desc");
			$objSettings->hideSetting("google_connect_integration");
			
			$objSettings->hideSetting("google_api_heading");
			$objSettings->hideSetting("google_api_key");
		}

		if(UELM_GlobalsUnlimitedElements::$enableWeatherAPI == false){
			$objSettings->hideSetting("openweather_api_heading");
			$objSettings->hideSetting("openweather_api_key");
		}

		if(UELM_GlobalsUnlimitedElements::$enableCurrencyAPI == false){
			$objSettings->hideSetting("exchangerate_api_heading");
			$objSettings->hideSetting("exchangerate_api_key");
		}

		$isWpmlExists = UELM_UniteCreatorWpmlIntegrate::isWpmlExists();

		//enable wpml integration settings
		if($isWpmlExists == true){

			$objSettings->updateSettingProperty("wpml_heading", "hidden", "false");
			$objSettings->updateSettingProperty("wpml_button", "hidden", "false");

		}

		if(UELM_GlobalsUC::$isProVersion == false || UELM_GlobalsUnlimitedElements::$enableLimitProFunctionality == false)
			$objSettings->hideSetting("edit_pro_settings");

		return($objSettings);
	}


	/**
	 * constructor
	 */
	public function __construct(){

		$this->headerTitle = esc_html__("General Settings", "unlimited-elements");
		$this->isModeCustomSettings = true;
		$this->customSettingsXmlFile = UELM_HelperProviderCoreUC_EL::$filepathGeneralSettings;
		$this->customSettingsKey = "uelm_general_settings";


		//set settings
		$this->display();
	}


}

new UELM_UniteCreatorViewElementorSettings();
