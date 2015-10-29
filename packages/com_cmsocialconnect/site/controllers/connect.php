<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Connect controller class.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectControllerConnect extends JControllerLegacy
{
	/**
	 * Connect to a social network. Is called when editing profile.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function socialConnect()
	{
		$app = JFactory::getApplication();
		$network = $app->input->get('network', '');
		$return = $app->getUserState('com_cmsocialconnect.connect.return', '');

		if (!empty($return))
		{
			$return = JRoute::_(base64_decode($return), false);
		}
		else
		{
			$return = 'index.php';
		}

		$this->setRedirect($return);

		$user = JFactory::getUser();

		if ($user->get('guest'))
		{
			$this->setMessage(JText::_('COM_CMSOCIALCONNECT_ERROR_NOT_LOGGED_IN'));

			return true;
		}

		$plugins = array();
		JPluginHelper::importPlugin('cmsocialconnect');
		$app->triggerEvent('onSocialGetPlugins', array(&$plugins));

		if (isset($plugins[$network]))
		{
			$results = $app->triggerEvent('onSocialConnect', array($network));

			// No plugins found.
			if (empty($results))
			{
				$this->setMessage(JText::_('COM_CMSOCIALCONNECT_ERROR_NO_SOCIAL_NETWORKS'));

				return true;
			}
			// Succeeded.
			elseif (in_array(false, $results, true) === false)
			{
				$data = $app->getUserState('com_cmsocialconnect.connect.data', array());
				$app->setUserState('com_cmsocialconnect.connect.data', null);

				if (count($data))
				{
					$model = JModelLegacy::getInstance('Connection', 'CMSocialConnectModel');

					if ($model->saveConnection($user->get('id'), $data))
					{
						$this->setMessage(JText::sprintf('COM_CMSOCIALCONNECT_CONNECT_CONNECT_SUCCESS', $plugins[$network]['name']));
					}
				}
			}
		}

		return true;
	}

	/**
	 * Disconnect from a social network. Is called when editing profile.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function socialDisconnect()
	{
		$app = JFactory::getApplication();
		$network = $app->input->get('network', '');
		$return = $app->getUserState('com_cmsocialconnect.disconnect.return', '');

		if (!empty($return))
		{
			$return = JRoute::_(base64_decode($return), false);
		}
		else
		{
			$return = 'index.php';
		}

		$this->setRedirect($return);

		$user = JFactory::getUser();

		if ($user->get('guest'))
		{
			$this->setMessage(JText::_('COM_CMSOCIALCONNECT_ERROR_NOT_LOGGED_IN'));

			return true;
		}

		$plugins = array();
		JPluginHelper::importPlugin('cmsocialconnect');
		$app->triggerEvent('onSocialGetPlugins', array(&$plugins));

		if (isset($plugins[$network]))
		{
			$model = JModelLegacy::getInstance('Connection', 'CMSocialConnectModel');

			if ($model->deleteConnection($user->get('id'), $network))
			{
				$this->setMessage(JText::sprintf('COM_CMSOCIALCONNECT_CONNECT_DISCONNECT_SUCCESS', $plugins[$network]['name']));
			}
			else
			{
				return true;
			}
		}

		return true;
	}
}
