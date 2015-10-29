<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_cmsocialconnect/helpers/cmsocialconnect.php';

$controller	= JControllerLegacy::getInstance('CMSocialConnect');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
