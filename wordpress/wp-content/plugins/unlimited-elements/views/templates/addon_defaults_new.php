<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

$addonTitle = $addon->getTitle();
$addonEditUrl = UELM_HelperUC::getViewUrl_EditAddon($addon->getId());
$addonsListUrl = UELM_HelperUC::getViewUrl(UELM_GlobalsUnlimitedElements::VIEW_ADDONS_ELEMENTOR);

?>

<div id="uc_addondefaults_wrapper" class="uc-addondefaults-wrapper">

	<h1><?php esc_html_e("Widget Defaults", "unlimited-elements"); ?> - <?php echo esc_html($addonTitle); ?></h1>

	<div class="uc-preview-addon-actions">
		<div class="uc-preview-addon-actions-primary">
			<button
				id="uc_addondefaults_button_save"
				class="unite-button-primary"
				data-text-default="<?php esc_attr_e("Save Defaults", "unlimited-elements"); ?>"
				data-text-loading="<?php esc_attr_e("Saving...", "unlimited-elements"); ?>"
			>
				<?php esc_html_e("Save Defaults", "unlimited-elements"); ?>
			</button>
		</div>
		<div class="uc-preview-addon-actions-secondary">
		
			<?php if(UELM_GlobalsUnlimitedElements::$enableEditWidget == true):?>
			<a class="unite-button-secondary" href="<?php echo esc_url($addonEditUrl); ?>">
				
					<?php if(UELM_GlobalsUnlimitedElements::$isGutenbergOnly == true):?>
					<?php esc_html_e("Edit Block", "unlimited-elements"); ?>
					<?php else:?>
					<?php esc_html_e("Edit Widget", "unlimited-elements"); ?>				
					<?php endif?>
					
			</a>
			<?php endif?>
			
			<a class="unite-button-secondary" href="<?php echo esc_url($addonsListUrl); ?>">
				<?php if(UELM_GlobalsUnlimitedElements::$isGutenbergOnly == true):?>
				<?php esc_html_e("Back to Blocks", "unlimited-elements"); ?>
				<?php else:?>
				<?php esc_html_e("Back to Widgets", "unlimited-elements"); ?>
				<?php endif?>
				
			</a>
			
		</div>
	</div>

	<?php require UELM_HelperUC::getPathTemplate("addon_preview"); ?>

</div>
<?php

$script = 'jQuery(document).ready(function () {
		var objView = new UELM_UniteCreatorAddonDefaultsAdmin();
		objView.init();
	});';

UELM_UniteProviderFunctionsUC::printCustomScript($script, true); 
