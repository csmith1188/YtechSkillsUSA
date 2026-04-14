<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorTestAddonNewView{

	/**
	 * constructor
	 */
	public function __construct(){

		$this->putHtml();
	}

	/**
	 * put html
	 */
	private function putHtml(){

		$addonID = UELM_UniteFunctionsUC::getGetVar("id", "", UELM_UniteFunctionsUC::SANITIZE_ID);

		$addon = new UELM_UniteCreatorAddon();
		$addon->initByID($addonID);

		$addonTitle = $addon->getTitle();
		$isTestData1 = $addon->isTestDataExists(1);

		$addonEditUrl = UELM_HelperUC::getViewUrl_EditAddon($addon->getId());
		$addonsListUrl = UELM_HelperUC::getViewUrl(UELM_GlobalsUnlimitedElements::VIEW_ADDONS_ELEMENTOR);

		?>

		<h1><?php esc_html_e("Widget Preview", "unlimited-elements"); ?> - <?php echo esc_html($addonTitle); ?></h1>

		<div class="uc-preview-addon-actions">
			<div class="uc-preview-addon-actions-primary">

				<button
					id="uc_testaddon_button_save"
					class="unite-button-secondary"
					data-text-default="<?php esc_attr_e("Save", "unlimited-elements"); ?>"
					data-text-loading="<?php esc_attr_e("Saving...", "unlimited-elements"); ?>"
				>
					<?php esc_html_e("Save", "unlimited-elements"); ?>
				</button>
				<button
					id="uc_testaddon_button_restore"
					class="unite-button-secondary"
					<?php echo ($isTestData1 === false ? 'style="display:none"' : ""); ?>
					data-text-default="<?php esc_attr_e("Restore", "unlimited-elements"); ?>"
					data-text-loading="<?php esc_attr_e("Restoring...", "unlimited-elements"); ?>"
				>
					<?php esc_html_e("Restore", "unlimited-elements"); ?>
				</button>
				<button
					id="uc_testaddon_button_delete"
					class="unite-button-secondary"
					<?php echo ($isTestData1 === false ? 'style="display:none"' : ""); ?>
					data-text-default="<?php esc_attr_e("Delete", "unlimited-elements"); ?>"
					data-text-loading="<?php esc_attr_e("Deleting...", "unlimited-elements"); ?>"
				>
					<?php esc_html_e("Delete", "unlimited-elements"); ?>
				</button>

				<span>|</span>

				<button id="uc_testaddon_button_clear" class="unite-button-secondary">
					<?php esc_html_e("Clear", "unlimited-elements"); ?>
				</button>
				<button id="uc_testaddon_button_check" class="unite-button-secondary">
					<?php esc_html_e("Check", "unlimited-elements"); ?>
				</button>
				<button id="uc_testaddon_button_edit" onclick="location.href='<?php echo esc_url($addonEditUrl); ?>'; return false;" class="unite-button-secondary uc-disabled">
					<?php esc_html_e("Edit", "unlimited-elements")?>
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
		
		<?php 
		
		$script = '
			jQuery(document).ready(function () {
				var objView = new UELM_UniteCreatorTestAddonNew();
				objView.init();
			});
		';
		
		UELM_UniteProviderFunctionsUC::printCustomScript($script);
	}

}

new UELM_UniteCreatorTestAddonNewView();
