<?php

class UELM_HttpRequest{

	const CACHE_KEY = "ue_http_request";

	const BODY_FORMAT_FORM = "form";
	const BODY_FORMAT_JSON = "json";
	const BODY_FORMAT_MULTIPART = "multipart";

	const METHOD_GET = "GET";
	const METHOD_PUT = "PUT";
	const METHOD_POST = "POST";

	const REQUEST_TIMEOUT = 120;

	private $debug = false;
	private $cacheTime = 0;
	private $bodyFormat;

	private $headers = array();
	private $query = array();
	private $body = array();

	private $validateResponse;

	/**
	 * Enable the debug mode.
	 *
	 * @param bool $debug
	 *
	 * @return $this
	 */
	public function debug($debug = true){

		$this->debug = $debug;

		return $this;
	}

	/**
	 * Set the cache time.
	 *
	 * @param int $seconds
	 *
	 * @return $this
	 */
	public function cacheTime($seconds){
		
		$this->cacheTime = $seconds;

		return $this;
	}

	/**
	 * Indicate the request contains form parameters.
	 *
	 * @return $this
	 */
	public function asForm(){

		return $this->bodyFormat(self::BODY_FORMAT_FORM)->contentType("application/x-www-form-urlencoded");
	}

	/**
	 * Indicate the request contains JSON.
	 *
	 * @return $this
	 */
	public function asJson(){

		return $this->bodyFormat(self::BODY_FORMAT_JSON)->contentType("application/json");
	}

	/**
	 * Indicate the request contains multipart parameters.
	 *
	 * @return $this
	 */
	public function asMultipart(){

		return $this->bodyFormat(self::BODY_FORMAT_MULTIPART)->contentType("multipart/form-data");
	}

	/**
	 * Indicate that JSON should be returned by the server.
	 *
	 * @return $this
	 */
	public function acceptJson(){

		return $this->accept("application/json");
	}

	/**
	 * Add the given headers to the request.
	 *
	 * @param array $headers
	 *
	 * @return $this
	 */
	public function withHeaders($headers){

		$this->headers = array_merge($this->headers, $headers);

		return $this;
	}

	/**
	 * Add the given parameters to the request query.
	 *
	 * @param array $query
	 *
	 * @return $this
	 */
	public function withQuery($query){

		$this->query = array_merge($this->query, $query);

		return $this;
	}

	/**
	 * Add the given parameters to the request body.
	 *
	 * @param array $body
	 *
	 * @return $this
	 */
	public function withBody($body){

		$this->body = array_merge($this->body, $body);

		return $this;
	}

	/**
	 * Set the validation function of the response.
	 *
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function validateResponse($callback){

		$this->validateResponse = $callback;

		return $this;
	}

	/**
	 * Make a GET request to the server.
	 *
	 * @param string $url
	 * @param array $query
	 *
	 * @return UELM_HttpResponse
	 * @throws UELM_HttpException
	 */
	public function get($url, $query = array()){
		
		return $this->withQuery($query)->request(self::METHOD_GET, $url);
	}

	/**
	 * Make a POST request to the server.
	 *
	 * @param string $url
	 * @param array $body
	 *
	 * @return UELM_HttpResponse
	 * @throws UELM_HttpException
	 */
	public function post($url, $body = array()){
		
		return $this->withBody($body)->request(self::METHOD_POST, $url);
	}

