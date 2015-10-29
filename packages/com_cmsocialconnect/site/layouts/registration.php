<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$app = JFactory::getApplication();
$socialData = $app->getUserState('com_cmsocialconnect.register.data', array(), 'array');

CMSocialConnectHelper::loadComponentRequirements();

$return = CMSocialConnectHelper::getReturnUrl();

if (empty($socialData))
{
	// Get the registration buttons of the social networks.
	JPluginHelper::importPlugin('cmsocialconnect');
	$socialButtons = array();
	$app->triggerEvent('onPrepareComponentRegisterButton', array(&$socialButtons));
}
else
{
	$socialButtons = '';
}

if (empty($socialData) && !empty($socialButtons))
{
?>
	<div class="cmsocialconnect">
		<form action="<?php echo JRoute::_('index.php?option=com_cmsocialconnect&task=registration.socialRegister'); ?>" method="post">
			<fieldset>
				<legend><?php echo JText::_('COM_CMSOCIALCONNECT_REGISTRATION_HEADING'); ?></legend>

				<div class="text-center">
					<?php echo implode(' ', $socialButtons); ?>
				</div>

				<input type="hidden" id="socialNetworkId" name="network" value="" />
				<input type="hidden" name="return" value="<?php echo $return; ?>" />
			</fieldset>
		</form>
	</div>
<?php
}
elseif (!empty($socialData))
{
?>
	<div class="cmsocialconnect">
		<form action="<?php echo JRoute::_('index.php?option=com_cmsocialconnect&task=registration.socialDisconnect'); ?>" method="post" class="well">
			<fieldset>
				<legend><?php echo JText::_('COM_CMSOCIALCONNECT_REGISTRATION_HEADING'); ?></legend>
				<p><?php
				echo JText::sprintf(
						'COM_CMSOCIALCONNECT_REGISTRATION_CONNECT_SUCCESS',
						$socialData['network_name'],
						$socialData['network_name']
					); ?></p>
				<div class="text-center">
					<button type="submit" class="btn"><i class="fa fa-sign-out"></i> <?php echo JText::_('COM_CMSOCIALCONNECT_DISCONNECT'); ?></button>
				</div>
				<input type="hidden" name="return" value="<?php echo $return; ?>" />
			</fieldset>
		</form>
	</div>
<?php
}
