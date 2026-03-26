<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorExporter extends UELM_UniteCreatorExporterBase{

	const MULTIPLE_FOLDERS_FILENAME = "multiple";
	const MUST_TYPE_FORCE_REGULAR = "force_regular_addon";

	private $addon;

	protected $objAddons;
	protected $pathExportAddons;
	protected $filenameAddon;
	protected $filepathAddonZip;
	protected $pathExportAddon;
	protected $pathExportAddonAssets;

	//export categories

	protected $pathExportCategories;
	protected $pathExportCategory;
	protected $pathExportCategoryAddons;

	protected $filepathCategoryZip;
	protected $filenameCategoryZip;

	//import
	private $isPathSingle = false;

	private $pathImportAddon;
	private $pathImportAddonAssets;
	private $isImportedOnce = false;
	private $textLogShort = "";
	private $importedAddonType = "";
	private $mustAddonType = null;
	private $downloadFilenameEnding = null;
	private $catExportData = null;
	private $lastImportedAddonID;
	private $lastImportedCatID;
	private static $arrImportedAddons = array();

	/**
	 * constructor
	 */
	public function __construct(){

		parent::__construct();

		$this->objAddons = new UELM_UniteCreatorAddons();
	}

	/**
	 * validate that the addon exists
	 */
	private function validateInited(){

		if(empty($this->addon))
			UELM_UniteFunctionsUC::throwError("export error: addon not inited");
	}

	/**
	 * print all globals variables
	 */
	private function printVars(){

		$vars = get_object_vars($this);

		uelm_dmp($vars);
	}

	/**
	 * init by addon
	 *
	 * @param $addon
	 */
	public function initByAddon(UELM_UniteCreatorAddon $addon){

		$this->addon = $addon;
	}

	private function a_________EXPORT_ADDON_______(){
	}

	/**
	 * create export folder if not exists
	 */
	private function prepareExportFolders_addons(){

		$this->prepareExportFolders_globalExport();

		$this->pathExportAddons = $this->pathExport . "addons/";
		UELM_UniteFunctionsUC::mkdirValidate($this->pathExportAddons, "Export Addons");

		//clear folder
		UELM_UniteFunctionsUC::deleteDir($this->pathExportAddons, false);

		$this->pathExportAddon = $this->pathExportAddons . "addon_" . UELM_UniteFunctionsUC::getRandomString(10) . "/";
		$this->pathExportAddonAssets = $this->pathExportAddon . "assets/";

		//create index.html
		UELM_UniteFunctionsUC::writeFile("", $this->pathExportAddons . "index.html");

		UELM_UniteFunctionsUC::mkdirValidate($this->pathExportAddon, "Export Addon");

		$addonName = $this->addon->getName();

		$tempFilenameZip = $addonName . "_" . UELM_UniteFunctionsUC::getRandomString(10) . ".zip";

		$this->filepathAddonZip = $this->pathExportAddons . $tempFilenameZip;
	}

	/**
	 * modify includes in config that it will be saved in new way
	 */
	private function modifyConfig($config){

		$includes = UELM_UniteFunctionsUC::getVal($config, "includes");

		$arrJs = UELM_UniteFunctionsUC::getVal($includes, "js");
		$arrCss = UELM_UniteFunctionsUC::getVal($includes, "css");

		$arrJs = UELM_HelperUC::arrUrlsToRelative($arrJs, true);
		$arrCss = UELM_HelperUC::arrUrlsToRelative($arrCss, true);

		if(!empty($arrJs))
			$includes["js"] = $arrJs;

		if(!empty($arrCss))
			$includes["css"] = $arrCss;

		$config["includes"] = $includes;

		//no export dynamic post ID
		unset($config["options"]["dynamic_post"]);

		//modify params, exclude dynamic post
		$params = UELM_UniteFunctionsUC::getVal($config, "params");
		if(empty($params))
			$params = array();

		foreach($params as $key => $param){
			$type = UELM_UniteFunctionsUC::getVal($param, "type");

			switch($type){
				case UELM_UniteCreatorDialogParam::PARAM_POSTS_LIST:

					//remove dynamic post example

					unset($config["params"][$key]["post_example"]);
					unset($config["params"][$key]["post_example_text"]);

				break;
			}
		}

		return ($config);
	}

	/**
	 * get xml from addon
	 */
	private function getJsonFromAddon(){

		$arrAddon = array();

		$addonType = $this->addon->getType();

		$addonName = $this->addon->getName();
		$addonAlias = $this->addon->getAlias();

		$catTitle = $this->addon->getCatTitle();
		//set alias and name by type
		if(!empty($addonType) && empty($addonAlias)){
			$addonAlias = $addonName;
			$addonName = $addonName . "_" . $addonType;
		}

		if($this->mustAddonType == self::MUST_TYPE_FORCE_REGULAR){
			$addonType = "";
			if(!empty($addonAlias)){
				$addonName = $addonAlias;
				$addonAlias = "";
			}
		}

		$arrAddon["addontype"] = $addonType;
		$arrAddon["cattitle"] = $catTitle;
		$arrAddon["name"] = $addonName;
		$arrAddon["alias"] = $addonAlias;

		$arrAddon["title"] = $this->addon->getTitle();
		$arrAddon["description"] = $this->addon->getDescription();

		$arrConfig = $this->addon->getConfig();
		$arrAddon["config"] = $this->modifyConfig($arrConfig);

		$arrTemplates = $this->addon->getTemplates();

		$arrTemplateNames = array();
		foreach($arrTemplates as $name => $content){
			if(!empty($content))
				$arrTemplateNames[] = $name;
		}

		$arrAddon["templates"] = $arrTemplateNames;

		$arrAddon = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_EXPORT_ADDON_DATA, $arrAddon);

		$json = json_encode($arrAddon, JSON_PRETTY_PRINT);

		return ($json);
	}

	/**
	 * get json category from addon, or from set cat data
	 */
	protected function getJsonCategoryFromAddon(){

		//get from global saved data (fro category addons)
		if(!empty($this->catExportData)){
			$jsonData = json_encode($this->catExportData);

			return ($jsonData);
		}

		//get from addon data
		$catID = $this->addon->getCatID();
		$addonType = $this->addon->getType();

		$arrData = $this->getExportCatData($catID, $addonType);
		if(empty($arrData))
			return (null);

		$jsonData = json_encode($arrData);

		return ($jsonData);
	}

	/**
	 * write file if not empty content
	 */
	private function writeFile($filename, $content){

		$filepath = $this->pathExportAddon . $filename;
		if(!empty($content))
			UELM_UniteFunctionsUC::writeFile($content, $filepath);
	}

	/**
	 * write export data
	 */
	private function createExportFiles(){

		//write addon main file
		$strJson = $this->getJsonFromAddon();
		$this->writeFile("addon.json", $strJson);

		//write category json file
		$strJsonCat = $this->getJsonCategoryFromAddon();
		if(!empty($strJsonCat))
			$this->writeFile("category.json", $strJsonCat);

		//write template files
		$arrTemplates = $this->addon->getTemplates();

		//write templates
		foreach($arrTemplates as $name => $content){
			$filename = $name . ".tpl";
			$this->writeFile($filename, $content);
		}

		//write default data
		$defaultData = $this->addon->getDefaultData();
		$defaultData = $this->addon->modifyAddonDataConvertToUrlAssets($defaultData);

		if(!empty($defaultData)){
			$defaultData = json_encode($defaultData, JSON_PRETTY_PRINT);
			$this->writeFile("default_data.json", $defaultData);
		}
	}

	/**
	 * check if assets path is ready for export
	 */
	private function isPathAssetsReadyForExport($pathAssets){

		$objAddonType = $this->addon->getObjAddonType();

		$isUnderAssets = UELM_HelperUC::isPathUnderAssetsPath($pathAssets, $objAddonType);

		if(!$isUnderAssets)
			return (false);

		if(is_dir($pathAssets) == false)
			return (false);

		$isPathAssets = UELM_HelperUC::isPathAssets($pathAssets, $objAddonType);
		if($isPathAssets == true)
			return (false);

		return (true);
	}

	/**
	 * copy addon assets
	 */
	private function copyAssets($imagesMode = 'all'){

		$options = $this->addon->getOptions();
		$dirAssets = $this->addon->getOption("path_assets");

		if(empty($dirAssets))
			return (false);

		$pathAssets = $this->addon->getPathAssetsFull();

		$isReady = $this->isPathAssetsReadyForExport($pathAssets);

		if($isReady == false)
			return (false);

		//make assets folder
		UELM_UniteFunctionsUC::mkdirValidate($this->pathExportAddonAssets, "assets folder");

		$pathAssetsDest = $this->pathExportAddonAssets . $dirAssets . "/";

		UELM_UniteFunctionsUC::mkdirValidate($pathAssetsDest, "assets dest folder: $dirAssets");

		UELM_UniteFunctionsUC::copyDir($pathAssets, $pathAssetsDest);

		//delete thumbs folders

		$arrDirs = UELM_UniteFunctionsUC::getDirList($pathAssets);

		foreach($arrDirs as $dir){
			switch($dir){
				case UELM_GlobalsUC::DIR_THUMBS_ELFINDER:
				case ".quarantine":
				case UELM_GlobalsUC::DIR_THUMBS:

					$pathDirDelete = $pathAssetsDest . $dir . "/";

					UELM_UniteFunctionsUC::deleteDir($pathDirDelete);
					
				break;
			}
		}
		switch($imagesMode) {
		case 'images-only':
			$fileList = UELM_UniteFunctionsUC::getFileList($pathAssets);
			foreach($fileList as $file) {
				$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION) ?? '');
				switch($ext) {
				case 'jpeg':
				case 'jpg':
				case 'svg':
				case 'gif':
				case 'png':
					break;
				default:
					unlink($pathAssetsDest . $file);
				}
			}	
		break;
		case 'no-images':
			$fileList = UELM_UniteFunctionsUC::getFileList($pathAssets);
			foreach($fileList as $file) {
				$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION) ?? '');
				switch($ext) {
					case 'jpeg':
					case 'jpg':
					case 'svg':
					case 'gif':
					case 'png':
						unlink($pathAssetsDest . $file);
					break;
				}
			}		
		break;
		}


	}

	/**
	 * make export zip file
	 */
	private function makeExportZipFile(){
		
		$zip = new UELM_UniteZipUC();
		$zip->makeZip($this->pathExportAddon, $this->filepathAddonZip);

		if(file_exists($this->filepathAddonZip) == false)
			UELM_UniteFunctionsUC::throwError("zip file {$this->filepathAddonZip} could not be created");
	}

	/**
	 * delete export addon folder
	 */
	private function deleteExportAddonFolder(){

		if(!empty($this->pathExportAddon) && is_dir($this->pathExportAddon))
			UELM_UniteFunctionsUC::deleteDir($this->pathExportAddon);
	}

	/**
	 * create category folder
	 */
	private function moveExportZipToFolder_createCategoryFolder($moveFolder){

		$addonType = $this->addon->getType();
		$catTitle = $this->addon->getCatTitle();

		$isSpecialType = UELM_HelperUC::isSpecialAddonType($addonType);
		if($isSpecialType == true)
			$catTitle = $addonType . "_" . $catTitle;

		if(empty($catTitle))
			return ($moveFolder);

		$moveFolder = $moveFolder . $catTitle . "/";

		if(is_dir($moveFolder))
			return ($moveFolder);

		//create the folder
		UELM_UniteFunctionsUC::mkdirValidate($moveFolder, $catTitle);

		//write category file
		$addonType = $this->addon->getType();
		$catID = $this->addon->getCatID();
		$exportCatData = $this->getExportCatData($catID, $addonType);
		$this->createCategoryDataFile($exportCatData, $moveFolder);

		return ($moveFolder);
	}

	/**
	 * write to folder
	 */
	private function moveExportZipToFolder($moveFolder, $createCategoryFolder = false){

		UELM_UniteFunctionsUC::validateDir($moveFolder);

		/**
		 * create category folder if needed
		 */
		if($createCategoryFolder === true)
			$moveFolder = $this->moveExportZipToFolder_createCategoryFolder($moveFolder);

		$addonAlias = $this->addon->getAlias();
		$addonName = $this->addon->getName();

		if(isset($this->filenameAddon))
			$filename = $this->filenameAddon . ".zip";
		elseif($this->mustAddonType == self::MUST_TYPE_FORCE_REGULAR)
			$filename = $addonAlias . ".zip";
		else
			$filename = $addonName . ".zip";

		$newFilepath = $moveFolder . $filename;

		$success = UELM_UniteFunctionsUC::move($this->filepathAddonZip, $newFilepath);

		if($success === false)
			UELM_UniteFunctionsUC::throwError("Can't move export zip:$filename to the export folder");
	}

	/**
	 * download the zip file
	 */
	private function downloadFile(){

		$addonName = $this->addon->getName();
		$filename = $addonName;

		if(!empty($this->downloadFilenameEnding))
			$filename .= $this->downloadFilenameEnding;

		$filename .= ".zip";

		UELM_UniteFunctionsUC::downloadFile($this->filepathAddonZip, $filename);
	}

	/**
	 * set addon filename
	 */
	public function setFilename($filename){

		$this->filenameAddon = $filename;
	}

	/**
	 * set download filename ending
	 */
	public function setDownloadFilenameEnding($ending){

		$this->downloadFilenameEnding = $ending;
	}

	/**
	 * export addon - create export file and send it to download.
	 */
	public function export($moveFolder = null, $createCategoryFolders = false, $catExportData = null){

		$this->validateInited();

		try{
			$this->catExportData = $catExportData;

			$this->prepareExportFolders_addons();

			$this->createExportFiles();

			$this->copyAssets();

			$this->makeExportZipFile();
			
			$this->deleteExportAddonFolder();
		
			if(!empty($moveFolder)){
				$this->moveExportZipToFolder($moveFolder, $createCategoryFolders);
				$this->deleteExportAddonFolder();
			}else{
				$this->downloadFile();
				exit();
			}
		}catch(Exception $e){
			$this->deleteExportAddonFolder();
			throw $e;
		}
	}
	
	/**
	 * export addon - create export file with no images and send save it in $moveFolder
	 */
	public function exportNoImages($moveFolder){

		$this->validateInited();

		try{

			$this->catExportData = $catExportData;

			$this->prepareExportFolders_addons();

			$this->createExportFiles();

			$imagesMode = 'no-images';
			$this->copyAssets($imagesMode);

			$this->makeExportZipFile();
			$this->deleteExportAddonFolder();

			if(!empty($moveFolder)){

				$this->moveExportZipToFolder($moveFolder, $createCategoryFolders);

				$this->deleteExportAddonFolder();
			}else{
				$this->downloadFile();
				exit();
			}
		}catch(Exception $e){
			$this->deleteExportAddonFolder();
			throw $e;
		}
	}
	
	/**
	 * export addon - create export file (images only) and send it to download.
	 */
	public function exportImagesOnly($moveFolder){

		$this->validateInited();

		try{

			$this->catExportData = $catExportData;

			$this->prepareExportFolders_addons();

			// $this->createExportFiles();

			$imagesMode = 'images-only';
			$this->copyAssets($imagesMode);

			$this->makeExportZipFile();
			$this->deleteExportAddonFolder();

			if(!empty($moveFolder)){

				$this->moveExportZipToFolder($moveFolder, $createCategoryFolders);

				$this->deleteExportAddonFolder();
			}else{
				$this->downloadFile();
				exit();
			}
		}catch(Exception $e){
			$this->deleteExportAddonFolder();
			throw $e;
		}

	}

	private function a_______EXPORT_CATEGORY______(){
	}

	/**
	 * prepare export categories folders
	 */
	private function prepareExportFolders_categories($catTitle, $exportType = ""){

		$this->prepareExportFolders_globalExport();

		//delete cats folder only on first time create
		$clearCatsFolder = true;
		if(!empty($this->pathExportCategories))
			$clearCatsFolder = false;

		//make folders
		$this->pathExportCategories = $this->pathExport . "categories/";
		UELM_UniteFunctionsUC::mkdirValidate($this->pathExportCategories, "Categories");

		//clear folder
		if($clearCatsFolder == true)
			UELM_UniteFunctionsUC::deleteDir($this->pathExportCategories, false);

		$this->pathExportCategory = $this->pathExportCategories . "category_" . UELM_UniteFunctionsUC::getRandomString(10) . "/";

		UELM_UniteFunctionsUC::mkdirValidate($this->pathExportCategory, "Export Category");

		//create index.html
		UELM_UniteFunctionsUC::writeFile("", $this->pathExportCategories . "index.html");

		//create actual category folder
		if(!empty($catTitle))
			$this->prepareExportFolders_category($catTitle, $exportType);
	}

	/**
	 * prepare category export folder and names
	 */
	private function prepareExportFolders_category($catTitle, $exportType = ""){

		UELM_UniteFunctionsUC::validateNotEmpty($catTitle);

		UELM_UniteFunctionsUC::validateNotEmpty($this->pathExportCategory);

		$this->pathExportCategoryAddons = $this->pathExportCategory . $catTitle . "/";
		UELM_UniteFunctionsUC::mkdirValidate($this->pathExportCategoryAddons, "$catTitle category");

		$this->prepareExportFolders_exportFilename($catTitle, $exportType);
	}

	/**
	 * prepare export folders file path and filename
	 */
	private function prepareExportFolders_exportFilename($catTitle, $exportType = "", $addSuffix = true){

		$suffix = "";

		if($addSuffix == true){
			if(!empty($exportType))
				$suffix .= "_" . $exportType;
			$suffix .= "_widgets";
		}

		$nameZip = $catTitle . $suffix;
		$nameZip = UELM_HelperUC::convertTitleToHandle($nameZip);

		$this->filenameCategoryZip = $nameZip . ".zip";

		$tempFilenameZip = $nameZip . "_" . UELM_UniteFunctionsUC::getRandomString(10) . ".zip";

		$this->filepathCategoryZip = $this->pathExportCategories . $tempFilenameZip;
	}

	/**
	 * create category addons zips
	 * put them in the category export folder
	 */
	private function createCategoryAddonsZips($arrAddons, $catExportData = null){

		foreach($arrAddons as $addon){
			$objExport = new UELM_UniteCreatorExporter();
			$objExport->initByAddon($addon);

			$objExport->setMustImportAddonType($this->mustAddonType);

			$objExport->export($this->pathExportCategoryAddons, false, $catExportData);
		}
	}

	/**
	 * make export zip file
	 */
	private function makeExportCategoryZipFile(){

		$zip = new UELM_UniteZipUC();
		$zip->makeZip($this->pathExportCategory, $this->filepathCategoryZip);

		if(file_exists($this->filepathCategoryZip) == false)
			UELM_UniteFunctionsUC::throwError("zip file {$this->filepathCategoryZip} could not be created");
	}

	/**
	 * download the category zip file
	 */
	private function downloadCategoryFile(){

		UELM_UniteFunctionsUC::downloadFile($this->filepathCategoryZip, $this->filenameCategoryZip);
	}

	/**
	 * get addons array and cat title from catID
	 */
	private function exportCatAddons_getArrAddons($catID, $exportType){

		$objCat = new UELM_UniteCreatorCategories();
		$arrCat = $objCat->getCat($catID);

		if(empty($exportType))
			$exportType = UELM_UniteFunctionsUC::getVal($arrCat, "type");

		$catTitle = UELM_UniteFunctionsUC::getVal($arrCat, "title");
		$catType = UELM_UniteFunctionsUC::getVal($arrCat, "type");

		UELM_UniteFunctionsUC::validateNotEmpty($catTitle);

		$objAddons = new UELM_UniteCreatorAddons();

		$arrAddons = $objAddons->getCatAddons($catID, false, null, $exportType);

		if(empty($arrAddons))
			UELM_UniteFunctionsUC::throwError("No addons found in category: $catTitle (id - $catID)");

		//set addon types
		foreach($arrAddons as $addon){
			$addon->setType($exportType);
		}

		$output = array();
		$output["title"] = $catTitle;
		$output["type"] = $catType;
		$output["addons"] = $arrAddons;

		return ($output);
	}

	/**
	 * export category data
	 */
	private function createCategoryDataFile($catExportData, $pathExport = null){

		$jsonData = json_encode($catExportData);

		if(empty($pathExport))
			$pathExport = $this->pathExportCategoryAddons;

		UELM_UniteFunctionsUC::validateDir($pathExport, "export category data");

		$filepathCategory = $pathExport . "category.json";
		UELM_UniteFunctionsUC::writeFile($jsonData, $filepathCategory);
	}

	/**
	 * get category export data
	 */
	protected function getExportCatData($catID, $exportType){

		if(empty($catID))
			return (null);

		if(empty($exportType))
			$exportType = null;

		$objCat = new UELM_UniteCreatorCategory();
		$objCat->initByID($catID);
		$catExportData = $objCat->getExportCatData($exportType);

		return ($catExportData);
	}

	/**
	 * export category addons
	 */
	public function exportCatAddons($catID, $exportType = null, $runFunc = null){

		$output = $this->exportCatAddons_getArrAddons($catID, $exportType);
		$arrAddons = $output["addons"];

		//get category data
		$catExportData = $this->getExportCatData($catID, $exportType);

		$catTitle = $catExportData["title"];

		try{
			$this->prepareExportFolders_categories($catTitle, $exportType);
			$this->createCategoryDataFile($catExportData);
			$this->createCategoryAddonsZips($arrAddons, $catExportData);

			if($runFunc === null){
				$this->makeExportCategoryZipFile();
				$this->downloadCategoryFile();
				exit();
			}else{
				call_user_func(array($this, $runFunc));
			}
		}catch(Exception $e){
			throw $e;
		}
	}

	private function a_______EXPORT_CATS______(){
	}

	/**
	 * export multiple categories and their addons
	 */
	public function exportCatsAndAddons($arrCats, $exportType = "", $runFunc = null, $filename = ""){

		UELM_UniteFunctionsUC::validateNotEmpty($arrCats);

		if(is_numeric($arrCats))
			$arrCats = array($arrCats);

		if(is_array($arrCats) == false)
			UELM_UniteFunctionsUC::throwError("export cats error: The categories should be array");

		try{
			//create general export folder
			$this->prepareExportFolders_categories("");

			foreach($arrCats as $catID){
				$output = $this->exportCatAddons_getArrAddons($catID, $exportType);

				$arrAddons = $output["addons"];
				$catTitle = $output["title"];
				$catAddonType = $output["type"];

				$catTitle = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_EXPORT_CAT_TITLE, $catTitle, $exportType, $catAddonType);

				$this->prepareExportFolders_category($catTitle, $exportType);
				$this->createCategoryAddonsZips($arrAddons);

				UELM_UniteProviderFunctionsUC::doAction("uc_export_category_addons", $catTitle);
			}

			//create zip file
			if(empty($filename)){
				if(count($arrCats) == 1)
					$filename = $catTitle;
				else
					$filename = self::MULTIPLE_FOLDERS_FILENAME;
			}

			UELM_UniteFunctionsUC::validateNotEmpty($filename, "export filename");

			if($runFunc === null){
				$this->prepareExportFolders_exportFilename($filename, $exportType);

				$this->makeExportCategoryZipFile();
				$this->downloadCategoryFile();
				exit();
			}else{
				call_user_func(array($this, $runFunc));
			}
		}catch(Exception $e){
			throw $e;
		}
	}

	private function a_______IMPORT_ADDON_______(){
	}

	/**
	 * validate that the temp file array
	 */
	private function validateArrTempFile($arrTempFile){

		$filename = UELM_UniteFunctionsUC::getVal($arrTempFile, "name");
		UELM_UniteFunctionsUC::validateNotEmpty($filename, "addon file name");

		$info = pathinfo($filename);
		$ext = UELM_UniteFunctionsUC::getVal($info, "extension");
		$ext = strtolower($ext);

		if($ext != "zip")
			UELM_UniteFunctionsUC::throwError("Wrong import addon file type: {$filename}, should be zip type only.");
	}

	/**
	 * prepare import addon folders
	 */
	private function prepareImportFolders(){

		//prepare import folder
		$this->prepareImportFolders_globalImport();

		if($this->isPathSingle == false){    //first path

			$pathImportBase = $this->pathImport . "first/";
		}else{
			$pathImportBase = $this->pathImport . "single/";
		}

		UELM_UniteFunctionsUC::mkdirValidate($pathImportBase, "import first");
		UELM_UniteFunctionsUC::deleteDir($pathImportBase, false);

		//create index.html
		UELM_UniteFunctionsUC::writeFile("", $pathImportBase . "index.html");

		self::$serial++;

		$this->pathImportAddon = $pathImportBase . "addon_" . self::$serial . "_" . UELM_UniteFunctionsUC::getRandomString(10) . "/";
		$this->pathImportAddonAssets = $this->pathImportAddon . "assets/";

		UELM_UniteFunctionsUC::mkdirValidate($this->pathImportAddon, "import addon");
	}

	/**
	 * delete import addon folder
	 */
	private function deleteImportAddonFolder(){

		if(!empty($this->pathImportAddon) && is_dir($this->pathImportAddon))
			UELM_UniteFunctionsUC::deleteDir($this->pathImportAddon);
	}

	/**
	 * unpack import addon from temp file
	 */
	private function extractImportAddonFile($arrTempFile){

		$filepath = UELM_UniteFunctionsUC::getVal($arrTempFile, "tmp_name");

		$zip = new UELM_UniteZipUC();
		$extracted = $zip->extract($filepath, $this->pathImportAddon);

		if($extracted == false)
			UELM_UniteFunctionsUC::throwError("The import addon zip didn't extracted");
	}

	/**
	 * import templates
	 */
	private function importAddonData_addTemplates($arrImport, $addonData){

		//prepare templates data
		$templateNames = UELM_UniteFunctionsUC::getVal($arrImport, "templates");

		$arrTemplates = array();
		foreach($templateNames as $templateName){
			$filenameTemplate = $templateName . ".tpl";
			$filepathTemplate = $this->pathImportAddon . $filenameTemplate;
			if(is_file($filepathTemplate) == false)
				UELM_UniteFunctionsUC::throwError("Template {$filenameTemplate} not found!");

			$templateContent = UELM_UniteFunctionsUC::fileGetContents($filepathTemplate);
			$arrTemplates[$templateName] = $templateContent;
		}

		$addonData["templates"] = json_encode($arrTemplates);

		return ($addonData);
	}

	/**
	 * add test data
	 */
	private function importAddonData_addDefaultData($addonData){

		$filenameTestData = "default_data.json";
		$filepathTestData = $this->pathImportAddon . $filenameTestData;

		if(file_exists($filepathTestData) == false)
			return ($addonData);

		$arrContent = UELM_UniteFunctionsUC::fileGetContents($filepathTestData);
		if(empty($arrContent))
			return ($addonData);

		//check if the string is json (decode and encode again)
		$arrContent = UELM_UniteFunctionsUC::jsonDecode($arrContent);

		if(is_array($arrContent) == false)
			return ($addonData);

		//add the default data (test_slot2)

		$jsonContent = json_encode($arrContent);

		$addonData["test_slot2"] = $jsonContent;

		return ($addonData);
	}

	/**
	 * import addon data
	 */
	private function importAddonData($catID, $overwrite = true, $forceToCat = true){

		$filenameAddon = "addon.json";
		$filepathData = $this->pathImportAddon . $filenameAddon;

		if(is_file($filepathData) == false)
			UELM_UniteFunctionsUC::throwError("Addon import file: $filenameAddon don't found");

		$contents = UELM_UniteFunctionsUC::fileGetContents($filepathData);

		if(empty($contents))
			UELM_UniteFunctionsUC::throwError("Empty import file {$filenameAddon} contents");

		$arrImport = @json_decode($contents, true);

		if(empty($arrImport))
			UELM_UniteFunctionsUC::throwError("Wrong import file {$filenameAddon} content");

		if(is_array($arrImport) == false)
			UELM_UniteFunctionsUC::throwError("Wrong addon import data, should be array");

		$addonName = UELM_UniteFunctionsUC::getVal($arrImport, "name");
		$addonType = UELM_UniteFunctionsUC::getVal($arrImport, "addontype");
		$addonAlias = UELM_UniteFunctionsUC::getVal($arrImport, "alias");
		$catTitle = UELM_UniteFunctionsUC::getVal($arrImport, "cattitle");
		$addonTitle = UELM_UniteFunctionsUC::getVal($arrImport, "title");

		$isSpecialAddonType = UELM_HelperUC::isSpecialAddonType($addonType);

		//---- add log text ------

		$logTitle = $addonTitle;
		if(empty($logTitle))
			$logTitle = $addonName;

		$objAddonType = UELM_UniteCreatorAddonType::getAddonTypeObject($addonType);
		$txtSingle = $objAddonType->textSingle;
		if(empty($txtSingle))
			$txtSingle = "addon";

		$txtSingle = strtolower($txtSingle);

		$textLog = "$txtSingle $logTitle";

		//if empty addon type - set by the given type
		if(empty($addonType) && !empty($this->mustAddonType)){
			$addonType = $this->mustAddonType;

			if(empty($addonAlias))
				$addonAlias = $addonName;

			$addonName = $addonName . "_" . $addonType;
		}

		//check if the addon exists, if needed
		if($overwrite == false){
			$isExists = $this->objAddons->isAddonExistsByName($addonName);

			if($isExists == true){
				$textLog .= " skipped";
				$this->addLog($textLog);

				return (false);
			}
		}

		$arrImport = apply_filters("uelm_uc_modify_addon_data_before_import", $arrImport);

		//check if addon type match
		if(!empty($this->mustAddonType) && $this->mustAddonType != $addonType){
			$textLog .= " wrong addon type, skipped";
			$this->addLog($textLog);

			return (false);
		}

		//import other addon types to blox, like from vc
		if(empty($this->mustAddonType) && !empty($addonType) && $isSpecialAddonType == false){
			$addonType = "";
			$addonName = $addonAlias;
			$addonAlias = "";
		}

		$objCategories = new UELM_UniteCreatorCategories();
		if($forceToCat == false){  //first addon category, then given category

			if(!empty($catTitle)){
				$catID = $objCategories->getCreateCatByTitle($catTitle, $addonType);
			}
		}else{  //force to category - first given cat, then addon cat
			if(empty($catID))
				$catID = $objCategories->getCreateCatByTitle($catTitle, $addonType);
		}

		if(empty($catID))
			$catID = 0;

		//prepare data
		$addonData = array();
		$addonData["name"] = $addonName;
		$addonData["alias"] = $addonAlias;
		$addonData["addontype"] = $addonType;
		$addonData["title"] = UELM_UniteFunctionsUC::getVal($arrImport, "title");
		$addonData["description"] = UELM_UniteFunctionsUC::getVal($arrImport, "description");
		$addonData["catid"] = $catID;

		$config = UELM_UniteFunctionsUC::getVal($arrImport, "config");
		if(is_array($config) == false)
			UELM_UniteFunctionsUC::throwError("Wrong addon config data");

		$addonData["config"] = json_encode($config);
		$addonData["is_active"] = true;

		//---- import templates ----

		$addonData = $this->importAddonData_addTemplates($arrImport, $addonData);

		// ------ import test data ----------

		$addonData = $this->importAddonData_addDefaultData($addonData);

		$objAddon = new UELM_UniteCreatorAddon();
		$isNewAdded = $objAddon->importAddonData($addonData);

		$this->lastImportedAddonID = $objAddon->getID();
		$this->lastImportedCatID = $catID;

		if($isNewAdded){
			$textLog .= " added";

			//make the last imported addons list
			self::$arrImportedAddons[$addonName] = true;
		}else
			$textLog .= " overwrited";

		$this->addLog($textLog);

		//save added type
		$this->importedAddonType = $addonType;

		return (true);
	}

	/**
	 * copy import assets folder
	 */
	private function copyImportAssetsFolder(){

		if(is_dir($this->pathImportAddonAssets) == false)
			return (false);

		$pathAssets = UELM_GlobalsUC::$pathAssets;

		if(!empty($this->importedAddonType)){
			$objAddonType = UELM_UniteCreatorAddonType::getAddonTypeObject($this->importedAddonType);
			$pathAssets = UELM_HelperUC::getAssetsPath($objAddonType);
		}

		UELM_UniteFunctionsUC::copyDir($this->pathImportAddonAssets, $pathAssets);
	}

	/**
	 * check if extracted addon single
	 */
	private function isExtractedAddonSingle(){

		if($this->isPathSingle == true)
			return (true);

		$filenameAddon = "addon.json";
		$filepathData = $this->pathImportAddon . $filenameAddon;

		if(file_exists($filepathData))
			return (true);

		return (false);
	}

	/**
	 * set import single type
	 */
	public function setImportSingleAddon(){

		$this->isPathSingle = true;
	}

	/**
	 * set must import addon type, the addons will be imported to this type
	 */
	public function setMustImportAddonType($addonType){

		if($addonType == UELM_GlobalsUC::ADDON_TYPE_REGULAR_ADDON)
			$this->mustAddonType = "";
		else
			$this->mustAddonType = $addonType;
	}

	/**
	 * import addon
	 * tempFile can be array or filepath
	 */
	public function import($catID, $arrTempFile, $overwrite = true, $forceToCat = true){

		if($this->isImportedOnce == true)
			UELM_UniteFunctionsUC::throwError("The import script can't run twice");

		$this->isImportedOnce = true;

		if(empty($catID) || is_numeric($catID) == false)
			$catID = 0;

		//crate array from filepath
		if(getType($arrTempFile) == "string"){
			$filepath = $arrTempFile;
			$arrInfo = pathinfo($filepath);
			$filename = UELM_UniteFunctionsUC::getVal($arrInfo, "basename");

			$arrTempFile = array();
			$arrTempFile["tmp_name"] = $filepath;
			$arrTempFile["name"] = $filename;
		}

		$this->validateArrTempFile($arrTempFile);

		try{
			$this->prepareImportFolders();

			$this->extractImportAddonFile($arrTempFile);

			$objAssets = new UELM_UniteCreatorAssets();
			
			$objAssets->deleteFilesInExtracted($this->pathImportAddon);
			
			$objAssets->validateAllowedFilesInExtracted($this->pathImportAddon);
			
			$isSingle = $this->isExtractedAddonSingle();
						
			if($isSingle == true){
				$isImported = $this->importAddonData($catID, $overwrite, $forceToCat);
		
				if($isImported == true)
					$this->copyImportAssetsFolder();
			}else{
				$this->importAddonsFromFolder($this->pathImportAddon, $catID, $overwrite, true, $forceToCat);
			}

			$this->deleteImportAddonFolder();
		}catch(Exception $e){
			$this->deleteImportAddonFolder();

			throw $e;
		}

		$logText = $this->getLogText();
		
		if(empty($logText))
			$logText = __("No Widgets Imported","unlimited-elements");
		
		return ($logText);
	}

	/**
	 * get imported addon type
	 */
	public function getImportedAddonType(){

		return ($this->importedAddonType);
	}

	/**
	 * get last imported addon id
	 */
	public function getImportedAddonID(){

		return ($this->lastImportedAddonID);
	}

	/**
	 * get last imported cat id
	 */
	public function getImportedCatID(){

		return ($this->lastImportedCatID);
	}

	/**
	 * get all imported addon names in this session
	 */
	public function getArrImportedAddonNames(){

		return (self::$arrImportedAddons);
	}

	private function a________BULK_IMPORT_________(){
	}

	/**
	 * get array of category data from some path
	 */
	protected function getCatagoryDataFromPath($path){

		$filepath = $path . "category.json";

		if(file_exists($filepath) == false)
			return (null);

		$contents = UELM_UniteFunctionsUC::fileGetContents($filepath);
		$arrData = UELM_UniteFunctionsUC::jsonDecode($contents);

		if(empty($arrData))
			$arrData = null;

		return ($arrData);
	}

	/**
	 * get short log text (only from import addons from folder)
	 */
	public function getTextLogShort(){

		return ($this->textLogShort);
	}

	/**
	 * import all addons from folder
	 */
	public function importAddonsFromFolder($path, $catIDParam = 0, $overwrite = false, $forceSingle = false, $forceToCat = true){

		if(is_dir($path) == false)
			return (false);

		if($catIDParam == "all" || empty($catIDParam))
			$catIDParam = null;

		$arrFiles = UELM_UniteFunctionsUC::getFileListTree($path, "zip");

		//make files with paths list
		$arrFilesFull = array();
		$arrCats = array();

		//create category from path if exists
		foreach($arrFiles as $file){
			$pathRelative = str_replace($path, "", $file);

			$parentDir = dirname($pathRelative);
			if($parentDir == ".")
				$parentDir = "";

			$info = pathinfo($pathRelative);

			if(strlen($parentDir) < 3)
				$parentDir = "";

			$item = array();
			$item["catname"] = $parentDir;
			$item["filepath"] = $file;

			//get category data
			if(!empty($parentDir)){
				$pathCategory = $path . $parentDir . "/";

				$catData = $this->getCatagoryDataFromPath($pathCategory);

				$arrCats[$parentDir] = $catData;
			}

			$arrFilesFull[] = $item;
		}

		//create categories
		if(empty($catIDParam) || $forceToCat == false && !empty($arrCats)){
			$objCategories = new UELM_UniteCreatorCategories();

			$addonType = $this->mustAddonType;
			if(empty($addonType))
				$addonType = "";

			foreach($arrCats as $catname => $catData){
				$catAddonType = null;

				//check category addon type
				if(!empty($catData)){
					$catAddonType = UELM_UniteFunctionsUC::getVal($catData, "type");

					$isSpecialAddonType = UELM_HelperUC::isSpecialAddonType($catAddonType);

					//validate addon type
					if($isSpecialAddonType == false && $catAddonType != $addonType)
						UELM_UniteFunctionsUC::throwError("Wrong category addon type: " . $addonType);

					$catname = UELM_UniteFunctionsUC::getVal($catData, "title");
					UELM_UniteFunctionsUC::validateNotEmpty($catname, "category title from category.json");
				}

				if(empty($catAddonType))
					$catAddonType = $addonType;

				$catID = $objCategories->getCreateCatByTitle($catname, $catAddonType, $catData);

				//modify cat name for special addon types
				if($isSpecialAddonType)
					$catname = $catAddonType . "_" . $catname;

				$arrCats[$catname] = $catID;
			}
		}

		try{
			//update category type on the way
			$isAddonTypeUpdated = false;
			$addonType = "";

			foreach($arrFilesFull as $item){
				$catName = $item["catname"];
				$filepath = $item["filepath"];

				if(file_exists($filepath) == false)
					continue;

				$catID = UELM_UniteFunctionsUC::getVal($arrCats, $catName);
				if(empty($catID) || $forceToCat == true && !empty($catIDParam))
					$catID = $catIDParam;

				if(empty($catID))
					$catID = 0;

				$objImporter = new UELM_UniteCreatorExporter();
				$objImporter->setMustImportAddonType($this->mustAddonType);

				if($forceSingle == true)
					$objImporter->setImportSingleAddon();

				$logText = $objImporter->import($catID, $filepath, $overwrite, true);

				//update category type if not updated
				if($isAddonTypeUpdated == false){
					$addonType = $objImporter->getImportedAddonType();

					if(!empty($addonType) && !empty($catID)){
						$objCats = new UELM_UniteCreatorCategories();
						$objCats->updateType($catID, $addonType);

						$isAddonTypeUpdated = true;
					}
				}

				$this->addLog($logText);
			}

			$logText = $this->getLogText();

			//create categories short text
			$textCats = "";
			if(!empty($arrCats)){
				$arrCatNames = array_keys($arrCats);
				$this->textLogShort = "Installed " . implode(",", $arrCatNames);
				$numCats = count($arrCatNames);

				if($numCats == 1)
					$this->textLogShort .= " bundle";
				else
					$this->textLogShort .= " bundles";
			}else{
				$numAddons = count($arrFilesFull);
				$this->textLogShort = "Installed " . $numAddons . " addons";
			}

			return ($logText);
		}catch(Exception $e){
			$message = $e->getMessage();

			$this->addLog("error: $message");

			throw $e;
		}
	}

}
