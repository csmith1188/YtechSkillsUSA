<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorAdmin extends UELM_UniteBaseAdminClassUC{

	const DEFAULT_VIEW = "addons";

	private static $isScriptsIncluded_settingsBase = false;


	/**
	 *
	 * the constructor
	 */
	public function __construct(){
				
		parent::__construct();
	}


	/**
	 *
	 * init all actions
	 */
	protected function init(){
		//some init content
	}

	/**
	 * add must scripts for any view
	 */
	public static function addMustScripts($specialSettings = ""){

		UELM_UniteProviderFunctionsUC::addScriptsFramework($specialSettings);

		//add color picker scripts
		$colorPickerType = UELM_GlobalsUC::$colorPickerType;

		switch($colorPickerType){
			case "spectrum":
				UELM_HelperUC::addScript("spectrum","unite-spectrum","js/spectrum");
				UELM_HelperUC::addStyle("spectrum","unite-spectrum","js/spectrum");
			break;
			case "farbtastic":
				UELM_HelperUC::addScript("farbtastic","unite-farbtastic","js/farbtastic");
				UELM_HelperUC::addStyle("farbtastic","unite-farbtastic","js/farbtastic");
			break;
			default:
				UELM_UniteFunctionsUC::throwError("Wrong color picker typ: ".$colorPickerType);
			break;
		}


		UELM_HelperUC::addScript("jquery.tipsy","tipsy-js");

		//font awsome - from admin always load the 5
		$urlFontAwesomeCSS = UELM_HelperUC::getUrlFontAwesome();
		UELM_HelperUC::addStyleAbsoluteUrl($urlFontAwesomeCSS, "font-awesome");

		UELM_HelperUC::addScript("settings", "unitecreator_settings");
		UELM_HelperUC::addScript("admin","unitecreator_admin");
		UELM_HelperUC::addStyle("admin","unitecreator_admin_css");

		UELM_HelperUC::addScriptAbsoluteUrl(UELM_GlobalsUC::$url_provider."assets/provider_admin.js", "provider_admin_js");
	}


	/**
	 *
	 * a must function. adds scripts on the page
	 * add scripts only if inside the plugin
	 * add all page scripts and styles here.
	 * pelase don't remove this function
	 * common scripts even if the plugin not load, use this function only if no choise.
	 */
	public static function onAddScripts(){
		
		self::addMustScripts();
		
		UELM_HelperUC::addScript("unitecreator_assets", "unitecreator_assets");
		UELM_HelperUC::addStyle("unitecreator_styles","unitecreator_css","css");

		$viewForIncludes = self::$view;
				
		//take from view aliased if exists
		if(isset(UELM_GlobalsUC::$arrViewAliases[$viewForIncludes]))
			$viewForIncludes = UELM_GlobalsUC::$arrViewAliases[$viewForIncludes];
		
		//remove third party script if exists
		UELM_UniteFunctionsWPUC::findAndRemoveInclude("selectWoo.full.min");
		
		//flatpickr
		UELM_HelperUC::addScript("flatpickr", "flatpickr_js", "js/flatpickr");
		UELM_HelperUC::addStyle("flatpickr", "flatpickr_css", "js/flatpickr");
		
		//include dropzone
		switch ($viewForIncludes){
			case UELM_GlobalsUC::VIEW_EDIT_ADDON:
			case UELM_GlobalsUC::VIEW_ASSETS:

				UELM_HelperUC::addScript("jquery.dialogextend.min", "jquery-ui-dialogextend","js/dialog_extend", true);

				//clear third party includes
				UELM_UniteFunctionsWPUC::findAndRemoveInclude("dropzone.min");

				//dropzone
				UELM_HelperUC::addScript("dropzone", "dropzone_js","js/dropzone");
				UELM_HelperUC::addStyle("dropzone", "dropzone_css","js/dropzone");

				//select 2
				UELM_HelperUC::addScript("select2.full.min", "ue_select2_js","js/select2");
				UELM_HelperUC::addStyle("select2", "ue_select2_css","js/select2");

				//include codemirror
				UELM_HelperUC::addScript("codemirror-custom.min", "codemirror_js","js/codemirror-custom");
				UELM_HelperUC::addScript("css", "codemirror_cssjs","js/codemirror-custom/mode/css");
				UELM_HelperUC::addScript("javascript", "codemirror_javascript","js/codemirror-custom/mode/javascript");
				UELM_HelperUC::addScript("xml", "codemirror_xml","js/codemirror-custom/mode/xml");
				UELM_HelperUC::addScript("htmlmixed", "codemirror_html","js/codemirror-custom/mode/htmlmixed");
				UELM_HelperUC::addScript("twig", "codemirror_twig","js/codemirror-custom/mode/twig");

				UELM_HelperUC::addScript("dialog", "codemirror_dialog","js/codemirror-custom/addon");
				UELM_HelperUC::addScript("searchcursor", "codemirror_search_cursor","js/codemirror-custom/addon");
				UELM_HelperUC::addScript("search", "codemirror_search","js/codemirror-custom/addon");
				UELM_HelperUC::addScript("multiplex", "codemirror_multiplex","js/codemirror-custom/addon");

				UELM_HelperUC::addStyle("codemirror-custom", "codemirror_css","js/codemirror-custom");
				UELM_HelperUC::addStyle("dialog", "codemirror_dialog_css","js/codemirror-custom/addon");

				UELM_HelperUC::addScript("unitecreator_includes", "unitecreator_includes");
				UELM_HelperUC::addScript("unitecreator_params_dialog", "unitecreator_params_dialog");
				UELM_HelperUC::addScript("unitecreator_params_editor", "unitecreator_params_editor");
				UELM_HelperUC::addScript("unitecreator_params_panel", "unitecreator_params_panel");
				UELM_HelperUC::addScript("unitecreator_variables", "unitecreator_variables");
				UELM_HelperUC::addScript("unitecreator_admin", "unitecreator_view_admin");

				//deregister wp scripts that conflicts

				wp_deregister_script("wp-codemirror");
				wp_deregister_style("wp-codemirror");

				wp_deregister_script("woo-variation-swatches");
				wp_deregister_style("woo-variation-swatches");

			break;
			case UELM_GlobalsUC::VIEW_TEST_ADDON:
			
				self::onAddScriptsBrowser();
				UELM_UniteCreatorManager::putScriptsIncludes(UELM_UniteCreatorManager::TYPE_ITEMS_INLINE);

				UELM_HelperUC::addScript("select2.full.min", "select2_js","js/select2");
				UELM_HelperUC::addStyle("select2", "select2_css","js/select2");

				UELM_HelperUC::addScript("unitecreator_addon_config", "unitecreator_addon_config");
				UELM_HelperUC::addStyle("unitecreator_admin_front","unitecreator_admin_front_css");
				UELM_HelperUC::addScript("unitecreator_testaddon_admin");
				UELM_HelperUC::addStyle("unitecreator_browser","unitecreator_browser_css");

			break;
			case "testaddonnew":

				self::onAddScriptsBrowser();

				UELM_UniteCreatorManager::putScriptsIncludes(UELM_UniteCreatorManager::TYPE_ITEMS_INLINE);

				self::addAddonPreviewAssets();

				UELM_HelperUC::addScript("unitecreator_testaddon_new", "unitecreator_testaddon_new");

			break;
			case UELM_GlobalsUC::VIEW_ADDON_DEFAULTS:

				self::onAddScriptsBrowser();

				UELM_UniteCreatorManager::putScriptsIncludes(UELM_UniteCreatorManager::TYPE_ITEMS_INLINE);

				self::addAddonPreviewAssets();

				UELM_HelperUC::addScript("unitecreator_addon_config", "unitecreator_addon_config");
				UELM_HelperUC::addStyle("unitecreator_admin_front", "unitecreator_admin_front_css");

				$isNew = UELM_UniteFunctionsUC::getGetVar("new", "false", UELM_UniteFunctionsUC::SANITIZE_KEY);
				$isNew = UELM_UniteFunctionsUC::strToBool($isNew);

				if($isNew === true)
					UELM_HelperUC::addScript("unitecreator_addondefaults_new", "unitecreator_addondefaults_new");
				else
					UELM_HelperUC::addScript("unitecreator_addondefaults_admin", "unitecreator_addondefaults_admin");

			break;
			case UELM_GlobalsUC::VIEW_SETTINGS:
			case UELM_GlobalsUC::VIEW_LAYOUTS_SETTINGS:

				UELM_HelperUC::addScript("unitecreator_admin_generalsettings", "unitecreator_admin_generalsettings");

			break;
			case UELM_GlobalsUC::VIEW_TEMPLATES_LIST:
			case UELM_GlobalsUC::VIEW_LAYOUTS_LIST:

				self::onAddScriptsBrowser();

				UELM_UniteCreatorManager::putScriptsIncludes(UELM_UniteCreatorManager::TYPE_PAGES);

				UELM_HelperUC::addScript("unitecreator_admin_layouts", "unitecreator_admin_layouts");

			break;
			default:
			case UELM_GlobalsUC::VIEW_ADDONS_LIST:
				UELM_UniteCreatorManager::putScriptsIncludes(UELM_UniteCreatorManager::TYPE_ADDONS);
			break;
			case "sort_pages":
			case "sort_sections":
				UELM_UniteCreatorManager::putScriptsIncludes(UELM_UniteCreatorManager::TYPE_PAGES);
			break;

		}

		//provider admin css always comes to end
		UELM_HelperUC::addStyleAbsoluteUrl(UELM_GlobalsUC::$url_provider."assets/provider_admin.css", "provider_admin_css");

		UELM_UniteProviderFunctionsUC::doAction(UELM_UniteCreatorFilters::ACTION_ADD_ADMIN_SCRIPTS);

	}


	/**
	 * add settings base options
	 */
	public static function addScripts_settingsBase($specialSettings = ""){

		//include those scripts only once
		if(self::$isScriptsIncluded_settingsBase == true)
			return(false);

		self::addMustScripts($specialSettings);

		UELM_HelperUC::addStyle("unitecreator_admin_front","unitecreator_admin_front_css");

		UELM_UniteCreatorManager::putScriptsIncludes(UELM_UniteCreatorManager::TYPE_ITEMS_INLINE);

		self::$isScriptsIncluded_settingsBase = true;
	}


	/**
	 * add scripts only for the browser
	 */
	public static function onAddScriptsBrowser(){
		self::addScripts_settingsBase();

		UELM_HelperUC::addStyle("unitecreator_browser","unitecreator_browser_css");
		UELM_HelperUC::addScript("unitecreator_browser", "unitecreator_browser");
		UELM_HelperUC::addScript("unitecreator_addon_config", "unitecreator_addon_config");
	}


	/**
	 * set globals by addon type
	 */
	public static function setAdminGlobalsByAddonType($objAddonType = null, $objAddon = null){

		if(empty($objAddonType))
			return($objAddonType);

		if(is_string($objAddonType))
			UELM_UniteFunctionsUC::throwError("The addon type should be object");

		if(!empty($objAddon)){

			UELM_GlobalsUC::$objActiveAddonForAssets = $objAddon;
		}

		$pathAssets = UELM_HelperUC::getAssetsPath($objAddonType);

		if($pathAssets != UELM_GlobalsUC::$pathAssets){

			UELM_GlobalsUC::$pathAssets = $pathAssets;

			UELM_GlobalsUC::$url_assets = UELM_HelperUC::getAssetsUrl($objAddonType);
		}

	}



	/**
	 * validate required php extensions
	 */
	private function validatePHPExtensions(){

		//check curl
		if(function_exists("curl_init") == false)
			UELM_HelperUC::addAdminNotice("Your PHP is missing \"CURL\" Extension. Blox needs this extension. Please enable it in php.ini");

	}


	/**
	 *
	 * admin main page function.
	 */
	public function adminPages(){

		$this->validatePHPExtensions();
		
		if(self::$view != UELM_GlobalsUC::VIEW_MEDIA_SELECT)
			self::setMasterView("master_view");

		self::requireView(self::$view);

	}



	/**
	 *
	 * onAjax action handler
	 */
	public static function onAjaxAction(){

		UELM_GlobalsUC::$isAjaxAction = true;

		$objActions = new UELM_UniteCreatorActions();
		$objActions->onAjaxAction();

	}

	/**
	 * add assets for the addon preview
	 */
	private static function addAddonPreviewAssets(){

		UELM_HelperUC::addScript("select2.full.min", "select2_js", "js/select2");
		UELM_HelperUC::addStyle("select2", "select2_css", "js/select2");

		UELM_HelperUC::includeUEAnimationStyles();

		UELM_HelperUC::addStyle("unitecreator_browser", "unitecreator_browser_css");
		UELM_HelperUC::addScript("unitecreator_helper", "unitecreator_helper");
		UELM_HelperUC::addScript("unitecreator_addon_preview_admin", "unitecreator_addon_preview_admin");

		$fontData = UELM_HelperUC::getFontPanelData();
		$googleFonts = UELM_UniteFunctionsUC::getVal($fontData, "arrGoogleFonts");
		$googleFontsBaseUrl = UELM_HelperHtmlUC::getGoogleFontBaseUrl();

		wp_localize_script("unitecreator_addon_preview_admin", "uelm_g_ucGoogleFonts", array(
			"fonts" => $googleFonts,
			"base_url" => $googleFontsBaseUrl,
		));
	}

}
