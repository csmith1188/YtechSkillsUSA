<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorLayoutsWork extends UELM_UniteElementsBaseUC{
	
	protected $lastDuplicatedID; 
	
	
	private function a_GET_LAYOUTS(){}
	
	
	/**
	 * get layout where from params
	 */
	private function getWhereFromParams($params){
		
		$where = array();
		
		$catID = UELM_UniteFunctionsUC::getVal($params, "catid");
		if(!empty($catID))
			$where["catid"] = $catID;
		
		$layoutType = UELM_UniteFunctionsUC::getVal($params, "layout_type");
		if(empty($layoutType))
			$layoutType = UELM_UniteCreatorDB::ISNULL;
		
		$where["layout_type"] = $layoutType;
		
		return($where);
	}
	
	
	/**
	 * get galleries array
	 */
	private function getRecords($order = "ordering", $params = array(), $layoutType = null){
				
		if(empty($params))
			$params = array();
			
		$params["layout_type"] = $layoutType;
		
		
		if(empty($order))
			$order = "ordering";
		
		$where = $this->getWhereFromParams($params);
		
		$response = $this->db->fetch(UELM_GlobalsUC::$table_layouts, $where, $order);
				
		return($response);
	}
	
	
	/**
	 * get records with paging
	 */
	private function getRecordsPaging($pagingOptions){
		
		$arrWhere = array();
		
		//search
		$search = UELM_UniteFunctionsUC::getVal($pagingOptions, "search");
				
		if(!empty($search))	{
			$search = $this->db->escape($search);
			$arrWhere["title"] = array("LIKE","%{$search}%");
		}
		
		$order = UELM_UniteFunctionsUC::getVal($pagingOptions, "ordering");
		
		$filterCat = UELM_UniteFunctionsUC::getVal($pagingOptions, "category");
				
		if(!empty($filterCat) && $filterCat != 'all'){
			$filterCat = (int)$filterCat;
	
			$arrWhere["catid"] = $filterCat;
		}
		
		//add layout type
		$layoutType = UELM_UniteFunctionsUC::getVal($pagingOptions, "layout_type");
		if(empty($layoutType))
			$layoutType = UELM_UniteCreatorDB::ISNULL;
		
		$arrWhere["layout_type"] = $layoutType;
				
		$response = $this->db->fetchPage(UELM_GlobalsUC::$table_layouts, $pagingOptions, $arrWhere, $order);
		
		
		return($response);
	}
	
	
	/**
	 * get layout from data
	 */
	private function getLayoutFromData($data){
	
		$layoutID = UELM_UniteFunctionsUC::getVal($data, "layout_id");
		UELM_UniteFunctionsUC::validateNumeric($layoutID);
		UELM_UniteFunctionsUC::validateNotEmpty($layoutID);
	
		$objLayout = new UELM_UniteCreatorLayout();
		$objLayout->initByID($layoutID);
	
		return($objLayout);
	}
	
	
	/**
	 * convert records to layouts objects
	 */
	private function recordsToLayouts($records){
		
		if(empty($records))
			return(array());
			
		$arrLayouts = array();
		foreach($records as $record){
			$layoutID = UELM_UniteFunctionsUC::getVal($record, "id");
			$objLayout = new UELM_UniteCreatorLayout();
			$objLayout->initByID($layoutID);
		
			$arrLayouts[] = $objLayout;
		}
		
		return($arrLayouts);
	}
	
	/**
	 *
	 * get addons array
	 */
	public function getArrLayouts($order = "ordering", $params = array(), $layoutType = null){
				
		$response = $this->getRecords($order, $params, $layoutType);
		
		$arrLayouts = $this->recordsToLayouts($response);
		
		return($arrLayouts);
	}
	
		
	/**
	 * get layouts with paging data
	 */
	public function getArrLayoutsPaging($pagingOptions){
		
		$response = $this->getRecordsPaging($pagingOptions);
		
		$rows = $response["rows"];
		unset($response["rows"]);
		
		$arrLayouts = $this->recordsToLayouts($rows);
		
		$output = array();
		$output["paging"] = $response;
		$output["layouts"] = $arrLayouts;
		
		return($output);
	}
	
	/**
	 * get layouts array short version - without content
	 */
	public function getArrLayoutsShort($addEmpty = false, $params = array(), $layoutType = null){
		
		if($layoutType == UELM_GlobalsUC::ADDON_TYPE_REGULAR_LAYOUT)
			$layoutType = null;
		
		if(!empty($layoutType))
			$params["layout_type"] = $layoutType;
		
		$where = $this->getWhereFromParams($params);
		
		$arrLayouts = $this->db->fetchFields(UELM_GlobalsUC::$table_layouts, "id, title", $where, "ordering");
		if(empty($arrLayouts))
			$arrLayouts = array();
		
		if($addEmpty == true){
			$arrItem = array("id"=>"empty", "title"=>"[Not Selected]");
			$arrAdd = array($arrItem);
			
			$arrLayouts = array_merge($arrAdd, $arrLayouts);
		}
		
		
		return($arrLayouts);
	}
	
	/**
	 * get layouts array short version - without content
	 */
	public function getArrLayoutsKeyValue($addEmpty = false, $layoutType = null){
		
		if(!empty($layoutType))
			$params["layout_type"] = $layoutType;
		
		$where = $this->getWhereFromParams($params);
		
		$arrLayouts = $this->db->fetchFields(UELM_GlobalsUC::$table_layouts, "id, title", $where, "ordering");
		if(empty($arrLayouts))
			$arrLayouts = array();
		
		if($addEmpty == true){
			$arrItem = array("id"=>"empty", "title"=>"[Not Selected]");
			$arrAdd = array($arrItem);
			
			$arrLayouts = array_merge($arrAdd, $arrLayouts);
		}
		
		
		return($arrLayouts);
	}
	
	
	/**
	 * get active template part
	 */
	public function getActiveTempaltePart($layoutType){
		
		$arrLayouts = $this->getArrLayouts(null, null, $layoutType);
		
		if(empty($arrLayouts))
			return(null);
		
		$firstLayout = $arrLayouts[0];
		
		return($firstLayout);
	}
	
	/**
	 * get items by id's
	 */
	private function getLayoutsByIDs($arrLayoutIDs){
		
		$tableLayouts = UELM_GlobalsUC::$table_layouts;
		$arrLayouts = $this->db->fetchByIDs($tableLayouts, $arrLayoutIDs);
		
		return($arrLayouts);
	}
	
	
	/**
	 * get category layouts. category id can be null, all number or 0 (uncategorized)
	 */ 	 
	public function getCatLayouts($catID, $objAddonType = null, $onlyRecords = false){
		
		if($catID == "zero")
			$catID = 0;
		
		$arrWhere = $this->db->getWhereCatIDWithAll($catID);
		
		$typeName = null;
		if(!empty($objAddonType)){
			$typeName = $objAddonType->typeName;
			if($objAddonType->isBasicType == true)
				$typeName = null;
		}
		
		//set addon type - if specific category - no need
		if((is_numeric($catID) == false || empty($catID) || $catID === "all") && $typeName !== null )
			$arrWhere[] = $this->db->getSqlAddonType($typeName, "layout_type");
				
		$where = "";
		if(!empty($arrWhere))
			$where = implode(" and ", $arrWhere);
			
		$records = $this->db->fetch(UELM_GlobalsUC::$table_layouts, $where, "catid, ordering");
		
		if($onlyRecords === true)
			return($records);
		
		$arrLayouts = $this->recordsToLayouts($records);
		
		return($arrLayouts);
	}
	
	/**
	 * get num category layouts
	 */
	public function getNumCatLayouts($catID, UELM_UniteCreatorAddonType $objAddonType){
		
		if($objAddonType->isLayout == false)
			UELM_UniteFunctionsUC::throwError("Wrong layout type");
		
		if($catID === 0){
			$catID = "zero";
		}
		
		$arrRecords = $this->getCatLayouts($catID, $objAddonType, true);
		
		$numRecords = count($arrRecords);
		
		return($numRecords);
	}
	
	
	/**
	 * get all layouts with categories by some layout type
	 */
	public function getLayoutsWithCategories($layoutType, $isShort = false){
				
		$arrLayouts = $this->getArrLayouts(null, array(), $layoutType);
		
		$generated = UELM_UniteFunctionsUC::getRandomString(5);
		$prefix = "layout_".$generated."_";
		
		$arrCats = array();
		foreach($arrLayouts as $key => $objLayout){
			
			$name = $objLayout->getName();
			if(empty($name))
				$name = $prefix.$key;
			
			$catTitle = $objLayout->getCategoryName();
						
			if(empty($catTitle))
				$catTitle = "Uncategorized";
			
			if(isset($arrCats[$catTitle]) == false)
				$arrCats[$catTitle] = array();
			
			if($isShort == false)
				$arrCats[$catTitle][$name] = $objLayout;
			else
				$arrCats[$catTitle][$name] = $objLayout->getShortData();
			
		}
		
		return($arrCats);
	}
	
	
	private function a_______OTHER_GETTERS_________(){}
	
	
	/**
	 * check if layout exists by title
	 */
	public function isLayoutExistsByTitle($title, $layoutType = null){
		
		$arrLayout = $this->getLayoutRecordByTitle($title, $layoutType);
		
		$isExists = !empty($arrLayout);
		
		return($isExists);
	}
	
	
	/**
	 * get layout record by title
	 */
	private function getLayoutRecordByTitle($title, $layoutType){
		
		$whereType = $this->db->getSqlAddonType($layoutType, "layout_type");
		
		$response = $this->db->fetch(UELM_GlobalsUC::$table_layouts, array("title"=>$title, UELM_UniteCreatorDB::ISNULL=>$whereType));
		if(empty($response))
			return($response);
			
		$response = $response[0];
			
		return($response);
	}
	
	
	/**
	 *
	 * get max order from categories list
	 */
	public function getMaxOrder(){
	
		$tableLayouts = UELM_GlobalsUC::$table_layouts;
		
		$query = "select MAX(ordering) as maxorder from {$tableLayouts}";
		
		$rows = $this->db->fetchSql($query);
	
		$maxOrder = 0;
		if(count($rows)>0)
			$maxOrder = $rows[0]["maxorder"];
	
		if(!is_numeric($maxOrder))
			$maxOrder = 0;
	
		return($maxOrder);
	}
	
	
	/**
	 * get take screenshot url, or template of it
	 */
	public function getUrlTakeScreenshot($isTakeScreenshot = true, $layoutID = null){
		
		$addParams = "screenshot=true";
		if($isTakeScreenshot == true)
			$addParams .= "&take_screenshot=true";
		
		$isFront = true;
		
		$url = UELM_HelperUC::getViewUrl_LayoutPreview($layoutID, true, $addParams, $isFront);
		
		return($url);
	}
	
	
	private function a_OPERATIONS(){}
		
	
	/**
	 * validate layout type
	 */
	public function validateLayoutType($layoutType){
		
		$objLayout = UELM_UniteCreatorAddonType::getAddonTypeObject($layoutType, true);
		
	}
	
	
	/**
	 * duplicate layouts
	 */
	public function duplicateLayouts($arrIDs, $catID){
		
		foreach($arrIDs as $layoutID){
			$layout = new UELM_UniteCreatorLayout();
			$layout->initByID($layoutID);
			$layout->duplicate();
		}
		
	}
	
	
	/**
	 * update category layout
	 * @param unknown_type $data
	 */
	public function updateLayoutCategoryFromData($data){
				
		$layoutID = UELM_UniteFunctionsUC::getVal($data, "layoutid");
		$catID = UELM_UniteFunctionsUC::getVal($data, "catid");
		
		$objLayout = new UELM_UniteCreatorLayout();
		$objLayout->initByID($layoutID);
		$objLayout->updateCategory($catID);
		
	}
	
	
	/**
	 * update layout properties from data
	 */
	public function updateParamsFromData($data){
		
		$layoutID = UELM_UniteFunctionsUC::getVal($data, "layoutid");
		$params = UELM_UniteFunctionsUC::getVal($data, "params");
					
		
		$objLayout = new UELM_UniteCreatorLayout();
		$objLayout->initByID($layoutID);
		$objLayout->updateParams($params);
		
		$isFromManager = UELM_UniteFunctionsUC::getVal($data, "from_manager");
		$isFromManager = UELM_UniteFunctionsUC::strToBool($isFromManager);
		
		if($isFromManager == true){
		
			$addonType = UELM_UniteFunctionsUC::getVal($data, "addontype");
			$objManager = UELM_UniteCreatorManager::getObjManagerByAddonType($addonType, $data);
			
			$htmlItem = $objManager->getAddonAdminHtml($objLayout);
			
			$response = array();
			$response["html_item"] = $htmlItem;
			
			return($response);
		}
				
		return(array());
	}
	
	
	/**
	 * update layout from data
	 */
	public function updateLayoutFromData($data){

		$layoutID = UELM_UniteFunctionsUC::getVal($data, "layoutid");
		
		
		$isTitleOnly = UELM_UniteFunctionsUC::getVal($data, "title_only");
		$isTitleOnly = UELM_UniteFunctionsUC::strToBool($isTitleOnly);
				
		$objLayout = new UELM_UniteCreatorLayout();
		
		$isUpdate = false;
		
		if(empty($layoutID)){
			
			$responseCreate = $objLayout->create($data);
			$layoutID = $responseCreate["id"];
			$name = $responseCreate["name"];
			
			$message = UELM_HelperUC::getText("layout_created");
			
		}else{
			
			$isUpdate = true;
			
			//update layout
			$objLayout->initByID($layoutID);
			
			$name = UELM_UniteFunctionsUC::getVal($data, "name");
			if(!empty($name))
				$name = UELM_HelperUC::convertTitleToAlias($name);
			else{
				if(!isset($data["name"]))
					$name = null;	//to avoid validation if not passed
			}
			
				
			if($isTitleOnly == true){
				$title = UELM_UniteFunctionsUC::getVal($data, "title");
				
				$objLayout->updateTitle($title, $name);
				$message = esc_html__("Title Saved","unlimited-elements");
				
			}else{
				$objLayout->update($data);
				$message = UELM_HelperUC::getText("layout_updated");
			}
			
		}
		
		$response = array();
		$response["is_update"] = $isUpdate;
		$response["layout_id"] = $layoutID;
		
		if(!empty($name))
			$response["page_name"] = $name;
		
		$response["message"] = $message;
		
		
		return($response);
	}
	
	
	/**
	 * delete layouts
	 */
	public function deleteLayouts($arrIDs){
		
		if(empty($arrIDs))
			UELM_UniteFunctionsUC::throwError("no id's to delete");
		
		$this->db->deleteMultipleByID(UELM_GlobalsUC::$table_layouts, $arrIDs);
		
	}
	
	/**
	 * delete layout from data
	 */
	public function deleteLayoutFromData($data){
		
		$objLayout = $this->getLayoutFromData($data);
		
		$objLayout->delete();
		
	}
	
	
	/**
	 * get layout name, and find name that don't exists in database using counter	 *
	 */
	public function getUniqueTitle($title, $layoutType = null){
	
		$counter = 1;
		
		$isExists = $this->isLayoutExistsByTitle($title, $layoutType);
		
		if($isExists == false)
			return($title);
				
		$limit = 1;
		while($isExists == true && $limit < 10){
			$limit++;
			$counter++;
			$newTitle = $title."-".$counter;
			$isExists = $this->isLayoutExistsByTitle($newTitle, $layoutType);
		}
		
		return($newTitle);
	}
	
	
	
	
	/**
	 * shift addons in category from some order (more then the order).
	 */
	public function shiftOrder($order, $layoutType){
		
		UELM_UniteFunctionsUC::validateNumeric($order);
		
		$tableLayouts = UELM_GlobalsUC::$table_layouts;
		
		$sqlLayoutType = $this->db->getSqlAddonType($layoutType,"layout_type");
		
		$query = "update {$tableLayouts} set ordering = ordering+1 where ordering > {$order} and {$sqlLayoutType}";
		
		$this->db->runSql($query);
	}
	
	
	/**
	 * export layout from get data
	 */
	public function exportLayout($data = null){
		
		if(!empty($data)){
			$layoutID = UELM_UniteFunctionsUC::getVal($data, "id");
		}else{
			$layoutID = UELM_UniteFunctionsUC::getGetVar("id","",UELM_UniteFunctionsUC::SANITIZE_ID);
		}
		
		$layoutID = (int)$layoutID;
				
		$objLayout = new UELM_UniteCreatorLayout();
		$objLayout->initByID($layoutID);
		
		$exporter = new UELM_UniteCreatorLayoutsExporter();
		$exporter->initByLayout($objLayout);
		$exporter->export();
	}
	
	
	/**
	 * import layouts
	 */
	public function importLayouts($data){
				
		$layoutID = UELM_UniteFunctionsUC::getVal($data, "layoutID");
		if(!empty($layoutID))
			$layoutID = (int)$layoutID;
		
		$arrTempFile = UELM_UniteFunctionsUC::getVal($_FILES, "import_layout");
		
		$isOverwriteAddons = UELM_UniteFunctionsUC::getVal($data, "overwrite_addons");
		
		$params = UELM_UniteFunctionsUC::getVal($data, "params");
		if(empty($params))
			$params = array();
		
		$exporter = new UELM_UniteCreatorLayoutsExporter();
		$exporter->import($arrTempFile, $layoutID, $isOverwriteAddons, $params);
		
		$noRedirect = UELM_UniteFunctionsUC::getVal($data, "no_redirect");
		$noRedirect = UELM_UniteFunctionsUC::strToBool($noRedirect);
		if($noRedirect == true)
			return(null);
		
		if(empty($layoutID))
			$urlRedirect = UELM_HelperUC::getViewUrl_LayoutsList($params);
		else
			$urlRedirect = UELM_HelperUC::getViewUrl_Layout($layoutID, $params);

		
		return($urlRedirect);
	}
	
	/**
	 * update ordering
	 */
	public function updateOrdering($layoutsIDs){
		
		if(empty($layoutsIDs))
			return(false);
		
		$this->db->updateRecordsOrdering(UELM_GlobalsUC::$table_layouts, $layoutsIDs);
		
	}
	
	
	/**
	 *
	 * save items order from data
	 */
	public function updateOrderFromData($data){
		
		$layoutsIDs = UELM_UniteFunctionsUC::getVal($data, "layouts_order");
		
		$this->updateOrdering($layoutsIDs);
		
	}
	
	/**
	 * move layouts
	 */
	public function moveLayouts($arrIDs, $catID, $targetParentID = null){
		
		$category = new UELM_UniteCreatorCategories();
		$category->validateCatExist($catID);
		
		foreach($arrIDs as $layoutID){
			$this->moveLayout($layoutID, $catID, $targetParentID);
		}
		
	}
	
	
	/**
	 *
	 * move layouts to some category by change category id
	 */
	protected function moveLayout($layoutID, $catID, $targetParentID = null){
		
		$layoutID = (int)$layoutID;
		$catID = (int)$catID;
		
		$arrUpdate = array();
		$arrUpdate["catid"] = $catID;
		$this->db->update(UELM_GlobalsUC::$table_layouts, $arrUpdate, array("id"=>$layoutID));
	}
	
	
	/**
	 * save section to library from data
	 */
	public function saveSectionToLibraryFromData($data){
		
		$dataForCreate = array();
		$title = UELM_UniteFunctionsUC::getVal($data, "section_title");
		$gridData = UELM_UniteFunctionsUC::getVal($data, "section_data");
		
		$dataForCreate["title"] = $title;
		$dataForCreate["grid_data"] = $gridData;
		
		$layoutType = UELM_GlobalsUC::ADDON_TYPE_LAYOUT_SECTION;
		
		$dataForCreate["layout_type"] = $layoutType;
		
		$isOverwrite = UELM_UniteFunctionsUC::getVal($data, "section_overwrite");
		$isOverwrite = UELM_UniteFunctionsUC::strToBool($isOverwrite);
				
		$objLayout = new UELM_UniteCreatorLayout();
		
		$record = null;
		if($isOverwrite == true)
			$record = $this->getLayoutRecordByTitle($title, $layoutType);
			
		if($record){		//update
			
			$objLayout->initByRecord($record);
			$objLayout->updateGridData($gridData);
			
			$layoutID = $objLayout->getID();
			
		}else{		//create
			
			$catID = UELM_UniteFunctionsUC::getVal($data, "section_library_category");
			$dataForCreate["catid"] = $catID;
			
			if($dataForCreate["catid"] == "new" || is_numeric($catID) == false || empty($catID)){
			
				$newCatTitle = UELM_UniteFunctionsUC::getVal($data, "section_category_new");
				$objCat = new UELM_UniteCreatorCategory();
				$catCreateResponse = $objCat->add($newCatTitle, $layoutType);
				
				$newCatID = $catCreateResponse["id"];
				
				$dataForCreate["catid"] = $newCatID;
			}
			
			$createResponse = $objLayout->create($dataForCreate);
			$layoutID = $createResponse["id"];
		}
		
		
		$response = array();
		$response["layoutid"] = $layoutID;
		
		
		return($response);
	}
	
	
	/**
	 * get layout grid data
	 */
	public function getLayoutGridDataForEditor($data){
		
		$layoutID = UELM_UniteFunctionsUC::getVal($data, "layoutid");
		
		$objLayout = new UELM_UniteCreatorLayout();
		$objLayout->initByID($layoutID);
		
		$gridData = $objLayout->getGridDataForEditor();
		$gridData = json_encode($gridData);
		
		$response = array();
		$response["grid_data"] = $gridData;
		
		return($response);
	}
	
	
}