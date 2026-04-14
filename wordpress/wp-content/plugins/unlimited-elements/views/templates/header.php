<?php

/**
 * @package Unlimited Elements
 * @author unlimited-elements.com
 * @copyright (C) 2021 Unlimited Elements, All Rights Reserved.
 * @license GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if(!isset($headerTitle))
	UELM_UniteFunctionsUC::throwError("header template error: \$headerTitle variable not defined");

$headerPrefix = UELM_HelperUC::getText("addon_library");

if(!empty(UELM_GlobalsUC::$alterViewHeaderPrefix))
	$headerPrefix = UELM_GlobalsUC::$alterViewHeaderPrefix;

$adminPageTitle = $headerTitle . " - " . $headerPrefix;

UELM_UniteProviderFunctionsUC::setAdminPageTitle($adminPageTitle);

?>

<div class="unite_header_wrapper">
	<div class="title_line">
		<div class="title_line_text">
			<?php 
			uelm_echo( $headerTitle ); ?>
		</div>
		<?php if(isset($headerAddHtml)): ?>
			<div class="title_line_add_html"><?php echo esc_html($headerAddHtml); ?></div>
		<?php endif ?>
	</div>
	<div class="unite-clear"></div>
</div>

<?php UELM_HelperHtmlUC::putHtmlAdminNotices() ?>
