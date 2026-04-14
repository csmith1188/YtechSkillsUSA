<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_InstaObjUserUC{
	
	public $isInited = false;
	public $username,$urlProfileImage,$id,$name,$externalUrl,$numFollows;
	public $numFollowedBy, $biography, $urlProfileImageHD, $userData, $numPosts;
	
	
	/**
	 * init user
	 */
	public function init($user){
		
		if(empty($user))
			return(false);
		
		if(is_array($user) == false)
			return(false);
		
		$this->username = UELM_UniteFunctionsUC::getVal($user, "username");
		$this->urlProfileImage = UELM_UniteFunctionsUC::getVal($user, "profile_picture");
		$this->id = UELM_UniteFunctionsUC::getVal($user, "id");
		$this->name = UELM_UniteFunctionsUC::getVal($user, "full_name");
		
		$this->isInited = true;
	}
	
		
	/**
	 * init by new API
	 */
	public function initByNew($user){
		
		
		$this->externalUrl = UELM_UniteFunctionsUC::getVal($user, "external_url");
				
		$this->name = UELM_UniteFunctionsUC::getVal($user, "full_name");
				
		$this->id = UELM_UniteFunctionsUC::getVal($user, "id");
		
		$media = UELM_UniteFunctionsUC::getVal($user, "edge_owner_to_timeline_media"); 
		$this->numPosts = UELM_UniteFunctionsUC::getVal($media, "count"); 
		
		$arrFollows = UELM_UniteFunctionsUC::getVal($user, "edge_follow");
		$this->numFollows = UELM_UniteFunctionsUC::getVal($arrFollows, "count");
				
		$arrFollowedBy = UELM_UniteFunctionsUC::getVal($user, "edge_followed_by");
		$this->numFollowedBy = UELM_UniteFunctionsUC::getVal($arrFollowedBy, "count");
				
		$this->urlProfileImage = UELM_UniteFunctionsUC::getVal($user, "profile_pic_url");
				
		$this->urlProfileImageHD = UELM_UniteFunctionsUC::getVal($user, "profile_pic_url_hd");
		
		$this->biography = UELM_UniteFunctionsUC::getVal($user, "biography");
				
		$this->username = UELM_UniteFunctionsUC::getVal($user, "username");
				
		$this->userData = $user;
		
		$this->isInited = true;
		
	}
	
	
	/**
	 * init by new API - from comment
	 */
	public function initByComment($user){
		
		$this->id = UELM_UniteFunctionsUC::getVal($user, "id");
		$this->urlProfileImage = UELM_UniteFunctionsUC::getVal($user, "profile_pic_url");
		$this->username = UELM_UniteFunctionsUC::getVal($user, "username");
		
		$this->isInited = true;
	}
	
	
}