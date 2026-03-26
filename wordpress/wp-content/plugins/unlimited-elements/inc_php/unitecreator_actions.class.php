<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteCreatorActions{


	/**
	 * on update layout response, function for override
	 */
	protected function onUpdateLayoutResponse($response){

		$isUpdate = $response["is_update"];

		//create
		if($isUpdate == false){
			UELM_HelperUC::ajaxResponseData($response);
		}else{
			//update

			$message = $response["message"];
			$pageName = UELM_UniteFunctionsUC::getVal($response, "page_name");

			$arrData = array();
			if(!empty($pageName))
				$arrData["page_name"] = $pageName;

			UELM_HelperUC::ajaxResponseSuccess($message, $arrData);
		}
	}

	/**
	 * get data array from request
	 */
	private function getDataFromRequest(){

		$data = UELM_UniteFunctionsUC::getPostGetVariable("data", "", UELM_UniteFunctionsUC::SANITIZE_NOTHING);
		if(empty($data))
			$data = $_REQUEST;

		if(is_string($data)){
			$arrData = json_decode($data,true);

			if(empty($arrData)){
				$arrData = stripslashes(trim($data));
				$arrData = json_decode($arrData, true);
			}

			$data = $arrData;
		}

		return ($data);
	}

	/**
	 * on ajax action
	 */
	public function onAjaxAction(){

		if(UELM_GlobalsUC::$inDev == true || UELM_GlobalsUC::$debugAjaxErrors == true){

			ini_set("display_errors", "on");
			error_reporting(E_ALL);
		}

		$actionType = UELM_UniteFunctionsUC::getPostGetVariable("action", "", UELM_UniteFunctionsUC::SANITIZE_KEY);

		if($actionType != UELM_GlobalsUC::PLUGIN_NAME . "_ajax_action")
			return (false);

		$action = UELM_UniteFunctionsUC::getPostGetVariable("client_action", "", UELM_UniteFunctionsUC::SANITIZE_KEY);

		UELM_GlobalsUC::$ajaxAction = $action;

		$operations = new UELM_ProviderOperationsUC();
		$addons = new UELM_UniteCreatorAddons();
		$assets = new UELM_UniteCreatorAssetsWork();
		$categories = new UELM_UniteCreatorCategories();
		$layouts = new UELM_UniteCreatorLayouts();
		$webAPI = new UELM_UniteCreatorWebAPI();

		$data = $this->getDataFromRequest();
		$addonType = $addons->getAddonTypeFromData($data);

		$data = UELM_UniteProviderFunctionsUC::normalizeAjaxInputData($data);

		try{

			//protection - it's intended to logged in users only with the capabilities defined in the plugin

			$nonce = UELM_UniteFunctionsUC::getPostGetVariable("nonce", "", UELM_UniteFunctionsUC::SANITIZE_NOTHING);
			UELM_UniteProviderFunctionsUC::verifyNonce($nonce);


			switch($action){
				case "remove_category":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $categories->removeFromData($data);
					
					UELM_HelperUC::ajaxResponseSuccess(esc_html__("The category deleted successfully", "unlimited-elements"), $response);
				break;
				case "update_category":

					UELM_HelperProviderUC::verifyAdminPermission();

					$categories->updateFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Category updated", "unlimited-elements"));
				break;
				case "update_cat_order":

					UELM_HelperProviderUC::verifyAdminPermission();

					$categories->updateOrderFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Order updated", "unlimited-elements"));
				break;
				case "get_category_settings_html":

					$manager = UELM_UniteCreatorManager::getObjManagerByAddonType($addonType);
					$response = $manager->getCatSettingsHtmlFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "get_cat_addons":

					$manager = UELM_UniteCreatorManager::getObjManagerByAddonType($addonType, $data);
					$response = $manager->getCatAddonsHtmlFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "get_layouts_params_settings_html":

					$manager = UELM_UniteCreatorManager::getObjManagerByAddonType($addonType, $data);
					$response = $manager->getAddonPropertiesDialogHtmlFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "get_catlist":

					$manager = UELM_UniteCreatorManager::getObjManagerByAddonType($addonType, $data);
					$response = $manager->getCatListFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "get_layouts_categories":
					$response = $categories->getLayoutsCatsListFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "get_addon_changelog":

					UELM_HelperProviderUC::verifyAdminPermission();
					UELM_HelperProviderUC::verifyAddonChangelogEnabled();

					$response = $operations->getAddonChangelogFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "add_addon_changelog":

					UELM_HelperProviderUC::verifyAdminPermission();
					UELM_HelperProviderUC::verifyAddonChangelogEnabled();

					$addons->addAddonChangelog($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Log added.", "unlimited-elements"));
				break;
				case "update_addon_changelog":

					UELM_HelperProviderUC::verifyAdminPermission();
					UELM_HelperProviderUC::verifyAddonChangelogEnabled();

					$addons->updateAddonChangelog($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Log updated.", "unlimited-elements"));
				break;
				case "delete_addon_changelog":

					UELM_HelperProviderUC::verifyAdminPermission();
					UELM_HelperProviderUC::verifyAddonChangelogEnabled();

					$addons->deleteAddonChangelog($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Log deleted.", "unlimited-elements"));
				break;
				case "get_addon_revisions":

					UELM_HelperProviderUC::verifyAdminPermission();
					UELM_HelperProviderUC::verifyAddonRevisionsEnabled();

					$response = $operations->getAddonRevisionsFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "create_addon_revision":

					UELM_HelperProviderUC::verifyAdminPermission();
					UELM_HelperProviderUC::verifyAddonRevisionsEnabled();

					$addons->createAddonRevision($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Revision created.", "unlimited-elements"));
				break;
				case "restore_addon_revision":

					UELM_HelperProviderUC::verifyAdminPermission();
					UELM_HelperProviderUC::verifyAddonRevisionsEnabled();

					$response = $addons->restoreAddonRevision($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Revision restored.", "unlimited-elements"), $response);
				break;
				case "download_addon_revision":

					UELM_HelperProviderUC::verifyAdminPermission();
					UELM_HelperProviderUC::verifyAddonRevisionsEnabled();

					$addons->downloadAddonRevision($data);
					exit;
				break;
				case "update_addon":

					UELM_HelperProviderUC::verifyAdminPermission();

					$addons->updateAddonFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Updated.", "unlimited-elements"));
				break;
				case "get_addon_bulk_dialog":

					$response = $operations->getAddonBulkDialogFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "update_addons_bulk":

					UELM_HelperProviderUC::verifyAdminPermission();

					$addons->updateAddonsBulkFromData($data);
					$response = $operations->getAddonBulkDialogFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "delete_addon":

					UELM_HelperProviderUC::verifyAdminPermission();
			
					$addons->deleteAddonFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("The addon deleted successfully", "unlimited-elements"));
				break;
				case "add_category":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $categories->addFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "add_addon":

					UELM_HelperProviderUC::verifyAdminPermission();

					if(UELM_GlobalsUC::$permisison_add === false)
						UELM_UniteFunctionsUC::throwError("Operation not permitted");

					$response = $addons->createFromManager($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Widget added successfully", "unlimited-elements"), $response);
				break;
				case "update_addon_title":

					UELM_HelperProviderUC::verifyAdminPermission();

					$addons->updateAddonTitleFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Widget updated successfully", "unlimited-elements"));
				break;
				case "update_addons_activation":

					UELM_HelperProviderUC::verifyAdminPermission();

					$addons->activateAddonsFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Widgets updated successfully", "unlimited-elements"));
				break;
				case "remove_addons":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $addons->removeAddonsFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Widgets Removed", "unlimited-elements"), $response);
				break;
				case "update_addons_order":
				
					UELM_HelperProviderUC::verifyAdminPermission();

					$addons->saveOrderFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Order Saved", "unlimited-elements"));
				break;
				case "update_layouts_order":

					UELM_HelperProviderUC::verifyAdminPermission();

					$layouts->updateOrderFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Order Saved", "unlimited-elements"));
				break;
				case "move_addons":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $addons->moveAddonsFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Done Operation", "unlimited-elements"), $response);
				break;
				case "duplicate_addons":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $addons->duplicateAddonsFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Duplicated Successfully", "unlimited-elements"), $response);
				break;
				case "get_addon_config_html":  //from elementor

					$response = $addons->getAddonConfigHTML($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "get_addon_settings_html":    //from elementor/gutenberg
					
					$html = $addons->getAddonSettingsHTMLFromData($data);
					
					UELM_HelperUC::ajaxResponseData(array("html" => $html));
				break;
				case "get_addon_item_settings_html":  //from elementor

					$html = $addons->getAddonItemsSettingsHTMLFromData($data);

					UELM_HelperUC::ajaxResponseData(array("html" => $html));
				break;
				case "get_addon_editor_data":  //from elementor

					$response = $addons->getAddonEditorData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "get_addon_output_data":  //from elementor editor bg/gutenberg
					
					$response = $addons->getAddonOutputData($data, true);
					
					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "show_preview":

					$addons->showAddonPreviewFromData($data);
					exit;
				break;
				case "save_addon_defaults":

					UELM_HelperProviderUC::verifyAdminPermission();

					$addons->saveAddonDefaultsFromData($data);
					
					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Saved", "unlimited-elements"));
				break;
				case "save_test_addon":

					UELM_HelperProviderUC::verifyAdminPermission();

					$addons->saveTestAddonData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Saved", "unlimited-elements"));
				break;
				case "get_test_addon_data":
					
					UELM_HelperProviderUC::verifyAdminPermission();
					
					$response = $addons->getTestAddonData($data);
					
					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "delete_test_addon_data":
					//Security Update 3
					UELM_HelperProviderUC::verifyAdminPermission();
					
					$addons->deleteTestAddonData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Test data deleted", "unlimited-elements"));
				break;
				case "export_addon":
										
					UELM_HelperProviderUC::verifyAdminPermission();

					$addons->exportAddon($data);
					exit;
				break;
				case "export_cat_addons":

					UELM_HelperProviderUC::verifyAdminPermission();

					$addons->exportCatAddons($data);
				break;
				case "import_addons":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $addons->importAddons($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Addons Imported", "unlimited-elements"), $response);
				break;
				case "import_layouts":

					UELM_HelperProviderUC::verifyAdminPermission();

					$urlRedirect = $layouts->importLayouts($data);

					if(!empty($urlRedirect))
						UELM_HelperUC::ajaxResponseSuccessRedirect(UELM_HelperUC::getText("layout_imported"), $urlRedirect);
					else
						UELM_HelperUC::ajaxResponseSuccess(UELM_HelperUC::getText("layout_imported"));

				break;
				case "get_image_url":
					
					$id = UELM_UniteFunctionsUC::getVal($data, "id");
					$size = UELM_UniteFunctionsUC::getVal($data, "size", "full");
					$url = UELM_UniteProviderFunctionsUC::getImageUrlFromImageID($id, $size);
					
					UELM_HelperUC::ajaxResponseData(array("url" => $url));
				break;
				case "get_version_text":

					$content = UELM_HelperHtmlUC::getVersionText();

					UELM_HelperUC::ajaxResponseData(array("text" => $content));
				break;
				case "update_plugin":

					UELM_HelperProviderUC::verifyAdminPermission();

					if(method_exists("UELM_UniteProviderFunctionsUC", "updatePlugin"))
						UELM_UniteProviderFunctionsUC::updatePlugin();
					else{
						echo "Functionality Don't Exists";
						exit;
					}

				break;
				case "update_general_settings":

					UELM_HelperProviderUC::verifyAdminPermission();

					$operations->updateGeneralSettingsFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Settings Saved", "unlimited-elements"));
				break;
				case "update_global_layout_settings":

					UELM_HelperProviderUC::verifyAdminPermission();

					UELM_UniteCreatorLayout::updateLayoutGlobalSettingsFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Settings Saved", "unlimited-elements"));
				break;
				case "update_layout":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $layouts->updateLayoutFromData($data);

					$this->onUpdateLayoutResponse($response);
				break;
				case "update_layout_category":

					UELM_HelperProviderUC::verifyAdminPermission();

					$layouts->updateLayoutCategoryFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Category Updated", "unlimited-elements"));
				break;
				case "update_layout_params":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $layouts->updateParamsFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Layout Updated", "unlimited-elements"), $response);
				break;
				case "delete_layout":

					UELM_HelperProviderUC::verifyAdminPermission();

					$layouts->deleteLayoutFromData($data);

					$urlLayouts = UELM_HelperUC::getViewUrl_LayoutsList();

					UELM_HelperUC::ajaxResponseSuccessRedirect(UELM_HelperUC::getText("layout_deleted"), $urlLayouts);
				break;
				case "export_layout":

					UELM_HelperProviderUC::verifyAdminPermission();

					$layouts->exportLayout($data);
					exit;
				break;
				case "activate_product":

					UELM_HelperProviderUC::verifyAdminPermission();

					$expireDays = $webAPI->activateProductFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Product Activated", "unlimited-elements"), array("expire_days" => $expireDays));
				break;
				case "deactivate_product":

					UELM_HelperProviderUC::verifyAdminPermission();

					$webAPI->deactivateProduct($data);

					UELM_HelperUC::ajaxResponseSuccess("Product Deactivated, please refresh the page");
				break;
				case "check_catalog":

					$isForce = UELM_UniteFunctionsUC::getVal($data, "force");
					$isForce = UELM_UniteFunctionsUC::strToBool($isForce);

					$response = $webAPI->checkUpdateCatalog($isForce);

					$operations->checkInstagramRenewToken();

					do_action("uelm_on_check_catalog_ajax_action");

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "install_catalog_addon":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $webAPI->installCatalogAddonFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Widget Installed", "unlimited-elements"), $response);
				break;
				case "install_catalog_page":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $webAPI->installCatalogPageFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Template Installed", "unlimited-elements"), $response);
				break;
				case "update_addon_from_catalog":  //by id

					UELM_HelperProviderUC::verifyAdminPermission();

					$urlRedirect = $addons->updateAddonFromCatalogFromData($data);

					if(!empty($urlRedirect))
						UELM_HelperUC::ajaxResponseSuccessRedirect(esc_html__("Widget Updated", "unlimited-elements"), $urlRedirect);
					else
						UELM_HelperUC::ajaxResponseSuccess(esc_html__("Widget Updated", "unlimited-elements"));

				break;
				case "save_screenshot":

					$response = $operations->saveScreenshotFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Screenshot Saved", "unlimited-elements"), $response);
				break;
				case "save_section_tolibrary":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $layouts->saveSectionToLibraryFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Section Saved", "unlimited-elements"), $response);
				break;
				case "get_grid_import_layout_data":

					$response = $layouts->getLayoutGridDataForEditor($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "save_custom_settings":

					UELM_HelperProviderUC::verifyAdminPermission();

					$operations->updateCustomSettingsFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Settings Saved", "unlimited-elements"));
				break;
				case "get_link_autocomplete":
					$response = $operations->getLinkAutocompleteFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "get_users_list_forselect":
					$arrUsersList = $operations->getUsersListForSelectFromData($data);

					UELM_HelperUC::ajaxResponseData($arrUsersList);
				break;
				case "get_terms_list_forselect":
					
					$arrTermsList = $operations->getTermsListForSelectFromData($data);

					UELM_HelperUC::ajaxResponseData($arrTermsList);
				break;
				case "get_posts_list_forselect":
					$arrPostList = $operations->getPostListForSelectFromData($data);

					UELM_HelperUC::ajaxResponseData($arrPostList);
				break;
				case "get_select2_post_titles":
					$arrData = $operations->getSelect2PostTitles($data);

					UELM_HelperUC::ajaxResponseData(array("select2_data" => $arrData));
				break;
				case "get_select2_terms_titles":
					$arrData = $operations->getSelect2TermsTitles($data);

					UELM_HelperUC::ajaxResponseData(array("select2_data" => $arrData));
				break;
				case "get_select2_users_titles":
					$arrData = $operations->getSelect2UsersTitles($data);

					UELM_HelperUC::ajaxResponseData(array("select2_data" => $arrData));
				break;
				case "get_post_child_params":

					$response = $operations->getPostAttributesFromData($data);

					UELM_HelperUC::ajaxResponseData($response);
				break;
				case "import_elementor_catalog_template":

					UELM_HelperProviderUC::verifyAdminPermission();

					$response = $webAPI->installCatalogTemplateFromData($data);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Template Imported", "unlimited-elements"), $response);
				break;
				case "save_instagram_connect_data":
				
					UELM_HelperProviderUC::verifyAdminPermission();

					$objServices = new UELM_UniteServicesUC();
					$objServices->includeInstagramAPI();

					UELM_HelperInstaUC::saveInstagramConnectDataAjax($data);
				break;
				case "renew_instagram_access_token":

					UELM_HelperProviderUC::verifyAdminPermission();

					$objServices = new UELM_UniteServicesUC();
					$objServices->includeInstagramAPI();

					UELM_HelperInstaUC::renewAccessToken();
					UELM_HelperInstaUC::redirectToGeneralSettings();
				break;
				case "save_google_connect_data":
					
					UELM_HelperProviderUC::verifyAdminPermission();

					$objServices = new UELM_UniteServicesUC();
					$objServices->includeGoogleAPI();
					
					try{
						$params = array();
						$error = UELM_UniteFunctionsUC::getVal($data, "error");

						if(empty($error) === false)
							UELM_UniteFunctionsUC::throwError($error);

						UELM_GoogleAPIHelper::saveCredentials($data);
						
					}catch(Exception $exception){
						$params = array("google_connect_error" => $exception->getMessage());
					}

					UELM_GoogleAPIHelper::redirectToSettings($params);
				break;
				case "remove_google_connect_data":
					
					UELM_HelperProviderUC::verifyAdminPermission();

					$objServices = new UELM_UniteServicesUC();
					$objServices->includeGoogleAPI();

					try{
						$params = array();
						$error = UELM_UniteFunctionsUC::getVal($data, "error");

						if(empty($error) === false)
							UELM_UniteFunctionsUC::throwError($error);

						UELM_GoogleAPIHelper::removeCredentials();
					}catch(Exception $exception){
						$params = array("google_connect_error" => $exception->getMessage());
					}

					UELM_GoogleAPIHelper::redirectToSettings($params);
				break;
				case "dismiss_notice":

					UELM_UCAdminNoticesManager::dismissNotice($data['id']);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Notice Dismissed", "unlimited-elements"));
				break;
				case "postpone_notice":

					UELM_UCAdminNoticesManager::postponeNotice($data['id'], $data['duration']);

					UELM_HelperUC::ajaxResponseSuccess(esc_html__("Notice Postponed", "unlimited-elements"));
				break;
				default:

					//check assets
					$found = $assets->checkAjaxActions($action, $data);

					if(!$found)
						$found = UELM_UniteProviderFunctionsUC::applyFilters(UELM_UniteCreatorFilters::FILTER_ADMIN_AJAX_ACTION, $found, $action, $data);

					if(!$found)
						UELM_HelperUC::ajaxResponseError("wrong ajax action: <b>$action</b> ");
				break;
			}
		}catch(Exception $e){
			$errorMessage = $e->getMessage();

			if(UELM_GlobalsUC::$SHOW_TRACE === true){
				$trace = $e->getTraceAsString();
				$errorMessage .= "<pre>" . $trace . "</pre>";
			}

			UELM_HelperUC::ajaxResponseError($errorMessage);
		}

		//it's an ajax action, so exit
		UELM_HelperUC::ajaxResponseError("No response output on <b> $action </b> action. please check with the developer.");
		exit;
	}

	/**
	 * on ajax action
	 */
	public function onAjaxFrontAction(){

		$actionType = UELM_UniteFunctionsUC::getPostGetVariable("action", "", UELM_UniteFunctionsUC::SANITIZE_KEY);

		if($actionType != UELM_GlobalsUC::PLUGIN_NAME . "_ajax_action")
			return (false);

		$action = UELM_UniteFunctionsUC::getPostGetVariable("client_action", "", UELM_UniteFunctionsUC::SANITIZE_KEY);
		$data = $this->getDataFromRequest();

		try{
			//switch($action){}
		}catch(Exception $e){
			$message = $e->getMessage();
			$errorMessage = $message;

			if(UELM_GlobalsUC::$SHOW_TRACE == true){
				$trace = $e->getTraceAsString();
				$errorMessage = $message . "<pre>" . $trace . "</pre>";
			}

			UELM_HelperUC::ajaxResponseError($errorMessage);
		}

		//it's an ajax action, so exit
		UELM_HelperUC::ajaxResponseError("No response output on <b> $action </b> action. please check with the developer.");
		exit();
	}

}
