<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_UniteCreatorCategories extends UELM_UniteCreatorCategoriesWork{
	
	
	/**
	 * modify category title before create
	 * function for override
	 */
	protected function modifyCatTitleBeforeCreate($title){
		
		if(UELM_UniteCreatorWebAPI::IS_CATALOG_UNLIMITED == true)
			return($title);
		
		$title = str_replace("Article", "Post", $title);
		
		$title = str_replace("article", "post", $title);
		
		return($title);
	}
	
	
	
}