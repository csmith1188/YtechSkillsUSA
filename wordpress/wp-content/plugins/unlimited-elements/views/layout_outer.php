<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_BloxViewLayoutOuter{
	
	protected $objPageBuilder;
	protected $objLayout, $objLayouts, $layoutID, $layoutType, $isTemplate;
	
	
	/**
	 * the constructor
	 */
	public function __construct(){
		
		$this->objLayouts = new UELM_UniteCreatorLayouts();
		
		$layoutID = UELM_UniteFunctionsUC::getGetVar("id", null, UELM_UniteFunctionsUC::SANITIZE_ID);
		
		$this->isTemplate = false;
		$this->objLayout = new UELM_UniteCreatorLayout();
				
		if(!empty($layoutID)){
			$this->layoutID = $layoutID;
			$this->objLayout->initByID($layoutID);
			$this->layoutType = $this->objLayout->getLayoutType();
						
			
		}else{			//init layout type for new layout
			
			//set layout type
			$layoutType = UELM_UniteFunctionsUC::getGetVar("layout_type", null, UELM_UniteFunctionsUC::SANITIZE_KEY);
			if(!empty($layoutType)){
				
				$this->objLayouts->validateLayoutType($layoutType);
				$this->layoutType = $layoutType;
				$this->objLayout->setLayoutType($layoutType);
				
			}
			
		}

		if(!empty($this->layoutType))
			$this->isTemplate = true;
		
		
		$this->objPageBuilder = new UELM_UniteCreatorPageBuilder();
		$this->objPageBuilder->initOuter($this->objLayout);
		
	}
	
	
	/**
	 * display
	 */
	protected function display(){
							
		$this->objPageBuilder->displayOuter();
						
	}
	
}


$pathProviderLayoutOuter = UELM_GlobalsUC::$pathProvider."views/layout_outer.php";

require_once $pathProviderLayoutOuter;

new UELM_BloxViewLayoutOuterProvider();
