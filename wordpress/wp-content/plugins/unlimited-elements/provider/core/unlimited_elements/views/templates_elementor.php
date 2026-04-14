<?php

//no direct accees
defined ('UELM_UNLIMITED_ELEMENTS_INC') or die ('restricted aceess');

require UELM_HelperUC::getPathViewObject("addons_view.class");


class UELM_UniteCreatorAddonsElementorView extends UELM_UniteCreatorAddonsView{

	protected $showButtons = true;
	protected $showHeader = false;
	protected $pluginTitle = null;


	/**
	 * get header text
	 * @return unknown
	 */
	protected function getHeaderText(){

		$headerTitle = esc_html__("Manage Templates for Elementor", "unlimited-elements");

		return($headerTitle);
	}


	/**
	 * addons view provider
	 */
	public function __construct(){
		
		if(UELM_GlobalsUnlimitedElements::$enableElementorSupport == false)
			UELM_UniteFunctionsUC::throwError("Elementor templates view not available.");
		
			
		$this->addonType = UELM_GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR_TEMPLATE;
		$this->product = UELM_GlobalsUnlimitedElements::PLUGIN_NAME;
		$this->pluginTitle = UELM_GlobalsUnlimitedElements::$pluginTitleCurrent;
		$this->headerTextInner = __("Elementor Templates", "unlimited-elements");


		parent::__construct();
	}


}


new UELM_UniteCreatorAddonsElementorView();
