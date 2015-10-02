<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_system_cmsocialconnect_facebook
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * CM Social Connect system plugin.
 *
 * @package     CMSocialConnect
 * @subpackage  plg_system_cmsocialnetwork
 * @since       1.0.0
 */
class PlgSystemCMSocialConnect extends JPlugin
{
	/**
	 * Do something
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function onAfterDispatch()
	{
		$app = JFactory::getApplication();
		$jinput = $app->input;

		if ($app->isAdmin())
		{
			return;
		}

		$option = $jinput->getCmd('option');
		$view = $jinput->getCmd('view');
		$layout = $jinput->getCmd('layout', 'default');

		if ($option == 'com_users' && $view == 'registration' && $layout == 'default')
		{
			require_once JPATH_SITE . '/components/com_users/controller.php';
			$controller = new UsersController;

			$view = $controller->getView($view, 'html');

			$layout = new JLayoutFile('registration', $basePath = JPATH_SITE . '/components/com_cmsocialconnect/layouts');
			$socialForm = $layout->render(array());

			ob_start();
			$view->display();
			$contents = ob_get_clean();
			$document = JFactory::getDocument();
			$document->setBuffer($socialForm . $contents, 'component');
		}
		elseif ($option == 'com_users' && $view == 'login' && $layout == 'default')
		{
			require_once JPATH_SITE . '/components/com_users/controller.php';
			$controller = new UsersController;

			$view = $controller->getView($view, 'html');

			$layout = new JLayoutFile('login', $basePath = JPATH_SITE . '/components/com_cmsocialconnect/layouts');
			$socialForm = $layout->render(array());

			ob_start();
			$view->display();
			$contents = ob_get_clean();
			$document = JFactory::getDocument();
			$document->setBuffer($socialForm . $contents, 'component');
		}
	}
}
