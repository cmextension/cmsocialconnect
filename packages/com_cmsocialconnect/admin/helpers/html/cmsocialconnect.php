<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * HTML utility class.
 *
 * @since  1.0.0
 */
abstract class JHtmlCMSocialConnect
{
	/**
	 * Get the list of networks for filter.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function networkOptions()
	{
		$options = array();
		$options[] = JHtml::_('select.option', 'test', 'test');

		return $options;
	}
}
