<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


require UELM_HelperUC::getPathViewObject("layouts_view.class");
require UELM_HelperUC::getPathViewProvider("provider_layouts_view.class");

if(!isset($layoutType))
	$layoutType = UELM_UniteFunctionsUC::getGetVar("layout_type", "",UELM_UniteFunctionsUC::SANITIZE_KEY);
	

$objLayouts = new UELM_UniteCreatorLayoutsViewProvider();
$objLayouts->setLayoutType($layoutType);
$objLayouts->display();
