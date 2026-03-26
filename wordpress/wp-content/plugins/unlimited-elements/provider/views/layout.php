<?php

defined('UELM_UNLIMITED_ELEMENTS_INC') or die;

class UELM_AddonLibraryViewLayoutProvider extends UELM_AddonLibraryViewLayout{
	
	
	/**
	 * add toolbar
	 */
	function __construct(){
		parent::__construct();
		
		$this->shortcodeWrappers = "wp";
		$this->shortcode = "blox_layout";
				
		$this->display();
	}
	
	
}