<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;


class UELM_InstaObjCommentUC{
	
	public $commentID;
	
	public $createdDateStamp;
	public $createdDate;
	
	public $text;
	public $fromUser;
	public $username;
	
	
	/**
	 * print all globals variables
	 */
	public function printVars(){
		
		$vars = get_object_vars($this);
		
		uelm_dmp($vars);
		exit();
	}
	
	
	/**
	 * get text
	 */
	public function getText(){
		
		$this->text = UELM_UniteProviderFunctionsIG::convertEmoji($this->text);
		
		return($this->text);
	}
	
	
	/**
	 * get username
	 */
	public function getUsername(){
		
		return($this->username);
	}
	
	/**
	 * init comment by array
	 */
	public function init($comment){
		
		//get date
		$this->createdDateStamp = UELM_UniteFunctionsUC::getVal($comment, "created_time");
		
		$this->createdDate = UELM_HelperInstaUC::stampToDate($this->createdDateStamp);
		
		//get text
		$this->text = UELM_UniteFunctionsUC::getVal($comment, "text");
		
		//get from user
		$fromUser = UELM_UniteFunctionsUC::getVal($comment, "from");
		
		$this->fromUser = new UELM_InstaObjUserUC();
		$this->fromUser->init($fromUser);
		
		
		//get id
		$this->commentID = UELM_UniteFunctionsUC::getVal($comment, "id");
		
	}
	
	/**
	 * init by data
	 */
	public function initByData($text, $username){
		$this->username = $username;
		$this->text = $text;
	}
	
	/**
	 * init by new API
	 */
	public function initNewAPI($data){
		
		if(isset($data["node"]))
			$data = $data["node"];
		
		$this->commentID = UELM_UniteFunctionsUC::getVal($data, "id");
		
		$dataUser = UELM_UniteFunctionsUC::getVal($data, "owner");
		if(empty($dataUser))
			$dataUser = UELM_UniteFunctionsUC::getVal($data, "user");
		
		$this->fromUser = new UELM_InstaObjUserUC();
		$this->fromUser->initByComment($dataUser);
		
		$this->username = $dataUser["username"];
		
		$this->text = UELM_UniteFunctionsUC::getVal($data, "text");
		
		$this->createdDateStamp = $data["created_at"];
		
	}
	
	
}