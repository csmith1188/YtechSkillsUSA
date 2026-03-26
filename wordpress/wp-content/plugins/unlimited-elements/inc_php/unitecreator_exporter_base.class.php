<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_UniteCreatorExporterBase extends UELM_UniteElementsBaseUC{
	
	protected $pathExport;
	protected $pathImport;
	public static $serial = 0;	//serial number
	private $arrLog = array();
	
	
	/**
	 * constructor
	 */
	public function __construct(){
	}
	
	/**
	 * add log text
	 */
	protected function addLog($text){
		$this->arrLog[] = $text;
	}
	
	
	/**
	 * get log text
	 */
	public function getLogText(){
		
		$text = implode("<br>", $this->arrLog);
		
		return($text);
	}
	
	
	/**
	 * prepare global export path
	 */
	protected function prepareExportFolders_globalExport(){
	
		$pathCache = UELM_GlobalsUC::$path_cache;
		
		UELM_UniteFunctionsUC::mkdirValidate($pathCache, "Cache");
	
		$pathExport = $pathCache."export/";
	
		UELM_UniteFunctionsUC::mkdirValidate($pathExport, "Export");
	
		$this->pathExport = $pathExport;
		
	}
	
	/**
	 * prepare global import folders
	 */
	protected function prepareImportFolders_globalImport(){
		
		//create cache folder
		$pathCache = UELM_GlobalsUC::$path_cache;
		UELM_UniteFunctionsUC::mkdirValidate($pathCache, "cache");
		
		//create import folder
		$this->pathImport = $pathCache."import/";
		UELM_UniteFunctionsUC::mkdirValidate($this->pathImport, "import");
			
		
		//create index.html
		UELM_UniteFunctionsUC::writeFile("", $this->pathImport."index.html");
		
	}
	
	
	
}