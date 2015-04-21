<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Routing class for com_cmsocialconnect.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectRouter extends JComponentRouterBase
{
	/**
	 * Build the route for the com_cmsocialconnect component.
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   1.0.0
	 */
	public function build(&$query)
	{
		$segments = array();

		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		$mView = (empty($menuItem->query['view'])) ? null : $menuItem->query['view'];

		if (isset($query['view']))
		{
			$view = $query['view'];

			if (empty($query['Itemid']) || empty($menuItem) || $menuItem->component != 'com_cmsocialconnect')
			{
				$segments[] = $query['view'];
			}

			unset($query['view']);
		}

		return $segments;
	}

	/**
	 * Parse the segments of a URL.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   1.0.0
	 */
	public function parse(&$segments)
	{
		$vars = array();

		$item = $this->menu->getActive();

		if (!isset($item))
		{
			$vars['view'] = $segments[0];

			return $vars;
		}

		return $vars;
	}
}
