<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

require UELM_GlobalsUC::$pathProvider . "views/form_entry_service.php";

$id = UELM_UniteFunctionsUC::getGetVar("entry", null, UELM_UniteFunctionsUC::SANITIZE_ID);
$action = UELM_UniteFunctionsUC::getGetVar("action", null, UELM_UniteFunctionsUC::SANITIZE_KEY);

if(empty($id) === false && $action === "view"){
	require UELM_HelperUC::getPathViewObject("form_entry_view.class");

	$formEntry = new UELM_UCFormEntryView($id);
	$formEntry->display();
}else{
	require UELM_HelperUC::getPathViewObject("form_entries_view.class");

	$formEntries = new UELM_FormEntriesView();
	$formEntries->display();
}
