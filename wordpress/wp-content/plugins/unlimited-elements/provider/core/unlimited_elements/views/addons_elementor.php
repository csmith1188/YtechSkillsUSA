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

		$headerTitle = esc_html__("Manage Widgets for Elementor", "unlimited-elements");

		return($headerTitle);
	}


	/**
	 * addons view provider
	 */
	public function __construct(){

		$this->addonType = UELM_GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR;
		$this->product = UELM_GlobalsUnlimitedElements::PLUGIN_NAME;
		$this->pluginTitle = UELM_GlobalsUnlimitedElements::$pluginTitleCurrent;
		$this->headerTextInner = __("Widgets", "unlimited-elements");
		
		if(UELM_GlobalsUnlimitedElements::$isGutenbergOnly == true)
			$this->headerTextInner = __("Gutenberg Blocks", "unlimited-elements");
		
		parent::__construct();
	}


}


new UELM_UniteCreatorAddonsElementorView();
