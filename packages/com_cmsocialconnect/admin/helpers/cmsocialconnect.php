<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * CMSocialConnect helper.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectHelper extends JHelperContent
{
	/**
	 * Configure the component's navigation menu.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function addSubmenu($vName = 'dashboard')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_CMSOCIALCONNECT_DASHBOARD'),
			'index.php?option=com_cmsocialconnect&view=dashboard',
			$vName == 'dashboard'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_CMSOCIALCONNECT_CONNECTIONS'),
			'index.php?option=com_cmsocialconnect&view=connections',
			$vName == 'connections'
		);
	}
}
