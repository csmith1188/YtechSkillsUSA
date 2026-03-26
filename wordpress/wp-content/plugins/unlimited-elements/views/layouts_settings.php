<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

require	UELM_HelperUC::getPathViewObject("settings_view.class");

class UELM_UniteCreatorViewLayoutsSettings extends UELM_UniteCreatorSettingsView{
	
	
	/**
	 * constructor
	 */
	public function __construct(){

		$this->headerTitle = UELM_HelperUC::getText("layouts_global_settings");
		$this->saveAction = "update_global_layout_settings";
		$this->textButton = UELM_HelperUC::getText("save_layout_settings");
		
		//set settings object
		$this->objSettings = UELM_UniteCreatorLayout::getGlobalSettingsObject();
		
		$this->display();
	}
	
}


new UELM_UniteCreatorViewLayoutsSettings();
