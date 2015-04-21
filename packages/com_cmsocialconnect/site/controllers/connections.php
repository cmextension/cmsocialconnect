<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Connections controller class.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectControllerConnections extends JControllerLegacy
{
	/**
	 * Connect to/disconnect from social networks. Used in Connection view to manage the connections.
	 * Redirect to Connect controller which is used in plugin events.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function manage()
	{
		$app = JFactory::getApplication();
		$network = $app->input->post->getString('cmsc_network', '');
		$task = $app->input->post->getString('cmsc_task', '');
		$return = $app->input->post->get('return', '', 'BASE64');

		if ($task == 'connect')
		{
			$app->setUserState('com_cmsocialconnect.connect.return', $return);
			$redirectUrl = 'index.php?option=com_cmsocialconnect&task=connect.socialConnect' .
			'&network=' . $network . '&return=' . $return;
			$app->redirect($redirectUrl);
		}
		elseif ($task == 'disconnect')
		{
			$app->setUserState('com_cmsocialconnect.disconnect.return', $return);
			$redirectUrl = 'index.php?option=com_cmsocialconnect&task=connect.socialDisconnect' .
			'&network=' . $network . '&return=' . $return;
			$app->redirect($redirectUrl);
		}

		$app->redirect('index.php');
	}
}
