<?php

/**
 * @package Unlimited Elements
 * @author UniteCMS http://unitecms.net
 * @copyright Copyright (c) 2016 UniteCMS
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class UELM_UCAdminNoticeRating extends UELM_UCAdminNoticeAbstract{

	/**
	 * get the notice identifier
	 */
	public function getId(){

		return 'rating';
	}

	/**
	 * get the notice html
	 */
	public function getHtml(){

		$heading = __('Could you please do us a BIG favor?', 'unlimited-elements');
		$content = __('Leave a 5-start rating on WordPress. Help us spread the word and boost our motivation.', 'unlimited-elements');

		$rateText = __('Ok, you deserve it', 'unlimited-elements');
		$rateUrl = UELM_GlobalsUC::URL_RATE;
		$rateVariant = UELM_UCAdminNoticeBuilder::ACTION_VARIANT_PRIMARY;
		$rateTarget = '_blank';

		$postponeText = __('Nope, maybe later', 'unlimited-elements');
		$postponeDuration = 168; // 7 days in hours

		$dismissText = __('I already did', 'unlimited-elements');

		$builder = $this->createBuilder();
		$builder->dismissible();
		$builder->withHeading($heading);
		$builder->withContent($content);
		$builder->withLinkAction($rateText, $rateUrl, $rateVariant, $rateTarget);
		$builder->withPostponeAction($postponeText, $postponeDuration);
		$builder->withDismissAction($dismissText);

		$html = $builder->build();

		return $html;
	}

	/**
	 * initialize the notice
	 */
	protected function init(){

		$this->setStart(240); // 10 days in hours
		$this->setDuration(240); // 10 days in hours
	}

}
