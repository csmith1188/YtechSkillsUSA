<?php

class UELM_HttpRequestException extends UELM_HttpException{

	private $request;

	/**
	 * Create a new class instance.
	 *
	 * @param string $message
	 * @param UELM_HttpRequest $request
	 *
	 * @return void
	 */
	public function __construct($message, $request){

		$this->request = $request;

		parent::__construct($message);
	}

	/**
	 * Get the request instance.
	 *
	 * @return UELM_HttpRequest
	 */
	public function getRequest(){

		return $this->request;
	}

}
