<?php

defined('UELM_UNLIMITED_ELEMENTS_INC') or die;

class UELM_UniteCreatorLayoutPreviewProvider extends UELM_UniteCreatorLayoutPreview{


	/**
	 * constructor
	 */
	public function __construct(){

		$this->showHeader = true;
		
		parent::__construct();
				
		$this->display();
	}
	
}