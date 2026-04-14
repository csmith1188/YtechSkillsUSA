<?php

if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_UniteProviderCoreFrontUC_Elementor extends UELM_UniteProviderFrontUC{
	
	private $objFiltersProcess;

	
	/**
	 *
	 * the constructor
	 */
	public function __construct(){
		
		UELM_HelperProviderCoreUC_EL::globalInit();
		$this->registerUploadMimeFilters();
		
		//run front filters process
		
		$this->objFiltersProcess = new UELM_UniteCreatorFiltersProcess();
		$this->objFiltersProcess->initWPFrontFilters();
		
		
		/*
		$disableFilters = UELM_HelperProviderCoreUC_EL::getGeneralSetting("disable_autop_filters");
		$disableFilters = UELM_UniteFunctionsUC::strToBool($disableFilters);
		
		if($disableFilters == true)
			$this->disableWpFilters();
		*/
		
		parent::__construct();
						
	}

	/**
	 * register upload mime filters based on settings
	 */
	private function registerUploadMimeFilters(){
		
		$allowedMimes = $this->getAllowedUploadMimes();
		if(empty($allowedMimes))
			return;
		
		add_filter("upload_mimes", function($mimes) use ($allowedMimes){
			return array_merge($mimes, $allowedMimes);
		});
		
		add_filter("wp_check_filetype_and_ext", function($data, $file, $filename, $mimes) use ($allowedMimes){
			
			$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			
			if(isset($allowedMimes[$extension])){
				$data["ext"] = $extension;
				$data["type"] = $allowedMimes[$extension];
			}
			
			return $data;
		}, 10, 4);
	}
	
	/**
	 * get allowed upload mime types from general settings
	 */
	private function getAllowedUploadMimes(){
		
		$allowedMimes = array();
		
		$allowDocuments = UELM_HelperProviderCoreUC_EL::getGeneralSetting("allow_upload_documents");
		$allowDocuments = UELM_UniteFunctionsUC::strToBool($allowDocuments);
		
		$allowZip = UELM_HelperProviderCoreUC_EL::getGeneralSetting("allow_upload_zip");
		$allowZip = UELM_UniteFunctionsUC::strToBool($allowZip);
		
		$allowSvg = UELM_HelperProviderCoreUC_EL::getGeneralSetting("allow_upload_svg");
		$allowSvg = UELM_UniteFunctionsUC::strToBool($allowSvg);
		
		if($allowDocuments === true){
			$allowedMimes = array_merge($allowedMimes, array(
				"pdf" => "application/pdf",
				"doc" => "application/msword",
				"docx" => "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
				"ppt" => "application/vnd.ms-powerpoint",
				"pptx" => "application/vnd.openxmlformats-officedocument.presentationml.presentation",
				"xls" => "application/vnd.ms-excel",
				"xlsx" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
				"rtf" => "application/rtf",
				"odt" => "application/vnd.oasis.opendocument.text",
				"ods" => "application/vnd.oasis.opendocument.spreadsheet",
				"odp" => "application/vnd.oasis.opendocument.presentation",
			));
		}
		
		if($allowZip === true)
			$allowedMimes["zip"] = "application/zip";
		
		if($allowSvg === true)
			$allowedMimes["svg"] = "image/svg+xml";
		
		return $allowedMimes;
	}
	
	
}
