<?php

class UELM_GoogleAPIHelper{

	const AUTH_URL = "https://accounts.google.com/o/oauth2/v2/auth";

	const SCOPE_CALENDAR_EVENTS = "https://www.googleapis.com/auth/calendar.events.readonly";
	const SCOPE_SHEETS_ALL = "https://www.googleapis.com/auth/spreadsheets";
	const SCOPE_USER_EMAIL = "https://www.googleapis.com/auth/userinfo.email";
	const SCOPE_YOUTUBE = "https://www.googleapis.com/auth/youtube.readonly";
	
	private static $credentials = array();

	/**
	 * Get the API key.
	 *
	 * @return string
	 */
	public static function getApiKey(){

		$apiKey = UELM_HelperProviderCoreUC_EL::getGeneralSetting("google_api_key");

		return $apiKey;
	}

	/**
	 * Get the access token.
	 *
	 * @return string
	 */
	public static function getAccessToken(){

		$credentials = self::getCredentials();
		$accessToken = UELM_UniteFunctionsUC::getVal($credentials, "access_token");

		return $accessToken;
	}

	/**
	 * Get the fresh access token.
	 *
	 * @return string
	 * @throws Exception
	 */
	public static function getFreshAccessToken(){
		
		if(self::isAccessTokenExpired() === true)
			self::refreshAccessToken();

		$accessToken = self::getAccessToken();

		return $accessToken;
	}

	/**
	 * Get the user's email address.
	 *
	 * @return string
	 */
	public static function getUserEmail(){

		$credentials = self::getCredentials();
		$user = UELM_UniteFunctionsUC::getVal($credentials, "user", array());
		$email = UELM_UniteFunctionsUC::getVal($user, "email");

		return $email;
	}

	/**
	 * Get the authorization URL.
	 *
	 * @return string
	 */
	public static function getAuthUrl(){

		$returnUrl = UELM_HelperUC::getUrlAjax("save_google_connect_data");
		$returnUrl = UELM_UniteFunctionsUC::encodeContent($returnUrl);
		
		$params = array(
			"client_id" => UELM_GlobalsUnlimitedElements::GOOGLE_CONNECTION_CLIENTID,
			"redirect_uri" => UELM_GlobalsUnlimitedElements::GOOGLE_CONNECTION_URL,
			"scope" => implode(" ", self::getScopes()),
			"access_type" => "offline",
			"prompt" => "consent select_account",
			"response_type" => "code",
			"include_granted_scopes" => "true",
			"state" => $returnUrl,
		);

		$url = self::AUTH_URL . "?" . http_build_query($params);

		return $url;
	}

	/**
	 * Get the revoke URL.
	 *
	 * @return string
	 */
	public static function getRevokeUrl(){

		$returnUrl = UELM_HelperUC::getUrlAjax("remove_google_connect_data");
		$returnUrl = UELM_UniteFunctionsUC::encodeContent($returnUrl);
		
		$params = array(
			"revoke_token" => self::getAccessToken(),
			"state" => $returnUrl,
			"time" => time(),
		);

		$url = UELM_GlobalsUnlimitedElements::GOOGLE_CONNECTION_URL . "?" . http_build_query($params);

		return $url;
	}

	/**
	 * Save the credentials.
	 *
	 * @param array $data
	 *
	 * @return void
	 * @throws Exception
	 */
	public static function saveCredentials($data){
		
		$accessToken = UELM_UniteFunctionsUC::getVal($data, "access_token");
		$refreshToken = UELM_UniteFunctionsUC::getVal($data, "refresh_token");
		$expiresAt = UELM_UniteFunctionsUC::getVal($data, "expires_at", 0);
		$scopes = UELM_UniteFunctionsUC::getVal($data, "scopes", array());
		$user = UELM_UniteFunctionsUC::getVal($data, "user", array());

		UELM_UniteFunctionsUC::validateNotEmpty($accessToken, "access_token");
		UELM_UniteFunctionsUC::validateNotEmpty($refreshToken, "refresh_token");
		UELM_UniteFunctionsUC::validateNotEmpty($expiresAt, "expires_at");
		UELM_UniteFunctionsUC::validateNotEmpty($scopes, "scopes");
		UELM_UniteFunctionsUC::validateNotEmpty($user, "user");
		
		//validate email
		$email = UELM_UniteFunctionsUC::getVal($user, "email");
		
		if(!empty($email))
			UELM_UniteFunctionsUC::validateEmail($email, "Google Connect Email");
		
		self::validateScopes($scopes);

		$credentials = array(
			"access_token" => $accessToken,
			"refresh_token" => $refreshToken,
			"expires_at" => $expiresAt,
			"scopes" => $scopes,
			"user" => $user,
		);

		self::setCredentials($credentials);
	}

	/**
	 * Remove the credentials.
	 *
	 * @return void
	 */
	public static function removeCredentials(){

		self::setCredentials(array());
	}

	/**
	 * Redirect to settings.
	 *
	 * @param array $params
	 *
	 * @return void
	 */
	public static function redirectToSettings($params = array()){

		$params = http_build_query($params) . "#tab=integrations";
		$url = UELM_HelperUC::getViewUrl(UELM_GlobalsUnlimitedElements::VIEW_SETTINGS_ELEMENTOR, $params);

		UELM_UniteFunctionsUC::redirectToUrl($url);
	}

