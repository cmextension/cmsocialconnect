<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Supports an HTML select list of networks.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class JFormFieldCMSCNetwork extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 *
	 * @since  1.0.0
	 */
	public $type = 'CMSCNetwork';

	/**
	 * Method to get the options to populate list.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.0.0
	 */
	protected function getOptions()
	{
		$options = array();
		$plugins = array();

		JPluginHelper::importPlugin('cmsocialconnect');
		JFactory::getApplication()->triggerEvent('onSocialGetPlugins', array(&$plugins));

		if (!empty($plugins))
		{
			foreach ($plugins as $plugin)
			{
				$options[] = JHTML::_('select.option', $plugin['id'], $plugin['name']);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
