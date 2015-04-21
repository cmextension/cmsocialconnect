<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Connection view.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectViewConnections extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		$this->items			= $this->get('Items');
		$this->pagination		= $this->get('Pagination');
		$this->state			= $this->get('State');
		$this->filterForm		= $this->get('FilterForm');
		$this->activeFilters	= $this->get('ActiveFilters');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		CMSocialConnectHelper::addSubmenu('connections');

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();

		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function addToolbar()
	{
		$state	= $this->get('State');
		$canDo	= JHelperContent::getActions('com_cmsocialconnect');
		$user	= JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_CMSOCIALCONNECT_MANAGER_CONNECTIONS'), 'link');

		if ($canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('', 'connections.delete');
		}

		if ($user->authorise('core.admin', 'com_cmsocialconnect'))
		{
			JToolbarHelper::preferences('com_cmsocialconnect');
		}

		JHtmlSidebar::setAction('index.php?option=com_cmsocialconnect&view=connections');
	}

	/**
	 * Returns an array of fields the table can be sorted by
	 *
	 * @return  array  Array containing the field name to sort by as the key and display text as value
	 *
	 * @since   1.0.0
	 */
	protected function getSortFields()
	{
		return array(
			'a.connected_date' => JText::_('COM_CMSOCIALCONNECT_CONNECTED_DATE'),
			'a.last_login_date' => JText::_('COM_CMSOCIALCONNECT_LAST_LOGIN_DATE')
		);
	}
}
