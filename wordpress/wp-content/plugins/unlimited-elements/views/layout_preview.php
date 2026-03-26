<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_UniteCreatorLayoutPreview{
	
	protected $showHeader = false;
	protected $showToolbar = true;
	protected $layoutID;
	protected $layout;
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$layoutID = UELM_UniteFunctionsUC::getGetVar("id", null, UELM_UniteFunctionsUC::SANITIZE_ID);
		UELM_UniteFunctionsUC::validateNotEmpty($layoutID, "Layout ID var");
		
		
		//---- other settings --- 
		
		$this->layoutID = $layoutID;
		
		$this->layout = new UELM_UniteCreatorLayout();
		$this->layout->initByID($layoutID);
				
	}
	
	
	/**
	 * get header title
	 */
	protected function getHeaderTitle(){
		
		$titleText = $this->layout->getTitle();
		
		$title = UELM_HelperUC::getText("preview_layout")." - ";
		
		return($title);
	}
	
	
	/**
	 * output layout
	 */
	protected function outputLayout($fullPage = false){
		
		UELM_HelperUC::outputLayout($this->layoutID, false, $fullPage);
		
	}
	
	
	/**
	 * display
	 */
	protected function display(){
		
		$layoutID = $this->layoutID;
		
		?>
			<div class="unite-content-wrapper unite-inputs">
					
					<div class="uc-layout-preview-wrapper">
					
						<?php 
							$this->outputLayout();							
						?>
						
						<div class="unite-clear"></div>
					</div>
					
			</div>
		
		<?php 
	}
	
	
}


$pathProviderLayout = UELM_GlobalsUC::$pathProvider."views/layout_preview.php";
require_once $pathProviderLayout;

new UELM_UniteCreatorLayoutPreviewProvider();
