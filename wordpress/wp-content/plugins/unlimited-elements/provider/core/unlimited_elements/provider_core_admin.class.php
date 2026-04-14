<?php

class UELM_UniteProviderCoreAdminUC_Elementor extends UELM_UniteProviderAdminUC{

	private $objFeedback;

	/**
	 * the constructor
	 */
	public function __construct($uelm_mainFilepath){
		
		$this->pluginName = UELM_GlobalsUnlimitedElements::PLUGIN_NAME;
		$this->pluginTitle = UELM_GlobalsUnlimitedElements::$pluginTitleCurrent;

		
		$this->textBuy = "Activate Plugin";
		$this->linkBuy = null;

		$this->defaultAddonType = UELM_GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR;
		$this->defaultView = (UELM_GlobalsUnlimitedElements::$enableDashboard === true)
			? UELM_GlobalsUnlimitedElements::VIEW_DASHBOARD
			: UELM_GlobalsUnlimitedElements::VIEW_ADDONS_ELEMENTOR;
	
		$this->arrAllowedViews = array(
			"addons_elementor",
			"licenseelementor",
			"email-test",
			"forms-logs", 
			"requests-log",	
			"troubleshooting-overload",
			"troubleshooting-globals",
			"troubleshooting-tables",
			"troubleshooting-phpinfo",
			"troubleshooting-memory-usage",
			"troubleshooting-connectivity",
			"instagram-test",
			"testaddon",
			"testaddonnew",
			"addon",
			"addondefaults",
			"svg_shapes",
			"wpml-fields",
			"testsettings",
			UELM_GlobalsUnlimitedElements::VIEW_DASHBOARD,
			UELM_GlobalsUnlimitedElements::VIEW_BACKGROUNDS,
			UELM_GlobalsUnlimitedElements::VIEW_TEMPLATES_ELEMENTOR,
			UELM_GlobalsUnlimitedElements::VIEW_FORM_ENTRIES,
			UELM_GlobalsUnlimitedElements::VIEW_SETTINGS_ELEMENTOR,
			UELM_GlobalsUnlimitedElements::VIEW_CUSTOM_POST_TYPES,
			UELM_GlobalsUnlimitedElements::VIEW_CHANGELOG,
			UELM_GlobalsUnlimitedElements::VIEW_CHANGELOG_IMPORT,
		);

		UELM_HelperProviderCoreUC_EL::globalInit();
		$this->registerUploadMimeFilters();

		//set permission
		$permission = UELM_HelperProviderCoreUC_EL::getGeneralSetting("edit_permission");

		if($permission == "editor")
			$this->setPermissionEditor();

		parent::__construct($uelm_mainFilepath);
	}

	/**
	 * modify category settings, add consolidate addons
	 */
	public function managerAddonsModifyCategorySettings($settings, $objCat, $filterType){

		if($filterType != UELM_UniteCreatorElementorIntegrate::ADDONS_TYPE)
			return ($settings);

		$settings->updateSettingProperty("category_alias", "disabled", "true");
		$settings->updateSettingProperty("category_alias", "description", esc_html__("The category name is unchangable, because of the addons consolidation, if changed it could break the layout.", "unlimited-elements"));

		return ($settings);
	}

	/**
	 * modify plugins view links
	 */
	public function modifyPluginViewLinks($arrLinks){

		if(UELM_GlobalsUC::$isProductActive == true)
			return ($arrLinks);

		if(empty($this->linkBuy))
			return ($arrLinks);

		$linkbuy = UELM_HelperHtmlUC::getHtmlLink($this->linkBuy, $this->textBuy, "", "uc-link-gounlimited", true);

		$arrLinks["gounlimited"] = $linkbuy;

		return ($arrLinks);
	}

