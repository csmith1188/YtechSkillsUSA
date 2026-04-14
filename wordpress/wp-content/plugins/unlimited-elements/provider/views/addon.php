<?php

defined('UELM_UNLIMITED_ELEMENTS_INC') or die;

class UELM_UniteCreatorAddonViewProvider extends UELM_UniteCreatorAddonView{

	/**
	 * add dynamic fields child keys
	 */
	protected function addDynamicChildKeys($arrChildKeys){
		
		$isDynamicAddon = UELM_UniteFunctionsUC::getVal($this->addonOptions, "dynamic_addon");
		$isDynamicAddon = UELM_UniteFunctionsUC::strToBool($isDynamicAddon);
		
		if($isDynamicAddon == false)
			return($arrChildKeys);
			
		$postID = UELM_UniteFunctionsUC::getVal($this->addonOptions, "dynamic_post");
		
		if(empty($postID))
			return($arrChildKeys);
		
		$post = get_post($postID);
		
		if(empty($post))
			return($arrChildKeys);

		//add current post
		$arrPostAdditions = UELM_HelperProviderUC::getPostAdditionsArray_fromAddonOptions($this->addonOptions);
		
		//add current post child keys
		$arrChildKeys["uc_current_post"] = $this->getChildParams_post($postID, $arrPostAdditions);
		
		
		return($arrChildKeys);
	}
		
	
	
	/**
	 * get image param add fields
	 */
	protected function getImageAddFields(){
		
		$arrFields = array();
		$arrFields[] = "title";
		$arrFields[] = "alt";
		$arrFields[] = "description";
		$arrFields[] = "caption";
		
		return($arrFields);
	}
	
	
	/**
	 * get thumb sizes
	 */
	protected function getThumbSizes(){
		
		$arrThumbSizes = UELM_UniteFunctionsWPUC::getArrThumbSizes();
		
		//modify sizes
		$arrSizesModified = array();
		
		foreach($arrThumbSizes as $key => $size){
			
			if($key == "medium")
				continue;
				
			$key = str_replace("-", "_", $key);
			
			$arrSizesModified[$key] = $size;
		}
		
		return($arrSizesModified);
	}
	
	
	
}