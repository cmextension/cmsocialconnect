<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_user_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Profile field for user's connected social networks.
 *
 * @package     CMSocialConnect
 * @subpackage  plg_user_cmsocialconnect
 * @since       1.0.0
 */
class JFormFieldCMSCSocialNetwork extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $type = 'CMSCSocialNetwork';

	/**
	 * Method to get the field input.
	 *
	 * @return  string  The field input.
	 *
	 * @since   1.0.0
	 */
	protected function getInput()
	{
		CMSocialConnectHelper::loadComponentLanguage();

		$layoutData = array('networks' => $this->value);
		$layout = new JLayoutFile('edit', $basePath = JPATH_SITE . '/components/com_cmsocialconnect/layouts');

		return $layout->render($layoutData);
	}
}
