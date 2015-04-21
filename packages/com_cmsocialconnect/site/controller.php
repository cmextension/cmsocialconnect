<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * CMSocialConnect controller.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe url parameters and their variable types,
	 *                               for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.0.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$document	= JFactory::getDocument();
		$vName		= $this->input->getCmd('view');
		$vFormat	= $document->getType();
		$lName		= $this->input->getCmd('layout', 'default');

		if ($view = $this->getView($vName, $vFormat))
		{
			if ($vName == 'connections')
			{
				$user = JFactory::getUser();

				if ($user->get('guest'))
				{
					$link = JRoute::_('index.php?option=com_users&view=login');
					$returnURL = JRoute::_('index.php?option=com_cmsocialconnect&view=connections');
					$fullURL = new JURI($link);
					$fullURL->setVar('return', base64_encode($returnURL));

					// Redirect to profile page.
					$this->setRedirect($fullURL);

					return;
				}
			}
		}

		return parent::display($cachable, $urlparams);
	}
}
