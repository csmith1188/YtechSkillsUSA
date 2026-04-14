<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorWebAPIWork{

	protected static $urlAPI;
	private static $arrCatalogData;
	protected $product;
	private $lastAPIData;
	private $arrDebug = array();

	const IS_CATALOG_UNLIMITED = true;
	const CATALOG_CHECK_PERIOD = 7200;	 		//2 hours
	const CATALOG_CHECK_PERIOD_NOTEXIST = 600;	//10 min
	const OPTION_ACTIVATION_BASE = "uelm_addon_library_activation";

	protected $optionActivation = self::OPTION_ACTIVATION_BASE;

	const OPTION_CATALOG = "uelm_addon_library_catalog";
	const OPTION_TIMEOUT_TRANSIENT = "uelm_addon_library_catalog_timeout";

	const EXPIRE_NEVER = "never";
	
	const TAG_FREE = "isfree";
	const TAG_TRENDING = "istrending";
	const TAG_AJAX = "isajax";
	const TAG_NEW = "isnew";
	const TAG_REMOTE = "isremote";
	const TAG_MULTISOURCE = "ismultisource";
	const TAG_ANIMATION = "isanimation";
	
	
	private function a______INIT__________(){}


	/**
	 * construct
	 */
	public function __construct(){

		if(empty(self::$urlAPI))
			self::$urlAPI = UELM_GlobalsUC::URL_API;

	}


	/**
	 * set product
	 */
	public function setProduct($product){

		UELM_UniteFunctionsUC::validateNotEmpty($product, "product");

		$this->product = $product;

		$this->optionActivation = self::OPTION_ACTIVATION_BASE."_".$product;

	}


	private function a__________GETTERS___________(){}



	/**
	 * get activated product data
	 */
	private function getActivatedData(){

		$arrActivation = UELM_UniteProviderFunctionsUC::getOption($this->optionActivation);
		if(empty($arrActivation))
			return(null);

		return($arrActivation);
	}


	/**
	 * get activation code
	 */
	private function getActivationCode(){

		$arrActivation = UELM_UniteProviderFunctionsUC::getOption($this->optionActivation);

		$code = UELM_UniteFunctionsUC::getVal($arrActivation, "code");

		if(empty($code) && UELM_GlobalsUC::$isProductActive == true)
			$code = "active_by_freemius";

		return($code);
	}


	/**
	 * get addon names array
	 */
	public function getArrAddonNames($arrCatalogAddons){

		if(empty($arrCatalogAddons))
			return(array());

		$arrNames = array();
		foreach($arrCatalogAddons as $catName => $arrCat){

			foreach($arrCat as $addon){

				$name = UELM_UniteFunctionsUC::getVal($addon, "name");

				unset($addon["name"]);

				$addon["cat"] = $catName;

				$arrNames[$name] = $addon;
			}
		}


		return($arrNames);
	}
	
	
	
	
	/**
	 * filter web categories by addon type
	 */
	private function filterWebCategoriesByAddonType($arrCatalogItems, $objAddonsType){

		if(empty($arrCatalogItems))
			return($arrCatalogItems);

		$arrExclude = $objAddonsType->arrCatalogExcludeCats;
		if(empty($arrExclude))
			return($arrCatalogItems);

		if(is_array($arrExclude) == false)
			return($arrCatalogItems);

		foreach($arrCatalogItems as $catName => $arrAddons){

			$nameLower = strtolower($catName);

			if(in_array($nameLower, $arrExclude) === false)
				continue;

			unset($arrCatalogItems[$catName]);
		}

		return($arrCatalogItems);
	}


	/**
	 * get catalog array by addons type
	 */
	public function getCatalogArray($objAddonsType){


		if(is_string($objAddonsType))
			$objAddonsType = UELM_UniteCreatorAddonType::getAddonTypeObject($objAddonsType);

		$key = $objAddonsType->catalogKey;
		$arrCatalog = $this->getCatalogArrayFromData();

		$arrCatalogItems = UELM_UniteFunctionsUC::getVal($arrCatalog, $key);
		if(empty($arrCatalogItems))
			$arrCatalogItems = array();

		$arrCatalogItems = $this->filterWebCategoriesByAddonType($arrCatalogItems, $objAddonsType);

		return($arrCatalogItems);
	}



	/**
	 * print catalog
	 */
	public function printCatalog(){

		$arrCatalog = $this->getCatalogArrayFromData();

		uelm_dmp($arrCatalog);
		exit();
	}


	/**
	 * get catalog addon names
	 */
	private function getArrCatalogAddonNames($isBG = false){

		$arrData = $this->getCatalogData();

		if(empty($arrData))
			return(array());

		if($isBG == true)
			$arrNames = UELM_UniteFunctionsUC::getVal($arrData, "catalog_bgaddon_names");
		else
			$arrNames = UELM_UniteFunctionsUC::getVal($arrData, "catalog_addon_names");

		return($arrNames);
	}


	/**
	 * check if product active or not
	 */
	public function isProductActive($product = null){

		if(!empty($product)){
			$this->setProduct($product);
		}

		if(UELM_GlobalsUC::$isProVersion == false)
			return(false);

		$data = $this->getActivatedData();

		if(empty($data))
			return(false);

		$stampExpire = UELM_UniteFunctionsUC::getVal($data, "expire");

		if($stampExpire === self::EXPIRE_NEVER)
			return(true);

		if(empty($stampExpire))
			return(false);

		if(is_numeric($stampExpire) == false)
			return(false);

		$stampExpire = (int)$stampExpire;
		$stampNow = time();

		if($stampExpire < $stampNow)
			return(false);

		return(true);
	}


	/**
	 * check if time to check catalog
	 */
	public function isTimeToCheckCatalog(){

		$timeout = UELM_UniteProviderFunctionsUC::getTransient(self::OPTION_TIMEOUT_TRANSIENT);

		if(empty($timeout))
			return(true);
		else
			return(false);
	}


	/**
	 * get catalog version
	 */
	public function getCurrentCatalogStamp(){

		$arrData = $this->getCatalogData();
		if(empty($arrData))
			return(null);

		$stamp = UELM_UniteFunctionsUC::getVal($arrData, "stamp");

		return($stamp);
	}


	/**
	 * get current catalog date
	 */
	public function getCurrentCatalogDate(){

		$isExists = $this->isCatalogExists();
		if($isExists == false)
			return("");

		$stamp = $this->getCurrentCatalogStamp();

		if(empty($stamp))
			return("");

		$date = UELM_UniteFunctionsUC::timestamp2Date($stamp);

		return($date);
	}

	/**
	 * check if the saved catalog exists
	 */
	public function isCatalogExists(){

		$arrData = $this->getCatalogData();

		if(empty($arrData))
			return(false);

		return(true);
	}

	/**
	 * is pages catalog exists
	 */
	public function isPagesCatalogExists(){

		if($this->isCatalogExists() == false)
			return(false);

		$arrPages = $this->getCatalogArray_pages();
		if(empty($arrPages))
			return(false);

		return(true);
	}

	/**
	 * check if addon exists in catalog
	 * if empty catalog return false
	 */
	public function isAddonExistsInCatalog($addonName, $isBG = false){

		$arrNames = $this->getArrCatalogAddonNames($isBG);

		if(isset($arrNames[$addonName]))
			return(true);

		return(false);
	}

	/**
	 * get simple addon by name
	 */
	public function getAddonByName($name, $isBG = false){

		$arrNames = $this->getArrCatalogAddonNames($isBG);

		$arrAddon = UELM_UniteFunctionsUC::getVal($arrNames, $name);

		if(empty($arrAddon))
			return(null);

		$arrAddon["name"] = $name;

		return($arrAddon);
	}

	/**
	 * get category addons array from catalog
	 */
	public function getArrCatAddons($title, $objAddonsType){
		
		$arrWebCatalog = $this->getCatalogArray($objAddonsType);
				
		if(empty($arrWebCatalog))
			return(array());
		
		$arrCatAddons = UELM_UniteFunctionsUC::getVal($arrWebCatalog, $title);
		
		return($arrCatAddons);
	}
	
	
	private function a___________MODIFY___________(){}
	
	/**
	 * modify addon data, unpack tags
	 */
	private function modifyArrData_addonTags($addon){
				
		$strTags = UELM_UniteFunctionsUC::getVal($addon, "tags");

		$addon[self::TAG_NEW] = false;
		$addon[self::TAG_AJAX] = false;
		$addon[self::TAG_MULTISOURCE] = false;
		$addon[self::TAG_REMOTE] = false;					
		$addon[self::TAG_TRENDING] = false;					
		$addon[self::TAG_ANIMATION] = false;					
		
		unset($addon["tags"]);
		
		if(empty($strTags))
			return($addon);
		
		for($i=0;$i<strlen($strTags);$i++){
			
			$tag = $strTags[$i];
			
			switch($tag){
				case "a":
					$addon[self::TAG_AJAX] = true;
				break;
				case "m":
					$addon[self::TAG_MULTISOURCE] = true;
				break;
				case "r":
					$addon[self::TAG_REMOTE] = true;					
				break;				
				case "t":
					$addon[self::TAG_TRENDING] = true;					
				break;
				case "n":
					$addon[self::TAG_NEW] = true;
				break;
				case "s":
					$addon[self::TAG_ANIMATION] = true;
				break;
				default:
				break;
			}
			
		}
		
		
		return($addon);
	}
	
	
	/**
	 * modify addons data - tags
	 */
	private function modifyArrData_addons($arrAddons){
		
		foreach($arrAddons as $cat=>$addonsList){
			
			foreach($addonsList as $key=>$addon){
				$arrAddons[$cat][$key] = $this->modifyArrData_addonTags($addon);			
			}
			
		}

		
		return($arrAddons);
	}
	
	/**
	 * modify data before save
	 */
	private function modifyArrData($arrData){
						
		$arrData["catalog_addon_names"] = array();
		
		$arrCatalog = UELM_UniteFunctionsUC::getVal($arrData, "catalog");
		$arrAddons = UELM_UniteFunctionsUC::getVal($arrCatalog, "addons");
		$arrBGAddons = UELM_UniteFunctionsUC::getVal($arrCatalog, "bg_addon");
		
		$addonNames = $this->getArrAddonNames($arrAddons);
		$addonNamesBG = $this->getArrAddonNames($arrBGAddons);
		
		$arrData["catalog_addon_names"] = $addonNames;
		$arrData["catalog_bgaddon_names"] = $addonNamesBG;
		
		$arrAddons = $this->modifyArrData_addons($arrAddons);
		$arrBGAddons = $this->modifyArrData_addons($arrBGAddons);
		
		
		$arrCatalog["addons"] = $arrAddons;
		$arrCatalog["bg_addon"] = $arrBGAddons;
		
		$arrData["catalog"] = $arrCatalog;
		
		return($arrData);
	}
	
	
	
	
	private function a___________DEBUG___________(){}


	/**
	 * debug the check catalog
	 */
	public function addDebug($str){

		$this->arrDebug[] = $str;
	}

	/**
	 * get debug
	 */
	public function getDebug(){

		return($this->arrDebug);
	}

	private function a___________GET_CATALOG___________(){}

	/**
	 * get catalog data
	 */
	public function getCatalogData(){

		if(!empty(self::$arrCatalogData))
			return(self::$arrCatalogData);

		$arrData = UELM_UniteProviderFunctionsUC::getOption(self::OPTION_CATALOG);

		if(is_array($arrData) == false)
			return(null);

		$arrData = $this->modifyArrData($arrData);

		self::$arrCatalogData = $arrData;


		return($arrData);
	}


	/**
	 * get full catalog array
	 */
	private function getCatalogArrayFromData($type = null){

		$arrData = $this->getCatalogData();
		if(empty($arrData))
			return(array());

		$arrCatalog = UELM_UniteFunctionsUC::getVal($arrData, "catalog");
		
		//return from old way
		if(!isset($arrCatalog["addons"])){
			$arrCatalogOutput = array();
			$arrCatalogOutput["addons"] = $arrCatalog;
			$arrCatalogOutput["pages"] = array();

			return($arrCatalogOutput);
		}

		if(!empty($type))
			$arrCatalog = UELM_UniteFunctionsUC::getVal($arrCatalog, $type);

		return($arrCatalog);
	}


	/**
	 * get catalog array
	 */
	protected function getCatalogArray_addons($isBG = false){
		
		$arrCatalog = $this->getCatalogArrayFromData();
		
		if($isBG === true)
			$arrCatalogAddons = $arrCatalog["bg_addon"];
		else
			$arrCatalogAddons = $arrCatalog["addons"];

		return($arrCatalogAddons);
	}


	/**
	 * get catalog array
	 */
	public function getCatalogArray_pages(){

		$arrCatalog = $this->getCatalogArrayFromData();

		$arrCatalogAddons = $arrCatalog["pages"];

		return($arrCatalogAddons);
	}
	
	/**
	 * get catalog "blog" section or null
	 */
	public function getCatalog_blog(){
		
		$arrCatalog = $this->getCatalogArrayFromData();
		
		$arrCatalogBlog = UELM_UniteFunctionsUC::getVal($arrCatalog, "blog");
		
		return($arrCatalogBlog);		
	}
	
	
	/**
	 * get addons by tags
	 */
	public function getCatalogAddonsByTags($tag, $isBG = false){
				
		$arrAddonsWithCats = $this->getCatalogArray_addons($isBG);
				
		$arrAddons = $this->flatAddonsArray($arrAddonsWithCats);
		
		$arrAddonsHasTags = array();
		
		foreach($arrAddons as $addon){
			
			$tagValue = UELM_UniteFunctionsUC::getVal($addon, $tag);
			
			if($tagValue === true)
				$arrAddonsHasTags[] = $addon;
		}
		
		return($arrAddonsHasTags);
	}
	
	/**
	 * get addons categories
	 */
	public function getCatalogAddonsCategories(){
		
		$arrAddons = $this->getCatalogArray_addons();
				
		$arrCats = array_keys($arrAddons);
		
		return($arrCats);
	}
	
	
	/**
	 * get categories aliases
	 */
	public function getCatalogAddonsCategoriesAliases(){
		
		$arrCats = $this->getCatalogAddonsCategories();

		$arrAliases = array();
				
		foreach($arrCats as $index=>$cat){
			
			$alias = UELM_HelperUC::convertTitleToAlias($cat);
			
			$arrAliases[] = $alias;
		}
		
		return($arrAliases);
	}
	
	
	private function a___________SETTERS___________(){}

	/**
	 * get array of addons without categories
	 */
	private function flatAddonsArray($arrAddonsWithCats){
		
		if(empty($arrAddonsWithCats))
			return($arrAddonsWithCats);
		
		$arrOutput = array();
			
		foreach($arrAddonsWithCats as $cat=>$arrAddons)
			$arrOutput = array_merge($arrOutput, $arrAddons);
		
		return($arrOutput);
	}
	
	
	/**
	 * modify data before request
	 */
	protected function modifyDataBeforeRequest($data){

		return($data);
	}

	/**
	 * get last api call data
	 */
	public function getLastAPICallData(){

		return($this->lastAPIData);
	}


	/**
	 * call API with some action and data
	 */
	protected function callAPI($action, $body = array(), $isRawResponse = false){

		// collect data
		$body["action"] = $action;
		$body["domain"] = UELM_GlobalsUC::$current_host;

		if(self::IS_CATALOG_UNLIMITED === true)
			$body["catalog_type"] = "unlimited";

		if(isset($body["code"]) === false)
			$body["code"] = $this->getActivationCode();

		if(array_key_exists("catalog_date", $body) === false)
			$body["catalog_date"] = $this->getCurrentCatalogStamp();

		$body["blox_version"] = UELM_UNLIMITED_ELEMENTS_VERSION;

		$body = $this->modifyDataBeforeRequest($body);
	
		$this->lastAPIData = array();
		$this->lastAPIData["request"] = $body;
		
		// make request
		$response = UELM_Http::make()->post(self::$urlAPI, $body);
		$data = $response->body();

		$this->addDebug("api response length: " . strlen($data));

		$this->lastAPIData["response"] = $data;

		// parse as string
		if($isRawResponse === true){
			$len = strlen($data);
			if($len < 200){ // ???
				$objResponse = @json_decode($data);
				if(empty($objResponse))
					return ($objResponse);
			}else
				return ($data);
		}

		// parse as json
		$data = $response->json();

		$success = UELM_UniteFunctionsUC::getVal($data, "success");
		$success = UELM_UniteFunctionsUC::strToBool($success);

		if($success === false){
			$message = UELM_UniteFunctionsUC::getVal($data, "message");

			if(empty($message) === true)
				$message = "Something went wrong.";

			UELM_UniteFunctionsUC::throwError("Server Error: $message");
		}

		return $data;
	}


	/**
	 * save activated product
	 * save purchase code and expire days
	 */
	private function saveActivatedProduct($code, $expireStamp){

		$arrActivation = array();
		$arrActivation["code"] = $code;


		if(empty($expireStamp))
			$arrActivation["expire"] = self::EXPIRE_NEVER;
		else
			$arrActivation["expire"] = $expireStamp;


		UELM_UniteProviderFunctionsUC::updateOption($this->optionActivation, $arrActivation);
	}


	/**
	 * delete saved catalog
	 */
	public function deleteCatalog(){

		UELM_UniteProviderFunctionsUC::deleteOption(self::OPTION_CATALOG);

	}


	/**
	 * deactivate product
	 */
	public function deactivateProduct($data = null){

		$product = UELM_UniteFunctionsUC::getVal($data, "product");

		if(!empty($product))
			$this->setProduct($product);


		UELM_UniteProviderFunctionsUC::deleteOption($this->optionActivation);

	}


	/**
	 * activate product from data
	 */
	public function activateProductFromData($data){

		$code = UELM_UniteFunctionsUC::getVal($data, "code");
		$codetype = UELM_UniteFunctionsUC::getVal($data, "codetype");
		$product = UELM_UniteFunctionsUC::getVal($data, "product");


		if(!empty($product))
			$this->setProduct($product);

		if(defined("UNLIMITED_ELEMENTS_UPRESS_VERSION") && $codetype == "upress")
			$code = UNLIMITED_ELEMENTS_UPRESS_ACTIVATION_CODE;

		UELM_UniteFunctionsUC::validateNotEmpty($code, "Activation Code");

		UELM_UniteFunctionsUC::validateNotEmpty($codetype, "Code Type");

		$reqData = array();
		$reqData["code"] = $code;
		$reqData["codetype"] = $codetype;

		if(!empty($product))
			$reqData["product"] = $product;

		$responseAPI = $this->callAPI("activate", $reqData);

		//-------------
		$expireStamp = UELM_UniteFunctionsUC::getVal($responseAPI, "expire_stamp");
		$expireDays = UELM_UniteFunctionsUC::getVal($responseAPI, "expire_days");

		//save activation
		$this->saveActivatedProduct($code, $expireStamp);


		return($expireDays);
	}



	/**
	 * save catalog data
	 */
	private function saveCatalogData($stamp, $arrCatalog){

		$arrData = array();
		$arrData["stamp"] = $stamp;
		$arrData["catalog"] = $arrCatalog;
		$arrData["catalog_addon_names"] = $this->getArrAddonNames($arrCatalog);

		$this->addDebug("Updating catalog option: ".self::OPTION_CATALOG);

		$response = UELM_UniteProviderFunctionsUC::updateOption(self::OPTION_CATALOG, $arrData,false, false);
		
		$arrSavedCatalog = UELM_UniteProviderFunctionsUC::getOption(self::OPTION_CATALOG);

		//error debug

		if(empty($arrSavedCatalog)){

			$strData = serialize($arrData);

			$len = strlen($strData);
			
			$this->addDebug("<span style='color:red;'>The wp option: <b>".self::OPTION_CATALOG."</b> not saved. Options size: $len chars.<br> Maybe because it's some mysql DB problem. <br>It should save large amount of data, but maybe there is a limit</span>");

		}else{

			$this->addDebug("Option updated successfully ");

		}
	}


	/**
	 * check or update catalog in web
	 */
	public function checkUpdateCatalog($isForce = false){

		try{

			$this->addDebug("Start check update catalog, force: $isForce");

			$isCatalogExists = $this->isCatalogExists();

			$this->addDebug("Catalog exists: ". $isCatalogExists);

			if($isCatalogExists == false){
				$checkPerioud = self::CATALOG_CHECK_PERIOD_NOTEXIST;
				$catalogStamp = null;
			}else{

				//update transient, for wait perioud
				$checkPerioud = self::CATALOG_CHECK_PERIOD;

				$catalogStamp = $this->getCurrentCatalogStamp();

				if(empty($catalogStamp))
					$checkPerioud = self::CATALOG_CHECK_PERIOD_NOTEXIST;

			}

			UELM_UniteProviderFunctionsUC::setTransient(self::OPTION_TIMEOUT_TRANSIENT, true, $checkPerioud);

			if($isForce === true)
				$catalogStamp = null;

			$data = array();
			$data["catalog_date"] = $catalogStamp;
			$data["include_pages"] = true;

			$response = $this->callAPI("check_catalog", $data);

			/*	print pages
			unset($response["catalog"]["addons"]);uelm_dmp($response["catalog"]);exit();
			*/

			$updateFound = UELM_UniteFunctionsUC::getVal($response, "update_found");
			$updateFound = UELM_UniteFunctionsUC::strToBool($updateFound);

			$this->addDebug("update found: ".$updateFound);

			$clientResponse = array();

			//response up to date
			if($updateFound == false){
				$clientResponse["update_found"] = false;
				$catalogDate = UELM_UniteFunctionsUC::timestamp2DateTime($catalogStamp);
				$clientResponse["message"] = "The catalog is up to date: ".$catalogDate;

				$this->addDebug($clientResponse["message"]);

				return($clientResponse);
			}

			$stamp = UELM_UniteFunctionsUC::getVal($response, "stamp");
			$arrCatalog = UELM_UniteFunctionsUC::getVal($response, "catalog");

			$this->saveCatalogData($stamp, $arrCatalog);

			//response catalog date
			$date = UELM_UniteFunctionsUC::timestamp2DateTime($stamp);
			$clientResponse["update_found"] = true;
			$clientResponse["catalog_date"] = $date;
			$clientResponse["message"] = "The catalog updated. Catalog Date: $date. \n Please refresh the browser to see the changes";

			$this->addDebug($clientResponse["message"]);

			return($clientResponse);

		}catch(Exception $e){

			$message = $e->getMessage();

			$clientResponse = array();
			$clientResponse["update_found"] = false;
			$clientResponse["error_message"] = $message;

			return($clientResponse);
		}

	}

	/**
	 * check if supported addon type
	 */
	protected function isAddonTypeSupported($objAddonsType){

		$isSupported = $objAddonsType->allowWebCatalog;

		return($isSupported);
	}


	/**
	 * merge addons with catalog from all the categories
	 */
	public function mergeAddonsWithCatalog($arrAddons, $objAddonsType){

		if($this->isAddonTypeSupported($objAddonsType) == false)
			return($arrAddons);

		$arrAssoc = UELM_UniteFunctionsUC::arrayToAssoc($arrAddons,"name");

		$arrWebCatalog = $this->getCatalogArray($objAddonsType);

		if(empty($arrWebCatalog))
			return($arrAddons);

		$addonType = $objAddonsType->typeName;
		if($objAddonsType->isBasicType == true)
			$addonType = "";


		foreach($arrWebCatalog as $cat=>$catAddons){

			foreach($catAddons as $arrAddon){
				$name = UELM_UniteFunctionsUC::getVal($arrAddon, "name");

				$name2 = null;
				if(!empty($addonType))
					$name2 = $name."_".$addonType;

				if(isset($arrAssoc[$name]))
					continue;

				if(!empty($name2) && isset($arrAssoc[$name]))
					continue;


				$arrAddon["isweb"] = true;
				$arrAddon["cat"] = $cat;
				$arrAddons[] = $arrAddon;
			}
		}


		return($arrAddons);
	}


	/**
	 * merge categories and layouts
	 */
	public function mergeCatsAndLayoutsWithCatalog($arrCats, $objAddonsType){

		if($this->isAddonTypeSupported($objAddonsType) == false)
			return($arrCats);

		if($this->isCatalogExists() == false)
			$this->checkUpdateCatalog();

		$arrWebCatalog = $this->getCatalogArray($objAddonsType);


		if(empty($arrWebCatalog))
			return($arrCats);

		foreach($arrWebCatalog as $cat=>$arrLayouts){

			if(!isset($arrCats[$cat]))
				$arrCats[$cat] = array();

			foreach($arrLayouts as $name=>$layout){

				if(isset($arrCats[$cat][$name]))
					continue;

				$layout["isweb"] = true;
				$arrCats[$cat][$name] = $layout;
			}

		}

		return($arrCats);
	}

	/**
	 * filter catalog by type
	 */
	private function filterCatalogBySearchString($arrWebCatalog, $strSearch){

		foreach($arrWebCatalog as $catTitle => $arrAddons){

			//filter by category, leave all the category if contains
			$isMatch = UELM_UniteFunctionsUC::isStringContains($catTitle, $strSearch);

			if($isMatch == true)
				continue;

			$arrAddonsNew = $this->filterCatalogAddonsBySearchString($arrAddons, $strSearch);

			//build only match array
			if(empty($arrAddonsNew)){
				unset($arrWebCatalog[$catTitle]);
				continue;
			}


			$arrWebCatalog[$catTitle] = $arrAddonsNew;
		}


		return($arrWebCatalog);
	}

	/**
	 * filter addons by search string
	 */
	private function filterCatalogAddonsBySearchString($arrAddons, $strSearch){

		if(empty($strSearch))
			return($arrAddons);

		if(empty($arrAddons))
			return(array());

		$arrAddonsNew = array();
		foreach($arrAddons as $name => $addon){

			$titleAddon = UELM_UniteFunctionsUC::getVal($addon, "title");

			$isAddonMatch = UELM_UniteFunctionsUC::isStringContains($titleAddon, $strSearch);

			if($isAddonMatch == false)
				continue;

			$arrAddonsNew[$name] = $addon;
		}

		return($arrAddonsNew);

	}

	/**
	 * merge cats with catalog cats
	 */
	public function mergeCatsAndAddonsWithCatalog($arrCats, $numAddonsOnly = false, $objAddonsType="", $params = null){

		$isSupported = $this->isAddonTypeSupported($objAddonsType);

		if($isSupported == false)
			return($arrCats);

		$isCatalogExists = $this->isCatalogExists();

		if($isCatalogExists == false)
			$this->checkUpdateCatalog();

		$arrWebCatalog = $this->getCatalogArray($objAddonsType);

		$filterSearch = UELM_UniteFunctionsUC::getVal($params, "filter_search");
		$filterSearch = trim($filterSearch);

		if(!empty($filterSearch))
			$arrWebCatalog = $this->filterCatalogBySearchString($arrWebCatalog, $filterSearch);

		if(empty($arrWebCatalog))
			return($arrCats);

		$addonType = $objAddonsType->typeName;
		if($objAddonsType->isBasicType == true)
			$addonType = "";

		foreach($arrWebCatalog as $dir=>$addons){

			//add directory
			if(isset($arrCats[$dir]) == false){

				$catHandle = UELM_HelperUC::convertTitleToHandle($dir);
				$catID = "ucweb_".$catHandle;

				$arrCats[$dir] = array(
					"id"=>$catID,
					"isweb"=>true,
					"title"=>$dir,
					"addons"=>array()
				);

				$numRegularAddons = 0;

			}else{
				$arrRegularAddons = UELM_UniteFunctionsUC::getVal($arrCats[$dir], "addons");
				if(empty($arrRegularAddons))
					$arrRegularAddons = array();

				$numRegularAddons = count($arrRegularAddons);
			}

			$numWebAddons = 0;

			//add addons from web to existing folder
			foreach($addons as $addonName => $arrAddon){

				$name = UELM_UniteFunctionsUC::getVal($arrAddon, "name");
				if(empty($name))
					$name = $addonName;

				$name2 = null;
				if(!empty($addonType))
					$name2 = $name."_".$addonType;


				//search for the addon in cats
				if(isset($arrCats[$dir]["addons"][$name])){
					continue;
				}

				if(!empty($name2) && isset($arrCats[$dir]["addons"][$name2]))
					continue;

				//addo not found, add the web addon
				$arrAddon["isweb"] = true;
				$arrCats[$dir]["addons"][$name] = $arrAddon;

				$parent = UELM_UniteFunctionsUC::getVal($arrAddon, "parent");

				//don't cound children
				if(empty($parent))
					$numWebAddons++;
			}

			$arrCats[$dir]["num_regular_addons"] = $numRegularAddons;
			$arrCats[$dir]["num_web_addons"] = $numWebAddons;
		}


		if($numAddonsOnly == false)
			return($arrCats);

		//replace the addons bu num addons
		foreach($arrCats as $dir=>$cat){

			$arrAddons = UELM_UniteFunctionsUC::getVal($cat, "addons");
			$numAddons = 0;
			if(!empty($arrAddons))
				$numAddons = count($arrAddons);

			$arrCats[$dir]["num_addons"] = $numAddons;

			if(isset($arrCats[$dir]["num_web_addons"]))
				$arrCats[$dir]["num_addons"] = $arrCats[$dir]["num_regular_addons"] + $arrCats[$dir]["num_web_addons"];

			unset($arrCats[$dir]["addons"]);
		}


		return($arrCats);
	}


	/**
	 * filter, get only parents
	 */
	private function filterCatalogAddons_getOnlyParents($arrCatalogAddons){

		if(empty($arrCatalogAddons))
			return($arrCatalogAddons);

		$arrAddonsNew = array();
		foreach($arrCatalogAddons as $name=>$addon){

			$parent = UELM_UniteFunctionsUC::getVal($addon, "parent");
			$isSingle = empty($parent);

			if($isSingle == false)
				continue;

			if(isset($addon["is_parent"])){
				$isParent = UELM_UniteFunctionsUC::getVal($addon, "is_parent");
				$isParent = UELM_UniteFunctionsUC::strToBool($isParent);

				if($isParent == false)
					continue;
			}

			$arrAddonsNew[$name] = $addon;
		}

		return($arrAddonsNew);
	}


	/**
	 * get child addons by parent id
	 */
	private function filterCatalogAddons_getChildAddons($arrCatalogAddons, $parentID){

		$arrAddonsNew = array();
		foreach($arrCatalogAddons as $name=>$addon){

			$isParent = UELM_UniteFunctionsUC::getVal($addon, "is_parent");
			if(!empty($isParent))
				continue;

			$addonParent = UELM_UniteFunctionsUC::getVal($addon, "parent");
			if($addonParent != $parentID)
				continue;

			$arrAddonsNew[$name] = $addon;
		}

		return($arrAddonsNew);
	}


	/**
	 * merge addons objects with the addons from catalog
	 */
	public function mergeCatAddonsWithCatalog($title, $arrAddons, $objAddonsType, $params = null){

		//uelm_dmp("merge");uelm_dmp($params);exit();

		//don't work with another addon types
		if($this->isAddonTypeSupported($objAddonsType) == false)
			return($arrAddons);

		$arrCatalogAddons = $this->getArrCatAddons($title, $objAddonsType);
		if(empty($arrCatalogAddons))
			return($arrAddons);


		$filterSearch = UELM_UniteFunctionsUC::getVal($params, "filter_search");
		$filterSearch = trim($filterSearch);

		if(!empty($filterSearch)){

			$arrCatalogAddons = $this->filterCatalogAddonsBySearchString($arrCatalogAddons, $filterSearch);
		}
		else		//don't filter searched by parent
		if($objAddonsType->hasParents == true){

			$parentID = UELM_UniteFunctionsUC::getVal($params, "parent_id");

			if(empty($parentID)){

				$arrCatalogAddons = $this->filterCatalogAddons_getOnlyParents($arrCatalogAddons);

			}
			else{
				$arrCatalogAddons = $this->filterCatalogAddons_getChildAddons($arrCatalogAddons, $parentID);
			}

		}

		if(empty($arrCatalogAddons))
			return($arrAddons);

		$addonType = $objAddonsType->typeName;
		if($objAddonsType->isBasicType == true)
			$addonType = "";

		$arrNames = array();
		foreach($arrAddons as $addon){

			$name = $addon->getName();
			$arrNames[$name] = true;
		}

		$arrWebAddons = array();
		$arrWebNames = array();


		//filter addons by names
		foreach($arrCatalogAddons as $addonName => $addon){

			//web addon name
			$name = UELM_UniteFunctionsUC::getVal($addon, "name");
			if(empty($name))
				$name = $addonName;

			//validations
			if(is_numeric($name) == false){

				//second variant of web addon name
				$name2 = null;
				if(!empty($addonType))
					$name2 = $name."_".$addonType;


				if(!empty($name2) && isset($arrNames[$name2]))
					continue;

				if(isset($arrNames[$name]))
					continue;

				if(empty($name))
					continue;
			}

			$addon["isweb"] = true;
			if(!isset($addon["name"]))
				$addon["name"] = $name;

			$arrWebNames[] = $name;
			$arrWebAddons[$name] = $addon;
		}

		//exclude web addons existing in another folders
		$arrWebAddons = $this->filterWebAddonsByInstalled($arrWebAddons, $arrWebNames);

		//add the web addons
		foreach($arrWebAddons as $addon)
			$arrAddons[] = $addon;

		return($arrAddons);
	}


	/**
	 * merge categories list with catalog
	 * for manager
	 */
	public function mergeCatsWithCatalog($arrCats){

		if($this->isCatalogExists() == false)
			$this->checkUpdateCatalog();

		$arrWebCatalog = $this->getCatalogArray_addons();

		if(empty($arrWebCatalog))
			return($arrCats);

		$arrCats = UELM_UniteFunctionsUC::arrayToAssoc($arrCats,"title");

		foreach($arrWebCatalog as $dir=>$addons){
			$arrDir = array();

			if(empty($addons))
				$addons = array();

			if(isset($arrCats[$dir]) == false)
				$arrCats[$dir] = array(
					"isweb"=>true,
					"title"=>$dir,
					"num_addons"=>count($addons)
				);

			//add number of web addons
		}


		return($arrCats);
	}
	
	
	
	/**
	 * filter web addons with installed addons
	 */
	private function filterWebAddonsByInstalled($arrWebAddons, $arrWebNames){

		if(empty($arrWebAddons))
			return($arrWebAddons);

		$objAddons = new UELM_UniteCreatorAddons();

		$params = array();
		$params["filter_names"] = $arrWebNames;
		$arrInstalledAddons = $objAddons->getArrAddonsShort("", $params);

		if(empty($arrInstalledAddons))
			return($arrWebAddons);

		foreach($arrInstalledAddons as $addon){
			$name = UELM_UniteFunctionsUC::getVal($addon, "name");
			unset($arrWebAddons[$name]);
		}

		return($arrWebAddons);
	}



	/**
	 * get imported addon data
	 */
	protected function getImportedAddonData($addonType, $addonID){


		return(array());
	}



	/**
	 * install catalog addon
	 */
	public function installCatalogAddonFromData($data){

		$name = UELM_UniteFunctionsUC::getVal($data, "name");
		$cat = UELM_UniteFunctionsUC::getVal($data, "cat");
		$addonType = UELM_UniteFunctionsUC::getVal($data, "type");

		$objAddonType = UELM_UniteCreatorAddonType::getAddonTypeObject($addonType);

		$catalogAddonType = $objAddonType->catalogKey;

		$apiData = array();
		$apiData["name"] = $name;
		$apiData["cat"] = $cat;
		$apiData["type"] = $catalogAddonType;

		$zipContent = $this->callAPI("get_addon_zip", $apiData, true);

		//save to folder
		$filename = $name.".zip";
		$filepath = UELM_GlobalsUC::$path_cache.$filename;
		UELM_UniteFunctionsUC::writeFile($zipContent, $filepath);

		$exporter = new UELM_UniteCreatorExporter();

		if($objAddonType->isBasicType == false){

			$addonType = $objAddonType->typeName;
			$exporter->setMustImportAddonType($addonType);
		}

		$exporter->import(null, $filepath);

		$importedAddonID = $exporter->getImportedAddonID();

		$objAddon = new UELM_UniteCreatorAddon();
		$objAddon->initByID($importedAddonID);

		$alias = $objAddon->getAlias();

		$response = array();
		$response["addonid"] = $importedAddonID;
		$response["alias"] = $alias;

		$addonData = $this->getImportedAddonData($addonType, $importedAddonID);
		if(!empty($addonData))
			$response = array_merge($response, $addonData);

		return($response);
	}

	/**
	 * install addon by name
	 * find the needed category from the catalog
	 */
	public function installCatalogAddonByName($name, $addonType){

		$isBG = false;
		if($addonType == UELM_GlobalsUC::ADDON_TYPE_BGADDON)
			$isBG = true;

		$addon = $this->getAddonByName($name, $isBG);

		if(empty($addon))
			return("widget not found: $name");

		$cat = UELM_UniteFunctionsUC::getVal($addon, "cat");

		$title = UELM_UniteFunctionsUC::getVal($addon, "title");

		$data = array();
		$data["name"] = $name;
		$data["cat"] = $cat;
		$data["type"] = $addonType;

		$this->installCatalogAddonFromData($data);

		if($isBG == true)
			$log = "Installed BG widget: $title";
		else
			$log = "Installed widget: $title";


		return($log);
	}


	/**
	 * install catalog addon
	 */
	public function installCatalogPageFromData($data){

		$name = UELM_UniteFunctionsUC::getVal($data, "name");
		$params = UELM_UniteFunctionsUC::getVal($data, "params");
		$addonType = UELM_UniteFunctionsUC::getVal($data, "type");


		$objAddonType = UELM_UniteCreatorAddonType::getAddonTypeObject($addonType);

		$catalogAddonType = $objAddonType->catalogKey;

		if(empty($params))
			$params = array();

		$layoutID = UELM_UniteFunctionsUC::getVal($params, "layout_id");
		if(empty($layoutID))
			$layoutID = null;

		$apiData = array();
		$apiData["name"] = $name;
		$apiData["type"] = $catalogAddonType;

		$zipContent = $this->callAPI("get_page_zip", $data, true);

		//save to folder
		$filename = $name.".zip";
		$filepath = UELM_GlobalsUC::$path_cache.$filename;
		UELM_UniteFunctionsUC::writeFile($zipContent, $filepath);

		$exporter = new UELM_UniteCreatorLayoutsExporter();
		$importedLayoutID = $exporter->import($filepath, $layoutID, true, $params);

		if(file_exists($filepath))
			@unlink($filepath);

		$arrResponse = array();
		$arrResponse["layoutid"] = $importedLayoutID;

		return($arrResponse);
	}


	/**
	 * get latest plugin version
	 */
	public function getLatestVersion() {
		return $this->callAPI( 'get_latest_version', array('product' => UELM_GlobalsUnlimitedElements::PLUGIN_NAME) );
	}


}
