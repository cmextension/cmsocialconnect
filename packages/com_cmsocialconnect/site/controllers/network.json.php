<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Network JSON controller class.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectControllerNetwork extends JControllerLegacy
{
	/**
	 * Disconnect from a social network.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function disconnect()
	{
		$app = JFactory::getApplication();

		// Cross Site Request Forgery (CSRF).
		if (!JSession::checkToken('get'))
		{
			echo new JResponseJson(null, JText::_('Invalid Token'), true);
			$app->close();
		}

		// User must be logged in.
		$user = JFactory::getUser();

		if ($user->get('guest'))
		{
			echo new JResponseJson(null, JText::_('Not logged in'), true);
			$app->close();
		}

		// Network ID must exist.
		$network = $app->input->getString('network', '');

		if ($network == '')
		{
			echo new JResponseJson(null, JText::_('Invalid network ID'), true);
			$app->close();
		}

		$model = JModelLegacy::getInstance('Connection', 'CMSocialConnectModel');

		if (!$model->deleteConnection($user->get('id'), $network))
		{
			echo new JResponseJson(null, $model->getError(), false);
			$app->close();
		}

		echo new JResponseJson;

		$app->close();
	}
}
