<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Login controller class.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectControllerLogin extends JControllerLegacy
{
	/**
	 * Register with social networks.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function socialLogin()
	{
		$app = JFactory::getApplication();
		$network = $app->input->get('network', '');
		$return = $app->input->post->get('return', '', 'BASE64');

		if (!empty($return))
		{
			$app->setUserState('com_cmsocialconnect.login.return', $return);
		}

		JPluginHelper::importPlugin('cmsocialconnect');
		$results = $app->triggerEvent('onSocialLogin', array($network));

		// No plugins found.
		if (empty($results))
		{
			$this->setMessage(JText::_('COM_CMSOCIALCONNECT_ERROR_NO_SOCIAL_NETWORKS'));
			$this->setRedirect('index.php');
		}
		// Login succeeded.
		elseif (in_array(false, $results, true) === false)
		{
			$return = $app->getUserState('com_cmsocialconnect.login.return');

			if (empty($return))
			{
				$return = 'index.php';
			}
			else
			{
				$return = base64_decode($return);
			}

			$this->setRedirect($return);
		}
		else
		{
			$this->setRedirect(JRoute::_('index.php?option=com_users&view=login', false));
		}
	}
}
