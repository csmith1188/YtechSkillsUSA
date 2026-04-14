<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_ConnectivityTestView{
	
	/**
	 * construction
	 */
	public function __construct(){
		
		$this->putHTML();
	}

/**
 * check zip file request
 */
private function checkZipFile(){

	//request single file
	uelm_dmp("requesting widget zip from API");

	$response = UELM_Http::make()->post(UELM_GlobalsUC::URL_API, array(
		"action" => "get_addon_zip",
		"name" => "team_member_box_overlay",
		"cat" => "Team Members",
		"type" => "addons",
		"catalog_date" => "1563618449",
		"code" => "",
	));

	$data = $response->body();

	if(empty($data))
		UELM_UniteFunctionsUC::throwError("Empty server response");

	$len = strlen($data);

	uelm_dmp("api response OK, received string size: $len");
}



/**
 * check zip file request
 */
private function checkCatalogRequest(){

		uelm_dmp("requesting catalog check");
		
		$response = UELM_Http::make()->post(UELM_GlobalsUC::URL_API, array(
			"action" => "check_catalog",
			"catalog_date" => "1563618449",
			"include_pages" => false,
			"domain" => "localhost",
			"platform" => "wp",
		));
		
		$data = $response->body();
	
		if(empty($data))
			UELM_UniteFunctionsUC::throwError("Empty server response");

		$len = strlen($data);
	
		if($len < 5000){
			
			uelm_dmp("The wrong response: ");
			uelm_dmpHtml($data);
			 
			UELM_UniteFunctionsUC::throwError("Response has wrong size: $len");
		}
		
		uelm_dmp("api response OK, received string size: $len");
		
		
}

/**
 * various
 */
private function checkVariousOptions(){ 

	$urlAPI = UELM_GlobalsUC::URL_API;
	
	uelm_dmp("checking get contents from the api: $urlAPI");
 	
	$response = UELM_UniteFunctionsUC::fileGetContents($urlAPI);
	
	$len = strlen($response);
	
	if($len == 0)
		UELM_UniteFunctionsUC::throwError("No response from API. Recieved string size: 0");
				
	if($len > 1000){
		
		uelm_dmp("Response has wrong size: $len");
		
		uelm_dmpHtml($response);
				
		return(false);
	}
	
	uelm_dmp("file get contents OK, received string size: $len");

}

/**
 * check and update catalog
 */
private function checkUpdateCatalog(){

	uelm_dmp("Trying to update the catalog from the api... Printing Debug...");

	$webAPI = new UELM_UniteCreatorWebAPI();

	$webAPI->checkUpdateCatalog(true);

	$arrDebug = $webAPI->getDebug();

	uelm_dmp($arrDebug);

	//print option content
	$optionCatalog = UELM_UniteCreatorWebAPI::OPTION_CATALOG;

	uelm_dmp("Option catalog raw data: $optionCatalog");
	
	$data = get_option($optionCatalog);

	uelm_dmp($data);

}


/**
 * check if catalog data is saved well
 */
private function checkingCatalogData(){

	$webAPI = new UELM_UniteCreatorWebAPI();
	$data = $webAPI->getCatalogData();
		
	uelm_dmp("Checking saved widgets catalog data");

	if(empty($data)){
	
		uelm_dmp("No catalog widgets data found!");

		$this->checkUpdateCatalog();

		return(false);
	}

	if(is_array($data) == false)
		UELM_UniteFunctionsUC::throwError("Catalog data is not array");

	$stamp = UELM_UniteFunctionsUC::getVal($data, "stamp");
	$catalog = UELM_UniteFunctionsUC::getVal($data, "catalog");

	if(empty($stamp))
		UELM_UniteFunctionsUC::throwError("No stamp found");

	if(empty($catalog))
		UELM_UniteFunctionsUC::throwError("Empty widgets catalog");

	$date = UELM_UniteFunctionsUC::timestamp2Date($stamp);

	uelm_dmp("catalog data found OK from date: $date");

	$showData = UELM_UniteFunctionsUC::getGetVar("showdata","", UELM_UniteFunctionsUC::SANITIZE_TEXT_FIELD);
	$showData = UELM_UniteFunctionsUC::strToBool($showData);

	if($showData == true)
		uelm_dmp($data);

}
	
	
	/**
	 * put view html
	 */
	private function putHTML(){
		?>
		
		
<h1>Unlimited Elements - API Access Test</h1>

<br>
		
<?php 
		
try{
	
		ini_set("display_errors",1);
		
		$this->checkVariousOptions();
	
		echo "<br><br>";
	
		$this->checkCatalogRequest();
	
		echo "<br><br>";
	
		$this->checkZipFile();
	
		echo "<br><br>";
	
		$this->checkingCatalogData();

}catch(Exception $e){

		$urlPHPFile = UELM_GlobalsUC::$urlPlugin."views/api-connect-test.php";
	 	
		$serverIP = $_SERVER["SERVER_ADDR"];
				
		?>
		
		<div style="font-size:18px;line-height:35px;">
			
			<hr>
		
		<?php 
			uelm_echo( $e->getMessage() );
		?>
			<hr>
			
			The request to the catalog url has failed. <br> Requesting from website ip: <?php echo esc_html($serverIP) ?> <br>
			
			Please contact your hosting provider and request to open firewall access to this address: 
			
			<br>
			
			<a href="https://api.unlimited-elements.com/">https://api.unlimited-elements.com/</a>
			
			<br>
			
			Also, you can test the very simple plain PHP file with the connectiviry test:
					
			<a href="<?php echo esc_url($urlPHPFile); ?>">api-connect-test.php</a>
			
			<br>
			
			If it will fail as well, please show this file to your server support.
		
		</div>

		<?php 

}
		
		
	}//end putHTML()
	
}


new UELM_ConnectivityTestView();

