<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;



function ueCheckCatalog(){

	$objAddon = new UELM_UniteCreatorAddon();
	
	$objAddon->getCatTitle();
	
	UELM_HelperProviderUC::showPostsDebug($arrPosts);
	
	$webAPI = new UELM_UniteCreatorWebAPI();
	
	$response = $webAPI->checkUpdateCatalog();

	$lastAPIData = $webAPI->getLastAPICallData();
	
	$arrAddons = $webAPI->getCatalogAddonsByTags(UELM_UniteCreatorWebAPI::TAG_ANIMATION);
	
	
	uelm_dmp("addons that support animation");
	UELM_UniteFunctionsUC::getGetVar("preview_id","",UELM_UniteFunctionsUC::SANITIZE_KEY);
	uelm_dmp($arrAddons);
	exit();
	
	
}

function uelm_checkSomeFunc(){

	$webAPI = new UELM_UniteCreatorWebAPI();
	$data = $webAPI->getCatalogData();
	
	uelm_dmp($data);
	
	uelm_dmp("check some func");
	exit();
}


uelm_checkSomeFunc();


exit();

