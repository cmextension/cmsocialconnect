<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * View class for manage connections.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectViewConnections extends JViewLegacy
{
	protected $items;

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
		$user = JFactory::getUser();

		$state			= $this->get('State');
		$connections	= $this->get('Items');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		// Get social network plugins.
		$plugins = array();
		$networks = array();
		JPluginHelper::importPlugin('cmsocialconnect');
		JFactory::getApplication()->triggerEvent('onSocialGetPlugins', array(&$plugins));

		if (!empty($plugins))
		{
			foreach ($plugins as $plugin)
			{
				if (!isset($networks[$plugin['id']]))
				{
					$networks[$plugin['id']] = array(
						'id'		=> $plugin['id'],
						'name'		=> $plugin['name'],
						'connected'	=> 0,
					);
				}
			}
		}

		if (!empty($connections))
		{
			foreach ($connections as $key => $connection)
			{
				if (isset($networks[$connection->network_id]))
				{
					$networks[$connection->network_id]['connected'] = 1;
				}
			}
		}

		$this->items = $connections;
		$this->state = $state;
		$this->params = &$state->params;
		$this->networks = $networks;

		// Escape strings for HTML output.
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$this->_prepareDocument();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();
		$menus	= $app->getMenu();
		$menu	= $menus->getActive();

		$head = JText::_('COM_CMSOCIALCONNECT_CONNECTIONS_DEFAULT_TITLE');

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', $head);
		}

		$title = $this->params->def('page_title', $head);

		if ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);
	}
}
