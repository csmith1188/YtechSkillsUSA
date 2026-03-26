<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$bottomLineClass = "";

if($view == "layout")
	$bottomLineClass = " unite-position-right";

UELM_UniteFunctionsUC::obStart();

self::requireView($view);

$htmlView = ob_get_contents();

ob_end_clean();


$htmlClassAdd = "";

if(!empty($view)){
	$htmlClassAdd = " unite-view-{$view}";
	$bottomLineClass .= " unite-view-{$view}";
}

$showMenu = true;

switch($view){
	case "testaddonnew":
	case UELM_GlobalsUC::VIEW_TEST_ADDON:
	case UELM_GlobalsUC::VIEW_ASSETS:
	case UELM_GlobalsUC::VIEW_EDIT_ADDON:
	case "addondefaults":
		$showMenu = false;
	break;
}


?>

<?php 
UELM_HelperHtmlUC::putGlobalsHtmlOutput(); 

$script = 'var g_view = "' . esc_attr(self::$view) . '";';
UELM_UniteProviderFunctionsUC::printCustomScript($script, true); 

?>

<?php UELM_HelperHtmlUC::putInternalAdminNotices() ?>

<div id="viewWrapper" class="unite-view-wrapper unite-admin unite-inputs <?php echo esc_attr($htmlClassAdd); ?>">

	<?php require_once(UELM_GlobalsUC::$pathTemplates . "head.php"); ?>

	<div class="ue-content-wrapper">

		<?php
			if($showMenu == true)
				require_once(UELM_GlobalsUC::$pathTemplates . "menu.php");
		?>

		<?php 
		uelm_echo( $htmlView ); 
		?>
		<?php

		$filenameProviderView = UELM_GlobalsUC::$pathProviderViews . $view . ".php";

		if(file_exists($filenameProviderView))
			require_once($filenameProviderView);

		?>
	</div>

</div>

<?php

$filepathProviderMasterView = UELM_GlobalsUC::$pathProviderViews . "master_view.php";

if(file_exists($filepathProviderMasterView))
	require_once $filepathProviderMasterView;

?>

<?php if(UELM_GlobalsUC::$blankWindowMode == false): ?>

	<?php UELM_HelperHtmlUC::putFooterAdminNotices() ?>

	<div id="uc_dialog_version" title="<?php 
	echo esc_html(__("Version Release Log. Current Version: ", "unlimited-elements") . ' ' . UELM_UNLIMITED_ELEMENTS_VERSION);
	?>" style="display:none;">
		<div class="unite-dialog-inside">
			<div id="uc_dialog_version_content" class="unite-dialog-version-content">
				<div id="uc_dialog_loader" class="loader_text"><?php esc_html_e("Loading...", "unlimited-elements")?></div>
			</div>
		</div>
	</div>

	<div class="unite-clear"></div> 

	<div class="unite-plugin-version-line unite-admin <?php echo esc_attr($bottomLineClass)?>">
		<?php UELM_UniteProviderFunctionsUC::putFooterTextLine() ?>
		<?php esc_html_e("Plugin version", "unlimited-elements"); ?> <?php echo esc_html(UELM_UNLIMITED_ELEMENTS_VERSION); ?>
		<?php if(defined("UNLIMITED_ELEMENTS_UPRESS_VERSION")) esc_html_e("upress", "unlimited-elements"); ?>
		(<a id="uc_version_link" href="#"><?php esc_html_e("view changelog", "unlimited-elements"); ?></a>)
		<?php UELM_UniteProviderFunctionsUC::doAction(UELM_UniteCreatorFilters::ACTION_BOTTOM_PLUGIN_VERSION)?>
	</div>

<?php endif; ?>
