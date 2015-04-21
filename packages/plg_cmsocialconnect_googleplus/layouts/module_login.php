<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_googleplus
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

$pluginId = $displayData['pluginId'];
$buttonClasses = $displayData['buttonClasses'];
?>
<button class="btn btn-block <?php echo implode(' ', $buttonClasses); ?>" onclick="this.form.elements['socialNetworkId'].value='<?php echo $pluginId; ?>';this.form.submit();">
	<i class="fa fa-google-plus-square"></i> <?php echo JText::_('PLG_CMSOCIALCONNECT_GOOGLEPLUS_LOGIN_WITH_BUTTON'); ?>
</button>
