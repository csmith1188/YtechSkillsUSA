<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorSettingsView{
	
	const SETTINGS_KEY_GENERAL_SETTINGS = "general_settings";
	
	protected $showHeader = true;
	protected $headerTitle = null;
	protected $saveAction = null;
	
	protected $isModeCustomSettings = false;	//any custom settings
	protected $customSettingsKey = null;
	protected $customSettingsXmlFile = null;
	
	protected $objSettings = null;
	protected $textButton = null;
	
	
	/**
	 * function for override
	 */
	protected function drawAdditionalTabs(){}
	
	protected function drawAdditionalTabsContent(){}
	
	
	/**
	 * draw save settings button
	 */
	protected function drawSaveSettingsButton($prefix){
		
		$buttonText = $this->textButton;
		if(empty($buttonText))
			$buttonText = esc_html__("Save Settings", "unlimited-elements");
		
		$addParams = "";
		if($this->isModeCustomSettings == true)
			$addParams = " data-settingskey='{$this->customSettingsKey}'";
		
		
		?>
			<div class="uc-button-action-wrapper">
			
				<a id="<?php echo esc_attr($prefix)?>_button_save_settings" data-prefix="<?php echo esc_attr($prefix)?>" <?php 
				uelm_echo( $addParams ); ?> class="unite-button-primary uc-button-save-settings" href="javascript:void(0)"><?php echo esc_html($buttonText)?></a>
				
				<div style="padding-top:6px;">
					
					<span id="<?php echo esc_attr($prefix)?>_loader_save" class="loader_text" style="display:none"><?php esc_html_e("Saving...", "unlimited-elements")?></span>
					<span id="<?php echo esc_attr($prefix)?>_message_saved" class="unite-color-green" style="display:none"></span>
					
				</div>
			</div>
			
			<div class="unite-clear"></div>
			
			<div id="<?php echo esc_attr($prefix)?>_save_settings_error" class="unite_error_message" style="display:none"></div>
		
		<?php 
	}
	
	
	/**
	 * validate that the view is inited
	 */
	private function validateInited(){
		
		if(empty($this->headerTitle))
			UELM_UniteFunctionsUC::throwError("Please init the header title variable");
		
		if($this->isModeCustomSettings == true){
			UELM_UniteFunctionsUC::validateNotEmpty($this->customSettingsKey, "Custom settings key");
			UELM_UniteFunctionsUC::validateNotEmpty($this->customSettingsXmlFile, "Custom settings xml file");
		}
		
		if(empty($this->saveAction))
			UELM_UniteFunctionsUC::throwError("Please init the save action");
		
		if(empty($this->objSettings))
			UELM_UniteFunctionsUC::throwError("Please init the settings object");
				
	}
	
	/**
	 * modify custom settings - function for override
	 */
	protected function modifyCustomSettings($settings){
		return($settings);
	}
	
	
	/**
	 * init the custom mode
	 */
	protected function initCustomMode(){
		
		$this->saveAction = "save_custom_settings";
		
		UELM_UniteFunctionsUC::validateNotEmpty($this->customSettingsXmlFile,"xml file( customSettingsXmlFile variable)");
		
		$this->objSettings = new UELM_UniteCreatorSettings();
		$this->objSettings->loadXMLFile($this->customSettingsXmlFile);
		
		$arrValues = UELM_HelperUC::$operations->getCustomSettingsValues($this->customSettingsKey);
		
		if(!empty($arrValues))
			$this->objSettings->setStoredValues($arrValues);
		
		$this->objSettings = $this->modifyCustomSettings($this->objSettings);
		
	}
	
	/**
	 * add scripts
	 */
	protected function addScripts(){
		
		UELM_HelperUC::addScript("unitecreator_admin_generalsettings", "unitecreator_admin_generalsettings");
		
	}
	
	/**
	 * display settings
	 */
	protected function display(){
		
		$this->addScripts();
		
		if($this->isModeCustomSettings == true)
			$this->initCustomMode();
		
		$this->validateInited();
				
		//show header
		if($this->showHeader == true){
			$headerTitle = $this->headerTitle;
			require UELM_HelperUC::getPathTemplate("header");
		}else
			require UELM_HelperUC::getPathTemplate("header_missing");
		
		
		$objSettings = $this->objSettings;
		
		//get saps
		$arrSaps = $objSettings->getArrSaps();
	
		$formID = "uc_general_settings";
	
		$objOutput = new UELM_UniteSettingsOutputWideUC();
		$objOutput->init($objSettings);
		$objOutput->setFormID($formID);
		
		$randomString = UELM_UniteFunctionsUC::getRandomString(5, true);
		
		require UELM_HelperUC::getPathTemplate("settings");
	}
	
	
}