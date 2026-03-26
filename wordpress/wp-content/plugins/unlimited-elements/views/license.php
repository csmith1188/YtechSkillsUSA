<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_BloxViewLicense{

	private $showHeader = true;
	
	
	/**
	 * put header html
	 */
	protected function putHeaderHtml(){
		
		$headerTitle = esc_html__(" License", "unlimited-elements");
		
		require UELM_HelperUC::getPathTemplate("header");
		
	}
	
	
	/**
	 * put the view
	 */
	public function display(){
				
		if($this->showHeader == true)
			$this->putHeaderHtml();
		else
			require UELM_HelperUC::getPathTemplate("header_missing");
		
			
		$path = UELM_HelperUC::getPathViewObject("activation_view.class");
		require_once $path;
		
		$pathProvider = UELM_GlobalsUC::$pathProviderViews."provider_activation_view.class.php";
		if(file_exists($pathProvider)){
			require_once $pathProvider;
			$objActivationView = new UELM_UniteCreatorActivationViewProvider();
			
		}else{
			$objActivationView = new UELM_UniteCreatorActivationView();
		}
		
		$webAPI = new UELM_UniteCreatorWebAPI();
		$isActive = $webAPI->isProductActive();
		
		?>
		<div class="unite-content-wrapper">
		<?php 
		
		if($isActive == true)
			$objActivationView->putHtmlDeactivate();
		else
			$objActivationView->putActivationHtml();
		
		$objActivationView->putJSInit();		
		
		?>
		</div>
		<?php 
	}
	
	
}


$objBloxViewLicense = new UELM_BloxViewLicense();
$objBloxViewLicense->display();
