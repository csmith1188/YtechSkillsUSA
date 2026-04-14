<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS http://unitecms.net
 * @copyright Copyright (c) 2016 UniteCMS
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

abstract class UELM_UCAdminNotices{

	const NOTICES_DISPLAY_LIMIT = 2;

	private static $initialized = false;

	/**
	 * init
	 */
	public static function init($notices){

		$shouldInitialize = self::shouldInitialize();

		if($shouldInitialize === false)
			return;

		self::initializeOptions();

		self::registerNotices($notices);
		self::registerHooks();

		self::checkDismissAction();

		self::$initialized = true;
	}

	/**
	 * display notices
	 */
	public static function displayNotices(){

		$notices = UELM_UCAdminNoticesManager::getNotices();
		$displayedCount = 0;

		foreach($notices as $notice){
			$isDebug = $notice->isDebug();

			if($isDebug === true){
				$noticeHtml = $notice->getHtml();
				uelm_echo($noticeHtml);

				continue;
			}

			if($displayedCount >= self::NOTICES_DISPLAY_LIMIT)
				return;

			$isDisplayable = $notice->shouldDisplay();

			if($isDisplayable === false)
				continue;

			$displayedCount++;

			$noticeHtml = $notice->getHtml();

			uelm_echo($noticeHtml);
		}
	}

	/**
	 * enqueue assets
	 */
	public static function enqueueAssets(){

		UELM_HelperUC::addStyleAbsoluteUrl(UELM_GlobalsUC::$url_provider . 'assets/admin_notices.css', 'uc_admin_notices');
		UELM_HelperUC::addScriptAbsoluteUrl(UELM_GlobalsUC::$url_provider . 'assets/admin_notices.js', 'uc_admin_notices');
	}

	/**
	 * check if notices need to be initialized
	 */
	private static function shouldInitialize(){

		if(self::$initialized === true)
			return false;

		if(UELM_GlobalsUC::$is_admin === false)
			return false;

		if(current_user_can('administrator') === false)
			return false;

		return true;
	}

	/**
	 * initialize options
	 */
	private static function initializeOptions(){

		// Set plugin installation time
		$installTime = UELM_UCAdminNoticesOptions::getOption('install_time');

		if(empty($installTime))
			UELM_UCAdminNoticesOptions::setOption('install_time', time());
	}

	/**
	 * check for dismiss action
	 */
	private static function checkDismissAction(){

		$id = UELM_UniteFunctionsUC::getPostGetVariable('uc_dismiss_notice', '', UELM_UniteFunctionsUC::SANITIZE_KEY);

		if(empty($id))
			return;

		UELM_UCAdminNoticesManager::dismissNotice($id);
	}

	/**
	 * register notices
	 */
	private static function registerNotices($notices){

		foreach($notices as $notice){
			UELM_UCAdminNoticesManager::addNotice($notice);
		}
	}

	/**
	 * register hooks
	 */
	private static function registerHooks(){

		UELM_UniteProviderFunctionsUC::addFilter('admin_notices', array(self::class, 'displayNotices'), 10, 3);
		UELM_UniteProviderFunctionsUC::addAction('admin_enqueue_scripts', array(self::class, 'enqueueAssets'));
	}

}
