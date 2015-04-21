<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_twitter
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

$pluginId = $displayData['pluginId'];
$buttonClasses = $displayData['buttonClasses'];
?>
<button class="btn <?php echo implode(' ', $buttonClasses); ?>" onclick="this.form.elements['socialNetworkId'].value='<?php echo $pluginId; ?>';this.form.submit();">
	<i class="fa fa-twitter-square"></i> <?php echo JText::_('PLG_CMSOCIALCONNECT_TWITTER_REGISTER_BUTTON'); ?>
</button>
