<?php
/**
 * @package    CMSocialConnect
 * @copyright  Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

// We need load the form's XML file from com_users.
JForm::addFormPath(JPATH_SITE . '/components/com_users/models/forms');

require_once JPATH_SITE . '/components/com_users/models/registration.php';

/**
 * Registration model.
 * We only take care of showing the registration form to add social network icons.
 * com_user takes care of creating new account as usual.
 * So let's just extend the registration class from com_users's one.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectModelRegistration extends UsersModelRegistration
{
}
