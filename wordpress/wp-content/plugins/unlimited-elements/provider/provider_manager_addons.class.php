<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorManagerAddons extends UELM_UniteCreatorManagerAddonsWork{

			
	/**
	 * check and add "imported_template_id" attribute if the addon is imported
	 */
	private function modifyCatAddons_checkImportedElementorTemplates($addons){
		
		if(empty($addons))
			return($addons);
		
		$arrImportedTemplates = UELM_HelperProviderCoreUC_EL::getImportedElementorTemplates();
		
		if(empty($arrImportedTemplates))
			return($addons);
					
		foreach($addons as $key => $addon){
			
			if(is_array($addon) == false)
				continue;
			
			$name = UELM_UniteFunctionsUC::getVal($addon, "name");
			
			$importedTemplateID = UELM_UniteFunctionsUC::getVal($arrImportedTemplates, $name);
			
			if(empty($importedTemplateID))
				continue;
				
			//add the imported attribute
			$addon["imported_templateid"] = $importedTemplateID;
			$addons[$key] = $addon;
		}
		
		
		return($addons);
	}
	
	
	/**
	 * modify category addons, function for override
	 */
	protected function modifyCatAddons($addons, $addonType){
		
		if(empty($addons))
			return($addons);
			
		if($addonType == "elementor_template")
			$addons = $this->modifyCatAddons_checkImportedElementorTemplates($addons);
				
		return($addons);
	}
	
	
	/**
	 * get current layout shortcode template
	 */
	protected function getShortcodeTemplate(){
		
		$shortcode = UELM_GlobalsProviderUC::SHORTCODE_LAYOUT;
		
		$shortcodeTemplate = "[$shortcode id=%id% title=\"%title%\"]";
		
		return($shortcodeTemplate);
	}
	
	
	/**
	 * construct the manager
	 */
	public function __construct(){
		
		parent::__construct();
		
		$urlLicense = UELM_HelperUC::getViewUrl(UELM_GlobalsUC::VIEW_LICENSE);
		$this->urlBuy = $urlLicense;
		
	}
	
	
}