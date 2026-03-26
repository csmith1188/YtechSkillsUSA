<?php

function uelm_wpmlAutoTranslationTest(){
	
	if(UELM_UniteCreatorWpmlIntegrate::isWpmlExists() == false && UELM_GlobalsUC::$inDev == false){
		
		uelm_dmp("wpml plugin not installed");
		return(false);
	}
	
	$arrWidgets = apply_filters("uelm_wpml_elementor_widgets_to_translate",array());
	
	uelm_dmp("Those widgets are selected for the wpml auto translate:");
	
	foreach($arrWidgets as $name => $fields){
		
		uelm_dmp("<b>$name</b>");
		
		$arrFields = UELM_UniteFunctionsUC::getVal($fields, "fields");
		
		uelm_dmp("main fields:");
		
		uelm_dmp($arrFields);
		
		if(isset($fields["integration-class"]) == false)
			continue;
			
		$widgetName = str_replace("ucaddon_","",$name)."_elementor";
		
		$arrItemsFields = UELM_UniteFunctionsUC::getVal(UELM_UniteCreatorWpmlIntegrate::$arrWidgetItemsData, $widgetName);

		
		uelm_dmp("items fields: ");
		uelm_dmp($arrItemsFields);
		
		
	}
	
}

uelm_wpmlAutoTranslationTest();