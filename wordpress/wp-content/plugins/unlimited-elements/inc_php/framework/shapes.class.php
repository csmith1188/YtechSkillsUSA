<?php
/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved. 
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 * */
if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UniteShapeManagerUC extends UELM_HtmlOutputBaseUC{
		
		private static $arrShapeDividersCache = array();
		private static $arrShapesCache = array();
		
		
		/**
		 * get output shapes by type
		 */
		private function getShapesOutputByType($arrAddons, $getType){
						
			$arrOutput = array();
			
			foreach($arrAddons as $addon){
				
				$alias = $addon->getAlias();
				
				switch($getType){
					case "picker":
						$title = $addon->getTitle();
						$arrOutput[$alias] = $title;
					break;
					case "short":
						$title = $addon->getTitle();
						$arrOutput[$title] = $alias;
					break;
					case "data":
						$svgContent = $addon->getHtml();
						$svgEncoded = base64_encode($svgContent);
						
						$arrOutput[$alias] = $svgEncoded;
					break;
					case "bgurl":
						$svgContent = $addon->getHtml();
						$svgEncoded = UELM_UniteFunctionsUC::encodeSVGForBGUrl($svgContent);
						
						$arrOutput[$alias] = $svgEncoded;
					break;
					case "svg":
						$svgContent = $addon->getHtml();
						$arrOutput[$alias] = $svgContent;
					break;
				}
			}
			
			return($arrOutput);
		}
		
		/**
		 * get shape deviders array
		 * getType - short / html
		 */
		public function getArrShapeDividers($getType = null){
			
			if(empty(self::$arrShapeDividersCache)){
				$objAddons = new UELM_UniteCreatorAddons();
				$params = array();
				$params["addontype"] = UELM_GlobalsUC::ADDON_TYPE_SHAPE_DEVIDER;
				
				self::$arrShapeDividersCache = $objAddons->getArrAddons("", $params);
			}
			
			if(empty($getType))
				return(self::$arrShapeDividersCache);
			
			$arrOutput = $this->getShapesOutputByType(self::$arrShapeDividersCache, $getType);
			
			
			return($arrOutput);
		}
		
		
		/**
		 * get shape deviders array
		 * getType - short / html
		 */
		public function getArrShapes($getType = null){
			
			if(empty(self::$arrShapesCache)){
				$objAddons = new UELM_UniteCreatorAddons();
				$params = array();
				$params["addontype"] = UELM_GlobalsUC::ADDON_TYPE_SHAPES;
				
				self::$arrShapesCache = $objAddons->getArrAddons("", $params);
			}
			
			if(empty($getType))
				return(self::$arrShapesCache);
			
			$arrOutput = self::getShapesOutputByType(self::$arrShapesCache, $getType);
			
			return($arrOutput);
		}
		
		
		/**
		 * get element shape evider html
		 */
		private function getElementShapeDividerHtml($position, $settings, $elementID){
			
			$enableKey = "enable_shape_devider_".$position;
			
			if(isset($settings[$enableKey])){
				$isEnable = UELM_UniteFunctionsUC::getVal($settings, "enable_shape_devider_".$position);
				$isEnable = UELM_UniteFunctionsUC::strToBool($isEnable);
				
				if($isEnable == false)
					return("");
			}
			
			$shapeType = UELM_UniteFunctionsUC::getVal($settings, "shape_devider_{$position}_type");
			
			if(empty($shapeType))
				return("");
			
			$class = "uc-shape-devider-{$position}";
			$selector = "#{$elementID} > .{$class}";
			
			$shapeColor = UELM_UniteFunctionsUC::getVal($settings, "shape_devider_{$position}_color");
			$isflip = UELM_UniteFunctionsUC::getVal($settings, "shape_devider_{$position}_flip");
			$isflip = UELM_UniteFunctionsUC::strToBool($isflip);
			$height = UELM_UniteFunctionsUC::getVal($settings, "shape_devider_{$position}_height");
			$placement = UELM_UniteFunctionsUC::getVal($settings, "shape_devider_{$position}_placement");
			$repeat = UELM_UniteFunctionsUC::getVal($settings, "shape_devider_{$position}_repeat");
			
			
			$arrShapes = self::getArrShapeDividers("svg");
			$shapeSVG = UELM_UniteFunctionsUC::getVal($arrShapes, $shapeType);
			
			if(empty($shapeSVG))
				return("");
			
			//replace color
			if(!empty($shapeColor)){
				$shapeSVG = str_replace('g fill="#ffffff"', 'g fill="'.$shapeColor.'"', $shapeSVG);
			}
						
			$shapeContent = UELM_UniteFunctionsUC::encodeSVGForBGUrl($shapeSVG);
			
			$arrCss = array();
			$arrCss["background-image"] = "url('{$shapeContent}')";
			
			//add rotation
			$rotation = "";
			
			if($isflip == true){
				if($position == "top")
					$arrCss["transform"] = "rotateY(180deg)";
				else 
					$arrCss["transform"] = "rotateX(180deg) rotateY(180deg)";
			}
			
			//set repeat
			$percentRepeat = 100;
			
			if(is_numeric($repeat)){
				if($repeat <= 0)
					$repeat = 1;
				
				if($repeat > 1)
					$percentRepeat = 100 / $repeat;
								
				$decimal = $percentRepeat - (int)$percentRepeat;
				if($decimal > 0)
					$percentRepeat = number_format($percentRepeat, 2);
			}
			
			if(!empty($height)){
				$height = UELM_UniteFunctionsUC::normalizeSize($height);
				$arrCss["height"] = $height;
				$arrCss["background-size"] = $percentRepeat."% ".$height;
			}
			
			
			//get mobile size css
			$arrCssSizes = array();
			foreach(UELM_GlobalsUC::$arrSizes as $size){
				
				$settingName = "shape_devider_{$position}_height_$size";
				
				$heightSize = UELM_UniteFunctionsUC::getVal($settings, $settingName);
				$arrCssSize = array();
				
				if(empty($heightSize))
					continue;
									
				$heightSize = UELM_UniteFunctionsUC::normalizeSize($heightSize);
				$arrCssSize["height"] = $heightSize;
				$arrCssSize["background-size"] = $percentRepeat."% ".$heightSize;
				
				$cssSize = UELM_UniteFunctionsUC::arrStyleToStrStyle($arrCssSize, $selector);
								
				$arrCssSizes[$size] = $cssSize;
			}
			
			
			if($placement == "beneath"){
				$arrCss["z-index"] = "0";
			}
			
			
			//--- output
						
			$css = UELM_UniteFunctionsUC::arrStyleToStrStyle($arrCss, $selector);
			
			UELM_HelperUC::putInlineStyle(self::BR2.$css);
			
			//put mobile size css
			if(!empty($arrCssSizes)){
				$cssMobileSize = UELM_HelperUC::getCssMobileSize($arrCssSizes);
				UELM_HelperUC::putInlineStyle(self::BR2.$cssMobileSize);
			}
			
			$html = "";
			
			$class = esc_attr($class);
			
			$html .= "<div class='uc-shape-devider {$class}'></div> \n";
			
			return($html);
		}
		
		
		/**
		 * get shpae devider addons from grid settings
		 */
		public function getShapeDividerNameFromSettings($settings, $position){

			//preserve old way
			$enableKey = "enable_shape_devider_".$position;
			
			if(isset($settings[$enableKey])){
				$isEnable = $settings[$enableKey];
				$isEnable = UELM_UniteFunctionsUC::strToBool($isEnable);
				
				if($isEnable == false)
					return(null);
			}
			
			$shapeType = UELM_UniteFunctionsUC::getVal($settings, "shape_devider_{$position}_type");
			
			if(empty($shapeType))
				return(null);
			
			return($shapeType);
		}
		
		
		/**
		 * get element shape deviders
		 */
		public function getElementShapeDividersHtml($settings, $elementID){
			
			$html = "";
			$html .= self::getElementShapeDividerHtml("top", $settings, $elementID);
			$html .= self::getElementShapeDividerHtml("bottom", $settings, $elementID);
			
			return($html);
		}
		
		
		/**
		 * output shapes css
		 */
		public function outputCssShapes(){
			
			header("Content-type: text/css");
			
			$arrShapes = self::getArrShapes("bgurl");
			$css = "";
			foreach($arrShapes as $name=>$urlShape)
				$css .= ".unite-shapecontent-{$name}{background-image:url({$urlShape})}".self::BR2;
			uelm_echo($css);
		}
		
		
		/**
		 * get json shapes array for the picker
		 */
		public function getJsonShapes(){
			
			try{
				
				$arrShapes = $this->getArrShapes("picker");
				
			}catch(Exception $e){
				
				$arrShapes = array();
			}
			
			$jsonShapes = json_encode($arrShapes);
			
			return($jsonShapes);
		}
		
		
		/**
		 * get shape svg content
		 */
		public function getShapeSVGContent($shapeName){
			
			try{
				
				$addon = new UELM_UniteCreatorAddon();
				$addon->initByAlias($shapeName, UELM_GlobalsUC::ADDON_TYPE_SHAPES);
				
				$svgContent = $addon->getHtml();
				
				return($svgContent);
				
			}catch(Exception $e){
				
				return($shapeName." not found");
			}
			
		}
		
		
		/**
		 * get shape content by id
		 */
		public function getShapeBGContentBYAddonID($addonID){
			
			$objAddon = new UELM_UniteCreatorAddon();
			$objAddon->initByID($addonID);
			
			$objAddonType = $objAddon->getObjAddonType();
			
			if($objAddonType->isSVG == false)
				UELM_UniteFunctionsUC::throwError("The addon is not svg");
			
			$svgContent = $objAddon->getHtml();
			$encodedContent = base64_encode($svgContent);
			
			return($encodedContent);
		}
		
		
		/**
		 * get devider content by data
		 */
		public static function getShapeDividerBGContentByData($data){
			
			$name = UELM_UniteFunctionsUC::getVal($data, "name");
			$SVGcontent = $this->getShapeSVGContent($name);
			$encodedContent = UELM_UniteFunctionsUC::encodeSVGForBGUrl($SVGcontent);
			
			$response = array();
			$response["content"] = $encodedContent;
			
			return($response);
		}
		
		
}