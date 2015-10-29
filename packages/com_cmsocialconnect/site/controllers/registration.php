<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Registration controller class.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectControllerRegistration extends JControllerLegacy
{
	/**
	 * Register with social networks.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function socialRegister()
	{
		$app = JFactory::getApplication();
		$network = $app->input->get('network', '');
		$return = $app->input->get('return', '', 'BASE64');

		if (!empty($return))
		{
			$returnUrl = base64_decode($return);
		}
		else
		{
			$returnUrl = 'index.php?option=com_users&view=registration';
		}

		JPluginHelper::importPlugin('cmsocialconnect');
		$app->triggerEvent('onSocialRegister', array($network, $returnUrl));

		// The plugins will handle the requests and redirect user to the next page.
		// In case there is no cmsocialconnect plugin enabled, we take user to home page.
		$this->setMessage(JText::_('COM_CMSOCIALCONNECT_ERROR_NO_SOCIAL_NETWORKS'));
		$this->setRedirect('index.php');
	}

	/**
	 * Disconnect from connected social network.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function socialDisconnect()
	{
		$app = JFactory::getApplication();
		$return = $app->input->get('return', '', 'BASE64');
		$socialData = $app->getUserState('com_cmsocialconnect.register.data', array(), 'array');

		if (!empty($socialData))
		{
			$app->setUserState('com_cmsocialconnect.register.data', null);
			$message = JText::sprintf(
						'COM_CMSOCIALCONNECT_REGISTRATION_DISCONNECT_SUCCESS',
						$socialData['network_name']
					);
			$this->setMessage($message);
		}

		if (!empty($return))
		{
			$returnUrl = base64_decode($return);
		}
		else
		{
			$returnUrl = 'index.php?option=com_users&view=registration';
		}

		$this->setRedirect(JRoute::_($returnUrl, false));
	}
}
