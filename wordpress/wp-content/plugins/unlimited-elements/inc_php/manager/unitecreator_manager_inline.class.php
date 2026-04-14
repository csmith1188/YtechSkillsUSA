<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_UniteCreatorManagerInline extends UELM_UniteCreatorManager{

	private $startAddon;
	private $itemsType;
	private $source = "";
	
	
	/**
	 * construct the manager
	 */
	public function __construct(){
		
		$this->type = self::TYPE_ITEMS_INLINE;
		
		$this->init();
	}
	
	/**
	 * set source
	 */
	public function setSource($source){
		
		$this->source = $source;
		$this->arrOptions["source"] = $source;
	}
	
	
	/**
	 * validate that the start addon exists
	 */
	private function validateStartAddon(){
		
		if(empty($this->startAddon))
			UELM_UniteFunctionsUC::throwError("The start addon not given");
		
	}
	
	
	/**
	 * init the data from start addon
	 */
	private function initStartAddonData(){
		
		$this->itemsType = $this->startAddon->getItemsType();
		
		//set init data
		$arrItems = $this->startAddon->getArrItemsForConfig();
		
		$strItems = "";
		if(!empty($arrItems)){
			$strItems = json_encode($arrItems);
			$strItems = htmlspecialchars($strItems);
		}
		
		$addHtml = " data-init-items=\"{$strItems}\" ";
		
		$this->setManagerAddHtml($addHtml);
		
	}
	
	
	/**
	 * set start addon
	 */
	public function setStartAddon($addon){
		$this->startAddon = new UELM_UniteCreatorAddon();	//just for code completion
		$this->startAddon = $addon;
		
		$this->initStartAddonData();
				
	}
	
	/**
	 * get category list
	 */
	protected function getCatList($selectCatID = null, $arrCats = null, $params = array()){
		
		$htmlCatList = "";
		
		return($htmlCatList);
	}
	
	
	/**
	 * get single item menu
	 */
	protected function getMenuSingleItem(){
		
		$arrMenuItem = array();
		$arrMenuItem["edit_item"] = esc_html__("Edit Item","unlimited-elements");
		$arrMenuItem["remove_items"] = esc_html__("Delete","unlimited-elements");
		$arrMenuItem["duplicate_items"] = esc_html__("Duplicate","unlimited-elements");
		
		return($arrMenuItem);
	}

	/**
	 * get multiple items menu
	 */
	protected function getMenuMulitipleItems(){
		$arrMenuItemMultiple = array();
		$arrMenuItemMultiple["remove_items"] = esc_html__("Delete","unlimited-elements");
		$arrMenuItemMultiple["duplicate_items"] = esc_html__("Duplicate","unlimited-elements");
		return($arrMenuItemMultiple);
	}
	
	
	/**
	 * get item field menu
	 */
	protected function getMenuField(){
		$arrMenuField = array();
		$arrMenuField["add_item"] = esc_html__("Add Item","unlimited-elements");
		$arrMenuField["select_all"] = esc_html__("Select All","unlimited-elements");
		
		return($arrMenuField);
	}
	
	
	/**
	 * put items buttons
	 */
	protected function putItemsButtons(){
		
		$this->validateStartAddon();
		
		$itemType = $this->startAddon->getItemsType();
		
		$buttonClass = "unite-button-primary button-disabled uc-button-item uc-button-add";
		
		//put add item button according the type
		switch($itemType){
			default:
			case UELM_UniteCreatorAddon::ITEMS_TYPE_DEFAULT:
			?>
 				<a data-action="add_item" type="button" class="<?php echo esc_attr($buttonClass)?>"><?php esc_html_e("Add Item","unlimited-elements")?></a>
			<?php 
			break;
			case UELM_UniteCreatorAddon::ITEMS_TYPE_IMAGE:
			?>
 				<a data-action="add_images" type="button" class="<?php echo esc_attr($buttonClass)?>"><?php esc_html_e("Add Images","unlimited-elements")?></a>
			<?php 
			break;
			case UELM_UniteCreatorAddon::ITEMS_TYPE_FORM:
				?>
 				<a data-action="add_form_item" type="button" class="<?php echo esc_attr($buttonClass)?>"><?php esc_html_e("Add Form Item","unlimited-elements")?></a>
 				<?php
			break;
		}
		
		?>
	 		<a data-action="select_all_items" type="button" class="unite-button-secondary button-disabled uc-button-item uc-button-select" data-textselect="<?php esc_html_e("Select All","unlimited-elements")?>" data-textunselect="<?php esc_html_e("Unselect All","unlimited-elements")?>"><?php esc_html_e("Select All","unlimited-elements")?></a>
	 		<a data-action="duplicate_items" type="button" class="unite-button-secondary button-disabled uc-button-item"><?php esc_html_e("Duplicate","unlimited-elements")?></a>
	 		<a data-action="remove_items" type="button" class="unite-button-secondary button-disabled uc-button-item"><?php esc_html_e("Delete","unlimited-elements")?></a>
	 		<a data-action="edit_item" type="button" class="unite-button-secondary button-disabled uc-button-item uc-single-item"><?php esc_html_e("Edit Item","unlimited-elements")?> </a>
		<?php 
	}
	
	
	/**
	 * put add edit item dialog
	 */
	private function putAddEditDialog(){
		
		$isLoadByAjax = $this->startAddon->isEditorItemsAttributeExists();
		
		
		$addHtml = "";
		if($isLoadByAjax == true){
			
			$addonID = $this->startAddon->getID();
			$addonID = esc_attr($addonID);
			$addHtml = "data-initbyaddon=\"{$addonID}\"";
		}
		
		?>
			<div title="<?php esc_html_e("Edit Item","unlimited-elements")?>" class="uc-dialog-edit-item" style="display:none">
				<div class="uc-item-config-settings" autofocus="true" <?php 
				uelm_echo($addHtml);?>>
					
					<?php if($isLoadByAjax == false): 
						
						if($this->startAddon)
						$this->startAddon->putHtmlItemConfig();
					?>
					<?php else:	 //load by ajax?>
						
						<div class="unite-dialog-loader-wrapper">
							<div class="unite-dialog-loader"><?php esc_html_e("Loading Settings", "unlimited-elements")?>...</div>
						</div>
						
					<?php endif?>
					
				</div>
			</div>
		<?php 
	}
	
	
	/**
	 * put form dialog
	 */
	protected function putFormItemsDialog(){
		
		$objDialogParam = new UELM_UniteCreatorDialogParam();
		$objDialogParam->init(UELM_UniteCreatorDialogParam::TYPE_FORM_ITEM, $this->startAddon);
		$objDialogParam->outputHtml();
		
	}
	
	
	/**
	 * put additional html here
	 */
	protected function putAddHtml(){
				
		if($this->itemsType == UELM_UniteCreatorAddon::ITEMS_TYPE_FORM)
			$this->putFormItemsDialog();
		else
			$this->putAddEditDialog();
		
	}
	
	/**
	 * before init
	 */
	protected function beforeInit($addonType){
		$this->hasCats = false;
	}
	
	
	
}