	/**
	 * add admin menu links
	 */
	protected function addAdminMenuLinks(){
		
		$urlMenuIcon = UELM_HelperProviderCoreUC_EL::$urlCore . "images/icon_menu.png";
		
		$mainMenuTitle = $this->pluginTitle;
		
		$verFlags = UELM_HelperUC::getActivePluginVersions();
		
		$this->addMenuPage($mainMenuTitle, "adminPages", $urlMenuIcon);

		if(UELM_GlobalsUnlimitedElements::$enableDashboard === true) {
			$this->addSubMenuPage(UELM_GlobalsUnlimitedElements::VIEW_DASHBOARD, __('Home', "unlimited-elements"), "adminPages");
		}
		
		if(UELM_GlobalsUnlimitedElements::$enableElementorSupport && UELM_GlobalsUnlimitedElements::$enableGutenbergSupport) { 
			$widgetsTitle = __('Widgets and Blocks', "unlimited-elements");
		} elseif(UELM_GlobalsUnlimitedElements::$enableGutenbergSupport) {
			$widgetsTitle = __('Blocks', "unlimited-elements");
		} else {
			$widgetsTitle = __('Widgets', "unlimited-elements");
		}
		
		$this->addSubMenuPage(UELM_GlobalsUnlimitedElements::VIEW_ADDONS_ELEMENTOR, $widgetsTitle, "adminPages");
				
		if(UELM_GlobalsUnlimitedElements::$enableElementorSupport && UELM_GlobalsUnlimitedElements::$enableGutenbergSupport) { 
			$bgWidgetsTitle = __('Background Widgets and Blocks', "unlimited-elements");
		} elseif(UELM_GlobalsUnlimitedElements::$enableElementorSupport == true) {
			$bgWidgetsTitle = __('Background Widgets', "unlimited-elements");
		} else {
			$bgWidgetsTitle = __('Background Blocks', "unlimited-elements");
		}
		
		if(UELM_HelperProviderUC::isBackgroundsEnabled() === true)
			$this->addSubMenuPage(UELM_GlobalsUnlimitedElements::VIEW_BACKGROUNDS, $bgWidgetsTitle, "adminPages");

		if($verFlags[UELM_GlobalsUC::VERSION_ELEMENTOR]) {
			$this->addSubMenuPage(UELM_GlobalsUnlimitedElements::VIEW_TEMPLATES_ELEMENTOR, __('Templates', "unlimited-elements"), "adminPages");
		}
		if(UELM_HelperProviderUC::isFormEntriesEnabled() === true)
			$this->addSubMenuPage(UELM_GlobalsUnlimitedElements::VIEW_FORM_ENTRIES, __('Form Entries', "unlimited-elements"), "adminPages");

		if(UELM_HelperProviderUC::isAddonChangelogEnabled() === true)
			$this->addSubMenuPage(UELM_GlobalsUnlimitedElements::VIEW_CHANGELOG, __('Changelog', "unlimited-elements"), "adminPages");

		$this->addSubMenuPage(UELM_GlobalsUnlimitedElements::VIEW_SETTINGS_ELEMENTOR, __('General Settings', "unlimited-elements"), "adminPages");

		if(defined("UNLIMITED_ELEMENTS_UPRESS_VERSION")){
			if(UELM_GlobalsUC::$isProductActive == false && self::$view != UELM_GlobalsUnlimitedElements::VIEW_LICENSE_ELEMENTOR)
				UELM_HelperUC::addAdminNotice("The Unlimited Elements Plugin is not active. Please activate it in license page.");

			$this->addSubMenuPage(UELM_GlobalsUnlimitedElements::VIEW_LICENSE_ELEMENTOR, __('Upress License', "unlimited-elements"), "adminPages");
		}

		$this->addLocalFilter("plugin_action_links_" . $this->pluginFilebase, "modifyPluginViewLinks");
		
		
	}

	/**
	 * allow feedback on uninstall
	 */
	private function initFeedbackUninstall(){

		$this->objFeedback = new UnlimitedElementsFeedbackUC();
		$this->objFeedback->init();
	}

	/**
	 * init
	 */
	protected function init(){

		UELM_UniteProviderFunctionsUC::addFilter(UELM_UniteCreatorFilters::FILTER_MANAGER_ADDONS_CATEGORY_SETTINGS, array($this, "managerAddonsModifyCategorySettings"), 10, 3);

		if(UELM_GlobalsUnlimitedElements::ALLOW_FEEDBACK_ONUNINSTALL === true)
			$this->initFeedbackUninstall();

		parent::init();
	}

	/**
	 * register upload mime filters based on settings
	 */
	private function registerUploadMimeFilters(){
		
		$allowedMimes = $this->getAllowedUploadMimes();
		
		if(empty($allowedMimes))
			return;
					
		add_filter("upload_mimes", function($mimes) use ($allowedMimes){
			return array_merge($mimes, $allowedMimes);
		});
		
		add_filter("wp_check_filetype_and_ext", function($data, $file, $filename, $mimes) use ($allowedMimes){
			
			$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			
			if(isset($allowedMimes[$extension])){
				$data["ext"] = $extension;
				$data["type"] = $allowedMimes[$extension];
			}
			
			return $data;
		}, 10, 4);
	}
	
	/**
	 * get allowed upload mime types from general settings
	 */
	private function getAllowedUploadMimes(){
		
		$allowedMimes = array();
		
		$allowDocuments = UELM_HelperProviderCoreUC_EL::getGeneralSetting("allow_upload_documents");
		$allowDocuments = UELM_UniteFunctionsUC::strToBool($allowDocuments);
		
		$allowZip = UELM_HelperProviderCoreUC_EL::getGeneralSetting("allow_upload_zip");
		$allowZip = UELM_UniteFunctionsUC::strToBool($allowZip);
		
		$allowSvg = UELM_HelperProviderCoreUC_EL::getGeneralSetting("allow_upload_svg");
		$allowSvg = UELM_UniteFunctionsUC::strToBool($allowSvg);
		
		if($allowDocuments === true){
			$allowedMimes = array_merge($allowedMimes, array(
				"pdf" => "application/pdf",
				"doc" => "application/msword",
				"docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
				"ppt" => "application/vnd.ms-powerpoint",
				"pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
				"xls" => "application/vnd.ms-excel",
				"xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
				"rtf" => "application/rtf",
				"odt" => "application/vnd.oasis.opendocument.text",
				"ods" => "application/vnd.oasis.opendocument.spreadsheet",
				"odp" => "application/vnd.oasis.opendocument.presentation",
			));
		}
		
		if($allowZip === true)
			$allowedMimes["zip"] = "application/zip";
		
		if($allowSvg === true)
			$allowedMimes["svg"] = "image/svg+xml";
		
		return $allowedMimes;
	}

}
