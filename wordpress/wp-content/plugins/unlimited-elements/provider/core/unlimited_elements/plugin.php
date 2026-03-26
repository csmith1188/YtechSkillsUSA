<?php
/**
 * @package unlimited elements plugin
 * @author UniteCMS http://unitecms.net
 * @copyright Copyright (c) 2017 UniteCMS
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

//no direct accees
defined ('UELM_UNLIMITED_ELEMENTS_INC') or die ('restricted aceess');

class UELM_UnlimitedElementsPluginUC extends UELM_UniteCreatorPluginBase{
	
	protected $extraInitParams = array();
	
	private $version = "1.0";
	private $pluginName = "uelm_plugin";
	private $title;
	private $description;

	
	
	/**
	 * constructor
	 */
	public function __construct(){
		
		$pathPlugin = dirname(__FILE__)."/";
		
		parent::__construct($pathPlugin);
		
		$this->title = "Unlimited Elements Blocks Library";	//can't translate here
		$this->description = "Create and use widgets for Elementor Page Builder";
						
		$this->init();
	}
	
	/**
	 * run admin
	 */
	public function runAdmin(){
		
		$this->includeCommonFiles();
		$this->runCommonActions();
		
		require_once UELM_GlobalsUC::$pathPlugin . "unitecreator_admin.php";
		require_once UELM_GlobalsUC::$pathProvider . "provider_admin.class.php";
		require_once $this->pathPlugin . "provider_core_admin.class.php";
		require_once $this->pathPlugin . "dialog_param_elementor.class.php";
		
		$uelm_mainFilepath = UELM_GlobalsUC::$pathPlugin."unlimited_elements.php";
		
		new UELM_UniteProviderCoreAdminUC_Elementor($uelm_mainFilepath);
		
	}

	
	/**
	 * run front 
	 */
	public function runFront(){
				
		$this->includeCommonFiles();
		$this->runCommonActions();
				
		require_once UELM_GlobalsUC::$pathProvider . "provider_front.class.php";
		require_once $this->pathPlugin . "provider_core_front.class.php";
		
		$uelm_mainFilepath = UELM_GlobalsUC::$pathPlugin."unlimited_elements.php";
						
		new UELM_UniteProviderCoreFrontUC_Elementor($uelm_mainFilepath);
		
	}
	
	/**
	 * include elementor common files
	 */
	private function includeCommonFiles_elementor(){
		
		require_once $this->pathPlugin . 'elementor/elementor_integrate.class.php';
		require_once $this->pathPlugin . "elementor/elementor_controls.class.php";
		
		if(is_admin()){
			require_once $this->pathPlugin . 'elementor/elementor_layout_exporter.class.php';
		}
		
	}
	
	/**
	 * include gutenberg common files
	 */
	private function includeCommonFiles_gutenberg(){
		
		require_once $this->pathPlugin . 'gutenberg/gutenberg_integrate.class.php';
	}
	
	
	/**
	 * include files
	 */
	protected function includeCommonFiles(){
		
		require_once $this->pathPlugin . 'globals.class.php';
		require_once $this->pathPlugin . 'addontype_elementor.class.php';
		require_once $this->pathPlugin . 'addontype_elementor_template.class.php';
		require_once $this->pathPlugin . 'helper_provider_core.class.php';
		
		if(UELM_GlobalsUnlimitedElements::$enableElementorSupport)
			$this->includeCommonFiles_elementor();
		
		
		if(UELM_GlobalsUnlimitedElements::$enableGutenbergSupport)
			$this->includeCommonFiles_gutenberg();
				
	}
	

	
	/**
	 * check and load elementor integration
	 */
	private function checkLoadElementor(){
				
		// Notice if the Elementor is not active
		if ( ! did_action( 'elementor/loaded' ) )
			return;
			
		if(UELM_GlobalsUnlimitedElements::$enableElementorSupport == false)
			return(false);
			
		$objIntegrate = new UELM_UniteCreatorElementorIntegrate();
		$objIntegrate->initElementorIntegration();
		
	}
	
	
	/**
	 * check and load gutenberg
	 */
	private function checkLoadGutenberg(){
		
		if(UELM_GlobalsUnlimitedElements::$enableGutenbergSupport == false)
			return(false);
		
		$isEnableGutenberg = UELM_HelperProviderCoreUC_EL::getGeneralSetting("gut_enable");
		$isEnableGutenberg = UELM_UniteFunctionsUC::strToBool($isEnableGutenberg);
		
		if($isEnableGutenberg == false)
			return(false);

		$gutenbergIntegrate = UELM_UniteCreatorGutenbergIntegrate::getInstance();
		$gutenbergIntegrate->init();
		
	}
	
	
	/**
	 * on plugins loaded
	 */
	public function onPluginsLoaded(){
		
		//register elementor template addon type
		
		$objAddonTypeElementorTempalte = new UELM_UniteCreatorAddonType_Elementor_Template();
		$this->registerAddonType(UELM_GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR_TEMPLATE, $objAddonTypeElementorTempalte);
		
		if(defined("UELM_UC_BOTH_VERSIONS_ACTIVE")){
			UELM_HelperUC::addAdminNotice("Both unlimited elements plugins, FREE and PRO are active! Please uninstall FREE version.");
		}
		
		//init integrations
		
		$objIntegrations = new UELM_UniteCreatorPluginIntegrations();
		$objIntegrations->initPluginIntegrations();
		
		
		$this->checkLoadElementor();
		
		$this->checkLoadGutenberg();
	}
	
	
	/**
	 * modify plugin variables if the plugin is not default (blox)
	 */
	private function modifyPluginVariables(){
				
		$pluginName = UELM_GlobalsUnlimitedElements::PLUGIN_NAME;
		
		UELM_GlobalsUC::$url_component_admin = admin_url()."admin.php?page={$pluginName}";
		UELM_GlobalsUC::$url_component_client = UELM_GlobalsUC::$url_component_admin;
		UELM_GlobalsUC::$url_component_admin_nowindow = UELM_GlobalsUC::$url_component_admin."&ucwindow=blank";
	}
	
	
	/**
	 * run common actions
	 */
	protected function runCommonActions(){
		
		$this->addAction("plugins_loaded", "onPluginsLoaded");
		$this->modifyPluginVariables();
		
		//register elementor addon type
		
		$objAddonTypeElementor = new UELM_UniteCreatorAddonType_Elementor();
		$this->registerAddonType(UELM_GlobalsUnlimitedElements::ADDONSTYPE_ELEMENTOR, $objAddonTypeElementor);
				
	}
	
	
	/**
	 * init the plugin
	 */
	protected function init(){
		
		$this->register($this->pluginName, $this->title, $this->version, $this->description, $this->extraInitParams);
		
	}
	
}


//run the plugin
new UELM_UnlimitedElementsPluginUC();
		
