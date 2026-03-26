<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorWebAPI extends UELM_UniteCreatorWebAPIWork{

	
	/**
	 * construct
	 */
	public function __construct(){
		
		if(defined("UNLIMITED_ELEMENTS_UPRESS_URL_API"))
			self::$urlAPI = UNLIMITED_ELEMENTS_UPRESS_URL_API;
		
		//self::$urlAPI = UELM_GlobalsUC::URL_API;
							
		parent::__construct(); 
	}
	
	
	/**
	 * is active by freemius
	 */
	private function isFreemiusActive(){
        
        $isActivated = UELM_HelperProviderUC::isActivatedByFreemius();
        
        return($isActivated);
	}
	
		
	
	/**
	 * is product active
	 */
	public function isProductActive($product = null){
		
		if(UELM_GlobalsUC::$isProVersion == false)
			return(false);
		
		$isActive = $this->isFreemiusActive();
		
		if($isActive == true)
			return(true);
		
		$isActive = parent::isProductActive($product);
		
		return $isActive;
				
	}
	
	/**
	 * filter catalog addons for another platforms items
	 */
	protected function filterCatalogAddons($arrCatalogAddons){
		
		if(empty($arrCatalogAddons))
			return($arrCatalogAddons);
		
		$arrCatalogAddonsNew = array();
		foreach($arrCatalogAddons as $catName => $arrAddons){
			
			$arrAddonsNew = array();
			
			if(UELM_UniteCreatorWebAPI::IS_CATALOG_UNLIMITED == false)
				$catName = str_replace("Article", "Post", $catName);
			
			foreach($arrAddons as $addon){
				
				$title = UELM_UniteFunctionsUC::getVal($addon, "title");
				$name = UELM_UniteFunctionsUC::getVal($addon, "name");
				
				$titleLow = strtolower($title);
				
				if(strpos($titleLow, "joomla") !== false)
					continue;
				
				if(strpos($name, "joomla") !== false)
					continue;
				
				if(strpos($name, "k2_basic") !== false)
					continue;
				
				if($name == "article")
					continue;
				
				//rename
				if(UELM_UniteCreatorWebAPI::IS_CATALOG_UNLIMITED == false)
					$title = str_replace("Article", "Post", $title);
				
				$addon["title"] = $title;
				
				$arrAddonsNew[] = $addon;
			}
			
			$arrCatalogAddonsNew[$catName] = $arrAddonsNew;
		}
		
		
		return($arrCatalogAddonsNew);
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param unknown_type $arrCatalogItems
	 */
	private function filterCategories($arrCatalogItems){
		
		if(UELM_GlobalsUnlimitedElements::$isGutenbergOnly == false)
			return($arrCatalogItems);
		
		if(empty(UELM_GlobalsUnlimitedElements::$gutenbergArrFilterCats))
			return($arrCatalogItems);
			
		foreach(UELM_GlobalsUnlimitedElements::$gutenbergArrFilterCats as $cat){
			if(isset($arrCatalogItems[$cat]))
				unset($arrCatalogItems[$cat]);
		}
		
		
		return($arrCatalogItems);
	}
	
	
	/**
	 * get catalog array by addons type
	 */
	public function getCatalogArray($objAddonsType){
		
		$arrCatalogItems = parent::getCatalogArray($objAddonsType);
		
		if($objAddonsType->isLayout == true)
			return($arrCatalogItems);
		
		$arrCatalogItems = $this->filterCatalogAddons($arrCatalogItems);
		
		$arrCatalogItems = $this->filterCategories($arrCatalogItems);
		
		return($arrCatalogItems);
	}
	
	
	/**
	 * get catalog array
	 */
	protected function getCatalogArray_addons($isBG = false){
		
		$arrCatalogAddons = parent::getCatalogArray_addons($isBG);
		
		$arrCatalogAddons = $this->filterCatalogAddons($arrCatalogAddons);
		
		return($arrCatalogAddons);		
	}
	
	
	/**
	 * modify data before request
	 */
	protected function modifyDataBeforeRequest($data){
		
		$data["platform"] = "wp";
		
		//get the right category name
		
		if(self::IS_CATALOG_UNLIMITED == false){
			$cat = UELM_UniteFunctionsUC::getVal($data, "cat");
			if(!empty($cat))
				$data["cat"] = str_replace("Post", "Article", $cat);			
		}
		
		return($data);
	}
	
	
	/**
	 * install from data
	 * redirect to wp back
	 */
	public function installCatalogPageFromData($data){
		
		$arrResponse = parent::installCatalogPageFromData($data);
		
		$pageID = $arrResponse["layoutid"];
		$params = UELM_UniteFunctionsUC::getVal($data, "params");
		
		$redirectToWP = UELM_UniteFunctionsUC::getVal($params, "redirect_to_wp_page");
		$redirectToWP = UELM_UniteFunctionsUC::strToBool($redirectToWP);
				
		if($redirectToWP == false)
			return($arrResponse);

		UELM_UniteFunctionsUC::validateNotEmpty($pageID, "page id");
		
		$urlRedirect = UELM_UniteFunctionsWPUC::getUrlEditPost($pageID);
		
		$arrResponse["url_redirect"] = $urlRedirect;
		
		return($arrResponse);
	}
	
	/**
	 * install catalog template to elementor library or page
	 */
	public function installCatalogTemplateFromData($data){
		
		@ini_set("max_execution_time", 300);
		
		//get elementor template addon type
		$addonType = UELM_GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR_TEMPLATE;
		$objAddonType = UELM_UniteCreatorAddonType::getAddonTypeObject($addonType);
		$catalogAddonType = $objAddonType->catalogKey;
		
		$name = UELM_UniteFunctionsUC::getVal($data, "name");
		$isImportAgain = UELM_UniteFunctionsUC::getVal($data, "import_again");
		
		//if already imported, delete previous template
		$isImportAgain = UELM_UniteFunctionsUC::strToBool($isImportAgain);
		
		if($isImportAgain == true){
			
			$importedTemplateID = UELM_HelperProviderCoreUC_EL::getImportedElementorTemplateID($name);
			
			if(empty($importedTemplateID))
				UELM_UniteFunctionsUC::throwError("Imported template id not found");
			
			$importedPost = get_post($importedTemplateID);
			if(empty($importedPost))
				UELM_UniteFunctionsUC::throwError("Imported template object not found");
			
		}
		
		$isCreatePage = UELM_UniteFunctionsUC::getVal($data, "create_page");
		$isCreatePage = UELM_UniteFunctionsUC::strToBool($isCreatePage);
		
		//validate page name
		if($isCreatePage == true){
			$pageName = UELM_UniteFunctionsUC::getVal($data, "page_name");
			$pageName = trim($pageName);
			
			if(empty($pageName))
				UELM_UniteFunctionsUC::throwError(__("Please enter page name","unlimited-elements"));
		}
		
		$apiData = array();
		$apiData["name"] = $name;
		$apiData["type"] = $catalogAddonType;
		
		$zipContent = $this->callAPI("get_page_zip", $apiData, true);
		
		$filename = $name.".zip";
		$filepath = UELM_GlobalsUC::$path_cache.$filename;
		UELM_UniteFunctionsUC::writeFile($zipContent, $filepath);
		
		$objExporter = new UELM_UniteCreatorLayoutsExporterElementor();
		$templateID = $objExporter->importElementorTemplateNew($filepath);
		
		if($isCreatePage == true){
			
			UELM_HelperProviderUC::changeElementorTemplateToPage($templateID, $pageName);
			
		}else{		//create template
			
			//delete previous template, and update current post title and name
			if($isImportAgain == true){
				
				$importedPostTitle = $importedPost->post_title;
				$importedPostName = $importedPost->post_name;
				
				UELM_UniteFunctionsWPUC::deletePost($importedTemplateID);
				UELM_UniteFunctionsWPUC::deletePostMetadata($importedTemplateID);
				
				$arrUpdate = array();
				$arrUpdate["post_title"] = $importedPostTitle;
				$arrUpdate["post_name"] = $importedPostName;
				
				UELM_UniteFunctionsWPUC::updatePost($templateID, $arrUpdate);
			}
			
			UELM_UniteFunctionsWPUC::addPrefixToPostName($templateID, UELM_GlobalsUnlimitedElements::PREFIX_TEMPLATE_PERMALINK);
			
			//set page term
			update_post_meta($templateID, "_elementor_template_type", "page");
			wp_set_object_terms( $templateID, 'page', 'elementor_library_type');
			
			add_post_meta($templateID, UELM_GlobalsUnlimitedElements::META_TEMPLATE_SOURCE, "unlimited");
			add_post_meta($templateID, UELM_GlobalsUnlimitedElements::META_TEMPLATE_SOURCE_NAME, $name);
		}
		
		if(file_exists($filepath))
			@unlink($filepath);
		
		//create response
		$arrLinks = UELM_HelperProviderUC::getImportedTemplateLinks($templateID);
		
		return($arrLinks);
	}
	
	
}