	/**
	 * Get the scopes.
	 *
	 * @return array
	 */
	private static function getScopes(){

		$scopes = array(
			self::SCOPE_SHEETS_ALL,
			self::SCOPE_USER_EMAIL,
		);

		if(UELM_GlobalsUnlimitedElements::$enableGoogleCalendarScopes === true)
			$scopes[] = self::SCOPE_CALENDAR_EVENTS;

		if(UELM_GlobalsUnlimitedElements::$enableGoogleYoutubeScopes === true)
			$scopes[] = self::SCOPE_YOUTUBE;

		return $scopes;
	}

	/**
	 * Validate the scopes.
	 *
	 * @param array $scopes
	 *
	 * @return void
	 * @throws Exception
	 */
	private static function validateScopes($scopes){

		$requestedScopes = self::getScopes();
		$grantedScopes = array_intersect($requestedScopes, $scopes);
		
		if(count($grantedScopes) !== count($requestedScopes))
			UELM_UniteFunctionsUC::throwError("Required permissions are missing. Please grant all requested permissions.");
	}

	/**
	 * Get the credentials.
	 *
	 * @return array
	 */
	private static function getCredentials(){

		if(empty(self::$credentials) === true)
			self::$credentials = UELM_HelperProviderUC::getGoogleConnectCredentials();

		return self::$credentials;
	}


	/**
	 * Get the credentials.
	 *
	 * @return array
	 */
	public static function isCredentials(){
		if(empty(self::$credentials) === false)
			return true;

		return false;
	}



	/**
	 * Set the credentials.
	 *
	 * @param array $credentials
	 *
	 * @return void
	 */
	private static function setCredentials($credentials){

		self::$credentials = $credentials;

		UELM_HelperProviderUC::saveGoogleConnectCredentials(self::$credentials);
	}

	/**
	 * Merge the credentials.
	 *
	 * @param array $credentials
	 *
	 * @return void
	 */
	private static function mergeCredentials($credentials){

		$credentials = array_merge(self::getCredentials(), $credentials);

		self::setCredentials($credentials);
	}

	/**
	 * Get the refresh token.
	 *
	 * @return string
	 */
	private static function getRefreshToken(){
		
		$credentials = self::getCredentials();
		$refreshToken = UELM_UniteFunctionsUC::getVal($credentials, "refresh_token");

		return $refreshToken;
	}

	/**
	 * Get the expiration time.
	 *
	 * @return int
	 */
	private static function getExpirationTime(){

		$credentials = self::getCredentials();
		$expirationTime = UELM_UniteFunctionsUC::getVal($credentials, "expires_at", 0);

		return $expirationTime;
	}


	/**
	 * Get the expiration format date.
	 *
	 * @return int
	 */
	public static function getExpirationDate(){

		$expirationDate = self::getExpirationTime();

		$date_format = get_option('date_format');
		$time_format = get_option('time_format');

		$formatted_date = date_i18n($date_format, $expirationDate);
		$formatted_time = date_i18n($time_format, $expirationDate);

		return "$formatted_date $formatted_time";

	}

	/**
	 * Determine if the access token is expired.
	 *
	 * @return bool
	 */
	public static function isAccessTokenExpired(){

		$accessToken = self::getAccessToken();
		$expirationTime = self::getExpirationTime();

		if(empty($accessToken) === true)
			return true;

		// Check if the token expires in the next 30 seconds
		if($expirationTime - 30 < time())
			return true;

		return false;
	}

	/**
	 * Refresh the access token.
	 *
	 * @return void
	 * @throws Exception
	 */
	private static function refreshAccessToken(){
		
		$refreshToken = self::getRefreshToken();

		if(empty($refreshToken) === true)
			UELM_UniteFunctionsUC::throwError("Refresh token is missing.");

		$request = UELM_Http::make();
		$request->acceptJson();
	
		$response = $request->get(UELM_GlobalsUnlimitedElements::GOOGLE_CONNECTION_URL, array("refresh_token" => $refreshToken, "time" => time()));
		$data = $response->json();

		if(isset($data["error"]) === true)
			UELM_UniteFunctionsUC::throwError("Unable to refresh the access token: {$data["error"]}");

		$accessToken = UELM_UniteFunctionsUC::getVal($data, "access_token");
		$expiresAt = UELM_UniteFunctionsUC::getVal($data, "expires_at", 0);
		$scopes = UELM_UniteFunctionsUC::getVal($data, "scopes", array());

		UELM_UniteFunctionsUC::validateNotEmpty($accessToken, "access_token");
		UELM_UniteFunctionsUC::validateNotEmpty($expiresAt, "expires_at");
		UELM_UniteFunctionsUC::validateNotEmpty($scopes, "scopes");

		self::validateScopes($scopes);

		$credentials = array(
			"access_token" => $accessToken,
			"expires_at" => $expiresAt,
			"scopes" => $scopes,
		);

		self::mergeCredentials($credentials);
	}

}