	/**
	 * print response debug
	 */
	private function printResponseDebug($requestResponse){
					
			$body = UELM_UniteFunctionsUC::getVal($requestResponse, "body");
			
			$body = UELM_UniteFunctionsUC::truncateString($body,1000);
			
			UELM_HelperHtmlUC::putHtmlDataDebugBox($body);
			
	}
	
	
	/**
	 * Make a request to the server.
	 * Cache only "get" responses
	 * @param string $method
	 * @param string $url
	 *
	 * @return UELM_HttpResponse
	 * @throws UELM_HttpException
	 */
	public function request($method, $url){
		
		$headers = $this->headers;
		$query = $this->query;
		$body = $this->prepareBody($method);
		$url = $this->prepareUrl($url, $query);
		
		if($this->isDebug() === true){
			uelm_dmp("Request data:");
			uelm_dmp($url);
			uelm_dmp($headers);
		}
		
		$cacheKey = $this->prepareCacheKey($url, $body);
		$cacheTime = $this->prepareCacheTime($method);
		
		if($cacheTime > 0){
			
			$requestResponse = UELM_UniteProviderFunctionsUC::getTransient($cacheKey);
			
			if(!empty($requestResponse)){
								
				if($this->isDebug() == true){
					uelm_dmp("get the response from cache");
					$this->printResponseDebug($requestResponse);
				}
				
				return new UELM_HttpResponse($requestResponse);
			}
			
		}
		
		$arrRequest = array(
				"method" => $method,
				"headers" => $headers,
				"body" => $body,
				"timeout" => self::REQUEST_TIMEOUT,
				"sslverify" => false
		);

		if($this->isDebug() === true){
			uelm_dmp("do the request!");
		}
		
		$wpResponse = wp_remote_request($url, $arrRequest);
		
		if(is_wp_error($wpResponse) === true)
			// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
			throw new UELM_HttpRequestException($wpResponse->get_error_message(), $this);

		$status = wp_remote_retrieve_response_code($wpResponse);
		$headers = wp_remote_retrieve_headers($wpResponse);
		$body = wp_remote_retrieve_body($wpResponse);
		
		$requestResponse = array(
			"status" => $status,
			"headers" => $headers,
			"body" => $body,
		);
		
		// Save request to changelog (only actual requests, not from cache)
		$this->saveRequestToChangelog($url);
		
		//validation
		
		if(is_callable($this->validateResponse) === true){
			
			$response = new UELM_HttpResponse($requestResponse);
			$validResponse = call_user_func($this->validateResponse, $response);

			if($validResponse === false)
				// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped
				throw new UELM_HttpResponseException("Response validation failed.", $response);
		}
			
		//show debug
		if($this->isDebug() === true){
			
			uelm_dmp("Fetched Response ($status):");
			$this->printResponseDebug($requestResponse);
			
		}
		
		//if empty body - cache time is mimimum
		if(empty($body) && $cacheTime > 0){
			
			if($this->isDebug())
				uelm_dmp("empty body - set time to 10 sec");
			
			$cacheTime = 10;
		}
		
		if($cacheTime > 0)
			UELM_UniteProviderFunctionsUC::setTransient($cacheKey, $requestResponse, $cacheTime);
		
		return new UELM_HttpResponse($requestResponse);
	}
	
	
	/**
	 * Determine if the debug mode is enabled.
	 *
	 * @return bool
	 */
	private function isDebug(){

		return $this->debug === true;
	}

	/**
	 * Set the body format of the request.
	 *
	 * @param string $format
	 *
	 * @return $this
	 */
	private function bodyFormat($format){

		$this->bodyFormat = $format;

		return $this;
	}

	/**
	 * Indicate the content type that should be returned by the server.
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	private function accept($type){

		return $this->withHeaders(["Accept" => $type]);
	}

	/**
	 * Set the content type of the request.
	 *
	 * @param string $type
	 *
	 * @return $this
	 */
	private function contentType($type){

		return $this->withHeaders(["Content-Type" => $type]);
	}

	/**
	 * Prepare the given URL for the request.
	 *
	 * @param string $url
	 * @param array $query
	 *
	 * @return string
	 */
	private function prepareUrl($url, $query){

		$url .= strpos($url, "?") === false ? "?" : "&";
		$url .= http_build_query($query);

		return $url;
	}

	/**
	 * Prepare the body for the request.
	 *
	 * @param string $method
	 *
	 * @return array|string|null
	 */
	private function prepareBody($method){

		if($method === self::METHOD_GET)
			return null;

		switch($this->bodyFormat){
			case self::BODY_FORMAT_JSON:
				return json_encode($this->body);
		}

		return $this->body;
	}

	/**
	 * Prepare the cache key for the request.
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	private function prepareCacheKey($url, $body = array()){
		
		$text = $url;
		
		if(!empty($body)){
			$text .= UELM_UniteFunctionsUC::encodeContent($body);
		}
		
		// Include cache time in the key so when cache time setting changes, 
		// the transient key changes and fresh data is fetched
		if($this->cacheTime > 0){
			$text .= ":cache_time:" . $this->cacheTime;
		}
			
		$key = self::CACHE_KEY . ":" . md5($text);
		
		return $key;
	}

	/**
	 * Prepare the cache time for the request.
	 *
	 * @param string $method
	 *
	 * @return int
	 */
	private function prepareCacheTime($method){

		return ($method === self::METHOD_GET) ? $this->cacheTime : 0;
	}

	/**
	 * Save request to changelog.
	 * Only saves actual requests, not requests served from cache.
	 *
	 * @param string $url
	 *
	 * @return void
	 */
	private function saveRequestToChangelog($url){

		try{

			$isChangelogEnabled = UELM_HelperProviderUC::isAddonChangelogEnabled();
			if($isChangelogEnabled === false)
				return;

			$changelog = new UELM_UniteCreatorAddonChangelog();
			$changelog->saveRequest($url);
		}catch(Exception $exception){
			// Silently fail - don't interrupt the request if logging fails
		}
	}

}
