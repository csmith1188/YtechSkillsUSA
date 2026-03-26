<?php

class UELM_GoogleAPIPlaylistItem extends UELM_GoogleAPIModel{

	const IMAGE_SIZE_DEFAULT = "default";
	const IMAGE_SIZE_MEDIUM = "medium";
	const IMAGE_SIZE_HIGH = "high";
	const IMAGE_SIZE_MAX = "maxres";

	/**
	 * Get the identifier.
	 *
	 * @return string
	 */
	public function getId(){

		$id = $this->getAttribute("id");

		return $id;
	}

	/**
	 * Get the title.
	 *
	 * @return string
	 */
	public function getTitle(){

		$title = $this->getSnippetValue("title");

		return $title;
	}

	/**
	 * Get the description.
	 *
	 * @param bool $asHtml
	 *
	 * @return string
	 */
	public function getDescription($asHtml = false){

		$description = $this->getSnippetValue("description");

		if($asHtml === true)
			$description = nl2br($description);

		return $description;
	}

	/**
	 * Get the date.
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function getDate($format){

		$date = $this->getSnippetValue("publishedAt");
		$time = strtotime($date);
		$date = uelm_date($format, $time);

		return $date;
	}

	/**
	 * Get the image URL.
	 *
	 * @param string $size
	 *
	 * @return string
	 */
	public function getImageUrl($size){

		$images = $this->getSnippetValue("thumbnails");
		$image = UELM_UniteFunctionsUC::getVal($images, $size, array());
		$url = UELM_UniteFunctionsUC::getVal($image, "url");

		return $url;
	}

	/**
	 * Get the video identifier.
	 *
	 * @return string
	 */
	public function getVideoId(){

		$id = $this->getDetailsValue("videoId");

		return $id;
	}

	/**
	 * Get the video date.
	 *
	 * @param string $format
	 *
	 * @return string
	 */
	public function getVideoDate($format){

		$date = $this->getDetailsValue("videoPublishedAt");
		$time = strtotime($date);
		$date = uelm_date($format, $time);

		return $date;
	}

	/**
	 * Get the video URL.
	 *
	 * @return string
	 */
	public function getVideoUrl(){

		$id = $this->getVideoId();
		$url = "https://youtube.com/watch?v=$id";

		return $url;
	}

	/**
	 * Get the snippet value.
	 *
	 * @param string $key
	 * @param mixed $fallback
	 *
	 * @return mixed
	 */
	private function getSnippetValue($key, $fallback = null){

		$snippet = $this->getAttribute("snippet");
		$value = UELM_UniteFunctionsUC::getVal($snippet, $key, $fallback);

		return $value;
	}

	/**
	 * Get the details value.
	 *
	 * @param string $key
	 * @param mixed $fallback
	 *
	 * @return mixed
	 */
	private function getDetailsValue($key, $fallback = null){

		$details = $this->getAttribute("contentDetails");
		$value = UELM_UniteFunctionsUC::getVal($details, $key, $fallback);

		return $value;
	}

}
