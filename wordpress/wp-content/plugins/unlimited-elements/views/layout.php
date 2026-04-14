<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_AddonLibraryViewLayout{
	
	protected $showButtons = true;
	protected $isEditMode = false;
	protected $isLiveView = false;
	protected $showHeader = true;
		
	protected $objPageBuilder;
	
	
	/**
	 * the constructor
	 */
	public function __construct(){
		
		$layoutID = UELM_UniteFunctionsUC::getGetVar("id", null, UELM_UniteFunctionsUC::SANITIZE_ID);
		
		$objLayout = new UELM_UniteCreatorLayout();
		
		if($layoutID)
			$objLayout->initByID($layoutID);
		
		$this->objPageBuilder = new UELM_UniteCreatorPageBuilder();
		$this->objPageBuilder->initInner($objLayout);
		
	}
	
	
	/**
	 * get header title
	 */
	protected function getHeaderTitle(){
		
		if(empty($this->objLayout)){
			
			$title = UELM_HelperUC::getText("new_layout");
			
		}else{
			$title = UELM_HelperUC::getText("edit_layout")." - ";
			$title .= $this->objLayout->getTitle();
		}
		
		return($title);
	}
	
	
	
		
	
	/**
	 * display
	 */
	protected function display(){
				
		$this->objPageBuilder->displayInner();		
	}
	
	
}

$pathProviderLayout = UELM_GlobalsUC::$pathProvider."views/layout.php";
require_once $pathProviderLayout;
new UELM_AddonLibraryViewLayoutProvider();
