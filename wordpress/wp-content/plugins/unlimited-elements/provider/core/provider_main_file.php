<?php 

try{
	
	//-------------------------------------------------------------
	
	//load core plugins
	
	$pathCorePlugins = dirname(__FILE__)."/";
	
	$pathUnlimitedElementsPlugin = $pathCorePlugins."unlimited_elements/plugin.php";
		require_once $pathUnlimitedElementsPlugin;
	
	$pathCreateAddonsPlugin = $pathCorePlugins."create_addons/plugin.php";
		require_once $pathCreateAddonsPlugin;
	
	if(is_admin() || (defined('WP_CLI') && WP_CLI) ){		//load admin part
		
		do_action(UELM_GlobalsProviderUC::ACTION_RUN_ADMIN);
		
		
	}else{		//load front part
		
		do_action(UELM_GlobalsProviderUC::ACTION_RUN_FRONT);
		
	}

	
	}catch(Exception $e){
		$message = $e->getMessage();
		$trace = $e->getTraceAsString();
		uelm_echo( "Error: <b>".$message."</b>");
		
		if(UELM_GlobalsUC::$SHOW_TRACE == true)
			uelm_dmp($trace);
	}