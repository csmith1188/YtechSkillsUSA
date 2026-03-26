<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


require UELM_HelperUC::getPathViewObject("addons_view.class");

$pathProviderAddons = UELM_GlobalsUC::$pathProvider."views/addons.php";

if(file_exists($pathProviderAddons) == true){
	require_once $pathProviderAddons;
	new UELM_UniteCreatorAddonsViewProvider();
}
else{
	new UELM_UniteCreatorAddonsView();
}

