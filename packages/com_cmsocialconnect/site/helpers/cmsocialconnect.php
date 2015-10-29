<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * CMSocialConnect front-end helper.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectHelper extends JControllerLegacy
{
	/**
	 * Check if a username is available.
	 *
	 * @param   string  $username  Username.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public static function isUsernameAvailable($username)
	{
		if ($username != '')
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('COUNT(*)')
				->from($db->quoteName('#__users'))
				->where($db->quoteName('username') . ' = ' . $db->quote($username));
			$count = $db->setQuery($query)->loadResult();

			// Check for a database error.
			if ($error = $db->getErrorMsg())
			{
				JError::raiseWarning(500, $error);

				return false;
			}

			return ($count) ? false : true;
		}

		return false;
	}

	/**
	 * Get the URL of the current page to use as return URL.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function getReturnUrl()
	{
		$app = JFactory::getApplication();
		$return = $app->input->get('return', '', 'BASE64');

		if (empty($return))
		{
			$vars = $app::getRouter()->getVars();

			$returnUrl = base64_encode('index.php?' . JUri::buildQuery($vars));
		}
		else
		{
			$returnUrl = $return;
		}

		return $returnUrl;
	}

	/**
	 * Load the language of a specific component. By default load com_cmsocialconnect's language.
	 *
	 * @param   string  $component  The component's name.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function loadComponentLanguage($component = 'com_cmsocialconnect')
	{
		$lang = JFactory::getLanguage();

		$lang->load($component, JPATH_BASE, null, false, false)
			|| $lang->load($component, JPATH_BASE . '/components/' . $component, null, false, false)
			|| $lang->load($component, JPATH_BASE, $lang->getDefault(), false, false)
			|| $lang->load($component, JPATH_BASE . '/components/' . $component, $lang->getDefault(), false, false);
	}

	/**
	 * Load the required material like language, styles, scripts,.. for com_cmsocialconnect.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function loadComponentRequirements()
	{
		self::loadComponentLanguage();

		$params = JComponentHelper::getParams('com_cmsocialconnect');
		$loadFontAwesome = (int) $params->get('load_fontawesome', '1');
		$loadCSS = (int) $params->get('load_css', '1');

		if ($loadFontAwesome)
		{
			JFactory::getDocument()->addStyleSheet('components/com_cmsocialconnect/assets/css/font-awesome.min.css');
		}

		if ($loadCSS)
		{
			JFactory::getDocument()->addStyleSheet('components/com_cmsocialconnect/assets/css/style.css');
		}
	}

	/**
	 * Get a connection.
	 *
	 * @param   string  $networkId      The ID of the social network.
	 * @param   string  $networkUserId  The ID of the social account.
	 *
	 * @return  boolean True if connect already exists.
	 *
	 * @since   1.0.0
	 */
	public static function getConnection($networkId, $networkUserId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->qn('#__cmsocialconnect_connections'))
			->where($db->qn('network_id') . ' = ' . $db->q($networkId))
			->where($db->qn('network_user_id') . ' = ' . $db->q($networkUserId));
		$connection = $db->setQuery($query)->loadObject();

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);

			return false;
		}

		return $connection;
	}

	/**
	 * Check if email is currently used.
	 *
	 * @param   string  $email  Email.
	 *
	 * @return  boolean True if email is already used
	 *
	 * @since   1.0.0
	 */
	public static function isEmailInUse($email)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->qn('#__users'))
			->where($db->qn('email') . ' = ' . $db->q($email));
		$count = $db->setQuery($query)->loadResult();

		return ($count == 0) ? false : true;
	}

	/**
	 * Update last login.
	 *
	 * @param   integer  $userId         The ID of the Joomla! user.
	 * @param   string   $networkId      The ID of the social network.
	 * @param   string   $networkUserId  The ID of the social account.
	 *
	 * @return  boolean True if connect already exists.
	 *
	 * @since   1.0.0
	 */
	public static function updateLastLogin($userId, $networkId, $networkUserId)
	{
		$now = JFactory::getDate()->toSql();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update($db->qn('#__cmsocialconnect_connections'))
			->set($db->qn('last_login_date') . ' = ' . $db->q($now))
			->where($db->qn('user_id') . ' = ' . $db->q($userId))
			->where($db->qn('network_id') . ' = ' . $db->q($networkId))
			->where($db->qn('network_user_id') . ' = ' . $db->q($networkUserId));
		$db->setQuery($query)->execute();

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);

			return false;
		}

		return true;
	}
}
