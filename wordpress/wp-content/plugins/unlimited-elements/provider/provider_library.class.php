<?php

class UELM_UniteCreatorLibrary extends UELM_UniteCreatorLibraryWork{
	
	
	/**
	 * get platform include by handle
	 */
	protected function getUrlPlatformInclude($handle){
				
		$urlInclude = null;
		
		switch($handle){
			case "jquery":
				$urlInclude = UELM_UniteProviderFunctionsUC::getUrlJQueryInclude();
			break;
			case "jquery-migrate":
				$urlInclude = UELM_UniteProviderFunctionsUC::getUrlJQueryMigrateInclude();
			break;
			//case "ue_remote_controls":
				//$urlInclude = UELM_GlobalsUC::$url_assets."aaa_remote/ue_remote_controls.js";
			//break;
		}
		
		return($urlInclude);
	}
	
	
	/**
	 * function for override, process provide library
	 * return true if library found and processed, and false if not
	 */
	public function processProviderLibrary($name){
				
		switch($name){
			case "jquery":
				UELM_UniteProviderFunctionsUC::addjQueryInclude();
				
			break;
			default:
				return(false);
			break;
		}
		
		return(true);
	}
	
	
}