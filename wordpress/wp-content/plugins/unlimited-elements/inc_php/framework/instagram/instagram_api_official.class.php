<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_InstagramAPIOfficialUC{

	const URL_REFRESH = "https://graph.instagram.com/refresh_access_token";
	//const URL_AUTHORIZE = "https://api.instagram.com/oauth/authorize";	//old
	const URL_AUTHORIZE = "https://www.instagram.com/oauth/authorize";
	//const APP_CLIENT_ID = "301063367606985";	//old
	const APP_CLIENT_ID = "282482552809594";
	const URL_APP_CONNECT = "https://unlimited-elements.com/instagram-connect/connect.php";
 
	private $accessToken;
	private $userID;
	private $limit = 30;
	private $maxItems = 30;

	const CACHE_RESPONSE = true;
	const DEBUG_SERVER_REQUEST = false;

	/**
	 * throw error
	 */
	private function throwError($message){

		UELM_UniteFunctionsUC::throwError("Instagram API Error: $message");
	}

	/**
	 * call api
	 */
	private function serverRequest($url, $cacheSeconds = null){

		$this->validateRequestCredentials($url);

		$request = UELM_Http::make();
		$request->debug(self::DEBUG_SERVER_REQUEST);
		$request->acceptJson();
		$request->cacheTime($this->shouldCacheRequest() === true ? $cacheSeconds : 0);

		$request->withHeaders(array(
			"Accept-Charset" => "utf-8;q=0.7,*;q=0.7",
			"User-Agent" => "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.2.8) Gecko/20100722 Firefox/3.6.8",
		));

		$request->validateResponse(function($response){

			$data = $response->json();

			if(isset($data["error"]) === true){
				$message = UELM_UniteFunctionsUC::getVal($data["error"], "message");

				if(UELM_GlobalsUC::$is_admin === false)
					$message = null;

				if(empty($message) === true)
					$message = "Oops! Something went wrong, please try again later.";

				$this->throwError($message);
			}
		});

		$response = $request->get($url);
		$data = $response->json();

		return $data;
	}

	/**
	 * validate request credentials
	 */
	private function validateRequestCredentials($url){

		$info = parse_url($url);
		$query = UELM_UniteFunctionsUC::getVal($info, "query");

		parse_str($query, $params);

		if(isset($params["access_token"]) === false)
			$this->throwError("Access token not found.");
	}

	/**
	 * should cache request
	 */
	private function shouldCacheRequest(){

		if(self::CACHE_RESPONSE === false)
			return false;

		$withoutCache = UELM_UniteFunctionsUC::getGetVar("ucnocache", "", UELM_UniteFunctionsUC::SANITIZE_TEXT_FIELD);
		$withoutCache = UELM_UniteFunctionsUC::strToBool($withoutCache);

		if(UELM_UniteFunctionsWPUC::isCurrentUserHasPermissions() === false)
			$withoutCache = false;

		return $withoutCache === false;
	}

	/**
	 * get request url for graph api
	 */
	private function getUrlRequest($type, $fields){

		$userID = $this->userID;
		$accessToken = $this->accessToken;
		$baseURL = "https://graph.instagram.com/";

		switch($type){
			case "user":
				$urlRequest = "{$baseURL}{$userID}?fields={$fields}";
			break;
			case "media":
				$limit = $this->limit;
				$urlRequest = "{$baseURL}{$userID}/media?limit={$limit}&fields={$fields}";
			break;
			default:
				$this->throwError("Wrong request type \"$type\".");
		}

		$since = "";

		$urlRequest .= "&access_token={$accessToken}&since=";

		return $urlRequest;
	}


	/**
	 * request new graph
	 */
	private function requestGraphNew($type, $fields){

		$urlRequest = $this->getUrlRequest($type, $fields);

		$arrData = $this->serverRequest($urlRequest);

		return($arrData);
	}


	/**
	 * request for user
	 * Enter description here ...
	 */
	private function requestUser(){

		$fields = array(
			"id",
			"media_count",
			"username",
			"account_type",
		);

		$strFields = implode(",", $fields);

		$response = $this->requestGraphNew("user", $strFields);

		return($response);
	}

	/**
	 * request for media
	 */
	private function requestMedia(){

		$fields = "media_url,thumbnail_url,caption,id,media_type,timestamp,username,permalink,children{media_url,id,media_type,timestamp,permalink,thumbnail_url}";

		$data = array();
		$count = 2;

		$urlNext = $this->getUrlRequest("media", $fields);

		$arrDataCombined = array();

		$maxRequest = 3;

		do{
			$response = $this->serverRequest($urlNext);

			$data = UELM_UniteFunctionsUC::getVal($response, "data");
			if(empty($data))
				$data = array();

			if(!empty($data))
				$arrDataCombined = array_merge($arrDataCombined, $data);

			$numItems = count($arrDataCombined);

			if($numItems >= $this->maxItems)
				return($arrDataCombined);

			$paging = UELM_UniteFunctionsUC::getVal($response, "paging");
			$urlNext = UELM_UniteFunctionsUC::getVal($paging, "next");

			$maxRequest--;
			if($maxRequest <= 0)
				$urlNext = null;

			if($numItems >= $this->maxItems)	//for insurance
				$urlNext = null;

		}while(!empty($urlNext));


		return($arrDataCombined);
	}


	/**
	 * init the access data
	 */
	private function initAccessData(){

		$arrData = UELM_HelperInstaUC::getInstagramSavedAccessData();

		$this->accessToken = UELM_UniteFunctionsUC::getVal($arrData, "access_token");
		$this->userID = UELM_UniteFunctionsUC::getVal($arrData, "user_id");

		if(empty($this->accessToken) || empty($this->userID))
			UELM_UniteFunctionsUC::throwError("Wrong access data");

	}


	/**
	 * get images from user
	 */
	private function getUserData_new($user, $lastID = null, $userID = null){

		$user = UELM_HelperInstaUC::sanitizeUser($user);

		UELM_HelperInstaUC::validateInstance($user, "user");

		$this->initAccessData();

		$arrUserData = $this->requestUser();

		$arrItemsData = $this->requestMedia();

		$objItems = new UELM_InstaObjUserUCItemsUC();
		$objItems->initOfficialAPI($arrItemsData, $arrUserData);

		return($objItems);
	}


	/**
	 * renew the token
	 */
	public function renewToken($currentToken){

		$request = UELM_Http::make();
		$request->acceptJson();

		$response = $request->get(self::URL_REFRESH, array(
			"grant_type" => "ig_refresh_token",
			"access_token" => $currentToken,
		));

		$data = $response->json();
		$newAccessToken = UELM_UniteFunctionsUC::getVal($data, "access_token");

		if(empty($newAccessToken) === true)
			$this->throwError("Unable to refresh the access token.");

		return $data;
	}

	private function ____END_NEW_REQUEST______(){}


	/**
	 * convert items to simple array
	 */
	private function convertItemsToSimpleArray($objItems, $maxItems = null){

		if($maxItems !== null){
			$maxItems = (int)$maxItems;
			if($maxItems < 1)
				$maxItems = null;
		}

		$arrItems = $objItems->getItems();

		$arrItemsData = array();

		foreach($arrItems as $index=>$item){

			if($maxItems && $index >= $maxItems)
				break;

			$data = $item->getDataSimple();
			$arrItemsData[] = $data;
		}


		return($arrItemsData);
	}


	/**
	 * get items data uf it's user or tag
	 */
	public function getItemsData($mixed, $lastID=null, $userID = null, $maxItems = null){


		$type = "";
		if(strpos($mixed,"@") === 0)
			$type = "user";
		else
			if(strpos($mixed,"#") === 0)
				$type = "tag";

		if(empty($type)){
			$type = "user";
			$mixed .= "@".$mixed;
		}

		try{

			if(empty($type))
				UELM_UniteFunctionsUC::throwError("Wrong type, should be user or tag");

			switch($type){
				case "user":

					//$objItems = $this->getUserData($mixed, $lastID, $userID);
					$objItems = $this->getUserData_new($mixed, $lastID, $userID);

				break;
				case "tag":
					$objItems = $this->getTagData($mixed, $lastID, $userID);
				break;
			}

			$arrItems = $this->convertItemsToSimpleArray($objItems, $maxItems);

		}catch(Exception $e){

			throw $e;
		}

		//renew here
		UELM_HelperInstaUC::checkRenewAccessToken_onceInAWhile();


		$pageData = $objItems->getArrPageData();

		$response = array();
		$response["main"] = $pageData;
		$response["items"] = $arrItems;


		return($response);
	}


}
