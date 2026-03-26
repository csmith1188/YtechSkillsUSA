<?php

class UELM_GoogleAPIPlace extends UELM_GoogleAPIModel{

	/**
	 * Get the reviews.
	 *
	 * @return UELM_GoogleAPIPlaceReview[]
	 */
	public function getReviews(){
		
		$reviews = $this->getAttribute("reviews", array());
		$reviews = UELM_GoogleAPIPlaceReview::transformAll($reviews);
		
		return $reviews;
	}
	
	/**
	 * get place info if available
	 */
	public function getPlaceInfo(){
		
		$arrInfo = $this->getAttribute("place_info");
		
		return($arrInfo);
	}
	
	/**
	 * get place info if available
	 */
	public function getSearchParams(){
		
		$arrParams = $this->getAttribute("search_parameters");
		
		return($arrParams);
	}
	
	/**
	 * get search meta data
	 */
	public function getMetaData(){
		
		$arrParams = $this->getAttribute("search_metadata");
		
		return($arrParams);
	}
	
	
	
}
