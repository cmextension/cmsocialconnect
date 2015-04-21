<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();

if (!$user->get('guest'))
{
	return;
}

$app = JFactory::getApplication();

// If current page is a menu item for com_user's login form,
// we have login redirect URL set in menu item's settings.
$loginRedirectUrl = $app->getParams()->get('login_redirect_url', '');

if (!empty($loginRedirectUrl))
{
	$return = base64_encode($loginRedirectUrl);
}
else
{
	$return = CMSocialConnectHelper::getReturnUrl();
}

$socialData = $app->getUserState('com_cmsocialconnect.login.data', array(), 'array');

CMSocialConnectHelper::loadComponentRequirements();

if (empty($socialData))
{
	// Get the registration buttons of the social networks.
	JPluginHelper::importPlugin('cmsocialconnect');
	$socialButtons = array();
	$app->triggerEvent('onPrepareComponentLoginButton', array(&$socialButtons));
}
else
{
	$socialButtons = '';
}

if (empty($socialData) && !empty($socialButtons))
{
?>
	<div class="cmsocialconnect">
		<form action="<?php echo JRoute::_('index.php?option=com_cmsocialconnect&task=login.socialLogin'); ?>" method="post">
			<fieldset>
				<legend><?php echo JText::_('COM_CMSOCIALCONNECT_LOGIN_HEADING'); ?></legend>

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
