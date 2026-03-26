<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorParamsEditor{
	
	const TYPE_MAIN = "main";
	const TYPE_ITEMS = "items";
	
	private $type = null;
	private $isHiddenAtStart = false;
	private $isItemsType = false;
	private $hasCats = false;
	
	private static $isDialogsPut = false;
	
	
	/**
	 * validate that the object is inited
	 */
	private function validateInited(){
		if(empty($this->type))
			UELM_UniteFunctionsUC::throwError("UELM_UniteCreatorParamsEditor error: editor not inited");
	}
	
	/**
	 * put category dialogs html
	 */
	public function putHtml_catDialogs(){
		
		if(self::$isDialogsPut == true)
			return(false);
		
		?>

			<div id="uc_dialog_attribute_category_addsection"  title="<?php esc_html_e("Add Section","unlimited-elements")?>" 
				 data-title_edit="<?php esc_attr_e("Edit Section","unlimited-elements")?>" 
				 data-title_add="<?php esc_attr_e("Add Section","unlimited-elements")?>" 
				 data-button_add="<?php esc_attr_e("Add Section","unlimited-elements")?>" 
				 data-button_update="<?php esc_attr_e("Update Section","unlimited-elements")?>" 
				 
				 style="display:none;">
				
				<div class="dialog_edit_title_inner unite-inputs mtop_20 mbottom_20" >
			
					<div class="unite-inputs-label">
						<?php esc_html_e("Section Title", "unlimited-elements")?>:
					</div>
					
					<input type="text" class="unite-input-wide uc-section-title">
					
					<div class="unite-inputs-sap"></div>
					
					<div class="uc-dialog-param">
					<?php 
						UELM_HelperHtmlUC::putHtmlConditions("section")
					?>
					</div>
						
					<div class="unite-inputs-sap"></div>
					<br>
					<br>
					<a id="uc_dialog_attribute_category_button_addsection" href="javascript:void(0)" class="unite-button-primary uc-button-add-section"><?php esc_attr_e("Add Section", "unlimited-elements");?></a>
					
					<div class="unite-dialog-error mtop_10 uc-error-message" data-error_empty="<?php esc_attr_e("Please fill the section title","unlimited-elements")?>" style="display:none"></div>
					
					
					
				</div>
				
			</div>
		
		<?php 
		self::$isDialogsPut = true;
		
	}
	
	/**
	 * output html of the params editor
	 */
	public function outputHtmlTable(){
					
		$this->validateInited();
		
		$style="";
		if($this->isHiddenAtStart == true)
			$style = "style='display:none'";
		
		$addClass = "";
		if($this->hasCats)
			$addClass .= " uc-has-cats";	
		 
		?>
			<div id="attr_wrapper_<?php echo esc_attr($this->type) ?>" class="uc-attr-wrapper unite-inputs <?php echo esc_attr($addClass)?>" data-type="<?php echo esc_attr($this->type)?>" <?php 
				uelm_echo($style)?> >
				
				<?php if($this->hasCats == true):?>
					<div class="uc-attr-cats-wrapper">
						
						<!-- Content Tab -->
						
						<div class="uc-attr-cats-tab uc-attr-tab-content">
							<?php esc_attr_e("Content","unlimited-elements")?>							
							
							<a href="javascript:void(0)" title="<?php esc_attr_e("Add Section","unlimited-elements")?>" class="uc-attr-cats__button-add" data-sectiontab="content">+</a>
						</div>
						
						<ul id="uc_attr_list_sections_content" class="uc-attr-list-sections" data-tab="content">
							<li id="cat_general_general" data-id="cat_general_general" class="uc-active" >
								<span class="uc-attr-list__section-title">
									<?php esc_attr_e("General","unlimited-elements")?> 
								</span>
								<span class="uc-attr-list__section-numitems"></span>
																
								<i class="uc-attr-list-sections__icon-edit fas fa-pen uc-hide-on-movemode" title="<?php esc_attr_e("Edit Section", "unlimited-elements")?>"></i>
								
								<i class="uc-attr-list-sections__icon-copy fas fa-copy uc-hide-on-movemode" title="<?php esc_attr_e("Copy Section", "unlimited-elements")?>"></i>
								
								<i class="uc-attr-list-sections__icon-move fas fa-bullseye uc-show-on-movemode" title="<?php esc_attr_e("Move Here", "unlimited-elements")?>"></i>
								
							</li>
						</ul>
						
						<!-- Style Tab -->
						
						<div class="uc-attr-cats-tab uc-attr-tab-style">
							<?php esc_attr_e("Style","unlimited-elements")?>
							
							<a href="javascript:void(0)" title="<?php esc_attr_e("Add Section","unlimited-elements")?>" class="uc-attr-cats__button-add" data-sectiontab="style">+</a>
							
						</div>
						<ul id="uc_attr_list_sections_style" class="uc-attr-list-sections" data-tab="style">
						</ul>
						
						<div class="uc-attr-cats-buttons-wrapper" xstyle="background-color:green;">
							
							<a id="uc_attr_button_switch_move_mode" class="unite-button-secondary uc-hide-on-movemode" href="javascript:void(0)"><?php esc_attr_e("Move Mode", "unlimited-elements")?></a>
							
							<a id="uc_attr_button_stop_move_mode" class="unite-button-secondary uc-show-on-movemode" href="javascript:void(0)"><?php esc_attr_e("Stop Move Mode", "unlimited-elements")?></a>
							
						</div>
						
						<div id="uc_attr_cats_selected_text" class="uc-attr-cats-selected-text">
							<span id="uc_attr_cats_selected_text_number"></span>
							<?php esc_attr_e("selected", "unlimited-elements")?>, 
							
							<a id="uc_attr_cats_selected_clear" href="javascript:void(0)" class="uc-attr-cats-selected-text-link" title="<?php esc_attr_e("Clear Selected Attributes","unlimited-elements")?>">
								<?php esc_attr_e("clear","unlimited-elements")?>
							</a>							
						</div>
						
						<div id="uc_attr_cats_copied_section" class="uc-attr-cats-copied-section" style="display:none">
							
							<div class="uc-attr-cats-copied-section__text">
							
								<span id="uc_attr_cats_copied_section_name" class="uc-attr-cats-copied-section__name"></span>
								
								<?php esc_attr_e("section copied", "unlimited-elements")?>
								
							</div>
							
							<div class="uc-attr-cats-copied-section__links">
								
								<a id="uc_attr_cats_copied_section_paste_content" href="javascript:void(0)" data-tab="content" class="uc-attr-cats-copied-section__link" title="<?php esc_attr_e("Paste section in content tab","unlimited-elements")?>">
									<?php esc_attr_e("to content","unlimited-elements")?>
								</a>
															
								<a id="uc_attr_cats_copied_section_paste_style" href="javascript:void(0)" class="uc-attr-cats-copied-section__link" data-tab="style" title="<?php esc_attr_e("Paste section in style tab","unlimited-elements")?>">
									<?php esc_attr_e("to style","unlimited-elements")?>
								</a>
							
							</div>			
							
							<a id="uc_attr_cats_copied_section_clear" href="javascript:void(0)" class="uc-attr-cats-copied-section__link uc-link-clear" title="<?php esc_attr_e("Clear the copied section","unlimited-elements")?>">
								<?php esc_attr_e("clear copied","unlimited-elements")?>
							</a>			
							
						</div>
						
					</div>
					
				<?php endif?>
				<div class="uc-attr-table-wrapper">
					
					<table class="uc-table-params unite_table_items">
						<thead>
							<tr>
								<th width="50px">
									<!--  
									<span class="uc-show-on-movemode" title="<?php esc_attr_e("Select / Deselect All", "unlimited-elements")?>">
										<input type='checkbox' class='uc-check-param-move-select-all'>
									</span>
									-->
								</th>
								<th width="200px">
									<?php esc_html_e("Title", "unlimited-elements")?>
								</th>
								<th width="160px">
									<?php esc_html_e("Name", "unlimited-elements")?>
								</th>
								<th width="100px">
									<?php esc_html_e("Type", "unlimited-elements")?>
								</th>
								<th width="270px">
									<?php esc_html_e("Attribute", "unlimited-elements")?>
								</th>
								<th width="200px">
									<?php esc_html_e("Operations", "unlimited-elements")?>
								</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
					
					<div class="uc-text-empty-params mbottom_20" style="display:none">
							<?php esc_html_e("No Attributes Found", "unlimited-elements")?>
					</div>
					
					<a class="uc-button-add-param unite-button-secondary" href="javascript:void(0)"><?php esc_html_e("Add Attribute", "unlimited-elements");?></a>
					
					<?php if($this->isItemsType):?>
					
					<a class="uc-button-add-imagebase unite-button-secondary mleft_10" href="javascript:void(0)"><?php esc_html_e("Add Image Base Fields", "unlimited-elements");?></a>
					
					<?php endif?>
				
				</div>	<!-- table wrapper -->
				
			</div>
			
			<!-- params editor dialogs -->
			
			<?php 
			if($this->hasCats == true)
				$this->putHtml_catDialogs();
			?>
			
		<?php 
	}

	
	/**
	 * set hidden at start. must be run before init
	 */
	public function setHiddenAtStart(){
		$this->isHiddenAtStart = true;
	}
	
	
	/**
	 * 
	 * init editor by type
	 */
	public function init($type, $hasCats = false){
		
		if($hasCats === true)
			$this->hasCats = true;
		
		switch($type){
			case self::TYPE_MAIN:
			break;
			case self::TYPE_ITEMS:
				$this->isItemsType = true;
			break;
			default:
				UELM_UniteFunctionsUC::throwError("Wrong editor type: {$type}");
			break;
		}
		
		
		$this->type = $type;
	}
	
	
}