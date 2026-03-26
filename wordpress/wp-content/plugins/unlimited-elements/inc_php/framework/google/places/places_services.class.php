<?php

/**
 * @link https://developers.google.com/maps/documentation/places/web-service/overview
 */
class UELM_GoogleAPIPlacesService extends UELM_GoogleAPIClient{
	
	private $isSerp = false;
	
	/**
	 * Get the place details.
	 *
	 * @param string $placeId
	 * @param array $params
	 *
	 * @return UELM_GoogleAPIPlace
	 */
	public function getDetails($placeId, $params = array(),$showDebug = false){
		
		$this->isSerp = false;
		
		$params["place_id"] = $placeId;
		
		$lang = UELM_UniteFunctionsUC::getVal($params, "lang");
		
		if(!empty($lang))
			$params["language"] = $lang;
		else
			$params["reviews_no_translations"] = true;
					
		$response = $this->get("/details/json", $params);

		//debug
		if($showDebug == true){
			
			UELM_HelperHtmlUC::putHtmlDataDebugBox_start();
						
			uelm_dmp("Official API Request Debug");
			
			$paramsForDebug = $params;
			
			uelm_dmp("Send Params");
			uelm_dmp($paramsForDebug);
			
			$dataShow = UELM_UniteFunctionsUC::modifyDataArrayForShow($response);
			
			uelm_dmp("Response Data");
			uelm_dmp($dataShow);
			
			UELM_HelperHtmlUC::putHtmlDataDebugBox_end();
		}
		
		$response = UELM_GoogleAPIPlace::transform($response["result"]);
		
		
		return $response;
	}
	
	/**
	 * get details using serp function
	 */
	public function getDetailsSerp($placeID, $apiKey, $params = array(),$showDebug = false, $cacheTime = 86400){

		if(empty($apiKey))
			UELM_UniteFunctionsUC::throwError("No serp api key");
		
		$this->isSerp = true;
		
		//cache time is passed as parameter (default: 1 day in seconds)
		
		$params["place_id"] = $placeID;
		$params["api_key"] = $apiKey;
		
		$headers = array();
		
		$request = UELM_Http::make();
				
		if(!empty($headers))
			$request->withHeaders($headers);
				
		$request->asJson();
		$request->acceptJson();
		
		$request->cacheTime($cacheTime);
		$request->withQuery($params);
		
		$url = "https://serpapi.com/search?engine=google_maps_reviews";
		
		//first call
		
		$response = $request->request(UELM_HttpRequest::METHOD_GET, $url);
		
		$data = $response->json();
		
		if($showDebug == true){
			
			UELM_HelperHtmlUC::putHtmlDataDebugBox_start();
						
			uelm_dmp("Serp API Request Debug");
			
			$paramsForDebug = $params;
			
			$apiKey = UELM_UniteFunctionsUC::getVal($paramsForDebug, "api_key");
			
			$paramsForDebug["api_key"] = substr($apiKey, 0, 10) . '********';
			
			uelm_dmp("Send Params");
			uelm_dmp($paramsForDebug);
			
			$dataShow = UELM_UniteFunctionsUC::modifyDataArrayForShow($data);
			
			uelm_dmp("Response Data");
			uelm_dmp($dataShow);
			
		}
		
		$error = UELM_UniteFunctionsUC::getVal($data, "error");
		if(!empty($error)){
			uelm_dmp($data);
			UELM_UniteFunctionsUC::throwError($error);
		}
		
		$pagination = UELM_UniteFunctionsUC::getVal($data, "serpapi_pagination");
		$nextPageToken = UELM_UniteFunctionsUC::getVal($pagination, "next_page_token");
		
		//second call:
		
		if(!empty($nextPageToken)){
			
			$params["next_page_token"] = $nextPageToken;
			$params["num"] = 20;
			
			$request->withQuery($params);
			
			$response = $request->request(UELM_HttpRequest::METHOD_GET, $url);
			$data2 = $response->json();

			if($showDebug == true){
				
				uelm_dmp("Second Request - Send Params2");
				uelm_dmp($params);
				
				$dataShow2 = UELM_UniteFunctionsUC::modifyDataArrayForShow($data);
				
				uelm_dmp("Second Request - Response Data");
				uelm_dmp($dataShow2);
				
			}
			
			$arrReviews2 = UELM_UniteFunctionsUC::getVal($data2, "reviews");
			
			if(!empty($arrReviews2))
				$data["reviews"] += $arrReviews2;
			
		}
		
		if($showDebug == true)
			UELM_HelperHtmlUC::putHtmlDataDebugBox_end();
		
		
		$place = UELM_GoogleAPIPlace::transform($data);		
		
		return($place);
	}
	
	
	/**
	 * Get the base URL for the API.
	 *
	 * @return string
	 */
	protected function getBaseUrl(){
		
		if($this->isSerp == true)
			return("https://serpapi.com/search?engine=google_maps_reviews");
		else		
			return "https://maps.googleapis.com/maps/api/place";
		
	}

}
