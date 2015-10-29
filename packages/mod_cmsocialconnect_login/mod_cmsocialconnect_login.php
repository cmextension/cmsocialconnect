<?php
/**
 * @package     CMSocialConnect
 * @subpackage  mod_cmsocialconnect_login
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/modules/mod_login/helper.php';
require_once JPATH_SITE . '/components/com_cmsocialconnect/helpers/cmsocialconnect.php';

$params->def('greeting', 1);

$bootstrap			= $params->get('bootstrap', 'bootstrap2');
$type				= ModLoginHelper::getType();
$return				= ModLoginHelper::getReturnURL($params, $type);
$twofactormethods	= ModLoginHelper::getTwoFactorMethods();
$user				= JFactory::getUser();
$layout				= $params->get('layout', 'default');

// Logged users must load the logout sublayout.
if (!$user->guest)
{
	$layout .= '_logout';
}

$layout .= '_' . $bootstrap;

CMSocialConnectHelper::loadComponentRequirements();

JPluginHelper::importPlugin('cmsocialconnect');
$socialButtons = array();
$app->triggerEvent('onPrepareModuleLoginButton', array(&$socialButtons));

require JModuleHelper::getLayoutPath('mod_cmsocialconnect_login', $layout);
