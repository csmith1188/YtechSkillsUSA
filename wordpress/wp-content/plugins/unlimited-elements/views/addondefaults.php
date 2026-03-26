<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorAddonDefaultsView{

	protected $showToolbar = true;
	protected $showHeader = true;
	protected $addon;
	protected $addonID;
	protected $isPreviewMode;
	protected $isDataExists;

	/**
	 * constructor
	 */
	public function __construct(){

		$this->putHtml();
	}

	/**
	 * get header text
	 */
	protected function getHeader(){

		$addonTitle = $this->addon->getTitle();

		$headerTitle = esc_html__("Widget Defaults", "unlimited-elements");
		$headerTitle .= " - " . $addonTitle;

		return ($headerTitle);
	}

	/**
	 * put header html
	 */
	protected function putHeaderHtml(){

		$headerTitle = $this->getHeader();
		require UELM_HelperUC::getPathTemplate("header");
	}

	/**
	 * get options
	 */
	private function getOptions($addon){

		$pathAssets = $addon->getPathAssets();

		$options = array();
		$options["path_assets"] = $pathAssets;

		return ($options);
	}

	/**
	 * put html
	 */
	private function putHtml(){

		//UELM_HelperHtmlUC::putAddonTypesBrowserDialogs();

		$addonID = UELM_UniteFunctionsUC::getGetVar("id", "", UELM_UniteFunctionsUC::SANITIZE_ID);

		if(empty($addonID))
			UELM_UniteFunctionsUC::throwError("Widget ID not given");

		$this->addonID = $addonID;

		$addon = new UELM_UniteCreatorAddon();
		$addon->setOperationType(UELM_UniteCreatorAddon::OPERATION_CONFIG);

		$addon->initByID($addonID);

		$this->addon = $addon;

		$objAddons = new UELM_UniteCreatorAddons();

		$isNeedHelperEditor = $objAddons->isHelperEditorNeeded($addon);

		$addonTitle = $addon->getTitle();

		$addonType = $addon->getType();

		$objAddonType = $addon->getObjAddonType();

		$urlEditAddon = UELM_HelperUC::getViewUrl_EditAddon($addonID);

		$arrOptions = $this->getOptions($addon);
		
		//init addon config
		$addonConfig = new UELM_UniteCreatorAddonConfig();
		$addonConfig->setStartAddon($addon);
		
		$this->isDataExists = $addon->isDefaultDataExists();

		$isPreviewMode = UELM_UniteFunctionsUC::getGetVar("preview", "", UELM_UniteFunctionsUC::SANITIZE_KEY);
		$isPreviewMode = UELM_UniteFunctionsUC::strToBool($isPreviewMode);

		$addonConfig->setSourceAddon();
		$addonConfig->startWithPreview($isPreviewMode);
		$addonConfig->disableFontsPanel();

		$this->isPreviewMode = $isPreviewMode;

		$isNew = UELM_UniteFunctionsUC::getGetVar("new", "false", UELM_UniteFunctionsUC::SANITIZE_KEY);
		$isNew = UELM_UniteFunctionsUC::strToBool($isNew);

		if($isNew === true)
			require UELM_HelperUC::getPathTemplate("addon_defaults_new");
		else
			require UELM_HelperUC::getPathTemplate("addon_defaults");
	}

}

new UELM_UniteCreatorAddonDefaultsView();
