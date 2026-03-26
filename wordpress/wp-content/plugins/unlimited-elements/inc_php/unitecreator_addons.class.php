<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorAddons extends UELM_UniteElementsBaseUC{

	protected function a_STATIC_METHODS(){
	}

	/**
	 * get addons thumbnails
	 */
	public function getArrAddonPreviewUrls($arrAddons, $keyType){

		$arrPreviews = array();

		foreach($arrAddons as $addon){
			switch($keyType){
				case "title":
					$key = $addon->getTitle();
				break;
				default:
					$key = $addon->getName();
				break;
			}

			$urlPreview = $addon->getPreviewImageUrl();

			if(empty($urlPreview))
				continue;

			$urlPreview = UELM_HelperUC::URLtoAssetsRelative($urlPreview);

			$arrPreviews[$key] = $urlPreview;
		}

		return ($arrPreviews);
	}
	
	
	/**
	 * get active filter where string
	 */
	public static function getFilterActiveWhere($filterActive = null, $prefix = null, $addonType = ""){

		if($filterActive === null)
			$filterActive = UELM_UniteCreatorManagerAddons::getStateFilterActive($addonType);

		$where = "";

		//set active fitler where
		switch($filterActive){
			case "active":
				$where = "is_active=1";
			break;
			case "not_active":
				$where = "is_active=0";
			break;
		}

		if(!empty($where) && !empty($prefix))
			$where = $prefix . "." . $where;

		return ($where);
	}

	protected function a______GETTERS_________(){
	}

	/**
	 *
	 * get items by id's
	 */
	private function getAddonsByIDs($addonIDs){
		
		$strAddons = implode(",", $addonIDs);
		
		UELM_UniteFunctionsUC::validateIDsList($strAddons,"addons id's");
		
		$tableAddons = UELM_GlobalsUC::$table_addons;
		$sql = "select * from {$tableAddons} where id in({$strAddons})";
		$arrAddons = $this->db->fetchSql($sql);
		
		return ($arrAddons);
	}

	/**
	 * get html of categories and items.
	 */
	protected function getCatsAndAddonsHtml($catID, $type, $data = null, $parentID = null){

		$objManager = UELM_UniteCreatorManager::getObjManagerByAddonType($type, $data);

		$options = array();
		if(!empty($parentID))
			$options["parent_id"] = $parentID;

		$response = $objManager->getCatsAndAddonsHtml($catID, "", false, $options);

		return ($response);
	}

	/**
	 *
	 * get layouts array
	 */
	public function getArrAddonsShort($order = "", $params = array(), $addonType = null){
		
		if(empty($params))
			$params = array();

		if(!empty($addonType))
			$params["addontype"] = $addonType;

		$arrWhere = array();

		$filterNames = UELM_UniteFunctionsUC::getVal($params, "filter_names");
		if(!empty($filterNames)){
			$strNames = "'" . implode("','", $filterNames) . "'";
			$arrWhere[] = "name in ($strNames)";
		}

		$addonType = UELM_UniteFunctionsUC::getVal($params, "addontype");

		$filterActive = UELM_UniteFunctionsUC::getVal($params, "filter_active");
		if(!empty($filterActive))
			$arrWhere[] = self::getFilterActiveWhere($filterActive, null, $addonType);

		$arrWhere[] = $this->db->getSqlAddonType($addonType);

		$where = "";
		if(!empty($arrWhere))
			$where = implode(" and ", $arrWhere);

		$response = $this->db->fetch(UELM_GlobalsUC::$table_addons, $where, $order);


		return ($response);
	}


	/**
	 * get addons list with name / title
	 */
	public function getArrAddonsNameTitle($order = "", $params = array(), $addonType = null, $isAlias = false){

		$arrAddons = $this->getArrAddonsShort($order, $params, $addonType);

		$field = "name";
		if($isAlias == true)
			$field = "alias";

		$arrAssoc = UELM_UniteFunctionsUC::arrayToAssoc($arrAddons, $field, "title");

		return ($arrAssoc);
	}

	/**
	 *
	 * get addons array
	 */
	public function getArrAddons($order = "", $params = array(), $addonType = null){

		if(empty($params))
			$params = array();

		$response = $this->getArrAddonsShort($order, $params, $addonType);

		$arrAddons = array();
		foreach($response as $record){
			$objAddon = new UELM_UniteCreatorAddon();
			$objAddon->initByDBRecord($record);
			$arrAddons[] = $objAddon;
		}
		
		return ($arrAddons);
	}

	/**
	 *
	 * get category items
	 */
	public function getCatAddons($catID, $isShort = false, $filterActive = null, $addonType = null, $includeImages = false, $extra = array()){

		$arrWhere = array();

		if(is_numeric($catID))
			$catID = (int)$catID;

		if($catID === null)
			$catID = "all";

		//get catID where
		if($catID === "all"){
			$arrWhere = array();
		}elseif(is_numeric($catID)){
			$catID = (int)$catID;
			$arrWhere[] = "catid=$catID";
		}else{      //multiple - array of id's

			if(is_array($catID) == false)
				UELM_UniteFunctionsUC::throwError("catIDs could be array or number");

			$strCats = implode(",", $catID);
			$strCats = $this->db->escape($strCats);    //for any case
			$arrWhere[] = "catid in($strCats)";
		}

		$whereFilterActive = self::getFilterActiveWhere($filterActive, null, $addonType);
		if(!empty($whereFilterActive))
			$arrWhere[] = $whereFilterActive;

		//set addon type - if specific category - no need
		if(is_numeric($catID) == false || empty($catID) || $catID === "all")
			$arrWhere[] = $this->db->getSqlAddonType($addonType);

		$filterSearch = UELM_UniteFunctionsUC::getVal($extra, "filter_search");
		$filterSearch = trim($filterSearch);

		if(!empty($filterSearch)){
			$filterSearch = $this->db->escape($filterSearch);
			$filterSearch = strtolower($filterSearch);

			$arrWhere[] = "title like '%$filterSearch%'";
		}

		$where = "";
		if(!empty($arrWhere))
			$where = implode(" and ", $arrWhere);

		$records = $this->db->fetch(UELM_GlobalsUC::$table_addons, $where, "catid, ordering");

		$arrAddons = array();
		foreach($records as $record){
			$objAddon = new UELM_UniteCreatorAddon();
			$objAddon->initByDBRecord($record);

			if($isShort == true){
				$arrAddons[] = $objAddon->getArrShort($includeImages);
			}else{
				$arrAddons[] = $objAddon;
			}
		}

		return ($arrAddons);
	}

	/**
	 * remove non found categories
	 * with 0 addons or if title not match
	 */
	private function getAddonsWidthCategories_removeEmptyCats($arrCatsAssoc, $searchString){

		if(empty($searchString))
			return ($arrCatsAssoc);

		foreach($arrCatsAssoc as $catTitle => $arrCat){
			$isTitleMatch = UELM_UniteFunctionsUC::isStringContains($catTitle, $searchString);
			$arrAddons = UELM_UniteFunctionsUC::getVal($arrCat, "addons");

			if(empty($arrAddons))
				$arrAddons = array();

			$numAddons = count($arrAddons);

			if($numAddons == 0 && $isTitleMatch == false)
				unset($arrCatsAssoc[$catTitle]);
		}

		return ($arrCatsAssoc);
	}

	/**
	 * get addons by categories
	 * $publishedCatOnly - get only from published ones
	 */
	public function getAddonsWidthCategories($publishedCatOnly = true, $isShort = false, $type = "", $extra = null){

		$getCatObjects = UELM_UniteFunctionsUC::getVal($extra, "get_cat_objects");
		$getCatObjects = UELM_UniteFunctionsUC::strToBool($getCatObjects);

		$objCats = new UELM_UniteCreatorCategories();

		if($getCatObjects == true)
			$arrCats = $objCats->getCatRecordsWithAddType("uncategorized", $type);
		else
			$arrCats = $objCats->getCatsShort("uncategorized", $type);

		$arrIDs = array_keys($arrCats);

		$arrCatsAssoc = array();

		//prepare structure
		foreach($arrCats as $catID => $record){
			//if it's record
			if(is_array($record))
				$title = $record["title"];
			else
				$title = $record;

			$cat = array();
			$cat["id"] = $catID;
			$cat["title"] = $title;
			$cat["type"] = $type;

			//add cat object
			if($getCatObjects == true && !empty($catID)){
				$objCat = new UELM_UniteCreatorCategory();
				$objCat->initByRecord($record);
				$cat["objcat"] = $objCat;
			}

			$cat["addons"] = array();

			$arrCatsAssoc[$title] = $cat;
		}

		$filterActive = null;
		if($publishedCatOnly == true)
			$filterActive = "active";

		$arrAdons = array();
		if(!empty($arrCatsAssoc))
			$arrAdons = $this->getCatAddons(null, false, $filterActive, $type, false, $extra);

		//put addons to category
		foreach($arrAdons as $addon){
			$addonCatTitle = $addon->getCatTitle();
			$addonCatID = $addon->getCatID();

			$addonCatID = (int)$addonCatID;

			$name = $addon->getName();

			if($isShort == true){
				$addonForInsert = $addon->getArrShort(true);
				$addonForInsert["name"] = $addon->getNameByType();
			}else
				$addonForInsert = $addon;

			$insertKey = $addonCatTitle;
			if($addonCatID === 0)
				$insertKey = UELM_HelperUC::getText("uncategorized");

			//skip addons without category
			if(empty($insertKey))
				continue;

			$arrCatsAssoc[$insertKey]["addons"][$name] = $addonForInsert;
		}

		//in case of search, filter empty categories
		$filterSearch = UELM_UniteFunctionsUC::getVal($extra, "filter_search");
		if(!empty($filterSearch))
			$arrCatsAssoc = $this->getAddonsWidthCategories_removeEmptyCats($arrCatsAssoc, $filterSearch);

		return ($arrCatsAssoc);
	}

	/**
	 * get addons with categories by comfortable format
	 */
	public function getAddonsWidthCategoriesShort($publishedCatOnly = true, $type = ""){

		$arrCats = $this->getAddonsWidthCategories($publishedCatOnly, true, $type);

		return $arrCats;
	}

	/**
	 * check if addon exists by name
	 */
	public function isAddonExistsByName($name, $addonType = null){

		$name = $this->db->escape($name);

		if(empty($addonType))
			$where = "name='{$name}'";
		else{
			$where = "alias='{$name}'";
			$where .= " and " . $this->db->getSqlAddonType($addonType);
		}

		$response = $this->db->fetch(UELM_GlobalsUC::$table_addons, $where);

		return (!empty($response));
	}

	/**
	 * get addon type from data
	 */
	public function getAddonTypeFromData($data){

		$type = UELM_UniteFunctionsUC::getVal($data, "addontype");

		if(empty($type))
			$type = UELM_UniteFunctionsUC::getVal($data, "type");

		UELM_HelperUC::runProviderFunc("validateDataAddonsType", $type, $data);

		return ($type);
	}

	/**
	 *
	 * get max order from categories list
	 */
	public function getMaxOrder($catID){

		UELM_UniteFunctionsUC::validateNotEmpty($catID, "category id");

		UELM_UniteFunctionsUC::validateNumeric($catID, "category id");
		
		$tableAddons = UELM_GlobalsUC::$table_addons;
		$query = "select MAX(ordering) as maxorder from {$tableAddons} where catid={$catID}";

		$rows = $this->db->fetchSql($query);

		$maxOrder = 0;
		if(count($rows) > 0)
			$maxOrder = $rows[0]["maxorder"];

		if(!is_numeric($maxOrder))
			$maxOrder = 0;

		return ($maxOrder);
	}

	/**
	 * get number of addons by category
	 */
	public function getNumAddons($catID = null, $filterActive = null, $objTypeName = null){
		
		$tableAddons = UELM_GlobalsUC::$table_addons;
		$addonType = $objTypeName->typeName;

		$arrWhere = array();
		if(!empty($filterActive)){
			$whereActive = self::getFilterActiveWhere($filterActive, "a", $addonType);
			if(!empty($whereActive))
				$arrWhere[] = $whereActive;
		}

		if($objTypeName->isBasicType == true)
			$addonType = null;

		$arrWhere[] = $this->db->getSqlAddonType($addonType);

		//all addons
		if($catID === null){
			$query = "select count(*) as num_addons from {$tableAddons}";
		}else{
			
			UELM_UniteFunctionsUC::validateNumeric($catID,"Category ID");
			
			$query = "select count(*) as num_addons from {$tableAddons} as a";
			$arrWhere[] = "a.catid=$catID";
		}

		if(!empty($arrWhere))
			$query .= " where " . implode(" and ", $arrWhere);

		$response = $this->db->fetchSql($query);

		if(empty($response))
			UELM_UniteFunctionsUC::throwError("Can't get number of zero addons");

		$numAddons = $response[0]["num_addons"];

		return ($numAddons);
	}

	/**
	 * get addon output, for the editor
	 */
	public function getAddonOutput($objAddon, $options = array()){
		
		$isWrap = UELM_UniteFunctionsUC::getVal($options, "wrap", false);
		$includeSelectors = UELM_UniteFunctionsUC::getVal($options, "selectors", false);
		$rootId = UELM_UniteFunctionsUC::getVal($options, "root_id");
		
		$params = array(
			"wrap_js_timeout" => $isWrap,
			"add_selectors_css" => $includeSelectors,
			"root_id" => $rootId,
		);
		
		$scriptsHardCoded = false;
		
		$isInFooter = UELM_HelperProviderCoreUC_EL::getGeneralSetting("js_in_footer");
		$isInFooter = UELM_UniteFunctionsUC::strToBool($isInFooter);
		
		$isBG = $objAddon->isBackground();
		
		if ($isInFooter == false)
			$scriptsHardCoded = true;
		
		if(UELM_GlobalsProviderUC::$isInsideEditor == true)
			$scriptsHardCoded = true;
		
		if(UELM_GlobalsProviderUC::$isUnderAjaxDynamicTemplate == true)
			$scriptsHardCoded = true;

		//output hard coded for background addons
		if($isBG == true)
			$scriptsHardCoded = true;
			
        $cssFilesPlace = "body";
        
		if(UELM_GlobalsProviderUC::$isInsideEditor == true)
			$cssFilesPlace = "body";
	        
		$putCssIncludesInBody = ($cssFilesPlace == "body") ? true : false;
		
        
		$cacheOutput = false;
		if(UELM_GlobalsProviderUC::$isInsideEditor == true)
			$cacheOutput = true;
		
		if($cacheOutput == true){
			UELM_UniteFunctionsUC::obStart();
		}
		
		$objOutput = new UELM_UniteCreatorOutput();
		$objOutput->setProcessType(UELM_UniteCreatorParamsProcessor::PROCESS_TYPE_OUTPUT_BACK);
		$objOutput->checkOutputDebug($objAddon);
		$objOutput->initByAddon($objAddon);

        if($cssFilesPlace == "footer")
        	$objOutput->processIncludes("css");
		
		
		$htmlBefore = null;
		
		if($cacheOutput == true){
			$htmlBefore = ob_get_contents();			
			ob_end_clean();
		}
		
		$html = $objOutput->getHtmlBody($scriptsHardCoded, $putCssIncludesInBody, true, $params);
		
		if(!empty($htmlBefore)){
			$html = $htmlBefore.$html;
		}
		
		$includes = $objOutput->getProcessedIncludes(true);
		$outputId = $objOutput->getWidgetID();

		$arr = array();
		$arr["html"] = $html;
		$arr["includes"] = $includes;

		if($includeSelectors === true)
			$arr["output_id"] = $outputId;

		return $arr;
	}
	
	
	/**
	 * check addon global variables
	 */
	private function checkInitAddonGlobalVars($addonData){
		
		//set edit mode
		$source = UELM_UniteFunctionsUC::getVal($addonData, "source");
		if($source == "editor")
			UELM_GlobalsProviderUC::$isInsideEditor = true;
		
		$platform = UELM_UniteFunctionsUC::getVal($addonData, "platform");
		
		if($platform == "gutenberg")
			UELM_GlobalsProviderUC::setGutenbergPlatform();
		
		
	}
	
	
	/**
	 * get addon output data
	 */
    public function getAddonOutputData($addonData, $isWrap = false){

        $this->checkInitAddonGlobalVars($addonData);

        $hasSettings  = UELM_UniteFunctionsUC::getVal($addonData, "settings");
        $hasConfig    = UELM_UniteFunctionsUC::getVal($addonData, "config");
        $hasElSettings= UELM_UniteFunctionsUC::getVal($addonData, "elementor_settings");

        if(empty($hasSettings) && empty($hasConfig) && empty($hasElSettings)){

            $tmpAddon = $this->initAddonByData($addonData);
			
			if(method_exists($tmpAddon, "getParamsDefaults")){
				$defaults = $tmpAddon->getParamsDefaults();
			} elseif(method_exists($tmpAddon, "getParamsManager")
					 && method_exists($tmpAddon->getParamsManager(), "getDefaultsAssoc")) {
				$defaults = $tmpAddon->getParamsManager()->getDefaultsAssoc();
			}
			
            // using test_slot2 for items only:  
            $td = $tmpAddon->getTestData(2);          
            $defaultItems = UELM_UniteFunctionsUC::getVal($td, "items");
            $defaults_tst = UELM_UniteFunctionsUC::getVal($td, "config", array());
            foreach($defaults_tst as $k => $v) {
                if(substr($k, 0, 5) == 'post_') {
                    $defaults[$k] = $v;
                }
            }

            if(!empty($defaults))
                $addonData["settings"] = $defaults;

            if(!empty($defaultItems))
                $addonData["items"] = $defaultItems;
        }

        $objAddon = $this->prepareAddonByData($addonData, true);

        $rootId = UELM_UniteFunctionsUC::getVal($addonData, "root_id");
        $includeSelectors = UELM_UniteFunctionsUC::getVal($addonData, "selectors");
        $includeSelectors = UELM_UniteFunctionsUC::strToBool($includeSelectors);

        $outputOptions = array(
            "root_id"    => $rootId,
            "selectors"  => $includeSelectors,
            "wrap"       => $isWrap,
        );

        $output = $this->getAddonOutput($objAddon, $outputOptions);
        return $output;
    }
	

	/**
	 * get addon config html by data
	 */
	public function getAddonConfigHTML($data){

		$objAddon = $this->initAddonByData($data);

		//init addon config
		$addonConfig = new UELM_UniteCreatorAddonConfig();
		$addonConfig->setStartAddon($objAddon);

		$html = $addonConfig->getHtmlFrame();

		$response = array();
		$response["html_config"] = $html;

		//get output data on live mode
		$getOutputData = UELM_UniteFunctionsUC::getVal($data, "getcontent");
		$getOutputData = UELM_UniteFunctionsUC::strToBool($getOutputData);
		if($getOutputData == true){
			$outputData = $this->getLayoutAddonOutputData($data);
			$response["output"] = $outputData;
		}

		return ($response);
	}

	/**
	 * get item settings html
	 */
	public function getAddonSettingsHTMLFromData($data){
		
		$this->checkInitAddonGlobalVars($data);
		
		$objAddon = $this->initAddonByData($data);
		
		//remember the addon for use inside the settings classes (used for multisource)
		UELM_GlobalsProviderUC::$activeAddonForSettings = $objAddon;
		
		$html = $objAddon->getHtmlConfig(false, true);
		
		$html = UELM_UniteFunctionsUC::minifyHTML($html);
		
		return ($html);
	}

	/**
	 * get addon editor data
	 * including addon config, and output if needed
	 */
	public function getAddonEditorData($data){

		$objAddon = $this->initAddonByData($data);

		$addonType = $objAddon->getType();

		$arrData = array();
		$arrData["addontype"] = $addonType;
		$arrData["name"] = $objAddon->getName();

		$arrExtra = array();
		$arrExtra["title"] = $objAddon->getTitle();
		$arrExtra["url_icon"] = $objAddon->getUrlIcon();
		$arrExtra["admin_labels"] = $objAddon->getAdminLabels();
		$arrExtra["has_items"] = $objAddon->isHasItems();
		$arrExtra["num_items"] = $objAddon->getNumItems();
		$arrExtra["id"] = $objAddon->getID();

		$objAddon->setIsInsideGrid();
		$arrExtra["html_settings"] = $objAddon->getHtmlConfig(false, true);

		$arrData["extra"] = $arrExtra;

		$returnOutput = UELM_UniteFunctionsUC::getVal($data, "return_output");
		$returnOutput = UELM_UniteFunctionsUC::strToBool($returnOutput);
		if($returnOutput == true){
			$objLayoutOutput = new UELM_UniteCreatorLayoutOutput();
			$objLayoutOutput->setAddonType($addonType);
			$arrData["output"] = $objLayoutOutput->getAddonOutput($objAddon);
		}

		return ($arrData);
	}

	/**
	 * get item settings html
	 */
	public function getAddonItemsSettingsHTMLFromData($data){

		$addonID = UELM_UniteFunctionsUC::getVal($data, "addonid");

		$addon = new UELM_UniteCreatorAddon();
		$addon->initByID($addonID);

		$html = $addon->getHtmlItemConfig();

		return ($html);
	}

	/**
	 * check if needed helper editor on admin addon output
	 */
	public function isHelperEditorNeeded(UELM_UniteCreatorAddon $addon){

		$hasItems = $addon->isHasItems();
		if($hasItems == false)
			return (false);

		$isItemsEditorExists = $addon->isEditorItemsAttributeExists();
		if($isItemsEditorExists == false)
			return (false);

		$isMainEditorExists = $addon->isEditorMainAttributeExists();
		if($isMainEditorExists == true)
			return (false);

		return (true);
	}

	/**
	 * prepare addon by data
	 */
public function prepareAddonByData($addonData, $isForOutput = false){
		
	$addonName = UELM_UniteFunctionsUC::getVal($addonData, "name");
	$addonType = UELM_UniteFunctionsUC::getVal($addonData, "addontype");
	$addonID   = UELM_UniteFunctionsUC::getVal($addonData, "id");

	// init addon
	$objAddon = new UELM_UniteCreatorAddon();

	if (empty($addonName) && !empty($addonID) && is_numeric($addonID)) {
		// init by id
		$objAddon->initByID($addonID);
	} else {
		// init by name or alias and type
		if (empty($addonType))
			$objAddon->initByName($addonName);
		else
			$objAddon->initByAlias($addonName, $addonType);
	}

	// Elementor settings path
	$elementorSettings = UELM_UniteFunctionsUC::getVal($addonData, "elementor_settings");
	if (!empty($elementorSettings)) {

		if (is_string($elementorSettings))
			$elementorSettings = UELM_UniteFunctionsUC::decodeContent($elementorSettings);

		$objIntegrate = new UELM_UniteCreatorElementorIntegrate();
		$objIntegrate->includePluginFiles();

		$objWidget = new UELM_UniteCreatorElementorBackgroundWidget();
		$objAddon  = $objWidget->setAddonSettingsFromElementorSettings($objAddon, $elementorSettings);

		return $objAddon;
	}

	// ---- Blox/UE settings path ----
	// prefer: settings -> settings_values -> config
	$arrSettings = UELM_UniteFunctionsUC::getVal($addonData, "settings");
	if (empty($arrSettings))
		$arrSettings = UELM_UniteFunctionsUC::getVal($addonData, "settings_values");
	if (empty($arrSettings))
		$arrSettings = UELM_UniteFunctionsUC::getVal($addonData, "config");

	if (is_string($arrSettings))
		$arrSettings = UELM_UniteFunctionsUC::decodeContent($arrSettings);

	// items may be passed explicitly or inside settings as uc_items
	$arrItems = UELM_UniteFunctionsUC::getVal($addonData, "items");
	if (empty($arrItems) && is_array($arrSettings) && array_key_exists("uc_items", $arrSettings)) {
		$arrItems = UELM_UniteFunctionsUC::getVal($arrSettings, "uc_items");
		unset($arrSettings["uc_items"]);
	}

	if (!empty($arrItems)) {
		if (is_string($arrItems))
			$arrItems = UELM_UniteFunctionsUC::decodeContent($arrItems);
		if (empty($arrItems))
			$arrItems = array();
		$objAddon->setArrItems($arrItems);
	}

	if (!empty($arrSettings) && is_array($arrSettings))
		$objAddon->setParamsValues($arrSettings);

	// optional fonts
	$arrFonts = UELM_UniteFunctionsUC::getVal($addonData, "fonts");
	if (!empty($arrFonts)) {
		if (is_string($arrFonts))
			$arrFonts = UELM_UniteFunctionsUC::decodeContent($arrFonts);
		$objAddon->setArrFonts($arrFonts);
	}

	// ---- Defaults hydration for output (when nothing was provided) ----
	if ($isForOutput === true) {
		$noExplicitSettings = empty($arrSettings) && empty($arrItems);

		if ($noExplicitSettings) {
			// 1) try defaults slot #2 (saved via saveAddonDefaultsFromData)
			$td           = $objAddon->getTestData(2); // ['config'=>..., 'items'=>...]
			// using test_slot2 for items only: $defaults     = UELM_UniteFunctionsUC::getVal($td, "config", array());
			$defaultItems = UELM_UniteFunctionsUC::getVal($td, "items");

			// 2) if slot empty take schema defaults
			if (empty($defaults)) {
				if (method_exists($objAddon, "getParamsDefaults")) {
					$defaults = $objAddon->getParamsDefaults();
				} else {
					$pm = method_exists($objAddon, "getParamsManager") ? $objAddon->getParamsManager() : null;
					if ($pm && method_exists($pm, "getDefaultsAssoc"))
						$defaults = $pm->getDefaultsAssoc();
				}
			}

			if (!empty($defaults) && is_array($defaults))
				$objAddon->setParamsValues($defaults);

			if (!empty($defaultItems))
				$objAddon->setArrItems($defaultItems);
		}
	}

	return $objAddon;
}

	protected function a____________SETTERS__________(){
	}

	/**
	 *
	 * delete addons
	 */
	private function deleteAddons($arrAddons){

		//sanitize
		$addons = array();

		foreach($arrAddons as $key => $addonID){
			
			$addon = new UELM_UniteCreatorAddon();
			$addon->initByID($addonID);
			
			$addons[] = $addon;
			$arrAddons[$key] = $addon->getID();
		}

		UELM_UniteProviderFunctionsUC::doAction("uc_before_delete_widgets", $arrAddons);

		$strAddonIDs = implode(",", $arrAddons);
		$this->db->delete(UELM_GlobalsUC::$table_addons, "id in($strAddonIDs)");

		foreach($addons as $addon){
			$addon->triggerAfterDeleteAction();
		}
	}

	/**
	 *
	 * save items order
	 */
	private function saveAddonsOrder($arrAddonIDs){
		
		//get items assoc
		$arrAddons = $this->getAddonsByIDs($arrAddonIDs);
		$arrAddons = UELM_UniteFunctionsUC::arrayToAssoc($arrAddons, "id");
		
		$order = 0;
		foreach($arrAddonIDs as $addonID){
			$order++;

			$arrAddon = UELM_UniteFunctionsUC::getVal($arrAddons, $addonID);
			if(!empty($arrAddon) && $arrAddon["ordering"] == $order)
				continue;
			
			$arrUpdate = array();
			$arrUpdate["ordering"] = $order;
			$this->db->update(UELM_GlobalsUC::$table_addons, $arrUpdate, array("id" => $addonID));
			
		}
	}

	/**
	 *
	 * copy items to some category
	 */
	private function copyAddons($arrAddonIDs, $catID){

		$category = new UELM_UniteCreatorCategories();
		$category->validateCatExist($catID);

		foreach($arrAddonIDs as $addonID){
			$this->copyAddon($addonID, $catID);
		}
	}

	/**
	 * migrate addons from some type to blox
	 * copy the addon, skip if exists, remove the type
	 */
	public function migrateAddonsFromType($addonType){

		uelm_dmp("migrate function disabled");
		exit();

		if(empty($addonType))
			return (false);

		$arrAddons = $this->getArrAddons("", array(), $addonType);

		foreach($arrAddons as $addon){
			$alias = $addon->getAlias();
			if(empty($alias))
				continue;

			$title = $addon->getTitle();

			$isExists = $this->isAddonExistsByName($alias);
			if($isExists == true)
				continue;

			$duplicatedID = $addon->duplicate();
			if(empty($duplicatedID))
				continue;

			$addonDuplicated = new UELM_UniteCreatorAddon();
			$addonDuplicated->initByID($duplicatedID);
			$addonDuplicated->convertToType(UELM_GlobalsUC::ADDON_TYPE_REGULAR_ADDON, $alias, $title);
		}
	}

	/**
	 * migrate addons from type to type
	 */
	public function migrateAddonsToType($typeFrom, $typeTo){

		$arrAddons = $this->getArrAddons("", array(), $typeFrom);

		$arrLog = array();
		$arrLog[] = "num addons: " . count($arrAddons);

		foreach($arrAddons as $addon){
			$title = $addon->getTitle();

			$converted = $addon->convertToType($typeTo);

			if($converted == true)
				$arrLog[] = "$title converted";
			else
				$arrLog[] = "$title skipped";
		}

		return ($arrLog);
	}

	/**
	 *
	 * move multiple items to some category
	 */
	private function moveAddons($arrAddonIDs, $catID){

		$category = new UELM_UniteCreatorCategories();
		$category->validateCatExist($catID);

		foreach($arrAddonIDs as $addonID){
			$this->moveAddon($addonID, $catID);
		}
	}

	/**
	 *
	 * move addons to some category by change category id
	 */
	private function moveAddon($addonID, $catID){

		$addonID = (int)$addonID;
		$catID = (int)$catID;

		$arrUpdate = array();
		$arrUpdate["catid"] = $catID;
		$this->db->update(UELM_GlobalsUC::$table_addons, $arrUpdate, array("id" => $addonID));
	}

	/**
	 *
	 * duplciate addons within same category
	 */
	private function duplicateAddons($arrAddonIDs, $catID){

		foreach($arrAddonIDs as $addonID){
			$addon = new UELM_UniteCreatorAddon();
			$addon->initByID($addonID);
			$addon->duplicate();
		}
	}

	/**
	 * create addon from data
	 */
	public function createFromData($data){

		$objAddon = new UELM_UniteCreatorAddon();
		$id = $objAddon->add($data);

		return ($id);
	}

	/**
	 * create addon from manager
	 */
	public function createFromManager($data){

		$title = UELM_UniteFunctionsUC::getVal($data, "title");
		$name = UELM_UniteFunctionsUC::getVal($data, "name");
		$description = UELM_UniteFunctionsUC::getVal($data, "description");
		$catID = UELM_UniteFunctionsUC::getVal($data, "catid");
		$parentID = UELM_UniteFunctionsUC::getVal($data, "parent_id");

		$addonType = $this->getAddonTypeFromData($data);
		$objAddonType = UELM_UniteCreatorAddonType::getAddonTypeObject($addonType);

		$objManager = UELM_UniteCreatorManager::getObjManagerByAddonType($addonType, $data);
		$isLayout = $objManager->getIsLayoutType();

		if($isLayout == false){
			$objAddon = new UELM_UniteCreatorAddon();
			$newAddonID = $objAddon->addSmall($title, $name, $description, $catID, $addonType);
			$urlAddon = UELM_HelperUC::getViewUrl_EditAddon($newAddonID);
			$htmlItem = $objManager->getAddonAdminHtml($objAddon);
		}else{
			//add layout

			$objLayout = new UELM_UniteCreatorLayout();
			$objLayout->setLayoutType($addonType);

			$params = array();
			if(!empty($parentID))
				$params["parent_id"] = $parentID;

			$newLayoutID = $objLayout->createSmall($title, $name, $description, $catID, $params);
			$urlAddon = UELM_HelperUC::getViewUrl_Layout($newLayoutID);

			$htmlItem = $objManager->getAddonAdminHtml($objLayout);
		}

		$objCats = new UELM_UniteCreatorCategories();
		$htmlCatList = $objCats->getHtmlCatList($catID, $objAddonType);

		$output = array();
		$output["htmlItem"] = $htmlItem;
		$output["htmlCats"] = $htmlCatList;
		$output["url_addon"] = $urlAddon;

		return ($output);
	}

	/**
	 * update addon from data
	 */
	public function updateAddonFromData($data){

		$addonData = UELM_UniteFunctionsUC::getVal($data, "addon_data");

		if(!empty($addonData))
			$data = UELM_UniteFunctionsUC::decodeContent($addonData);

		$addonID = UELM_UniteFunctionsUC::getVal($data, "id");

		$objAddon = new UELM_UniteCreatorAddon();
		$objAddon->initByID($addonID);
		$objAddon->update($data);
	}

	/**
	 * duplicate addon from data
	 */
	public function duplicateAddonFromData($data){

		$addonID = UELM_UniteFunctionsUC::getVal($data, "addonID");

		$objAddon = new UELM_UniteCreatorAddon();
		$objAddon->initByID($addonID);

		$response = $objAddon->duplicate(true);

		$htmlRow = UELM_HelperHtmlUC::getTableAddonsRow($response["id"], $response["title"]);

		return ($htmlRow);
	}

	/**
	 * import addon from library
	 */
	public function importAddonFromLibrary($data){

		$path = UELM_UniteFunctionsUC::getVal($data, "path");
		if(empty($path))
			UELM_UniteFunctionsUC::throwError("Empty Path");

		$library = new UELM_UniteCreatorLibrary();
		$addonData = $library->getPluginDataByPath($path);

		$objAddon = new UELM_UniteCreatorAddon();
		$addonID = $objAddon->add($addonData);
		$title = $objAddon->getTitle(true);

		$htmlRow = UELM_HelperHtmlUC::getTableAddonsRow($addonID, $title);

		return ($htmlRow);
	}

	/**
	 * delete addon from imput data
	 */
	public function deleteAddonFromData($data){
		
		$addonID = UELM_UniteFunctionsUC::getVal($data, "addonID");
		UELM_UniteFunctionsUC::validateNotEmpty($addonID, "Widget ID");

		UELM_UniteFunctionsUC::validateNumeric($addonID,"widget id");
		
		$addon = new UELM_UniteCreatorAddon();
		$addon->initByID($addonID);
		
		$addonID = (int)$addonID;
		
		$this->db->delete(UELM_GlobalsUC::$table_addons, "id={$addonID}");
		
		$addon->triggerAfterDeleteAction();
	}

	/**
	 * update item title
	 */
	public function updateAddonTitleFromData($data){

		$itemID = $data["itemID"];
		$title = $data["title"];
		$name = $data["name"];
		$description = $data["description"];

		$addonType = $this->getAddonTypeFromData($data);
		$isLayout = UELM_HelperUC::isLayoutAddonType($addonType);

		if($isLayout == false){
			$addon = new UELM_UniteCreatorAddon();
			$addon->initByID($itemID);
			$addon->updateNameTitle($name, $title, $description);
		}else{
			$objLayout = new UELM_UniteCreatorLayout();
			$objLayout->initByID($itemID);
			$objLayout->updateTitle($title);
			$objLayout->updateParam("description", $description);

			//check update isfree param
			if(isset($data["isfree"])){
				$isFree = UELM_UniteFunctionsUC::getVal($data, "isfree");
				$isFree = UELM_UniteFunctionsUC::strToBool($isFree);

				$objLayout->updateParam("isfree", $isFree);
			}
		}
	}

	/**
	 * update items activation from data
	 *
	 * @param $data
	 */
	public function activateAddonsFromData($data){

		$arrIDs = UELM_UniteFunctionsUC::getVal($data, "addons_ids");
		if(is_array($arrIDs) == false)
			return (false);

		if(empty($arrIDs))
			return (fale);

		$strIDs = implode(",", $arrIDs);

		UELM_UniteFunctionsUC::validateIDsList($strIDs, "id's list");

		$isActive = UELM_UniteFunctionsUC::getVal($data, "is_active");
		$isActive = (int)UELM_UniteFunctionsUC::strToBool($isActive);

		$tableAddons = UELM_GlobalsUC::$table_addons;
		$query = "update {$tableAddons} set is_active={$isActive} where id in($strIDs)";

		$this->db->runSql($query);
	}

	/**
	 * remove items from data
	 */
	public function removeAddonsFromData($data){

		$catID = UELM_UniteFunctionsUC::getVal($data, "catid");
		$type = $this->getAddonTypeFromData($data);
		$parentID = UELM_UniteFunctionsUC::getVal($data, "parentID");
		$parentID = (int)$parentID;

		$addonsIDs = UELM_UniteFunctionsUC::getVal($data, "arrAddonsIDs");

		if(UELM_HelperUC::isLayoutAddonType($type) == false){    //delete addons
			$this->deleteAddons($addonsIDs);
		}else{    //delete layouts

			$objLayouts = new UELM_UniteCreatorLayouts();
			$objLayouts->deleteLayouts($addonsIDs);
		}

		$response = $this->getCatsAndAddonsHtml($catID, $type, $data, $parentID);

		return ($response);
	}

	/**
	 *
	 * save items order from data
	 */
	public function saveOrderFromData($data){
		
		$addonType = $this->getAddonTypeFromData($data);
		$isLayout = UELM_HelperUC::isLayoutAddonType($addonType);

		$addonsIDs = UELM_UniteFunctionsUC::getVal($data, "addons_order");
		
		UELM_UniteFunctionsUC::validateIDsList($addonsIDs,"addons_order");
		
		if(empty($addonsIDs))
			return (false);
		
		if($isLayout == false)
			$this->saveAddonsOrder($addonsIDs);
		else{
			$objLayouts = new UELM_UniteCreatorLayouts();
			$objLayouts->updateOrdering($addonsIDs);
		}
	}

	/**
	 *
	 * copy / move addons to some category
	 *
	 * @param $data
	 */
	public function moveAddonsFromData($data){

		$targetCatID = UELM_UniteFunctionsUC::getVal($data, "targetCatID");
		$selectedCatID = UELM_UniteFunctionsUC::getVal($data, "selectedCatID");
		$targetParentID = UELM_UniteFunctionsUC::getVal($data, "parentID");
		$targetParentID = (int)$targetParentID;

		$type = $this->getAddonTypeFromData($data);
		$isLayouts = UELM_HelperUC::isLayoutAddonType($type);

		$arrAddonIDs = UELM_UniteFunctionsUC::getVal($data, "arrAddonIDs");

		UELM_UniteFunctionsUC::validateNotEmpty($targetCatID, "category id");
		UELM_UniteFunctionsUC::validateNotEmpty($arrAddonIDs, "addon id's");

		if($isLayouts == false)    //addons
			$this->moveAddons($arrAddonIDs, $targetCatID);
		else{
			$objLayouts = new UELM_UniteCreatorLayouts();
			$objLayouts->moveLayouts($arrAddonIDs, $targetCatID, $targetParentID);
		}

		$repsonse = $this->getCatsAndAddonsHtml($selectedCatID, $type, $data, $targetParentID);

		return ($repsonse);
	}

	/**
	 * duplicate items
	 */
	public function duplicateAddonsFromData($data){

		$catID = UELM_UniteFunctionsUC::getVal($data, "catID");
		$arrIDs = UELM_UniteFunctionsUC::getVal($data, "arrIDs");
		$parentID = UELM_UniteFunctionsUC::getVal($data, "parentID");

		$type = $this->getAddonTypeFromData($data);

		$isLayouts = UELM_HelperUC::isLayoutAddonType($type);

		if($isLayouts == false)
			$this->duplicateAddons($arrIDs, $catID);
		else{
			$objLayouts = new UELM_UniteCreatorLayouts();
			$objLayouts->duplicateLayouts($arrIDs, $catID);
		}

		$response = $this->getCatsAndAddonsHtml($catID, $type, $data, $parentID);

		return ($response);
	}

	/**
	 * shift addons in category from some order (more then the order).
	 */
	public function shiftOrder($catID, $order){

		$tableAddons = UELM_GlobalsUC::$table_addons;

		$query = "update $tableAddons set ordering = ordering+1 where catid={$catID} and ordering > {$order}";

		$this->db->runSql($query);
	}

	/**
	 * init addon by data
	 */
	public function initAddonByData($data){
				
		if(is_string($data)){
			$data = json_decode($data, true);
		}

		$addonID = UELM_UniteFunctionsUC::getVal($data, "id");
		$addonName = UELM_UniteFunctionsUC::getVal($data, "name");
		$arrConfig = UELM_UniteFunctionsUC::getVal($data, "config");
		$arrItemsData = UELM_UniteFunctionsUC::getVal($data, "items");
		$addonType = UELM_UniteFunctionsUC::getVal($data, "addontype");
		$arrFonts = UELM_UniteFunctionsUC::getVal($data, "fonts");
		$arrOptions = UELM_UniteFunctionsUC::getVal($data, "options");

		$isInsideGrid = UELM_UniteFunctionsUC::getVal($data, "is_inside_grid");
		$isInsideGrid = UELM_UniteFunctionsUC::strToBool($isInsideGrid);

		$objAddon = new UELM_UniteCreatorAddon();

		if(!empty($addonID))
			$objAddon->initByID($addonID);
		else{
			if(!empty($addonType))
				$objAddon->initByAlias($addonName, $addonType);
			else
				$objAddon->initByName($addonName);
		}

		$objAddon->setParamsValues($arrConfig);

		if(is_array($arrItemsData))
			$objAddon->setArrItems($arrItemsData);

		if(!empty($arrFonts) && is_array($arrFonts))
			$objAddon->setArrFonts($arrFonts);

		if($isInsideGrid == true)
			$objAddon->setIsInsideGrid();

		return ($objAddon);
	}

	/**
	 * show preview by data
	 */
	public function showAddonPreviewFromData($data){

		try{
			$objAddon = $this->initAddonByData($data);

			$objOutput = new UELM_UniteCreatorOutput();
			$objOutput->setPreviewAddonMode();

			$objOutput->initByAddon($objAddon);
			$objOutput->putPreviewHtml();
		}catch(Exception $e){
			$message = $e->getMessage();

			$errorMessage = UELM_HelperUC::getHtmlErrorMessage($message, UELM_GlobalsUC::$SHOW_TRACE_FRONT);

			uelm_echo($errorMessage);
		}

		exit();
	}

	/**
	 * save test addon data to some slot
	 */
	public function saveTestAddonData($data, $slot = 1){

		$addonID = UELM_UniteFunctionsUC::getVal($data, "id");

		if(!empty($addonID)){
			$config = UELM_UniteFunctionsUC::getVal($data, "settings_values", array());
			$items = UELM_UniteFunctionsUC::getVal($config, "uc_items", array());
			$fonts = "";

			unset($config["uc_items"]);

			$objAddon = new UELM_UniteCreatorAddon();
			$objAddon->initByID($addonID);
		}else{
			$addonName = UELM_UniteFunctionsUC::getVal($data, "name");
			$addontype = UELM_UniteFunctionsUC::getVal($data, "addontype");

			$config = UELM_UniteFunctionsUC::getVal($data, "config", array());
			$items = UELM_UniteFunctionsUC::getVal($data, "items", array());
			$fonts = UELM_UniteFunctionsUC::getVal($data, "fonts");

			$objAddon = new UELM_UniteCreatorAddon();
			$objAddon->initByMixed($addonName, $addontype);
		}

		$objAddon->saveTestSlotData($slot, $config, $items, $fonts);
	}

	/**
	 * save addon defaults from data
	 */
	public function saveAddonDefaultsFromData($data){

		$this->saveTestAddonData($data, 2);
	}

	/**
	 * get test addon data
	 */
	public function getTestAddonData($data){
				
		$objAddon = $this->initAddonByData($data);

		$slotNum = UELM_UniteFunctionsUC::getVal($data, "slotnum");
		$isCombine = UELM_UniteFunctionsUC::getVal($data, "combine");
		$isCombine = UELM_UniteFunctionsUC::strToBool($isCombine);

		$data = $objAddon->getTestData($slotNum);

		if($isCombine === true){
			$config = UELM_UniteFunctionsUC::getVal($data, "config", array());
			$items = UELM_UniteFunctionsUC::getVal($data, "items");

			if(!empty($items))
				$config["uc_items"] = $items;

			$output = array("settings_values" => $config);

			return $output;
		}
		
		
		return $data;
	}

	/**
	 * delete test addon data
	 *
	 * @param $data
	 */
	public function deleteTestAddonData($data){

		$objAddon = $this->initAddonByData($data);
		$slotNum = UELM_UniteFunctionsUC::getVal($data, "slotnum");
		
		$slotNum = (int)$slotNum;
		
		$objAddon->clearTestDataSlot($slotNum);
	}

	/**
	 * export addon
	 */
	public function exportAddon($data){
				
		$addonType = $this->getAddonTypeFromData($data);
		$isLayout = UELM_HelperUC::isLayoutAddonType($addonType);
		
		try{
			if($isLayout == false){
				$addon = $this->initAddonByData($data);
				$exporter = new UELM_UniteCreatorExporter();
				$exporter->initByAddon($addon);
				$exporter->export();
			}else{
				$layoutID = UELM_UniteFunctionsUC::getVal($data, "id");

				$objLayout = new UELM_UniteCreatorLayout();
				$objLayout->initByID($layoutID);

				$objExporter = new UELM_UniteCreatorLayoutsExporter();
				$objExporter->initByLayout($objLayout);
				$objExporter->export();
			}
		}catch(Exception $e){
			$message = "Export error: " . $e->getMessage();
			echo esc_html($message);
		}

		$message = "Export error:item not exported";
		echo esc_html($message);
		exit();
	}

	/**
	 * export category addons
	 */
	public function exportCatAddons($data, $exportType = ""){

		try{
			$catID = UELM_UniteFunctionsUC::getVal($data, "catid");
			UELM_UniteFunctionsUC::validateNotEmpty($catID);

			$objCats = new UELM_UniteCreatorCategories();
			$objCats->validateCatExist($catID);

			$exporter = new UELM_UniteCreatorExporter();
			$exporter->exportCatAddons($catID, $exportType);
		}catch(Exception $e){
			$message = "Export category addons error: " . $e->getMessage();
			echo esc_html($message);
		}

		$message = "Export category addons error: addons not exported";
		echo esc_html($message);
		exit();
	}

	/**
	 * import addons
	 */
	public function importAddons($data){

		$catID = UELM_UniteFunctionsUC::getVal($data, "catid");
		$addonType = $this->getAddonTypeFromData($data);
		$isLayout = UELM_HelperUC::isLayoutAddonType($addonType);

		$isOverwrite = UELM_UniteFunctionsUC::getVal($data, "isoverwrite");
		$isOverwrite = UELM_UniteFunctionsUC::strToBool($isOverwrite);

		$importType = UELM_UniteFunctionsUC::getVal($data, "importtype");

		switch($importType){
			case "autodetect":
				$forceToCat = false;
			break;
			case "specific":
				$forceToCat = true;
			break;
			default:
				UELM_UniteFunctionsUC::throwError("Wrong type: $importType");
			break;
		}

		if(empty($catID))
			$catID = null;

		$arrTempFile = UELM_UniteFunctionsUC::getVal($_FILES, "file");

		//---- addon -----

		if($isLayout == false){
			$exporter = new UELM_UniteCreatorExporter();
			$exporter->setMustImportAddonType($addonType);

			$importLog = $exporter->import($catID, $arrTempFile, $isOverwrite, $forceToCat);

			$catID = $exporter->getImportedCatID();
		}else{
			//----- layout -------

			$arrParams = array();
			if(!empty($forceToCat))
				$arrParams["force_to_cat"] = $catID;

			if($addonType == UELM_GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR_TEMPLATE){
				$exporterLayouts = new UELM_UniteCreatorLayoutsExporterElementor();
				$exporterLayouts->importElementorLayoutNew($arrTempFile, $isOverwrite, $data);
			}else{
				$exporterLayouts = new UELM_UniteCreatorLayoutsExporter();
				$exporterLayouts->import($arrTempFile, null, $isOverwrite, $arrParams);
			}

			$importLog = $exporterLayouts->getLogText();
		}

		$response = $this->getCatsAndAddonsHtml($catID, $addonType, $data);
		$response["import_log"] = $importLog;

		return ($response);
	}

	/**
	 * add addon changelog
	 */
	public function addAddonChangelog($data){

		try{
			$addonId = UELM_UniteFunctionsUC::getVal($data, "addon_id");
			$type = UELM_UniteFunctionsUC::getVal($data, "type");
			$text = UELM_UniteFunctionsUC::getVal($data, "text");

			UELM_UniteFunctionsUC::validateNotEmpty($addonId, "addon id");
			UELM_UniteFunctionsUC::validateNotEmpty($type, "type");
			UELM_UniteFunctionsUC::validateNotEmpty($text, "text");

			$changelog = new UELM_UniteCreatorAddonChangelog();
			$changelog->addChangelog($addonId, $type, $text);
		}catch(Exception $e){
			$message = "Add changelog error: " . $e->getMessage();
			echo esc_html($message);
			exit();
		}
	}

	/**
	 * update addon changelog
	 */
	public function updateAddonChangelog($data){

		try{
			$id = UELM_UniteFunctionsUC::getVal($data, "id");
			$type = UELM_UniteFunctionsUC::getVal($data, "type");
			$text = UELM_UniteFunctionsUC::getVal($data, "text");

			UELM_UniteFunctionsUC::validateNotEmpty($id, "id");
			UELM_UniteFunctionsUC::validateNotEmpty($type, "type");
			UELM_UniteFunctionsUC::validateNotEmpty($text, "text");

			$changelog = new UELM_UniteCreatorAddonChangelog();

			$changelog->updateChangelog($id, array(
				"type" => $type,
				"text" => $text,
			));
		}catch(Exception $e){
			$message = "Update changelog error: " . $e->getMessage();
			echo esc_html($message);
			exit();
		}
	}

	/**
	 * delete addon changelog
	 */
	public function deleteAddonChangelog($data){

		try{
			$id = UELM_UniteFunctionsUC::getVal($data, "id");

			UELM_UniteFunctionsUC::validateNotEmpty($id, "id");

			$changelog = new UELM_UniteCreatorAddonChangelog();
			$changelog->deleteChangelog($id);
		}catch(Exception $e){
			$message = "Delete changelog error: " . $e->getMessage();
			echo esc_html($message);
			exit();
		}
	}

	/**
	 * create addon revision
	 */
	public function createAddonRevision($data){

		try{
			$addon = $this->initAddonByData($data);

			$revisioner = new UELM_UniteCreatorAddonRevisioner();
			$revisioner->createAddonRevision($addon);
		}catch(Exception $e){
			$message = "Create addon revision error: " . $e->getMessage();
			echo esc_html($message);
			exit();
		}
	}

	/**
	 * restore addon revision
	 */
	public function restoreAddonRevision($data){

		try{
			$addon = $this->initAddonByData($data);
			$revisionId = UELM_UniteFunctionsUC::getVal($data, "revision_id");

			$revisioner = new UELM_UniteCreatorAddonRevisioner();
			$importLog = $revisioner->restoreAddonRevision($addon, $revisionId);

			$response = array();
			$response["import_log"] = $importLog;

			return $response;
		}catch(Exception $e){
			$message = "Restore addon revision error: " . $e->getMessage();
			echo esc_html($message);
			exit();
		}
	}

	/**
	 * download addon revision
	 */
	public function downloadAddonRevision($data){

		try{
			$addon = $this->initAddonByData($data);
			$revisionId = UELM_UniteFunctionsUC::getVal($data, "revision_id");

			$revisioner = new UELM_UniteCreatorAddonRevisioner();
			$revisioner->downloadAddonRevision($addon, $revisionId);
		}catch(Exception $e){
			$message = "Download addon revision error: " . $e->getMessage();
			echo esc_html($message);
			exit();
		}
	}

	/**
	 * update addon from catalog
	 */
	public function updateAddonFromCatalogFromData($data){

		$widgetName = UELM_UniteFunctionsUC::getVal($data, "widget_name");

		$objAddon = new UELM_UniteCreatorAddon();

		$addonID = null;

		if(!empty($widgetName)){
			$alias = str_replace("ucaddon_", "", $widgetName);

			$objAddon->initByAlias($alias, UELM_GlobalsUC::ADDON_TYPE_ELEMENTOR);
		}else{    //init by id

			$addonID = UELM_UniteFunctionsUC::getVal($data, "id");
			$addonID = (int)$addonID;

			$objAddon->initByID($addonID);
		}

		$installData = array();

		$installData["name"] = $objAddon->getAlias();
		$installData["cat"] = $objAddon->getCatTitle();
		$installData["type"] = $objAddon->getType();

		$webAPI = new UELM_UniteCreatorWebAPI();

		$webAPI->checkUpdateCatalog(true);
		$webAPI->installCatalogAddonFromData($installData);

		if(empty($addonID))
			return (null);

		$urlRedirect = UELM_HelperUC::getViewUrl_EditAddon($addonID);

		return ($urlRedirect);
	}

	/**
	 * update bulk params in addons from data
	 * return bulk dialog html
	 */
	public function updateAddonsBulkFromData($data){

		$paramType = UELM_UniteFunctionsUC::getVal($data, "param_type");
		$paramData = UELM_UniteFunctionsUC::getVal($data, "param_data");
		$paramName = UELM_UniteFunctionsUC::getVal($paramData, "name");

		$sourceAddonID = UELM_UniteFunctionsUC::getVal($data, "addon_id");
		$targetAddonIDs = UELM_UniteFunctionsUC::getVal($data, "addon_ids");
		$action = UELM_UniteFunctionsUC::getVal($data, "action_bulk");

		$position = UELM_UniteFunctionsUC::getVal($data, "param_position");

		//get position in addon

		$objAddon = new UELM_UniteCreatorAddon();
		$objAddon->initByID($sourceAddonID);

		$isMain = ($paramType == "main");

		if(empty($position))
			$position = $objAddon->getParamPosition($paramName, $isMain);

		//clear category data
		unset($paramData[UELM_GlobalsUC::ATTR_CATID]);
		
		
		//update addons

		foreach($targetAddonIDs as $addonID){
			$objTargetAddon = new UELM_UniteCreatorAddon();
			$objTargetAddon->initByID($addonID);

			switch($action){
				case "update":
					$objTargetAddon->addUpdateParam_updateDB($paramData, $isMain, $position);
				break;
				case "delete":
					$objTargetAddon->deleteParam_updateDB($paramName, $isMain);
				break;
				default:
					UELM_UniteFunctionsUC::throwError("Wrong bulk action: $action");
				break;
			}
		}
	}

	/**
	 * install addons from catalog if not exists
	 */
	public function installMultipleAddons($arrAddonNames, $addonType){

		if(empty($addonType))
			return ("installMultipleAddons - addonType is empty");

		if(empty($arrAddonNames))
			return ("installMultipleAddons - no addons found");

		$numAddons = count($arrAddonNames);

		if($numAddons > 25)
			UELM_UniteFunctionsUC::throwError("Too much widgets to install: $numAddons");

		$webAPI = new UELM_UniteCreatorWebAPI();

		$strLog = "";

		foreach($arrAddonNames as $alias){
			$isExists = $this->isAddonExistsByName($alias, $addonType);

			if(!empty($strLog))
				$strLog .= "\n";

			if($isExists == true){
				$strLog .= "Skipped widget install: $alias";
				continue;
			}

			$strLog .= $webAPI->installCatalogAddonByName($alias, $addonType);
		}

		return ($strLog);
	}

}
