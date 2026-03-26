<h1>Unlimited Elements - Instagram Test</h1>

<br>

<?php

function uelm_UnlimitedElementsputInstagramTest(){
	
	$objServices = new UELM_UniteServicesUC();
	
	$arrData = $objServices->getInstagramSavedDataArray();
	
	if(empty($arrData)){
		uelm_dmp("no saved instagram data found");
		return(false);
	}

	
	
	uelm_dmp("<b>Saved Instagram Data</b>");
	
	foreach($arrData as $key=>$value){
		
		if($key == "expires")
			$value = UELM_UniteFunctionsUC::timestamp2Date($value);
		
		uelm_dmp("$key: $value");
		
	}
	
	$userName = $arrData["username"];
	
	$response = $objServices->getInstagramData($userName);
	
	if(!empty($response))
		uelm_dmp("<b>Instagram data found, all ok</b>");
	else 
		uelm_dmp("<b>Error: No Instagram Data Fetched</b>");
	
	uelm_dmp($response);
	
	
}


try{

	uelm_UnlimitedElementsputInstagramTest();
	
}catch(Exception $e){
	
	UELM_HelperHtmlUC::outputException($e);
	
}

