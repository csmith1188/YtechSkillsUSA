<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_ParamsManager{

	/**
	 * Determine if the parameter passes conditions.
	 *
	 * @param array $params
	 * @param array $param
	 *
	 * @return bool
	 * @throws Exception
	 */
	public static function isParamPassesConditions($params, $param){

		$enableCondition = UELM_UniteFunctionsUC::getVal($param, "enable_condition");
		$enableCondition = UELM_UniteFunctionsUC::strToBool($enableCondition);

		if($enableCondition === false)
			return true;

		$conditions = array(
			array(
				"attribute" => UELM_UniteFunctionsUC::getVal($param, "condition_attribute"),
				"operator" => UELM_UniteFunctionsUC::getVal($param, "condition_operator"),
				"value" => UELM_UniteFunctionsUC::getVal($param, "condition_value"),
			),
			array(
				"attribute" => UELM_UniteFunctionsUC::getVal($param, "condition_attribute2"),
				"operator" => UELM_UniteFunctionsUC::getVal($param, "condition_operator2"),
				"value" => UELM_UniteFunctionsUC::getVal($param, "condition_value2"),
			),
		);

		foreach($conditions as $condition){
			$passed = self::checkCondition($params, $condition);

			if($passed === false)
				return false;
		}

		return true;
	}

	/**
	 * Find a parameter by the given name.
	 *
	 * @param array $params
	 * @param string $name
	 *
	 * @return array|null
	 */
	private static function findParamByName($params, $name){

		foreach($params as $param){
			$paramName = UELM_UniteFunctionsUC::getVal($param, "name");

			if($paramName === $name){
				return $param;
			}
		}

		return null;
	}

	/**
	 * Check the parameter condition.
	 *
	 * @param array $params
	 * @param array $condition
	 *
	 * @return bool
	 * @throws Exception
	 */
	private static function checkCondition($params, $condition){

		if(empty($condition["attribute"]) === true)
			return true;

		$conditionedParam = self::findParamByName($params, $condition["attribute"]);

		if($conditionedParam === null)
			return false;

		if(is_array($condition["value"]) === false)
			$condition["value"] = array($condition["value"]);

		$conditionedValue = UELM_UniteFunctionsUC::getVal($conditionedParam, "value");

		switch($condition["operator"]){
			case "equal":
				return in_array($conditionedValue, $condition["value"]);
			case "not_equal":
				return !in_array($conditionedValue, $condition["value"]);
			default:
				UELM_UniteFunctionsUC::throwError("Operator \"{$condition["operator"]}\" is not implemented.");
		}
	}

}
