<?php

class UELM_HttpResponseException extends UELM_HttpException{

	private $response;

	/**
	 * Create a new class instance.
	 *
	 * @param string $message
	 * @param UELM_HttpResponse $response
	 *
	 * @return void
	 */
	public function __construct($message, $response){

		$this->response = $response;

		parent::__construct($message, $response->status());
	}

	/**
	 * Get the response instance.
	 *
	 * @return UELM_HttpResponse
	 */
	public function getResponse(){

		return $this->response;
	}

}
