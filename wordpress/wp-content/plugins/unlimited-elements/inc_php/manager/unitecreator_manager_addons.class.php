<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorManagerAddonsWork extends UELM_UniteCreatorManager{

	const STATE_FILTER_CATALOG = "manager_filter_catalog";
	const STATE_FILTER_ACTIVE = "fitler_active_addons";
	const STATE_LAST_ADDONS_CATEGORY = "last_addons_cat";

	const FILTER_CATALOG_MIXED = "mixed";
	const FILTER_CATALOG_INSTALLED = "installed";
	const FILTER_CATALOG_WEB = "web";

	protected $numLocalCats = 0;
	private $filterAddonType = null;
	protected $objAddonType = null, $isLayouts = false, $enableActiveFilter = true, $enableEnterName = true;
	protected $enableSearchFilter = true;
	protected $enablePreview = true, $enableViewThumbnail = false, $enableMakeScreenshots = false;
	protected $enableDescriptionField = true, $enableEditGroup = false, $enableCopy = false;
	protected $enableActions = true;	//enable add/edit actions
	protected $enableEditAddon = true;
	
	protected $textAddAddon, $textSingle, $textPlural, $textSingleLower, $textPluralLower;

	private $filterActive = "";
	private $showAddonTooltip = false, $showTestAddon = true;

	protected $filterCatalogState;
	protected $defaultFilterCatalog;
	protected $objBrowser;
	protected $urlBuy;
	protected $pluginName;
	protected $putUpdateCatalogButton = true;
	private $urlAjax;
	private $product;		//product for web api
	private $putItemButtonsType = "multiple";
	private $isInsideParent = false;
	private $isWebCatalogMode = false;
	private $showAddonFilters = true;
	private $showAddonFilters_catalog = true;

	public static $stateLabelCounter = 0;

	/**
	 * construct the manager
	 */
	public function __construct(){

		$this->pluginName = UELM_GlobalsUC::PLUGIN_NAME;
		$this->urlAjax = UELM_GlobalsUC::$url_ajax;
		$this->hasHeaderLine = true;

	}

	/**
	 * set plugin name
	 */
	public function setPluginName($pluginName){

		$this->pluginName = $pluginName;
	}


	/**
	 * set filter active state
	 */
	public static function setStateFilterCatalog($filterCatalog, $addonType = ""){

		if(empty($filterCatalog))
			return(false);

		UELM_HelperUC::setState(self::STATE_FILTER_CATALOG, $filterCatalog);

	}

	/**
	 * get filter active statge
	 */
	protected function getStateFilterCatalog(){

		if(UELM_GlobalsUC::$enableWebCatalog == false)
			return(self::FILTER_CATALOG_INSTALLED);

		if($this->objAddonType->allowWebCatalog == false)
			return(self::FILTER_CATALOG_INSTALLED);

		if($this->objAddonType->isWebCatalogMode == true)
			return(self::FILTER_CATALOG_MIXED);

		$filterCatalog = UELM_HelperUC::getState(self::STATE_FILTER_CATALOG);
		if(empty($filterCatalog))
			$filterCatalog = $this->defaultFilterCatalog;


		return($filterCatalog);
	}


	/**
	 * set filter active state
	 */
	public static function setStateFilterActive($filterActive, $addonType = ""){

		if(empty($filterActive))
			return(false);

		UELM_HelperUC::setState(UELM_UniteCreatorManagerAddons::STATE_FILTER_ACTIVE, $filterActive);

	}

	/**
	 * get filter active statge
	 */
	public static function getStateFilterActive($addonType = ""){

		$filterActive = UELM_HelperUC::getState(UELM_UniteCreatorManagerAddons::STATE_FILTER_ACTIVE);

		return($filterActive);
	}


	private function a___________INIT________(){}

	/**
	 * validate that addon type is set
	 */
	protected function validateAddonType(){

		if(empty($this->objAddonType))
			UELM_UniteFunctionsUC::throwError("addons manager error: no addon type is set");

		if($this->objAddonType->isLayout != $this->isLayouts)
			UELM_UniteFunctionsUC::throwError("addons manager error: mismatch addon and layout types");

	}


	/**
	 * before init
	 */
	protected function beforeInit($addonType){

		try{

			UELM_HelperUC::validateDBTablesExists();
		
		}catch(Exception $e){
			UELM_UniteFunctionsUC::throwError("DB Tables don't installed. Please refresh the page.");
		}

		$this->type = self::TYPE_ADDONS;
		$this->viewType = self::VIEW_TYPE_THUMB;
		$this->defaultFilterCatalog = self::FILTER_CATALOG_INSTALLED;
		
		$this->urlBuy = UELM_GlobalsUC::$url_buy_platform;
		$this->hasCats = true;
		
		if(empty($this->filterAddonType))
			$this->setAddonType($addonType);

		$this->objBrowser = new UELM_UniteCreatorBrowser();
		$this->objBrowser->initAddonType($addonType);

		if(UELM_GlobalsUC::$is_admin_debug_mode == true)
			$this->putDialogDebug = true;

	}

	/**
	 * run after init
	 */
	protected function afterInit($addonType){

		$this->validateAddonType();

		$this->itemsLoaderText = esc_html__("Getting ","unlimited-elements").$this->textPlural;
		$this->textItemsSelected = $this->textPluralLower . esc_html__(" selected","unlimited-elements");

		if($this->enableActiveFilter == true)
			$this->filterActive = self::getStateFilterActive($addonType);

		$this->filterCatalogState = $this->getStateFilterCatalog();


		//set selected category
		$lastCatID = UELM_HelperUC::getState(self::STATE_LAST_ADDONS_CATEGORY);
		if(!empty($lastCatID))
			$this->selectedCategory = $lastCatID;

		UELM_UniteProviderFunctionsUC::doAction(UELM_UniteCreatorFilters::ACTION_MODIFY_ADDONS_MANAGER, $this);

	}

	/**
	 * init layout specific permissions
	 */
	protected function initByAddonType_layout(){

		$this->isLayouts = true;

		if($this->objAddonType->isLayout == false)
			return(false);

		$this->enableActiveFilter = false;
		$this->enableEnterName = false;
		$this->showTestAddon = false;
		$this->enablePreview = true;
		$this->enableViewThumbnail = false;
		$this->enableEditGroup = true;
		$this->enableCopy = true;

		$this->addClass = "uc-manager-layouts";

		$this->isWebCatalogMode = true;

		$this->enableActions = false;
		$this->enableCatsActions = false;
		$this->enableStatusLineOperations = false;

		UELM_UniteProviderFunctionsUC::doAction("uc_manager_init_by_layout", $this);

	}

	/**
	 * init by layout master mode
	 */
	public function initByAddonType_layoutMaster(){

		$this->isWebCatalogMode = true;
		$this->enableActions = true;
		$this->enableCatsActions = true;
		$this->enableStatusLineOperations = true;

	}

	/**
	 * init some settings by addon type
	 */
	protected function initByAddonType(){

		//svg permissions
		if($this->objAddonType->isSVG == true){
			$this->showTestAddon = false;
		}

		//layout permissions
		if($this->objAddonType->isLayout == true)
			$this->initByAddonType_layout();
		else{
			
			if(UELM_GlobalsUnlimitedElements::$enableEditWidget == false)
				$this->enableEditAddon = false;
		}

		$single = $this->objAddonType->textSingle;
		$plural = 	$this->objAddonType->textPlural;

		$pluralLower = strtolower($plural);

		$this->textSingle = $single;
		$this->textPlural = $plural;
		$this->textSingleLower = strtolower($single);
		$this->textPluralLower = strtolower($plural);

		//set text
		// translators: %s is the addon type name
		$this->arrText["confirm_remove_addons"] = sprintf(esc_html__("Are you sure you want to delete those %s?", "unlimited-elements"), $pluralLower);

		$objLayouts = new UELM_UniteCreatorLayouts();

		$this->arrOptions["is_layout"] = $this->isLayouts;
		$this->arrOptions["url_screenshot_template"] = $objLayouts->getUrlTakeScreenshot();

		$this->textAddAddon = esc_html__("Add ", "unlimited-elements") . $single;

		//set default filter
		if($this->objAddonType->allowManagerWebCatalog == true)
			$this->defaultFilterCatalog = self::FILTER_CATALOG_MIXED;

		if(!empty($this->objAddonType->browser_urlBuyPro))
			$this->urlBuy = $this->objAddonType->browser_urlBuyPro;

		if($this->objAddonType->showDescriptionField == false)
			$this->enableDescriptionField = false;

		if($this->objAddonType->enableCategories == false)
			$this->hasCats = false;


	}


	/**
	 * set filter addon type to use only it
	 */
	public function setAddonType($addonType){

		$this->filterAddonType = $addonType;

		$this->objAddonType = UELM_UniteCreatorAddonType::getAddonTypeObject($addonType, $this->isLayouts);

		$this->initByAddonType();
	}


	/**
	 * set manager name
	 */
	public function setManagerNameFromData($data){

		$name = UELM_UniteFunctionsUC::getVal($data, "manager_name");
		$addontype = UELM_UniteFunctionsUC::getVal($data, "manager_addontype");
		if(empty($addontype))
			$addontype = UELM_UniteFunctionsUC::getVal($data, "addontype");

		$passData = UELM_UniteFunctionsUC::getVal($data, "manager_passdata");

		if(!empty($name))
			$this->setManagerName($name);

		if(!empty($passData) && is_array($passData)){
			$this->arrPassData = $passData;
		}


		$this->init($addontype);

		$this->setProductFromData($data);

	}


	private function a__________ADDON_HTML_______(){}


	/**
	 * get addon admin html add
	 */
	protected function getAddonAdminAddHtml(UELM_UniteCreatorAddon $objAddon){

		$addHtml = "";

		$addHtml = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_MANAGER_ADDON_ADDHTML, $addHtml, $objAddon);

		return($addHtml);
	}

	/**
	 * get addon admin html add
	 */
	protected function getLayoutAdminAddHtml(UELM_UniteCreatorLayout $objLayout){

		$addHtml = "";

		$addHtml = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_MANAGER_LAYOUT_ADDHTML, $addHtml, $objLayout);


		return($addHtml);
	}

	/**
	 * get addon admin html add
	 */
	protected function getLayoutAdminLIAddHtml(UELM_UniteCreatorLayout $objLayout){

		$addHtml = "";

		$addHtml = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_MANAGER_LAYOUT_LI_ADDHTML, $addHtml, $objLayout);

		return($addHtml);
	}




	/**
	 * get data of the admin html from addon
	 */
	private function getAddonAdminHtml_getDataFromAddon(UELM_UniteCreatorAddon $objAddon){

		$data = array();

		$objAddon->validateInited();

		$title = $objAddon->getTitle();

		$name = $objAddon->getNameByType();

		$description = $objAddon->getDescription();

		//set html icon
		$urlIcon = $objAddon->getUrlIcon();

		//get preview html
		$urlPreview = $objAddon->getPreviewImageUrl();

		$itemID = $objAddon->getID();

		$isActive = $objAddon->getIsActive();

		$addHtml = $this->getAddonAdminAddHtml($objAddon);

		$fontIcon = $objAddon->getFontIcon();

		$svgIcon = $objAddon->getPreviewIconUrl();

		$linkDemo = $objAddon->getOption("link_preview");


		$data["title"] = $title;
		$data["name"] = $name;
		$data["description"] = $description;
		$data["url_icon"] = $urlIcon;
		$data["url_preview"] = $urlPreview;
		$data["id"] = $itemID;
		$data["is_active"] = $isActive;
		$data["font_icon"] = $fontIcon;
		$data["svg_icon"] = $svgIcon;
		$data["add_html"] = $addHtml;
		$data["link_demo"] = $linkDemo;

		return($data);
	}

	/**
	 * get data from layout
	 */
	private function getAddonAdminHtml_getDataFromLayout(UELM_UniteCreatorLayout $objLayout){

		$addHtml = $this->getLayoutAdminAddHtml($objLayout);
		$liAddHtml = $this->getLayoutAdminLIAddHtml($objLayout);

		$data = array();

		$data["title"] = $objLayout->getTitle();
		$data["name"] = $objLayout->getName();
		$data["description"] = $objLayout->getDescription();
		$data["url_icon"] = $objLayout->getIcon();
		$data["url_preview"] = $objLayout->getPreviewImage();
		$data["url_preview_default"] = $objLayout->getDefaultPreviewImage();
		$data["id"] = $objLayout->getID();
		$data["is_active"] = true;		//no setting in layout yet
		$data["add_html"] = "";
		$data["url_edit"] = $objLayout->getUrlEditPost();
		$data["url_view_post"] = $objLayout->getUrlViewPost();
		$data["is_group"] = $objLayout->isGroup();
		$data["add_html"] = $addHtml;
		$data["li_add_html"] = $liAddHtml;


		return($data);
	}


	/**
	 * get add html of web addon
	 */
	private function getWebAddonData($addon){

		$isFree = $this->objBrowser->isWebAddonFree($addon);

		$state = UELM_UniteCreatorBrowser::STATE_PRO;
		if($isFree == true)
			$state = UELM_UniteCreatorBrowser::STATE_FREE;

		$options = array();
		if($this->isWebCatalogMode == true)
			$options["web_catalog_mode"] = true;

		$data = $this->objBrowser->getCatalogAddonStateData($state, $this->isLayouts, null, $addon, $options);

		if(empty($data))
			$data = array();

		$typeName = $this->objAddonType->typeName;

		$data["url_preview_default"] = UELM_HelperUC::getDefaultPreviewImage($typeName);

		return($data);
	}


	/**
	 * get addons or layout by type
	 */
	private function getCatAddonsOrLayouts($catID, $filterActive, $params = null){

		$isLayout = $this->objAddonType->isLayout;

		//UELM_UniteFunctionsUC::showTrace();

		if($isLayout == false){		//addons
			$objAddons = new UELM_UniteCreatorAddons();
			$addons = $objAddons->getCatAddons($catID, false, $filterActive, $this->filterAddonType, false, $params);

			return($addons);
		}


		//layouts
		$objLayouts = new UELM_UniteCreatorLayouts();
		$arrLayouts = $objLayouts->getCatLayouts($catID, $this->objAddonType, false, $params);


		return($arrLayouts);
	}


	/**
	 * get web API
	 */
	private function getWebAPI(){

		$webAPI = new UELM_UniteCreatorWebAPI();

		if(!empty($this->product))
			$webAPI->setProduct($this->product);

		return($webAPI);
	}

	/**
	 * modify category addons, function for override
	 */
	protected function modifyCatAddons($addons, $addonType){

		return($addons);
	}

	/**
	 * get category addons, objects or array from catalog
	 */
	private function getCatAddons($catID, $title = "", $isweb = false, $params = null){

		$filterType = $this->filterAddonType;
		$filterActive = self::getStateFilterActive($this->filterAddonType);

		$filterCatalog = $this->getStateFilterCatalog();

		$filterSearch = UELM_UniteFunctionsUC::getVal($params, "filter_search");
		$filterSearch = trim($filterSearch);

		//if category title match the search, then get all the addons
		if(!empty($filterSearch)){

			$isTitleMatch = UELM_UniteFunctionsUC::isStringContains($title, $filterSearch);

			if($isTitleMatch == true)
				unset($params["filter_search"]);
		}

		$addons = array();


		switch($filterCatalog){
			case self::FILTER_CATALOG_WEB:
			break;
			case self::FILTER_CATALOG_INSTALLED:
				if($isweb == false)
					$addons = $this->getCatAddonsOrLayouts($catID, $filterActive, $params);

				return($addons);
			break;
			case self::FILTER_CATALOG_MIXED:
				if($isweb == false)
					$addons = $this->getCatAddonsOrLayouts($catID, $filterActive, $params);
			break;
		}

		//mix with the catalog

		//get category title
		if(!empty($catID) && empty($title)){
			$objCategories = new UELM_UniteCreatorCategories();
			$arrCat = $objCategories->getCat($catID);
			$title = UELM_UniteFunctionsUC::getVal($arrCat, "title");
		}

		if(empty($title))
			return($addons);

		if($this->objAddonType->allowManagerWebCatalog == false)
			return($addons);

		$webAPI = $this->getWebAPI();

		$addons = $webAPI->mergeCatAddonsWithCatalog($title, $addons, $this->objAddonType, $params);

		$addonType = $this->objAddonType->typeName;

		$addons = $this->modifyCatAddons($addons, $addonType);


		return($addons);
	}

	/**
	 * get additional addhtml, function for override
	 */
	protected function getAddonAdminHtml_AddHtml($addHtml, $objAddon){


		return($addHtml);
	}


	/**
	 * get html addon
	 */
	public function getAddonAdminHtml($objAddon){


		self::$stateLabelCounter = 0;

		$isLayout = false;

		if(is_array($objAddon))
			$data = $objAddon;
		else{

			$isLayout = $this->objAddonType->isLayout;

			if($this->objAddonType->isLayout == false)
				$data = $this->getAddonAdminHtml_getDataFromAddon($objAddon);
			else
				$data = $this->getAddonAdminHtml_getDataFromLayout($objAddon);
		}

		//--- prepare data

		$title = UELM_UniteFunctionsUC::getVal($data, "title");
		$name = UELM_UniteFunctionsUC::getVal($data, "name");
		$description = UELM_UniteFunctionsUC::getVal($data, "description");
		$urlIcon = UELM_UniteFunctionsUC::getVal($data, "url_icon");
		$urlPreview = UELM_UniteFunctionsUC::getVal($data, "url_preview");
		$urlPreviewDefault = UELM_UniteFunctionsUC::getVal($data, "url_preview_default");

		$itemID = UELM_UniteFunctionsUC::getVal($data, "id");
		$isActive = UELM_UniteFunctionsUC::getVal($data, "is_active");
		$addHtml = UELM_UniteFunctionsUC::getVal($data, "add_html");
		$liAddHTML = UELM_UniteFunctionsUC::getVal($data, "li_add_html");

		$isweb = UELM_UniteFunctionsUC::getVal($data, "isweb");
		$fontIcon = UELM_UniteFunctionsUC::getVal($data, "font_icon");
		$svgIcon = UELM_UniteFunctionsUC::getVal($data, "svg_icon");
		$urlEdit = UELM_UniteFunctionsUC::getVal($data, "url_edit");
		$urlViewPost = UELM_UniteFunctionsUC::getVal($data, "url_view_post");
		$isGroup = UELM_UniteFunctionsUC::getVal($data, "is_group");
		$isGroup = UELM_UniteFunctionsUC::strToBool($isGroup);
		$linkToDemo = UELM_UniteFunctionsUC::getVal($data, "link_demo");


		$state = null;

		if($isweb == true){

			$urlPreview = UELM_UniteFunctionsUC::getVal($data, "image");

			if(UELM_GlobalsUC::ENABLE_CATALOG_SHORTPIXEL == true)
				$urlPreview = UELM_GlobalsUC::SHORTPIXEL_PREFIX.$urlPreview;

			$isActive = true;
			$webData = $this->getWebAddonData($data);

			$urlPreviewDefault = UELM_UniteFunctionsUC::getVal($webData, "url_preview_default");

			$addHtml .= $webData["html_state"];
			$addHtml .= $webData["html_additions"];
			$state = $webData["state"];

			$itemID = UELM_UniteFunctionsUC::getSerialID("webaddon");
			$liAddHTML .= " data-itemtype='web' data-state='{$state}'";

			$isGroup = UELM_UniteFunctionsUC::getVal($data, "is_parent");
			$isGroup = UELM_UniteFunctionsUC::strToBool($isGroup);

			//for imported template
			if($this->isLayouts == true)
				$importedTemplateID = UELM_UniteFunctionsUC::getVal($data, "imported_templateid");

		}


		//protection for url preview
		$arrInfo = pathinfo($urlPreview);
		$extension = UELM_UniteFunctionsUC::getVal($arrInfo, "extension");
		if(empty($extension))
			$urlPreview = null;


		UELM_UniteFunctionsUC::validateNotEmpty($itemID, "item id");

		$addHtml = $this->getAddonAdminHtml_AddHtml($addHtml, $objAddon);

		//put add html for layout
		if($this->isLayouts == true){

			//add group if available
			if($isGroup == true){

	        	$addStateClass = "";
	        	if(self::$stateLabelCounter > 0)
	        	$addStateClass = "uc-state-label".self::$stateLabelCounter;

				$stateLabel = __("Template Kit","unlimited-elements");
				$htmlState = "<div class='uc-state-label uc-state-group $addStateClass'>
					<div class='uc-state-label-text'>{$stateLabel}</div>
				</div>";

				$addHtml .= $htmlState;
				$liAddHTML .= " data-isgroup='true'";

				self::$stateLabelCounter++;
			}

			//add imported if available
			if(!empty($importedTemplateID)){

	        	$addStateClass = "";
	        	if(self::$stateLabelCounter > 0)
	        	$addStateClass = "uc-state-label".self::$stateLabelCounter;

				$stateLabel = __("Imported","unlimited-elements");
				$htmlState = "<div class='uc-state-label uc-state-imported $addStateClass'>
					<div class='uc-state-label-text'>{$stateLabel}</div>
				</div>";

				$arrLinks = UELM_HelperProviderUC::getImportedTemplateLinks($importedTemplateID);
				$linkView = $arrLinks["url"];
				$linkEdit = $arrLinks["url_edit"];

				$linkView = htmlspecialchars($linkView);
				$linkEdit = htmlspecialchars($linkEdit);

				$addHtml .= $htmlState;
				$liAddHTML .= " data-isimported='true' data-linkview='$linkView' data-linkedit='$linkEdit'";

				self::$stateLabelCounter++;
			}

		}

		//--- prepare output

		$title = htmlspecialchars($title);
		$name = htmlspecialchars($name);
		$description = htmlspecialchars($description);

		$descOutput = $description;

		$htmlPreview = "";

		$class = "uc-addon-thumbnail";
		$classThumb = "";
		$styleThumb = "";

		if(empty($urlPreview)){
			$classThumb = " uc-no-thumb";

			//replace by default preview
			if(!empty($urlPreviewDefault)){
				$classThumb = " uc-default-preview";
				$urlPreview = $urlPreviewDefault;
			}
		}

		if(!empty($urlPreview)){
			$styleThumb = "style=\"background-image:url('{$urlPreview}')\"";
		}

		if($this->isWebCatalogMode && !empty($urlPreview)){
			$urlPreviewHtml = htmlspecialchars($urlPreview);
			$htmlPreview = "data-preview='$urlPreviewHtml'";
		}


		if($isActive == false)
			$class .= " uc-item-notactive";

		if($isweb == true)
			$class .= " uc-item-web";

		$class = "class=\"{$class}\"";

		$addData = "";
		if(!empty($urlEdit)){
			$liAddHTML .= " data-urledit=\"$urlEdit\"";
		}

		if(!empty($urlViewPost))
			$liAddHTML .= " data-urlview=\"$urlViewPost\"";


		//set html output
		$htmlItem  = "<li id=\"uc_item_{$itemID}\" data-id=\"{$itemID}\" data-title=\"{$title}\" data-name=\"{$name}\" data-description=\"{$description}\" {$liAddHTML} {$htmlPreview} {$class} >";

		if($state == UELM_UniteCreatorBrowser::STATE_PRO){
			$urlBuy = $this->urlBuy;

			$htmlItem .= "<a class='uc-link-item-pro' href='$urlBuy' target='_blank'>";
		}

		if(!empty($svgIcon)){

			$title = "<img class=\"uc-item-title__image\" src=\"{$svgIcon}\"></img>".$title;

		}else{

			//add icon to title
			if(!empty($fontIcon))
				$title = "<i class=\"{$fontIcon}\"></i> ".$title;

		}


		//if svg type - set preview url as svg
		if($this->objAddonType->isSVG == true){

			$classThumb .= " uc-type-shape-devider";

			if($isweb == false){
				$urlPreview = null;

				$svgContent = $objAddon->getHtml();
				$urlPreview = UELM_UniteFunctionsUC::encodeSVGForBGUrl($svgContent);
			}

		}

			//output thumb
			$htmlItem .= "	<div class=\"uc-item-thumb{$classThumb} unselectable\" unselectable=\"on\" {$styleThumb}>";

			//draw item actions
			$actionEdit = "edit_addon";
			if($isLayout == true){
				$actionEdit = "edit_addon_blank";

				if($isGroup == true)
					$actionEdit = "edit_layout_group";
			}

			$urlIconEdit = UELM_GlobalsUC::$urlPluginImages."icon_item_edit.svg";
			$urlIconPreview = UELM_GlobalsUC::$urlPluginImages."icon_item_preview.svg";
			$urlIconDuplicate = UELM_GlobalsUC::$urlPluginImages."icon_item_duplicate.svg";
			$urlIconMenu = UELM_GlobalsUC::$urlPluginImages."icon_item_menu.svg";

			$textPreview = __("Preview ", "unlimited-elements").$this->textSingle;
			$textEdit = __("Edit ", "unlimited-elements").$this->textSingle;

			if($isGroup == true){
				$textPreview = __("Preview Template Kit", "unlimited-elements");
				$textEdit = __("Edit Template Kit", "unlimited-elements");
			}

			$textDuplicate = __("Duplicate ", "unlimited-elements").$this->textSingle;

			$htmlItem .= "<div class=\"uc-item-actions\">";
			
			if($this->enableEditAddon == true){
				
				$htmlItem .= "	<a href='javascript:void(0)' class='uc-item-action uc-item-action-edit uc-tip' onfocus='this.blur()' data-action='{$actionEdit}' title='{$textEdit}' ><img src='{$urlIconEdit}'></a>";
				
			} else {
								
				$textEdit .= __(" (Pro Version Only)", "unlimited-elements");
				
				$htmlItem .= "	<a href='javascript:void(0)' class='uc-item-action uc-item-action-edit uc-tip uc-disabled' data-action='{$actionEdit}' title='{$textEdit}' ><img src='{$urlIconEdit}'></a>";
			}

			$textViewDemo = __("View ", "unlimited-elements").$this->textSingle.__(" Demo and Help", "unlimited-elements");
			
			if($isGroup == false){

				//preview widget
				if(!empty($linkToDemo))
					$htmlItem .= "	<a href='{$linkToDemo}' target='_blank' class='uc-item-action uc-item-action-preview uc-tip' onfocus='this.blur()' title='$textViewDemo'><img src='{$urlIconPreview}'></a>";
				else
					$htmlItem .= "	<a href='javascript:void(0)' class='uc-item-action uc-item-action-preview uc-tip' onfocus='this.blur()' data-action='preview_addon' title='$textPreview'><img src='{$urlIconPreview}'></a>";

				$htmlItem .= "	<a href='javascript:void(0)' class='uc-item-action uc-item-action-duplicate uc-tip' onfocus='this.blur()' data-action='duplicate_item' title='$textDuplicate'><img src='{$urlIconDuplicate}'></a>";

			}

			$htmlItem .= "	<a href='javascript:void(0)' class='uc-item-action uc-item-action-menu' onfocus='this.blur()' data-action='open_menu'><img src='{$urlIconMenu}'></a>";

			$htmlItem .= "	<div class='unite-clear'></div>";

			$htmlItem .= "</div>";

			$htmlItem .= "</div>";


			$htmlItem .= "	<div class=\"uc-item-title unselectable\" unselectable=\"on\">{$title}</div>";

			if($addHtml)
				$htmlItem .= $addHtml;


		if($state == UELM_UniteCreatorBrowser::STATE_PRO){
			$htmlItem .= "</a>";
		}

		$htmlItem .= "</li>";


		return($htmlItem);
	}


	/**
	 * get html of cate items
	 */
	public function getCatAddonsHtml($catID, $title = "", $isweb = false, $params = array()){

		$addons = $this->getCatAddons($catID, $title, $isweb, $params);

		$htmlAddons = "";

		foreach($addons as $addon){

			$html = $this->getAddonAdminHtml($addon);
			$htmlAddons .= $html;
		}

		return($htmlAddons);
	}


	/**
	 * get html of categories and items.
	 */
	public function getCatsAndAddonsHtml($catID, $catTitle = "", $isweb = false, $params = array()){

		$arrCats = $this->getArrCats($params);

		//change category if needed
		$arrCatsAssoc = UELM_UniteFunctionsUC::arrayToAssoc($arrCats, "id");

		if(isset($arrCatsAssoc[$catID]) == false){

			$catID = null;

			$firstCat = reset($arrCats);

			if(!empty($firstCat)){
				$catID = $firstCat["id"];
				$catTitle = $firstCat["title"];
				$isweb = UELM_UniteFunctionsUC::getVal($firstCat, "isweb");
				$isweb = UELM_UniteFunctionsUC::strToBool($isweb);
			}
		}


		$objCats = new UELM_UniteCreatorCategories();
		$htmlCatList = $this->getCatList($catID, null, $params);

		$htmlAddons = $this->getCatAddonsHtml($catID, $catTitle, $isweb, $params);

		$response = array();
		$response["htmlItems"] = $htmlAddons;
		$response["htmlCats"] = $htmlCatList;

		return($response);
	}


	/**
	 * set last selected category state
	 */
	private function setStateLastSelectedCat($catID){
		UELM_HelperUC::setState(self::STATE_LAST_ADDONS_CATEGORY, $catID);
	}


	/**
	 * set product from data
	 */
	private function setProductFromData($data){

		//get product
		$product = "";
		$passData = UELM_UniteFunctionsUC::getVal($data, "manager_passdata");
		if(empty($passData))
			return(false);

		$product = UELM_UniteFunctionsUC::getVal($passData, "product");

		if(empty($product))
			return(false);


		$this->product = $product;

		$this->objBrowser->setProduct($product);

	}


	/**
	 * get category items html
	 */
	public function getCatAddonsHtmlFromData($data){

		$this->validateAddonType();

		$catID = UELM_UniteFunctionsUC::getVal($data, "catID");
		$catTitle = UELM_UniteFunctionsUC::getVal($data, "title");
		$parentID = UELM_UniteFunctionsUC::getVal($data, "parent_id");

		$this->setProductFromData($data);

		$objAddons = new UELM_UniteCreatorAddons();

		$resonseCombo = UELM_UniteFunctionsUC::getVal($data, "response_combo");
		$resonseCombo = UELM_UniteFunctionsUC::strToBool($resonseCombo);

		$filterActive = UELM_UniteFunctionsUC::getVal($data, "filter_active");

		$filterSearch = UELM_UniteFunctionsUC::getVal($data, "filter_search");

		$filterSearch = trim($filterSearch);

		$isweb = UELM_UniteFunctionsUC::getVal($data, "isweb");
		$isweb = UELM_UniteFunctionsUC::strToBool($isweb);

		if($isweb == false && $catID != "all")
			UELM_UniteFunctionsUC::validateNumeric($catID,"category id");

		if(UELM_GlobalsUC::$enableWebCatalog == true){

			$filterCatalog = UELM_UniteFunctionsUC::getVal($data, "filter_catalog");
			self::setStateFilterCatalog($filterCatalog);
		}

		self::setStateFilterActive($filterActive);
		$this->setStateLastSelectedCat($catID);

		$params = array();

		if(!empty($filterSearch))
			$params["filter_search"] = $filterSearch;

		if(!empty($parentID)){
			$this->isInsideParent = true;
			$params["parent_id"] = $parentID;
		}


		if($resonseCombo == true){

			//uelm_dmp($isweb);uelm_dmp($catTitle);uelm_dmp($catID);uelm_dmp($params);exit();

			$response = $this->getCatsAndAddonsHtml($catID, $catTitle, $isweb, $params);

		}else{
			$itemsHtml = $this->getCatAddonsHtml($catID, $catTitle, $isweb, $params);
			$response = array("itemsHtml"=>$itemsHtml);
		}


		return($response);
	}


	private function a________DIALOGS________(){}

	/**
	 * put debug dialog
	 */
	private function putDialogDebug(){

		?>

		<div id="uc_manager_dialog_debug" title="Debug Dialog" style="display:none;">

			<h2>Url API: </h2>

			<?php echo esc_url(UELM_GlobalsUC::URL_API)?>
		</div>

		<?php
	}

	/**
	 * put import addons dialog
	 */
	private function putDialogImportAddons(){

		$importText = esc_html__("Import ", "unlimited-elements").$this->textPlural;
		$textSelect = esc_html__("Select ","unlimited-elements") . $this->textPluralLower . __(" export zip file (or files)","unlimited-elements");
		$textLoader = esc_html__("Uploading ","unlimited-elements") . $this->textSingleLower. __(" file...", "unlimited-elements");
		$textSuccess = $this->textSingle . esc_html__(" Added Successfully", "unlimited-elements");

		$dialogTitle = $importText;

		//overwrite checkbox
		$textOverwrite = esc_html__("Overwrite Existing ", "unlimited-elements").$this->textPlural;
		if($this->isLayouts == true){
			$textOverwrite = esc_html__("Overwrite Widgets", "unlimited-elements");
		}

		$nonce = "";
		if(method_exists("UELM_UniteProviderFunctionsUC", "getNonce"))
			$nonce = UELM_UniteProviderFunctionsUC::getNonce();
		?>

			<div id="dialog_import_addons" class="unite-inputs" title="<?php echo esc_attr($dialogTitle)?>" style="display:none;">

				<div class="unite-dialog-top"></div>

				<div class='dialog-import-addons-left'>

					<div class="unite-inputs-label">
						<?php echo esc_html($textSelect)?>:
					</div>

					<div class="unite-inputs-sap-small"></div>

					<form id="dialog_import_addons_form" action="<?php echo esc_attr($this->urlAjax)?>" name="form_import_addon" class="dropzone uc-import-addons-dropzone">
						<input type="hidden" name="action" value="<?php echo esc_attr($this->pluginName)?>_ajax_action">
						<input type="hidden" name="client_action" value="import_addons">

						<?php 
						if(!empty($nonce)) {
							?>
							<input type="hidden" name="nonce" value="<?php echo esc_attr($nonce)?>">
							<?php 
						}

						$script = 'if (typeof Dropzone !== "undefined") { Dropzone.autoDiscover = false; }';
						UELM_UniteProviderFunctionsUC::printCustomScript($script, true);

						?>
					</form>
						<div class="unite-inputs-sap-double"></div>

						<div class="unite-inputs-label">
							<?php esc_html_e("Import to Category", "unlimited-elements")?>:

						<select id="dialog_import_catname">
							<option value="autodetect" ><?php esc_html_e("[Autodetect]", "unlimited-elements")?></option>
							<option id="dialog_import_catname_specific" value="specific"><?php esc_html_e("Current Category", "unlimited-elements")?></option>
						</select>

						</div>

						<div class="unite-inputs-sap-double"></div>

						<div class="unite-inputs-label">
							<label for="dialog_import_check_overwrite">

								<input type="checkbox" <?php echo ($this->isLayouts ? '' : 'checked="checked"');?>  id="dialog_import_check_overwrite"></input>

								<?php echo esc_html($textOverwrite) ?>

							</label>
						</div>


				</div>

				<div id="dialog_import_addons_log" class='dialog-import-addons-right' style="display:none">

					<div class="unite-bold"> <?php echo esc_html($importText).esc_html__(" Log","unlimited-elements")?> </div>

					<br>

					<div id="dialog_import_addons_log_text" class="dialog-import-addons-log"></div>
				</div>

				<div class="unite-clear"></div>

				<?php
					$prefix = "dialog_import_addons";
					$buttonTitle = $importText;
					$loaderTitle = $textLoader;
					$successTitle = $textSuccess;
					UELM_HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>


			</div>
		<?php
	}


	/**
	 * put quick edit dialog
	 */
	private function putDialogQuickEdit(){
		?>
			<!-- dialog quick edit -->

			<div id="dialog_edit_item_title"  title="<?php esc_html_e("Quick Edit","unlimited-elements")?>" style="display:none;">

				<div class="dialog_edit_title_inner unite-inputs mtop_20 mbottom_20" >

					<div class="unite-inputs-label-inline">
						<?php esc_html_e("Title", "unlimited-elements")?>:
					</div>
					<input type="text" id="dialog_quick_edit_title" class="unite-input-wide">

					<?php if($this->enableEnterName):?>
					<div class="unite-inputs-sap"></div>

					<div class="unite-inputs-label-inline">
						<?php esc_html_e("Name", "unlimited-elements")?>:
					</div>
					<input type="text" id="dialog_quick_edit_name" class="unite-input-wide">

					<?php else:?>

					<input type="hidden" id="dialog_quick_edit_name">

					<?php endif?>

					<div class="unite-inputs-sap"></div>

					<div class="unite-inputs-label-inline">
						<?php esc_html_e("Description", "unlimited-elements")?>:
					</div>

					<textarea class="unite-input-wide" id="dialog_quick_edit_description"></textarea>

					<?php UELM_UniteProviderFunctionsUC::doAction("uc_quick_edit_dialog_html", $this)?>

				</div>

			</div>

		<?php
	}


	/**
	 * put category edit dialog
	 */
	protected function putDialogEditCategory(){

		$prefix = "uc_dialog_edit_category";

		?>
			<div id="uc_dialog_edit_category" class="uc-dialog-edit-category" data-custom='yes' title="<?php esc_html_e("Edit Category","unlimited-elements")?>" style="display:none;" >

				<div class="unite-dialog-top"></div>

				<div class="unite-dialog-inner-constant">
					<div id="<?php echo esc_attr($prefix)?>_settings_loader" class="loader_text"><?php esc_html_e("Loading Settings", "unlimited-elements")?>...</div>

					<div id="<?php echo esc_attr($prefix)?>_settings_content"></div>

				</div>

				<?php
					$buttonTitle = esc_html__("Update Category", "unlimited-elements");
					$loaderTitle = esc_html__("Updating Category...", "unlimited-elements");
					$successTitle = esc_html__("Category Updated", "unlimited-elements");
					UELM_HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>

			</div>

		<?php
	}

	/**
	 * put category edit dialog
	 */
	protected function putDialogAddonProperties(){

		$prefix = "uc_dialog_addon_properties";

		$textTitle =  $this->textSingle.esc_html__(" Properties", "unlimited-elements");


		?>
			<div id="uc_dialog_addon_properties" class="uc-dialog-addon-properties" data-custom='yes' title="<?php echo esc_attr($textTitle)?>" style="display:none;" >

				<div class="unite-dialog-top"></div>

				<div class="unite-dialog-inner-constant">
					<div id="<?php echo esc_attr($prefix)?>_settings_loader" class="loader_text uc-settings-loader"><?php esc_html_e("Loading Properties", "unlimited-elements")?>...</div>

					<div id="<?php echo esc_attr($prefix)?>_settings_content" class="uc-settings-content"></div>

				</div>

				<?php
					$buttonTitle = esc_html__("Update ", "unlimited-elements").$this->textSingle;
					$loaderTitle = esc_html__("Updating...", "unlimited-elements");
					$successTitle = $this->textSingle.esc_html__(" Updated", "unlimited-elements");
					UELM_HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>

			</div>

		<?php
	}


	/**
	 * put add addon dialog
	 */
	private function putDialogAddAddon(){

		$styleDesc = "";
		if($this->enableDescriptionField == false)
			$styleDesc = "style='display:none'";


		?>
			<!-- add addon dialog -->

			<div id="dialog_add_addon" class="unite-inputs" title="<?php echo esc_attr($this->textAddAddon)?>" style="display:none;">

				<div class="unite-dialog-top"></div>

				<div class="unite-inputs-label">
					<?php echo esc_html($this->textSingle).esc_html__(" Title", "unlimited-elements")?>:
				</div>

				<input type="text" id="dialog_add_addon_title" class="dialog_addon_input unite-input-100" />

				<?php if($this->enableEnterName):?>
				<div class="unite-inputs-sap"></div>

				<div class="unite-inputs-label">
					<?php echo esc_html($this->textSingle.__(" Name", "unlimited-elements"))?>:
				</div>

				<input type="text" id="dialog_add_addon_name" class="dialog_addon_input unite-input-100" />

				<?php else:?>

				<input type="hidden" id="dialog_add_addon_name" value="" />

				<?php endif?>

				<?php
					if($this->enableDescriptionField == false):		//description placeholder
					?>
					<div class="vert_sap30"></div>
					<?php
					endif;
				?>

				<div class="unite-dialog-description-wrapper" 
					<?php 
					if($this->enableDescriptionField == false) {
						?> style='display:none' <?php
					}
					?>
					>

					<div class="unite-inputs-sap"></div>

					<div class="unite-inputs-label">
						<?php echo esc_html($this->textSingle).esc_html__(" Description", "unlimited-elements")?>:
					</div>

					<textarea id="dialog_add_addon_description" class="dialog_addon_input unite-input-100" ></textarea>
				</div>

				<?php

					$prefix = "dialog_add_addon";
					$buttonTitle = $this->textAddAddon;
					$loaderTitle = esc_html__("Adding ","unlimited-elements").$this->textSingle."...";
					$successTitle = $this->textSingle. esc_html__(" Added Successfully", "unlimited-elements");
					UELM_HelperHtmlUC::putDialogActions($prefix, $buttonTitle, $loaderTitle, $successTitle);
				?>

			</div>

		<?php
	}

	/**
	 * put preview addon dialog
	 */
	private function putDialogPreviewAddons(){

		$textPreviw = "Preview ".$this->textSingle;

		?>

		<div id="uc_dialog_item_preview" title="<?php echo esc_attr($textPreviw)?>" style="display:none;">

			<iframe src="" width="100%" height="100%"  style="overflow-x: hidden;overflow-y:auto;">

		</iframe>

		</div>

		<?php
	}

	/**
	 * put preview template dialog
	 */
	private function putPreviewTemplateDialog(){

		//set warning text
		$maxExecutionTime = (int)@ini_get("max_execution_time");

		$warningText = "";

		if($maxExecutionTime > 0 && $maxExecutionTime <= 30){
			@ini_set("max_execution_time", 300);

			$maxTime = @ini_get("max_execution_time");
			$maxTime = (int)$maxTime;

			if($maxTime <= 30)
				// translators: %d is a number
				$warningText = sprintf(__("Notice: Your php setting: max_execution_time is <b>%d</b> seconds. It is not efficient enough for importing the template. Please increase this value in php.ini. If you don't know how to change it please contact your hosting provider.", "unlimited-elements"), $maxExecutionTime);
		}

		$dialogTitle = __("Preview Template", "unlimited-elements");
		$confirmImportAgainMessage = __("This import will overwrite the existing imported template. Continue?","unlimited-elements");
		$confirmImportAgainMessage = htmlspecialchars($confirmImportAgainMessage);

		$urlImageBase = UELM_GlobalsUC::$urlPluginImages;

		$isRTL = UELM_GlobalsUC::$isAdminRTL;

		?>
		<div id="uc_dialog_preview_template" class="uc-dialog-preview-template unite-inputs<?php echo ($isRTL ? ' uc-rtl' : '')?>" title="<?php echo esc_attr($dialogTitle)?>" style="display:none;">

				<div class="uc-dialog-preview-template__preview">
					<img src="" class="uc-dialog-preview-template__image">
				</div>

				<div class="uc-dialog-preview-template__right">

					<div class="uc-dialog-preview-template__buttons-panel">

						<a id="uc_dialog_import_template_button_prev" href="javascript:void(0)" class="uc-dialog-preview-template__button-top uc-button-disabled" title="<?php esc_attr_e("To Previous Template", "unlimited-elements")?>">
							<img src="<?php echo esc_url($urlImageBase)?>icon-gray-prev.svg">
						</a><a id="uc_dialog_import_template_button_next" href="javascript:void(0)" class="uc-dialog-preview-template__button-top" title="<?php esc_attr_e("To Next Template", "unlimited-elements")?>">
							<img src="<?php echo esc_url($urlImageBase)?>icon-gray-next.svg">
						</a><a id="uc_dialog_import_template_button_close" href="javascript:void(0)" class="uc-dialog-preview-template__button-top" title="<?php esc_attr_e("Back To Catalog", "unlimited-elements")?>">
							<img src="<?php echo esc_url($urlImageBase)?>icon-gray-close.svg">
						</a>

					</div>

					<div class="uc-dialog-preview-template__title">Template Title</div>

					<div class="uc-dialog-preview-template__right-operations">

						<h2><?php esc_attr_e("Import Template","unlimited-elements")?></h2>

						<p>
							<?php esc_attr_e("To get started click the \"Import Template\" button.","unlimited-elements")?>
							<?php esc_attr_e("After import is completed the template will show under Elementor Saved Templates list for future use.","unlimited-elements")?>
						</p>

						<br>

						<a href="javascript:void(0)" class="unite-button-primary uc-dialog-preview-template__button-import uc-show-when-new uc-hide-when-loading uc-hide-when-just-imported"><?php esc_attr_e("Import Template","unlimited-elements")?></a>
						<a href="javascript:void(0)" class="unite-button-primary uc-dialog-preview-template__button-import-again uc-show-when-imported uc-hide-when-loading uc-hide-when-just-imported" data-message-confirm="<?php echo esc_attr($confirmImportAgainMessage)?>" ><?php esc_attr_e("Import Template Again","unlimited-elements")?></a>

						<div id="uc_dialog_import_template_loader" class="uc-dialog-preview-template__loader" style="display:none">
							<span class="template-dialog-loader">
								<span>I</span>
								<span>m</span>
								<span>p</span>
								<span>o</span>
								<span>r</span>
								<span>t</span>
								<span>i</span>
								<span>n</span>
								<span>g</span>
							</span>
						</div>
						<div id="uc_dialog_import_template_success" class="uc-dialog-preview-template__import-success" style="display:none"></div>
						<div id="uc_dialog_import_template_error" class="uc-dialog-preview-template__import-error" style="display:none"></div>

						<div id="uc_dialog_import_template_imported_message_top"></div>

						<div id="uc_dialog_import_template_imported_message" class="uc-dialog-preview-template__imported-message" style="display:none">

							<div class="uc-dialog-preview-template__imported-message-text">
								<span class="uc-show-when-new"><?php esc_attr_e("Template Imported Successfully","unlimited-elements")?>.</span>
								<span class="uc-show-when-imported"><?php esc_attr_e("Template Already Imported","unlimited-elements")?>.</span>
							</div>

							<div class="uc-dialog-preview-template__action-buttons-wrapper">
								<a href="#" class="unite-button-secondary uc-dialog-preview-template__imported-message-link1" target="_blank" data-text-bottom="<?php esc_attr_e("View Page", "unlimited-elements")?>" data-text-top="<?php esc_attr_e("View Template", "unlimited-elements")?>"><?php esc_attr_e("View Template", "unlimited-elements")?></a>
								<a href="#" class="unite-button-secondary uc-dialog-preview-template__imported-message-link2" target="_blank"><?php esc_attr_e("Edit With Elementor","unlimited-elements")?></a>
							</div>
						</div>

						<div class="uc-dialog-preview-template__create-page-wrapper">

							<h2><?php esc_attr_e("Create Page From Template","unlimited-elements")?></h2>

							<div class="uc-dialog-preview-template__import-page-wrapper">

								<input type="text" placeholder="<?php esc_attr_e("Enter Page Name", "unlimited-elements")?>" class="uc-dialog-preview-template__page-name">
								<a href="javascript:void(0)" class="unite-button-secondary uc-dialog-preview-template__button-create-page uc-disable-when-loading"><?php esc_attr_e("Create Page","unlimited-elements")?></a>

							</div>

							<div id="uc_dialog_import_template_createpage_loader" class="uc-dialog-preview-template__createpage-loader" style="display:none">

								<span class="template-dialog-loader">
									<span>I</span>
									<span>m</span>
									<span>p</span>
									<span>o</span>
									<span>r</span>
									<span>t</span>
									<span>i</span>
									<span>n</span>
									<span>g</span>
								</span>

							</div>

							<div id="uc_dialog_import_template_createpage_error" class="uc-dialog-preview-template__createpage-error" style="display:none"></div>

							<div id="uc_dialog_import_template_imported_message_bottom"></div>
						</div>
					</div>

					<div class="uc-dialog-preview-template__right-message-pro">

						<?php esc_attr_e("This template is available only for the PRO version users of Unlimited Elements plugin.","unlimited-elements")?>

						<br><br>

						<?php esc_attr_e("You can purchase a pro version here","unlimited-elements")?>:

						<br><br>

						<a href="<?php echo esc_url(UELM_GlobalsUC::$url_buy_platform)?>" class="unite-button-primary" target="_blank">Buy Unlimited Elements PRO</a>

					</div>

					<?php if(!empty($warningText)):?>
					<div class="uc-dialog-preview-template__right-warning-message">
						<?php 
						uelm_echo($warningText);
						?>
					</div>
					<?php endif?> 

				</div>		<!-- right --> 

		</div>
		<?php
	}

	private function a______MENUS_______(){}


	/**
	 * get single item menu
	 */
	protected function getMenuSingleItem(){

		$arrMenuItem = array();


			if($this->isLayouts == false){
				
				if($this->enableEditAddon == true){
					$arrMenuItem["edit_addon"] = esc_html__("Edit ","unlimited-elements").$this->textSingle;
					$arrMenuItem["edit_addon_blank"] = esc_html__("Edit In New Tab","unlimited-elements");
				}
			}else{
				$arrMenuItem["edit_addon_blank"] = esc_html__("Edit ","unlimited-elements").$this->textSingle;
			}
		
			if($this->enableEditGroup)
				$arrMenuItem["edit_layout_group"] = esc_html__("Edit Template Kit","unlimited-elements");
			
			if($this->enablePreview == true)
				$arrMenuItem["preview_addon"] = esc_html__("Preview","unlimited-elements");

			if($this->enableViewThumbnail)
				$arrMenuItem["preview_thumb"] = esc_html__("View Thumbnail","unlimited-elements");

			if($this->enableMakeScreenshots)
				$arrMenuItem["make_screenshots"] = esc_html__("Make Thumbnail","unlimited-elements");

			$arrMenuItem["quick_edit"] = esc_html__("Quick Edit","unlimited-elements");

			if($this->enableCopy == true)
				$arrMenuItem["copy"] = esc_html__("Copy","unlimited-elements");			

		$arrMenuItem["remove_item"] = esc_html__("Delete","unlimited-elements");

		if($this->showTestAddon){
			$arrMenuItem["test_addon"] = esc_html__("Test ","unlimited-elements").$this->textSingle;
			$arrMenuItem["test_addon_blank"] = esc_html__("Test In New Tab","unlimited-elements");
		}

		$arrMenuItem["export_addon"] = esc_html__("Export ","unlimited-elements").$this->textSingle;

		$arrMenuItem = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_MANAGER_MENU_SINGLE, $arrMenuItem);

		return($arrMenuItem);
	}



	/**
	 * get item field menu
	 */
	protected function getMenuField(){

		if($this->enableActions == false)
			return parent::getMenuField();

		$arrMenuField = array();

		$arrMenuField["select_all"] = esc_html__("Select All","unlimited-elements");

		$arrMenuField = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_MANAGER_MENU_FIELD, $arrMenuField);

		return($arrMenuField);
	}



	/**
	 * get multiple items menu
	 */
	protected function getMenuMulitipleItems(){
		$arrMenuItemMultiple = array();
		$arrMenuItemMultiple["remove_item"] = esc_html__("Delete","unlimited-elements");

		if($this->enableMakeScreenshots == true)
			$arrMenuItemMultiple["make_screenshots"] = esc_html__("Make Thumbnails","unlimited-elements");

		$arrMenuItemMultiple = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_MANAGER_MENU_MULTIPLE, $arrMenuItemMultiple);

		return($arrMenuItemMultiple);
	}


	/**
	 * get category menu
	 */
	protected function getMenuCategory(){

		$arrMenuCat = array();
		$arrMenuCat["edit_category"] = esc_html__("Edit Category","unlimited-elements");
		$arrMenuCat["delete_category"] = esc_html__("Delete Category","unlimited-elements");

		$arrMenuCat = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_MANAGER_MENU_CATEGORY, $arrMenuCat);

		if($this->enableCatsActions == false){
			$arrMenuCat = array();
			$arrMenuCat["no_action"] = esc_html__("No Action","unlimited-elements");
		}


		return($arrMenuCat);
	}

	private function a_______DATA______(){}


	/**
	 * filter categories without web addons
	 */
	private function filterCatsWithoutWeb($arrCats){

		foreach($arrCats as $key=>$cat){
			$isweb = UELM_UniteFunctionsUC::getVal($cat, "isweb");
			$isweb = UELM_UniteFunctionsUC::strToBool($isweb);
			if($isweb == true)
				continue;

			$numWebAddons = UELM_UniteFunctionsUC::getVal($cat, "num_web_addons");
			if($numWebAddons == 0)
				unset($arrCats[$key]);
		}

		return($arrCats);
	}


	/**
	 * get categories with catalog
	 */
	private function getCatsWithCatalog($filterCatalog, $params = array()){

		$objAddons = new UELM_UniteCreatorAddons();
		$webAPI = $this->getWebAPI();

		$arrCats = $objAddons->getAddonsWidthCategories(true, true, $this->filterAddonType, $params);

		//hide filters if no addons installed

		if( (count($arrCats) == 1 &&
			 isset($arrCats["Uncategorized"]))  &&
			empty($params) &&
			$filterCatalog == self::FILTER_CATALOG_MIXED){

			$this->showAddonFilters_catalog = false;
		}

		if(empty($params))
			$arrCats = $this->modifyLocalCats($arrCats);

		if($this->objAddonType->allowManagerWebCatalog == true)
			$arrCats = $webAPI->mergeCatsAndAddonsWithCatalog($arrCats, true, $this->objAddonType, $params);

		if($filterCatalog == self::FILTER_CATALOG_WEB)
			$arrCats = $this->filterCatsWithoutWeb($arrCats);

		return($arrCats);
	}


	/**
	 * modify local categories - create one if empty, and required
	 */
	protected function modifyLocalCats($arrCats){

		if(!empty($arrCats))
			return($arrCats);

		if($this->objAddonType->allowNoCategory == true)
			return($arrCats);

		//add default category
		$objCategory = new UELM_UniteCreatorCategory();
		$objCategory->addDefaultByAddonType($this->objAddonType);

		$arrCats = $this->objCats->getListExtra($this->objAddonType);

		return($arrCats);
	}

	/**
	 * clear uncategorized category
	 */
	private function getArrCats_clearUncategorized($arrCats){

		//modify categories, clear uncategorized if empty
		foreach($arrCats as $dir=>$cat){

			$isweb = UELM_UniteFunctionsUC::getVal($cat, "isweb");
			if($isweb === true)
				continue;

			$arrAddons = UELM_UniteFunctionsUC::getVal($cat, "addons");

			$catID = UELM_UniteFunctionsUC::getVal($cat, "id");
			if($catID === 0 && empty($arrAddons)){

				$numAddons = UELM_UniteFunctionsUC::getVal($cat, "num_addons");
				$numAddons = UELM_UniteFunctionsUC::strToBool($numAddons);

				if($numAddons == 0){
					unset($arrCats[$dir]);
					return($arrCats);
				}

			}

		}

		return($arrCats);
	}

	/**
	 * get categories
	 */
	protected function getArrCats($params = array(), $forceCatalog = false){

		$filterCatalog = $this->getStateFilterCatalog();

		switch($filterCatalog){
			case self::FILTER_CATALOG_MIXED:
			case self::FILTER_CATALOG_WEB:

				$arrCats = $this->getCatsWithCatalog($filterCatalog, $params);

			break;
			default:	//installed type

				$filterSearch = UELM_UniteFunctionsUC::getVal($params, "filter_search");
				if(empty($filterSearch))
					$filterSearch = "";

				$filterSearch = trim($filterSearch);

				$catsParams = array();
				if(!empty($filterSearch))
					$catsParams["filter_search_addons"] = $filterSearch;

				$arrCats = $this->objCats->getListExtra($this->objAddonType, "","", false, $catsParams);

				$arrCats = $this->modifyLocalCats($arrCats);

			break;
		}


		//don't clear uncategorized at elements master
		$isClear = true;
		$addAll = false;
		if($this->objAddonType->typeName == UELM_GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR_TEMPLATE && $this->objAddonType->allowWebCatalog == false){
			$isClear = false;
			$addAll = true;
		}

		if($addAll == true)
			$arrCats = $this->getCatList_addAllCategory($arrCats);

		if($isClear == true)
			$arrCats = $this->getArrCats_clearUncategorized($arrCats);


		return($arrCats);
	}

	/**
	 * add "all" category, for master templates
	 */
	private function getCatList_addAllCategory($arrCats){

		$arrCat = array();
		$arrCat["id"] = "all";
		$arrCat["title"] = __("All","unlimited-elements");
		$arrCat["alias"] = "";
		$arrCat["ordering"] = 0;
		$arrCat["params"] = "";
		$arrCat["type"] = "";
		$arrCat["num_addons"] = "";

		array_unshift($arrCats, $arrCat);

		return($arrCats);
	}

	/**
	 * get category list
	 */
	protected function getCatList($selectCatID = null, $arrCats = null, $params = array()){

		if($arrCats === null)
			$arrCats = $this->getArrCats($params);

		//check for error
		if(empty($arrCats)){

			$state = $this->getStateFilterCatalog();

			if($state !== self::FILTER_CATALOG_INSTALLED){

				$urlApiConnectivity = UELM_HelperUC::getViewUrl("troubleshooting-connectivity");

				UELM_HelperUC::addAdminNotice("No widgets fetched from the API. Please check <a href='$urlApiConnectivity'>api connectivity</a> from general settings - troubleshooting");
			}

		}

		$htmlCatList = $this->objCats->getHtmlCatList($selectCatID, $this->objAddonType, $arrCats);

		return($htmlCatList);
	}


	/**
	 * get cat list from data
	 */
	public function getCatListFromData($data){

		$selectedCat = UELM_UniteFunctionsUC::getVal($data, "selected_catid");
		$filterActive = UELM_UniteFunctionsUC::getVal($data, "filter_active");
		$filterCatalog = UELM_UniteFunctionsUC::getVal($data, "filter_catalog");

		$typeDistinct = $this->objAddonType->typeNameDistinct;

		self::setStateFilterActive($filterActive, $typeDistinct);
		self::setStateFilterCatalog($filterCatalog, $typeDistinct);

		$htmlCats = $this->getCatList($selectedCat);

		$response = array();
		$response["htmlCats"] = $htmlCats;

		return($response);
	}


	/**
	 * get category settings from cat ID
	 */
	protected function getCatagorySettings(UELM_UniteCreatorCategory $objCat){

		$title = $objCat->getTitle();
		$alias = $objCat->getAlias();
		$params = $objCat->getParams();
		$catID = $objCat->getID();

		$settings = new UELM_UniteCreatorSettings();

		$settings->addStaticText("Category ID: <b>$catID</b>","some_name");
		$settings->addTextBox("category_title", $title, esc_html__("Category Title","unlimited-elements"));
		$settings->addTextBox("category_alias", $alias, esc_html__("Category Name","unlimited-elements"));
		$settings->addIconPicker("icon","",esc_html__("Category Icon", "unlimited-elements"));

		$settings = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_MANAGER_ADDONS_CATEGORY_SETTINGS, $settings, $objCat, $this->filterAddonType);

		$settings->setStoredValues($params);

		return($settings);
	}

	private function a______HEADER_LINE______(){}


	/**
	 * put catalog filters
	 */
	protected function putFiltersCatalog(){


		if(UELM_GlobalsUC::$enableWebCatalog == false)
			return(false);

		if($this->objAddonType->allowManagerWebCatalog == false)
			return(false);

		//don't filter web catalog mode
		if($this->objAddonType->isWebCatalogMode == true)
			return(false);

		$classActive = "class='uc-active'";

		$filterCatalog = $this->filterCatalogState;

		?>
			<div class="uc-filter-set-wrapper uc-filter-set-checkbox">
				<label>
					<input id="uc_filter_catalog_installed" type="checkbox" data-state_active="<?php 
						echo esc_attr(self::FILTER_CATALOG_INSTALLED);
						?>" data-state_notactive="<?php echo esc_attr(self::FILTER_CATALOG_MIXED);?>" <?php echo ( $filterCatalog == self::FILTER_CATALOG_INSTALLED ? ' checked="checked"' : '' ); ?>>
					<?php esc_attr_e("Show Only Installed", "unlimited-elements")?>
				</label>
			</div>

		<?php
	}

	/**
	 * put search filter
	 */
	protected function putFilterSearch(){

		$textPlaceholder = __("Search...","unlimited-elements");

		?>
			<div class="uc-filters-set-search">

				<input id="uc_manager_addons_input_search" class="uc-filter-search-input" type="text" placeholder="<?php echo esc_attr($textPlaceholder)?>">

				<i id="uc_manager_addons_icon_search" class="fa fa-search uc-icon-search" title="<?php esc_attr_e("Search Widget","unlimited-elements")?>"></i>

				<a id="uc_manager_addons_clear_search" href="javascript:void(0)" onfocus="this.blur()" class="uc-filter-button-clear" title="<?php esc_attr_e("Clear Search","unlimited-elements")?>" style="display:none" >
					<i class="fa fa-times uc-icon-clear"></i>
				</a>
			</div>

		<?php

	}


	/**
	 * put items filters links
	 */
	private function putItemsFilters_active(){

		$classActive = "class='uc-active'";
		$filter = $this->filterActive;
		if(empty($filter))
			$filter = "all";
		/*
		$style = "style='display:none'";
		if($this->filterCatalogState == "installed")
			$style = "";
		*/

		$arrFilter = array();
		$arrFilter["all"] = __("Show all states", "unlimited-elements");
		$arrFilter["active"] = __("Active state only","unlimited-elements");
		$arrFilter["not_active"] = __("Not active state only","unlimited-elements");

		$htmlSelect = UELM_HelperHtmlUC::getHTMLSelect($arrFilter, $filter, "id='uc_manager_filter_active' class='uc-select-filter-active'", true);

		?>
		<div class="uc-filter-set-wrapper uc-filter-set-active" 
				<?php 
				//show only if installed
				if($this->filterCatalogState != "installed") {
					?>
					style='display:none'
					<?php
				}
				?>
			>

			<?php 
			uelm_echo($htmlSelect); 
			?>

		</div>
		<?php
	}


	/**
	 * put filters - function for override
	 */
	private function putHeaderLineFilters(){

		if($this->showAddonFilters == false)
			return(false);

		?>

		<div class="uc-items-filters">

			<?php
				if($this->enableActiveFilter)
					$this->putItemsFilters_active();
			?>

			<?php
				if($this->showAddonFilters_catalog == true)
					$this->putFiltersCatalog()
			?>

			<?php
				if($this->enableSearchFilter == true)
					$this->putFilterSearch();
			?>

			<?php $this->putShortcode()?>

			<div class="unite-clear"></div>

		</div>

		<?php
	}

	/**
	 * put html header line
	 * function for override
	 */
	protected function putHtmlHeaderLine(){

		?>
		<div class="uc-manager-header-line">

			<?php if(!empty($this->headerLineText)):?>
			<div class="uc-manager-header-text">
				<?php 
				uelm_echo($this->headerLineText);
				?>
			</div>
			<?php endif?>

			<div class="uc-manager-header-filters">
				<?php 
				$this->putHeaderLineFilters();
				?>
			</div>

			<div class="unite-clear"></div>

		</div>

		<?php

	}

		/**
	 * put after buttons html
	 */
	protected function putHtmlAfterButtons(){

		if($this->enableEditGroup == false)
			return(false);

		?>
		 	<div id="uc_manager_group" class="uc-manager-group">

		 		<a href="javascript:void(0)" class="uc-manager-group-back"><?php esc_attr_e("Back To Category","unlimited-elements")?></a>

		 		<div class="uc-manager-group-text"><?php esc_attr_e("Template Kit","unlimited-elements")?></div>

		 	</div>

		<?php

	}

	private function a______STATUS_LINE______(){}

	/**
	 * add copy panel to status line
	 *
	 */
	protected function putStatusLineOperationsAdditions(){

		if($this->enableCopy == true):
		?>
		<div class="item_operations_wrapper uc-bottom-copypanel" style="display:none">

			 <?php esc_attr_e("Copied", "unlimited-elements")?>: <span class="uc-copypanel-addon"></span>

			 <a class="unite-button-secondary button-disabled uc-button-copypanel-move" href="javascript:void(0)"><?php esc_attr_e("Move Here","unlimited-elements")?></a>
			 <a class="unite-button-secondary uc-button-copypanel-cancel" href="javascript:void(0)"><?php esc_attr_e("Cancel","unlimited-elements")?></a>
		 </div>

		<?php
		endif;

	}


	private function a______OTHERS______(){}



	/**
	 * get addon type object
	 */
	public function getObjAddonType(){

		return($this->objAddonType);
	}

	/**
	 * return if layouts or addons type
	 */
	public function getIsLayoutType(){
		$this->validateAddonType();

		return($this->isLayouts);
	}


	/**
	 * get no items text
	 */
	protected function getNoItemsText(){

		$text = $this->objAddonType->textNoAddons;

		UELM_UniteFunctionsUC::validateNotEmpty($text,"text addon type");

		return($text);
	}


	/**
	 * get html categories select
	 */
	protected function getHtmlSelectCats(){

		if($this->hasCats == false)
			UELM_UniteFunctionsUC::throwError("the function ");

		$htmlSelectCats = $this->objCats->getHtmlSelectCats($this->filterAddonType);

		return($htmlSelectCats);
	}


	/**
	 * put content to items wrapper div
	 */
	protected function putListWrapperContent(){
		$addonType = $this->filterAddonType;
		if(empty($addonType))
			$addonType = "default";

		$filepathEmptyAddons = UELM_GlobalsUC::$pathProviderViews."empty_addons_text_{$addonType}.php";
		if(file_exists($filepathEmptyAddons) == false)
			return(false);

		?>
		<div id="uc_empty_addons_wrapper" class="uc-empty-addons-wrapper" style="display:none">

			<?php include $filepathEmptyAddons?>

		</div>
		<?php
	}

	/**
	 * put multiple buttons
	 */
	protected function putMultipleItemButtons(){
		?>
		 	<a data-action="remove_item" type="button" class="unite-button-secondary button-disabled uc-button-item uc-multiple-items"><?php esc_html_e("Delete","unlimited-elements")?></a>
		 	<a data-action="duplicate_item" type="button" class="unite-button-secondary button-disabled uc-button-item uc-multiple-items"><?php esc_html_e("Duplicate","unlimited-elements")?></a>

	 		<?php if($this->enableActiveFilter == true):?>

		 		<a data-action="activate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-notactive-item uc-multiple-items"><?php esc_html_e("Activate","unlimited-elements")?></a>
		 		<a data-action="deactivate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-active-item uc-multiple-items"><?php esc_html_e("Deactivate","unlimited-elements")?></a>

	 		<?php endif?>

		<?php
	}


	/**
	 * put items buttons
	 */
	protected function putItemsButtons(){

		if($this->enableActions == false)
			return(false);

		$textImport = esc_html__("Import ","unlimited-elements") . $this->textPlural;
		$textEdit = esc_html__("Edit ","unlimited-elements") . $this->textSingle;
		$textTest = "Test ".$this->textSingle;

		?>

			<?php
			 UELM_UniteProviderFunctionsUC::doAction(UELM_UniteCreatorFilters::ACTION_MANAGER_ITEM_BUTTONS1);
			?>
 			<a data-action="add_addon" type="button" class="unite-button-primary button-disabled uc-button-item uc-button-add"><?php echo esc_html($this->textAddAddon)?></a>
 			<a data-action="import_addon" type="button" class="unite-button-secondary button-disabled uc-button-item uc-button-add"><?php echo esc_html($textImport)?></a>

 			<?php
				if($this->putItemButtonsType == "multiple"){
					$this->putMultipleItemButtons();
					return(false);
				}
 			?>

			<?php
			 	UELM_UniteProviderFunctionsUC::doAction(UELM_UniteCreatorFilters::ACTION_MANAGER_ITEM_BUTTONS2);
			?>

		 		<a data-action="remove_item" type="button" class="unite-button-secondary button-disabled uc-button-item"><?php esc_html_e("Delete","unlimited-elements")?></a>
		 		<a data-action="edit_addon" type="button" class="unite-button-primary button-disabled uc-button-item uc-single-item"><?php echo esc_html($textEdit)?> </a>
		 		<a data-action="preview_addon" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php esc_html_e("Preview", "unlimited-elements")?> </a>

		 		<?php if($this->showTestAddon):?>
		 		<a data-action="test_addon" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php echo esc_html($textTest)?></a>
				<?php endif?>

				<?php
				 UELM_UniteProviderFunctionsUC::doAction(UELM_UniteCreatorFilters::ACTION_MANAGER_ITEM_BUTTONS3);
				?>

				<?php if($this->enablePreview == true):?>

		 			<a data-action="preview_addon" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php esc_html_e("Preview", "unlimited-elements")?> </a>

				<?php endif?>

	 		<?php if($this->enableActiveFilter == true):?>

		 		<a data-action="activate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-notactive-item"><?php esc_html_e("Activate","unlimited-elements")?></a>
		 		<a data-action="deactivate_addons" type="button" class="unite-button-secondary button-disabled uc-button-item uc-active-item"><?php esc_html_e("Deactivate","unlimited-elements")?></a>

	 		<?php endif?>

	 		<?php if($this->enableMakeScreenshots == true):?>

	 		<a data-action="make_screenshots" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php esc_html_e("Make Thumb", "unlimited-elements")?> </a>
	 		<a data-action="make_screenshots" type="button" class="unite-button-secondary button-disabled uc-button-item uc-multiple-items"><?php esc_html_e("Make Thumbs", "unlimited-elements")?> </a>

	 		<?php endif?>
		<?php
	}

	/**
	 * get current layout shortcode template
	 */
	protected function getShortcodeTemplate(){

		$shortcodeTemplate = "{blox_page id=%id% title=\"%title%\"}";

		return($shortcodeTemplate);
	}


	/**
	 * put shortcode in the filters area
	 */
	protected function putShortcode(){

		if($this->objAddonType->enableShortcodes == false)
			return(false);

		$shortcodeTemplate = $this->getShortcodeTemplate();
		$shortcodeTemplate = htmlspecialchars($shortcodeTemplate);

		?>
		<div class="uc-single-item-related">
			<div class="uc-filters-set-title"><?php esc_html_e("Shortcode", "unlimited-elements")?>:</div>
			<div class="uc-filters-set-content"> <input type="text" readonly class="uc-filers-set-shortcode" data-template="<?php echo esc_attr($shortcodeTemplate)?>" value=""></div>
		</div>

		<?php

	}


	/**
	 * get category settings html
	 */
	public function getCatSettingsHtmlFromData($data){

		$catID = UELM_UniteFunctionsUC::getVal($data, "catid");
		UELM_UniteFunctionsUC::validateNotEmpty($catID, "category id");

		$objCat = new UELM_UniteCreatorCategory();
		$objCat->initByID($catID);

		$settings = $this->getCatagorySettings($objCat);

		$output = new UELM_UniteSettingsOutputWideUC();
		$output->init($settings);

		UELM_UniteFunctionsUC::obStart();
		$output->draw("uc_category_settings");

		$htmlSettings = ob_get_contents();

		ob_end_clean();

		$response = array();
		$response["html"] = $htmlSettings;

		return($response);
	}

	/**
	 *
	 * get properties html from data
	 */
	public function getAddonPropertiesDialogHtmlFromData($data){

		if($this->objAddonType->isLayout == false)
			UELM_UniteFunctionsUC::throwError("The addon type should be layouts for props");

		$layoutID = UELM_UniteFunctionsUC::getVal($data, "id");
		$objLayout = new UELM_UniteCreatorLayout();
		$objLayout->initByID($layoutID);

		$settings = $objLayout->getPageParamsSettingsObject();

		$htmlSettings = UELM_HelperHtmlUC::drawSettingsGetHtml($settings,"settings_addon_props");

		$output = array();
		$output["html"] = $htmlSettings;

		return($output);
	}






	/**
	 * put scripts
	 */
	private function putScripts(){

		$arrPlugins = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_MANAGER_ADDONS_PLUGINS, array());

		$script = "
			var g_ucManagerAdmin;
			
			jQuery(document).ready(function(){
				var selectedCatID = \"{$this->selectedCategory}\";
				g_ucManagerAdmin = new UCManagerAdmin();";

		if(!empty($arrPlugins)){
			foreach($arrPlugins as $plugin)
				$script .= "\n				g_ucManagerAdmin.addPlugin('{$plugin}');";
		}

		$script .= "
				g_ucManagerAdmin.initManager(selectedCatID);
			});
		";


		UELM_UniteProviderFunctionsUC::printCustomScript($script);
	}


	/**
	 * put preview tooltips
	 */
	protected function putPreviewTooltips(){
		?>
		<div id="uc_manager_addon_preview" class="uc-addon-preview-wrapper" style="display:none"></div>
		<?php
	}

	/**
	 * get single item menu
	 */
	protected function getMenuSingleItemActions(){

		$arrMenuItem = array();
		
		if($this->enableEditAddon == true) {
			$arrMenuItem["edit_addon_blank"] = esc_html__("Edit In New Tab","unlimited-elements");
		}
		
		if($this->enableEditGroup)
			$arrMenuItem["edit_layout_group"] = esc_html__("Edit Template Kit","unlimited-elements");

		if($this->enableViewThumbnail)
			$arrMenuItem["preview_thumb"] = esc_html__("View Thumbnail","unlimited-elements");

		if($this->enableMakeScreenshots)
			$arrMenuItem["make_screenshots"] = esc_html__("Make Thumbnail","unlimited-elements");

		$arrMenuItem["quick_edit"] = esc_html__("Quick Edit","unlimited-elements");

		if($this->enableCopy == true)
			$arrMenuItem["copy"] = esc_html__("Copy","unlimited-elements");

		$arrMenuItem["remove_item"] = esc_html__("Delete","unlimited-elements");

		if($this->showTestAddon){
			$arrMenuItem["test_addon"] = esc_html__("Test ","unlimited-elements").$this->textSingle;
			$arrMenuItem["test_addon_blank"] = esc_html__("Test In New Tab","unlimited-elements");
		}

		$arrMenuItem["export_addon"] = esc_html__("Export ","unlimited-elements").$this->textSingle;

		return($arrMenuItem);
	}


	/**
	 * put single item actions menu
	 */
	private function putMenuSingleItemActions(){

		$arrMenuItem = $this->getMenuSingleItemActions();

		if(!is_array($arrMenuItem))
			$arrMenuItem = array();

		$this->putRightMenu($arrMenuItem, "rightmenu_item_actions", "single_item_actions");

	}


	/**
	 * put additional html here
	 */
	protected function putAddHtml(){

		$this->putDialogQuickEdit();
		$this->putDialogAddAddon();
		$this->putDialogAddonProperties();
		$this->putDialogImportAddons();
		$this->putDialogPreviewAddons();

		$this->putMenuSingleItemActions();

		if($this->putDialogDebug == true)
			$this->putDialogDebug();

		if($this->isWebCatalogMode == true)
			$this->putPreviewTemplateDialog();

		if($this->showAddonTooltip)
			$this->putPreviewTooltips();

		$this->putScripts();
	}


	/**
	 * put init items, will not run, because always there are cats
	 */
	protected function putInitItems(){

		if($this->hasCats == true)
			return(false);

		$htmlAddons = $this->getCatAddonsHtml(null);
		
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $htmlAddons;
	}


	/**
	 *
	 * set the custom data to manager wrapper div
	 */
	protected function onBeforePutHtml(){

		$addonsType = $this->objAddonType->typeNameDistinct;

		$addHTML = "data-addonstype=\"{$addonsType}\"";

		$this->setManagerAddHtml($addHTML);
	}



}
