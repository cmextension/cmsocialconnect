<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_cmsocialconnect'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Load helpers.
require_once JPATH_COMPONENT_ADMINISTRATOR . '/helpers/cmsocialconnect.php';
JHtml::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/helpers/html');

// Load scripts and styles.
JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$doc = JFactory::getDocument();
$doc->addStyleSheet('../components/com_cmsocialconnect/assets/css/font-awesome.min.css');
$doc->addStyleSheet('components/com_cmsocialconnect/assets/css/style.css');

$controller = JControllerLegacy::getInstance('CMSocialConnect');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
