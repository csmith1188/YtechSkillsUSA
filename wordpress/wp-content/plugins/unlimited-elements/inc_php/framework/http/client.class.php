<?php

abstract class UELM_Http{

	/**
	 * Create a new request instance.
	 *
	 * @return UELM_HttpRequest
	 */
	public static function make(){

		return new UELM_HttpRequest();
	}

}
