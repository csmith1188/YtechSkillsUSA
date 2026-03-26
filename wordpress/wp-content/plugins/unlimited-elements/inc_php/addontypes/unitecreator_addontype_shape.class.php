<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorAddonType_Shape extends UELM_UniteCreatorAddonType{
	
	
	
	/**
	 * init the addon type
	 */
	protected function init(){
		
		$this->typeName = UELM_GlobalsUC::ADDON_TYPE_SHAPES;
		$this->textSingle = __("Shape", "unlimited-elements");
		$this->textPlural = __("Shapes", "unlimited-elements");
		$this->isSVG = true;
		$this->textShowType = $this->textSingle;
		$this->titlePrefix = $this->textSingle." - ";
		$this->isBasicType = false;
		$this->allowWebCatalog = true;
		$this->allowManagerWebCatalog = true;
		$this->catalogKey = $this->typeName;
		
	}
	
	
}
