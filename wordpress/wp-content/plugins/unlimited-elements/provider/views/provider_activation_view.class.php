<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorActivationViewProvider extends UELM_UniteCreatorActivationView{
	
	const ENABLE_STAND_ALONE = true;
	
	/**
	 * init by envato
	 */
	private function initByEnvato(){
				
		$this->textGoPro = esc_html__("Activate Blox Pro", "unlimited-elements");
		
		if(self::ENABLE_STAND_ALONE == true)
			$this->textGoPro = esc_html__("Activate Blox Pro - Envato", "unlimited-elements");
		
		$this->textPasteActivationKey = esc_html__("Paste your envato purchase code here <br> from the pro version item", "unlimited-elements");
		$this->textPlaceholder = esc_html__("xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx","unlimited-elements");
		
		$this->textLinkToBuy = null; 
		$this->urlPricing = null;
		
		$this->textDontHave = esc_html__("We used to sell this product in codecanyon.net <br> Activate from this screen only if you bought it there.","unlimited-elements");
		
		$this->textActivationFailed = esc_html__("You probably got your purchase code wrong", "unlimited-elements");
		$this->codeType = self::CODE_TYPE_ENVATO;
		$this->isExpireEnabled = false;
		
		
		if(self::ENABLE_STAND_ALONE == true){
			
			$urlRegular = UELM_HelperUC::getViewUrl("license");
			$htmlLink = UELM_HelperHtmlUC::getHtmlLink($urlRegular, esc_html__("Activate With Blox Key", "unlimited-elements"),"","blue-text");
			
			$this->textSwitchTo = esc_html__("Don't have Envato Activation Key? ","unlimited-elements").$htmlLink;
		}
		
		$this->textDontHaveLogin = null;
		
	}
	
	
	/**
	 * init by blox wp
	 */
	private function initByBloxWP(){
		
		$urlEnvato = UELM_HelperUC::getViewUrl("license","envato=true");
		$htmlLink = UELM_HelperHtmlUC::getHtmlLink($urlEnvato, esc_html__("Activate With Envato Key", "unlimited-elements"),"","blue-text");
		
		$this->urlPricing = "http://blox-builder.com/go-pro/";
		$this->textSwitchTo = esc_html__("Have Envato Market Activation Key? ","unlimited-elements").$htmlLink;
		
	}
	
	
	
	/**
	 * init the variables
	 */
	public function __construct(){
				
		parent::__construct();
		
		$this->textGoPro = esc_html__("Activate Blox Pro", "unlimited-elements");
		$this->writeRefreshPageMessage = false;
		
		$isEnvato = UELM_UniteFunctionsUC::getGetVar("envato", "", UELM_UniteFunctionsUC::SANITIZE_KEY);
		$isEnvato = UELM_UniteFunctionsUC::strToBool($isEnvato);
		
		if(self::ENABLE_STAND_ALONE == false)
			$isEnvato = true;
		
		if($isEnvato == true)
			$this->initByEnvato();
		else
			$this->initByBloxWP();
			
	}
	
		
}