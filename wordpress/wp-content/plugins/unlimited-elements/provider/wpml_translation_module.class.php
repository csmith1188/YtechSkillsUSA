<?php

/**
 * Class WPML_Elementor_Price_List
 */
class UELM_UNITE_CREATOR_WPML_Translation_Module extends WPML_Elementor_Module_With_Items{
//class UELM_UNITE_CREATOR_WPML_Translation_Module{
	
	private $ucIsInited = false;
	private $ucData = array();
	
	
	/**
	 * init the class
	 */
	private function ucInit(){
		
		if($this->ucIsInited == true)
			return(false);
		
		$this->ucIsInited = true;
			
		$class = get_class($this);
		
		$widgetName = str_replace("UE_WPML_INTEGRATION__", "", $class);
		
		if(empty($widgetName))
			return(false);
		
		$arrData = UELM_UniteFunctionsUC::getVal(UELM_UniteCreatorWpmlIntegrate::$arrWidgetItemsData, $widgetName);
		
		if(empty($arrData))
			return(false);
		
		$arrData = UELM_UniteFunctionsUC::arrayToAssoc($arrData,"field");
		
		$this->ucData = $arrData;
	}
	
	/**
	 * @return string
	 */
	public function get_items_field() {
		return 'uc_items';
	}

	/**
	 * @return array
	 */
	public function get_fields() {
		
		$this->ucInit();
		
		if(empty($this->ucData))
			return(array());
		
		$arrFields = array();
		foreach($this->ucData as $field => $arrField){
			$arrFields[] = $field;
		}
		
		return $arrFields;
	}

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_title( $field ) {
		
		$this->ucInit();
		
		$arrField = UELM_UniteFunctionsUC::getVal($this->ucData, $field);

		$title = UELM_UniteFunctionsUC::getVal($arrField, "type");
		
		return($title);		
	}
	

	/**
	 * @param string $field
	 *
	 * @return string
	 */
	protected function get_editor_type( $field ) {
		
		$this->ucInit();
		
		$arrField = UELM_UniteFunctionsUC::getVal($this->ucData, $field);

		$type = UELM_UniteFunctionsUC::getVal($arrField, "editor_type");
		
		return($type);		
	}
	
	/**
	 * print test settings
	 */
	public function printTest(){
		
		$fields = $this->get_fields();
		
		foreach($fields as $field){
			
			$title = $this->get_title($field);
			$type = $this->get_editor_type($field);
			
			uelm_dmp("---------------");
			uelm_dmp($field);
			uelm_dmp($title);
			uelm_dmp($type);
		}
		
		if(empty($fields))
			uelm_dmp("no fields found");
		
	}
	
}