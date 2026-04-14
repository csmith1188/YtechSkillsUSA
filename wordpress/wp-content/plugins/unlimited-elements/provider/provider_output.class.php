<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2012 Unite CMS, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorOutput extends UELM_UniteCreatorOutputWork{
	
	private static $arrStyleHandlesCache = null;
	private static $arrScriptsHandlesCache = null;
	
	
	/**
	 * process html before output, function for override
	 */
	protected function processHtml($html){
		
		$html = do_shortcode($html);
		
		return($html);
	}
	
	/**
	 * put header additions in header html, functiob for override
	 */
	protected function putPreviewHtml_headerAdd(){
	}
	
	
	/**
	 * put footer additions in body html, functiob for override
	 */
	protected function putPreviewHtml_footerAdd(){
	}
	
	
	/**
	 * get wp done styles
	 */
	private function getWPDoneStyleHandles(){
		
		if(self::$arrStyleHandlesCache !== null)
			return(self::$arrStyleHandlesCache);
		
		$wpStyles = wp_styles();
		$arrDoneStyles = $wpStyles->done;
		
		if(empty($arrDoneStyles))
			$arrDoneStyles = array();
		
		$arrDoneStyles = UELM_UniteFunctionsUC::arrayToAssoc($arrDoneStyles);
		
		self::$arrStyleHandlesCache = $arrDoneStyles;
		
		return($arrDoneStyles);
	}
	
	
	/**
	 * exclude alrady existing includes on page
	 * function for override
	 */
	protected function excludeExistingInlcudes($arrIncludes){
		
		if(empty($arrIncludes))
			return($arrIncludes);
		
		$arrIncludesNew = array();
		
		foreach($arrIncludes as $include){
			
			$handle = UELM_UniteFunctionsUC::getVal($include, "handle");
			$type = UELM_UniteFunctionsUC::getVal($include, "type");
			
			//treat only css for now
			
			if($type == "css"){
				
				$arrStyles = $this->getWPDoneStyleHandles();
				
				$isExists = isset($arrStyles[$handle]);
				if($handle == "font-awesome" && $isExists == false){
					$isExists = isset($arrStyles["font-awesome-4-shim"]);
				}
				
				if($isExists == true)		//skip already existing
					continue;
			}
			
			
			$arrIncludesNew[] = $include;
			
		}
		
		
		return($arrIncludesNew);
	}
	
	
}