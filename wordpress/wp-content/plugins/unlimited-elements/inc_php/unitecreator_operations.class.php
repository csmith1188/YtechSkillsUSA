<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UCOperations extends UELM_UniteElementsBaseUC{

	private static $arrGeneralSettings = null;
	private static $arrLayoutsGeneralSettings = null;
	private static $arrCustomSettingsCache = array();
	private static $arrUrlThumbCache = array();
	private static $lastRequestResponse = null;
	
	const GENERAL_SETTINGS_OPTION = "unitecreator_general_settings";

	private function a_______GENERAL_SETTING________(){
	}


	/**
	 * get general settings values based on custom object
	 */
	public function getGeneralSettingsCustomObject($filepathSettings){

		$arrValues = UELM_UniteProviderFunctionsUC::getOption(self::GENERAL_SETTINGS_OPTION);

		$objSettings = new UELM_UniteCreatorSettings();
		$objSettings->loadXMLFile($filepathSettings);

		if(!empty($arrValues))
			$objSettings->setStoredValues($arrValues);

		return ($arrValues);
	}

	/**
	 * get general settings key
	 */
	private function getGeneralSettingsKey($key){

		if($key == self::GENERAL_SETTINGS_OPTION)
			return ($key);

		if($key == "general_settings")
			return (self::GENERAL_SETTINGS_OPTION);

		$key = "uelm_unite_creator_" . $key;

		return ($key);
	}


	/**
	 * update general settings
	 */
	public function updateGeneralSettingsFromData($data, $isValidate = true, $settingsKey = self::GENERAL_SETTINGS_OPTION){

		$arrValues = UELM_UniteFunctionsUC::getVal($data, "settings_values");

		//validations:
		if($isValidate == true)
			UELM_UniteProviderFunctionsUC::doAction(UELM_UniteCreatorFilters::ACTION_VALIDATE_GENERAL_SETTINGS, $arrValues, $settingsKey);

		$arrCurrentSettings = UELM_UniteProviderFunctionsUC::getOption($settingsKey);
		if(empty($arrCurrentSettings))
			$arrCurrentSettings = array();

		if(empty($arrValues))
			$arrValues = array();

		$arrValues = array_merge($arrCurrentSettings, $arrValues);

		//clear cache
		if(isset(self::$arrCustomSettingsCache[$settingsKey]))
			unset(self::$arrCustomSettingsCache[$settingsKey]);

		UELM_UniteProviderFunctionsUC::updateOption($settingsKey, $arrValues);
	}

	/**
	 * validate that there are no keys in custom settings from general
	 */
	private function validateNoGeneralSettingsKeysInCustomSettings($arrValues){

		if(is_array($arrValues) == false)
			return (false);

		$arrSettings = $this->getGeneralSettings();
		if(is_array($arrSettings) == false)
			return (false);

		$arrIntersect = array_intersect_key($arrSettings, $arrValues);
		if(empty($arrIntersect))
			return (false);

		//----- invalid:

		$strIntersect = print_r($arrIntersect, true);
		UELM_UniteFunctionsUC::throwError("The custom settings should not contain general settings keys:" . $strIntersect);
	}

	/**
	 * update unlimited settings
	 */
	public function updateUnlimitedElementsGeneralSettings($arrValues){

		$key = "uelm_general_settings";
		$customSettingsKey = self::getGeneralSettingsKey($key);

		$data = array();
		$data["settings_values"] = $arrValues;
		$this->updateGeneralSettingsFromData($data, false, $customSettingsKey);
	}

	/**
	 * update custom settings from data
	 */
	public function updateCustomSettingsFromData($data){

		$arrValues = UELM_UniteFunctionsUC::getVal($data, "settings_values");
		$key = UELM_UniteFunctionsUC::getVal($data, "settings_key");

		//update general settings
		if($key == "general_settings"){
			$this->validateNoGeneralSettingsKeysInCustomSettings($arrValues);
			$this->updateGeneralSettingsFromData($data, false);
		}else{
			$customSettingsKey = self::getGeneralSettingsKey($key);

			$this->updateGeneralSettingsFromData($data, false, $customSettingsKey);
		}
	}


	private function a_________CUSTOM_SETTINGS___________(){
	}

	/**
	 * get raw values from general settings
	 */
	public function getCustomSettingsValues($customSettingsKey){

		$settingsKey = self::getGeneralSettingsKey($customSettingsKey);

		$arrValues = UELM_UniteProviderFunctionsUC::getOption($settingsKey);

		return ($arrValues);
	}

	/**
	 * get raw values from general settings
	 */
	public function getCustomSettingsObject($filepathSettings, $settingsKey){

		$objSettings = new UELM_UniteCreatorSettings();
		$objSettings->loadXMLFile($filepathSettings);

		$arrValues = $this->getCustomSettingsValues($settingsKey);

		if(!empty($arrValues))
			$objSettings->setStoredValues($arrValues);

		return ($objSettings);
	}

	/**
	 * get raw values from general settings
	 */
	public function getCustomSettingsObjectValues($filepathSettings, $settingsKey){
				
		if(isset(self::$arrCustomSettingsCache[$settingsKey]))
			return (self::$arrCustomSettingsCache[$settingsKey]);

		$objSettings = $this->getCustomSettingsObject($filepathSettings, $settingsKey);

		$arrValues = $objSettings->getArrValues();

		self::$arrCustomSettingsCache[$settingsKey] = $arrValues;

		return ($arrValues);
	}

	private function a__________OTHER_FUNCTIONS___________(){
	}

	/**
	 * check instagram update
	 */
	public function checkInstagramRenewToken(){

		try{
			//try to upgrade instagram if exists
			$objServices = new UELM_UniteServicesUC();
			$objServices->includeInstagramAPI();

			$isRenewed = UELM_HelperInstaUC::checkRenewAccessToken_onceInAWhile();
		}catch(Exception $e){
		}
	}

	/**
	 * get error message html
	 */
	public function getErrorMessageHtml($message, $trace = ""){

		$html = '<div class="unite-error-message">';
		$html .= '<div class="unite-error-message-inner">';
		$html .= $message;
		$html .= '</div>';

		if(!empty($trace)){
			$html .= '<div class="unite-error-trace">';
			$html .= "<pre>{$trace}</pre>";
			$html .= "</div>";
		}

		$html .= '</div>';

		return ($html);
	}

	/**
	 * put error mesage from the module
	 */
	public function putModuleErrorMessage($message, $trace = ""){
		uelm_echo(self::getErrorMessageHtml($message, $trace));
	}

	/**
	 * get thumb width from thumb size
	 */
	protected function getThumbWidthFromSize($sizeName){

		switch($sizeName){
			case UELM_GlobalsUC::THUMB_SIZE_NORMAL:
				$size = UELM_GlobalsUC::THUMB_WIDTH;
			break;
			case UELM_GlobalsUC::THUMB_SIZE_LARGE:
				$size = UELM_GlobalsUC::THUMB_WIDTH_LARGE;
			break;
			default:
				$size = UELM_GlobalsUC::THUMB_WIDTH;
			break;
		}

		return ($size);
	}

	/**
	 * create thumbs from image by url
	 * the image must be relative path to the platform base
	 */
	public function createThumbs($urlImage, $thumbSize = null){
		if(empty($urlImage))
			UELM_UniteFunctionsUC::throwError("empty image url");

		$thumbWidth = $this->getThumbWidthFromSize($thumbSize);

		$urlImage = UELM_HelperUC::URLtoRelative($urlImage);

		$info = UELM_HelperUC::getImageDetails($urlImage);

		//check thumbs path
		$pathThumbs = $info["path_thumbs"];

		if(!is_dir($pathThumbs))
			UELM_UniteFunctionsUC::mkdir($pathThumbs);

		if(!is_dir($pathThumbs))
			UELM_UniteFunctionsUC::throwError("Can't make thumb folder: {$pathThumbs}. Please check php and folder permissions");

		$filepathImage = $info["filepath"];
		
		try{
			$filenameThumb = $this->imageView->makeThumb($filepathImage, $pathThumbs, $thumbWidth);
		
		}catch(Exception $e){
			$filenameThumb = null;
		}
		
		$urlThumb = "";
		if(!empty($filenameThumb)){
			$urlThumbs = $info["url_dir_thumbs"];
			$urlThumb = $urlThumbs . $filenameThumb;
		}else
			$urlThumb = UELM_UniteFunctionsUC::getVal($info, "url_full");
		

		return ($urlThumb);
	}

	/**
	 * get title param array
	 */
	private function getParamTitle(){

		$arr = array();

		$arr["type"] = "uc_textfield";
		$arr["title"] = __("Title","unlimited-elements");
		$arr["name"] = "title";
		$arr["description"] = "";
		$arr["default_value"] = "";
		$arr["limited_edit"] = true;
		
		return ($arr);
	}

	/**
	 * check that params always have item param on top
	 */
	public function checkAddParamTitle($params){

		if(empty($params)){
			$paramTitle = $this->getParamTitle();
			$params[] = $paramTitle;

			return ($params);
		}

		//search for param title
		foreach($params as $param){
			$name = UELM_UniteFunctionsUC::getVal($param, "name");
			if($name == "title")
				return ($params);
		}

		//if no title param - add it to top
		$paramTitle = $this->getParamTitle();
		array_unshift($params, $paramTitle);

		return ($params);
	}

	/**
	 * get addon changelog from data
	 */
	public function getAddonChangelogFromData($data){

		require_once UELM_GlobalsUC::$pathViewsObjects . "addon_view.class.php";
		$objAddonView = new UELM_UniteCreatorAddonView();

		$response = $objAddonView->getChangelogContents($data);

		return ($response);
	}

	/**
	 * get addon revisions from data
	 */
	public function getAddonRevisionsFromData($data){

		require_once UELM_GlobalsUC::$pathViewsObjects . "addon_view.class.php";
		$objAddonView = new UELM_UniteCreatorAddonView();

		$response = $objAddonView->getRevisionsContents($data);

		return ($response);
	}

	/**
	 * get bulk addon dialog from data
	 */
	public function getAddonBulkDialogFromData($data){

		require_once UELM_GlobalsUC::$pathViewsObjects . "addon_view.class.php";
		$objAddonView = new UELM_UniteCreatorAddonView();

		$response = $objAddonView->getBulkDialogContents($data);

		return ($response);
	}

	/**
	 * get the cats
	 */
	private function getPostsFromTwig_getCats($strCats){

		if(empty($strCats))
			return (false);

		//get taxonomy

		$taxnonomy = "post";

		$arrData = explode("|", $strCats);

		if(count($arrData) == 2){
			$taxnonomy = $arrData[0];

			$strCats = $arrData[1];
		}

		$arrCats = explode(",", $strCats);

		$taxQuery = array();

		foreach($arrCats as $cat){
			$item = array();
			$item["taxonomy"] = $taxnonomy;
			$item["field"] = "name";
			$item["terms"] = $arrCats;

			$taxQuery[] = $item;
			$taxQuery["relation"] = "or";
		}

		return ($taxQuery);
	}

	/**
	 * get posts from twig
	 * simple function
	 */
	public function getPostsFromTwig($postType, $strCats, $strArgs){

		if(empty($postType))
			$postType = "post";

		$arrCats = $this->getPostsFromTwig_getCats($strCats);

		uelm_dmp("get posts");

		uelm_dmp($arrCats);
	}

	private function a____________DEBUG____________(){}
		
	/**
	 * modify field for debug
	 */
	public function modifyDebugField($field){

		if(is_string($field) == false)
			return ($field);

		$maxChars = 200;

		$field = trim($field);

		$numchars = strlen($field);

		$field = htmlspecialchars($field);

		if(strlen($field) < $maxChars)
			return ($field);

		//remove spaces
		$field = str_replace(" ", "", $field);
		$field = str_replace("\n", "", $field);

		if(strlen($field) > $maxChars)
			$field = substr($field, 0, $maxChars) . "... ($numchars chars)";

		return ($field);
	}

	/**
	 * put debug of post custom fields
	 */
	public function putPostCustomFieldsDebug($postID, $showCustomFields = false){

		if($postID == "current"){
			$post = get_post();
			$postID = $post->ID;
		}else
			$post = get_post($postID);

		if(empty($post))
			return (false);

		$postTitle = $post->post_title;

		if($showCustomFields == false)
			$arrCustomFields = UELM_UniteFunctionsWPUC::getPostMeta($postID);
		else{
			$arrCustomFields = UELM_UniteFunctionsWPUC::getPostCustomFields($postID, false);

			if(empty($arrCustomFields))
				$arrCustomFields = UELM_UniteFunctionsWPUC::getPostMeta($postID);
		}

		if(empty($arrCustomFields))
			$arrCustomFields = array();

		foreach($arrCustomFields as $key => $field){
			$arrCustomFields[$key] = $this->modifyDebugField($field);
		}

		$htmlFields = UELM_HelperHtmlUC::getHtmlArrayTable($arrCustomFields, "No Meta Fields Found");

		$fieldsTitle = "Meta";
		if($showCustomFields == true)
			$fieldsTitle = "Custom";
		
		uelm_echo("<br>{$fieldsTitle} fields for post: <b>$postTitle </b>, post id: $postID <br>");

		uelm_dmp($htmlFields);
	}

	/**
	 * put term custom fields - for debug
	 */
	public function putTermCustomFieldsDebug($term){

		if(empty($term))
			UELM_UniteFunctionsUC::throwError("print term debug: the termid option is empty");

		if(is_array($term)){
			$termID = UELM_UniteFunctionsUC::getVal($term, "id");
			$name = UELM_UniteFunctionsUC::getVal($term, "name");
		}else{
			$termID = $term->term_id;
			$name = $term->name;
		}

		$arrCustomFields = UELM_UniteFunctionsWPUC::getTermCustomFields($termID, false);

		foreach($arrCustomFields as $key => $field){
			$arrCustomFields[$key] = $this->modifyDebugField($field);
		}

		$htmlFields = UELM_HelperHtmlUC::getHtmlArrayTable($arrCustomFields, "No Meta Fields Found");

		$fieldsTitle = "Meta";

		uelm_echo("<br>{$fieldsTitle} fields for term: <b>$name </b>, term id: $termID <br>");

		uelm_dmp($htmlFields);
	}

	/**
	 * terms custom fields debug
	 */
	public function putTermsCustomFieldsDebug($arrTerms,$showCustomFields = false){

		if(empty($arrTerms))
			return (false);

		uelm_dmp("Show the terms meta fields. Please turn off this option before release.");

		foreach($arrTerms as $term){
			if(is_array($term))
				$termID = UELM_UniteFunctionsUC::getVal($term, "id");
			else
				$termID = $term->term_id;

			$this->putTermCustomFieldsDebug($term,$showCustomFields);
		}
	}

	/**
	 * put posts meta fields debug
	 */
	public function putPostsCustomFieldsDebug($arrPosts, $showCustomFields = false){
		
		if(empty($arrPosts))
			return (false);
		
		uelm_dmp("Show the posts meta fields. Please turn off this option before release.");

		foreach($arrPosts as $post){
			$postID = $post->ID;

			$this->putPostCustomFieldsDebug($postID, $showCustomFields);
		}
	}
	
	/**
	 * put posts meta fields debug
	 */
	public function putMenuCustomFieldsDebug($arrItems,$showCustomFields = false){

		if(empty($arrItems))
			return (false);

		uelm_dmp("Show the menu meta fields. Please turn off this option before release.");

		foreach($arrItems as $item){

			$menuItemID = UELM_UniteFunctionsUC::getVal($item, "id");

			$this->putPostCustomFieldsDebug($menuItemID, $showCustomFields);
		}
	}


	/**
	 * put custom fields array to debug
	 */
	public function putCustomFieldsArrayDebug($arrCustomFields, $title = null){

		if(!empty($title))
			uelm_dmp("$title custom fields debug. turn off before release");

		foreach($arrCustomFields as $key => $field){
			$arrCustomFields[$key] = $this->modifyDebugField($field);
		}

		$htmlFields = UELM_HelperHtmlUC::getHtmlArrayTable($arrCustomFields, "No Meta Fields Found");

		uelm_dmp($htmlFields);
	}
	
	/**
	 * put post terms debug
	 */
	public function putPostTermsDebug($postID){
		$strTerms = UELM_UniteFunctionsWPUC::getPostTermsTitlesString($postID, true);
		?>
		<div>Post Terms: <span style='color:green;'>"<?php echo esc_html($strTerms);?>"</span></div>
		<?php
	}
	
	/**
	 * put post object debug
	 */
	public function putPostObjectDebug($post){
		
		$post = (array)$post;
		
		$arrPostForShow = UELM_UniteFunctionsUC::modifyDataArrayForShow($post);
		
		uelm_dmp($arrPostForShow);
	}
	
	/**
	 * put posts full debug - show all info about each post
	 */
	public function putPostsFullDebug($arrPosts, $includePostObject = false){
		
		foreach($arrPosts as $post){
			$postID = $post->ID;
			
			$postTitle = $post->post_title;
			
			echo "<br><hr>";
			
			uelm_dmp("Post: <b>$postTitle</b>");
			
			if($includePostObject == true)
				$this->putPostObjectDebug($post, $includePostObject);
			
			$this->putPostCustomFieldsDebug($postID);
			$this->putPostTermsDebug($postID);
			
		}
		
		
	}
	
	private function a____________URL_CONTENTS____________(){
	}

	/**
	 * get local file contents
	 */
	public function getLocalFileContentsByUrl($url){

		$urlRelative = UELM_HelperUC::URLtoRelative($url);

		$isFile = $urlRelative != $url;

		if($isFile == false)
			return (null);

		$pathFile = UELM_HelperUC::urlToPath($url);

		if(empty($pathFile))
			return (null);

		$content = UELM_UniteFunctionsUC::fileGetContents($pathFile);

		return ($content);
	}

	/**
	 * get url contents from file or url with cache
	 */
	public function getUrlContents($url, $debug = false, $operateError = false){
		
		if($debug === true)
			uelm_dmp("get contents from url: $url");

		$urlRelative = UELM_HelperUC::URLtoRelative($url);

		$isFile = $urlRelative != $url;

		if($isFile === true){
			$pathFile = UELM_HelperUC::urlToPath($url);

			if(empty($pathFile)){
				if($debug === true){
					$pathFile = UELM_GlobalsUC::$path_base . $urlRelative;

					uelm_dmp("file not exists:  $pathFile");
					exit;
				}

				return null;
			}

			if($debug === true)
				uelm_dmp("file detected: $pathFile");

			$content = UELM_UniteFunctionsUC::fileGetContents($pathFile);

			return $content;
		}

		try{
			$request = UELM_Http::make();
			$request->debug($debug);
			$request->cacheTime(180); // 3 minutes
			
			$response = $request->get($url);
			
			//save last request
			self::$lastRequestResponse = $response;
			
			if($operateError){
				
				$responseError = $response->getError();
				
				if(!empty($responseError))
					UELM_UniteFunctionsUC::throwError($responseError);
				
			}
			
			$data = $response->body();
			
			return $data;
		}catch(Exception $e){
			
			if($operateError == true)
				throw($e);
		}

		return null;
	}
	
	/**
	 * get last request response
	 */
	public function getLastRequestResponse(){
		
		return self::$lastRequestResponse;
	}
	
	
	private function a____________DATE____________(){}

	/**
	 * get nice display of date ranges, ex. 4-5 MAR 2021
	 */
	public function getDateRangeString($startTimeStamp, $endTimeStamp, $pattern = null){

		$displayDate = "";

		$startDate = getDate($startTimeStamp);
		$endDate = getDate($endTimeStamp);

		//--- check same date

		if($startDate["year"] . $startDate["mon"] . $startDate["mday"] == $endDate["year"] . $endDate["mon"] . $endDate["mday"]){
			$displayDate = uelm_date('j M Y', $endTimeStamp);

			return ($displayDate);
		}

		//--- check different years

		if($startDate["year"] != $endDate["year"]){
			$displayDate = uelm_date('j M Y', $startTimeStamp) . " - " . uelm_date('j M Y', $endTimeStamp);

			return ($displayDate);
		}

		//--- check same year

		// diff days in the same month
		if($startDate["mon"] == $endDate["mon"])
			$displayDate = uelm_date('j', $startTimeStamp) . "-" . uelm_date('j M Y', $endTimeStamp);

		// diff months
		$displayDate = uelm_date('j M', $startTimeStamp) . " - " . uelm_date('j M Y', $endTimeStamp);

		return $displayDate;
	}

	private function a____________SCREENSHOTS____________(){
	}

	/**
	 * get filepath of layout screenshot. existing or new
	 */
	private function saveScreenshot_layout_getFilepath($objLayout, $ext){

		//check existing path
		$pathImage = $objLayout->getPreviewImageFilepath();

		if($pathImage){
			$isGenerated = UELM_UniteFunctionsUC::isPathUnderBase($pathImage, UELM_GlobalsUC::$path_images_screenshots);

			$info = pathinfo($pathImage);
			$extExisting = UELM_UniteFunctionsUC::getVal($info, "extension");
			if($extExisting == $ext && $isGenerated == true)
				return ($pathImage);
		}

		$title = $objLayout->getTitle();
		$type = $objLayout->getLayoutType();

		$filename = "layout_" . UELM_HelperUC::convertTitleToHandle($title);

		if(!empty($type))
			$filename .= "_" . $type;

		$addition = "";
		$counter = 1;
		do{
			$filepath = UELM_GlobalsUC::$path_images_screenshots . $filename . $addition . "." . $ext;
			$isExists = file_exists($filepath);

			$counter++;
			$addition = "_" . $counter;
		}while($isExists == true);

		return ($filepath);
	}

	/**
	 * save layout screenshot
	 */
	private function saveScreenshot_layout($layoutID, $screenshotData, $ext){

		$objLayout = new UELM_UniteCreatorLayout();
		$objLayout->initByID($layoutID);

		UELM_UniteFunctionsUC::checkCreateDir(UELM_GlobalsUC::$path_images_screenshots);

		//create filename
		$filepath = $this->saveScreenshot_layout_getFilepath($objLayout, $ext);

		//delete previous
		$pathExistingImage = $objLayout->getPreviewImageFilepath();
		$isGenerated = UELM_UniteFunctionsUC::isPathUnderBase($pathExistingImage, UELM_GlobalsUC::$path_images_screenshots);
		if($isGenerated == true && $pathExistingImage != $filepath && file_exists($pathExistingImage))
			@unlink($pathExistingImage);

		//write current
		UELM_UniteFunctionsUC::writeFile($screenshotData, $filepath);

		if(file_exists($filepath) == false){
			UELM_UniteFunctionsUC::throwError("The screenshot could not be created");
		}

		$urlScreenshot = UELM_HelperUC::pathToRelativeUrl($filepath);

		$objLayout->updateParam("preview_image", $urlScreenshot);
		$objLayout->updateParam("page_image", "");

		$urlScreenshotFull = UELM_HelperUC::URLtoFull($urlScreenshot);

		return ($urlScreenshotFull);
	}

	/**
	 * save screenshot
	 * Enter description here ...
	 */
	public function saveScreenshotFromData($data){

		try{
			$source = UELM_UniteFunctionsUC::getVal($data, "source");
			$layoutID = UELM_UniteFunctionsUC::getVal($data, "layoutid");
			$screenshotData = UELM_UniteFunctionsUC::getVal($data, "screenshot_data");
			$ext = UELM_UniteFunctionsUC::getVal($data, "ext");

			UELM_UniteFunctionsUC::validateNotEmpty($layoutID, "layoutID");

			switch($ext){
				case "jpg":
					$screenshotData = $this->imageView->convertJPGDataToJPG($screenshotData);
				break;
				case "png":
					$screenshotData = $this->imageView->convertPngDataToPng($screenshotData);
				break;
				default:
					UELM_UniteFunctionsUC::throwError("wrong extension");
				break;
			}

			switch($source){
				case "layout":
					$urlScreenshot = $this->saveScreenshot_layout($layoutID, $screenshotData, $ext);
				break;
				case "addon":
					uelm_dmp("save addon screenshot");
				break;
				default:
					UELM_UniteFunctionsUC::throwError("wrong save source");
				break;
			}
		}catch(Exception $e){
			$errorMessage = $e->getMessage();
			$output = array();
			$output["error_message"] = $errorMessage;

			return ($errorMessage);
		}

		$output = array();
		$output["url_screenshot"] = $urlScreenshot;
		$output["layoutid"] = $layoutID;

		return ($output);
	}

	/**
	 * get post list for select
	 */
	public function getPostListForSelectFromData($data, $addNotSelected = false){

		uelm_dmp("getPostListForSelect: function for overide");
		exit();
	}

	/**
	 * get post additions
	 */
	private function getPostAttributesFromData_getPostAdditions($data){

		$arrAdditions = array();

		$enableCustomFields = UELM_UniteFunctionsUC::getVal($data, "enable_custom_fields");
		$enableCustomFields = UELM_UniteFunctionsUC::strToBool($enableCustomFields);

		$enableCategory = UELM_UniteFunctionsUC::getVal($data, "enable_category");
		$enableCategory = UELM_UniteFunctionsUC::strToBool($enableCategory);

		$enableWoo = UELM_UniteFunctionsUC::getVal($data, "enable_woo");
		$enableWoo = UELM_UniteFunctionsUC::strToBool($enableWoo);

		if($enableCustomFields == true)
			$arrAdditions[] = UELM_GlobalsProviderUC::POST_ADDITION_CUSTOMFIELDS;

		if($enableCategory == true)
			$arrAdditions[] = UELM_GlobalsProviderUC::POST_ADDITION_CATEGORY;

		if($enableWoo == true)
			$arrAdditions[] = UELM_GlobalsProviderUC::POST_ADDITION_WOO;

		return ($arrAdditions);
	}

	/**
	 * get post list for select
	 */
	public function getPostAttributesFromData($data){

		$postID = UELM_UniteFunctionsUC::getVal($data, "postid");

		//UELM_UniteFunctionsUC::validateNotEmpty($postID, "post id");

		require_once UELM_GlobalsUC::$pathViewsObjects . "addon_view.class.php";
		require_once UELM_GlobalsUC::$pathProvider . "views/addon.php";

		$objAddonView = new UELM_UniteCreatorAddonViewProvider();

		$arrPostAdditions = $this->getPostAttributesFromData_getPostAdditions($data);

		$arrParams = $objAddonView->getChildParams_post($postID, $arrPostAdditions);

		$response = array();
		$response["child_params_post"] = $arrParams;

		return ($response);
	}

	/**
	 *
	 * modify the text from widget
	 */
	public function modifyTextFromWidget($text){

		//convert current page
		
		if(strpos($text, "%current_page_url%") !== false){
			$urlPage = UELM_UniteFunctionsWPUC::getUrlCurrentPage(true);

			$text = str_replace("%current_page_url%", $urlPage, $text);
		}

		if(strpos($text, "%current_page_title%") !== false){
			$post = get_post();
			if($post){
				$title = $post->post_title;
				$text = str_replace("%current_page_title%", $title, $text);
			}
		}

		return ($text);
	}

	/**
	 * get last query post ids
	 */
	public function getLastQueryPostIDs(){

		$query = UELM_GlobalsProviderUC::$lastPostQuery;

		if(empty($query)){
			return (null);
		}

		$posts = $query->posts;

		if(empty($posts))
			return (null);

		$arrPostIDs = array();

		foreach($posts as $post){
			$postID = $post->ID;

			$arrPostIDs[] = $postID;
		}

		if(empty($arrPostIDs))
			return (null);

		$strPostIDs = implode(",", $arrPostIDs);

		return ($arrPostIDs);
	}

	/**
	 * get last query data
	 */
	public function getLastQueryData(){

		$query = UELM_GlobalsProviderUC::$lastPostQuery;

		if(empty($query)){
			return (null);
		}

		$objPagination = new UELM_UniteCreatorElementorPagination();
		$data = $objPagination->getPagingData();

		$totalPages = UELM_UniteFunctionsUC::getVal($data, "total");

		if($totalPages == 0)
			$totalPages = 1;

		$numPosts = 0;
		if(isset($query->posts))
			$numPosts = count($query->posts);

		$totalPosts = 0;
		if(isset($query->found_posts))
			$totalPosts = $query->found_posts;

		$arrQuery = $query->query;

		$postType = UELM_UniteFunctionsUC::getVal($arrQuery, "post_type");

		$orderBy = UELM_UniteFunctionsUC::getVal($arrQuery, "orderby");
		$orderDir = UELM_UniteFunctionsUC::getVal($arrQuery, "order");

		if(is_array($orderBy)){
			$orderDir = UELM_UniteFunctionsUC::getArrFirstValue($orderBy);
			$orderBy = UELM_UniteFunctionsUC::getFirstNotEmptyKey($orderBy);
		}

		$orderBy = strtolower($orderBy);
		$orderDir = strtolower($orderDir);

		if($orderBy == "id")
			$orderBy = "ID";
		
		//fix by actual num posts
		if(UELM_GlobalsProviderUC::$lastNumPosts > $numPosts){
			$totalPosts += (UELM_GlobalsProviderUC::$lastNumPosts - $numPosts);
			$numPosts = UELM_GlobalsProviderUC::$lastNumPosts;
		}
			
		$output = array();
		$output["count_posts"] = $numPosts;
		$output["total_posts"] = $totalPosts;
		$output["page"] = UELM_UniteFunctionsUC::getVal($data, "current");
		$output["num_pages"] = $totalPages;

		if(!empty($orderBy)){

			if($orderBy == "meta_value"){

				$metaKey = UELM_UniteFunctionsUC::getVal($arrQuery, "meta_key");

				if(!empty($metaKey))
					$orderBy = "meta__{$metaKey}__text";
			}

			if($orderBy == "meta_value_num"){

				$metaKey = UELM_UniteFunctionsUC::getVal($arrQuery, "meta_key");

				if(!empty($metaKey))
					$orderBy = "meta__{$metaKey}__number";
			}


			$output["orderby"] = $orderBy;
		}


		if(!empty($orderDir))
			$output["orderdir"] = $orderDir;

		if($postType == "product")
			$output["woo"] = true;

		return ($output);
	}

}
