<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_user_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$networks = $displayData['networks'];

$display = array();

if (is_array($networks) && count($networks))
{
	foreach ($networks as $network)
	{
		if ($network['connected'])
		{
			$display[] = $network['name'];
		}
	}
}

if (!empty($display))
{
	echo implode(', ', $display);
}
else
{
	echo JText::_('PLG_USER_CMSOCIALCONNECT_NO_CONNECTED_NETWORKS');
}
