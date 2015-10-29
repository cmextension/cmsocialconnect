<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_facebook
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Plugin to integrate with Facebook social network.
 *
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_facebook
 * @since       1.0.0
 */
class PlgCMSocialConnectFacebook extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Network ID. Must be unique.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $pluginId;

	/**
	 * Network name.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	protected $pluginName;

	/**
	 * Array of CSS classes for login and register buttons.
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $buttonClasses = array('cmsc-button', 'cmsc-facebook');

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An optional associative array of configuration settings.
	 *                             Recognized key values include 'name', 'group', 'params', 'language'
	 *                             (this list is not meant to be comprehensive).
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		$this->pluginId = 'facebook';
		$this->pluginName = JText::_('PLG_CMSOCIALCONNECT_FACEBOOK_NAME');
	}

	/**
	 * Build HTML code for social network's button to display in login form.
	 * Add your button's HTML to $buttons array.
	 *
	 * @param   array  &$buttons  Array of the HTML of social network's buttons.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onPrepareComponentRegisterButton(&$buttons)
	{
		$data = array('pluginId' => $this->pluginId, 'buttonClasses' => $this->buttonClasses);
		$layout = new JLayoutFile('component_registration', $basePath = JPATH_PLUGINS . '/cmsocialconnect/' . $this->pluginId . '/layouts');
		$html = $layout->render($data);

		$buttons[] = $html;

		return true;
	}

	/**
	 * Get plugin info. Used in building a list of enabled social plugins.
	 * Every social plugin adds it array info into $plugins.
	 * array('id' => the plugin ID, 'name' => the plugin name)
	 *
	 * @param   array  &$plugins  Array o plugins.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onSocialGetPlugins(&$plugins)
	{
		$plugins[$this->pluginId] = array(
			'id'	=> $this->pluginId,
			'name'	=> $this->pluginName
		);

		return true;
	}

	/**
	 * Register with social network account.
	 *
	 * @param   string  $pluginId   The ID of the social network that is currently used.
	 * @param   string  $returnUrl  Return URL.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onSocialRegister($pluginId, $returnUrl)
	{
		// If we are not called, do nothing.
		if ($pluginId != $this->pluginId)
		{
			return true;
		}

		$app = JFactory::getApplication();

		$redirectUrl = JURI::base() . 'index.php?option=com_cmsocialconnect&task=registration.socialRegister&network=' . $this->pluginId;

		$fbUser = $this->getUser($redirectUrl);

		if ($fbUser === false)
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_ERROR_GET_SOCIAL_NETWORK_USER_FAILURE', $this->pluginName));
			$app->redirect(JRoute::_($returnUrl, false));
		}

		// Log user in if this social account was already connected.
		$connection = CMSocialConnectHelper::getConnection($pluginId, $fbUser->id);

		if (isset($connection->id) && (int) $connection->id > 0)
		{
			$user = JFactory::getUser($connection->user_id);

			if ($user->get('id') > 0)
			{
				$options = array('action' => 'core.login.site');

				$credentials = array(
					'username' => $user->get('username'),
				);

				JPluginHelper::importPlugin('user');
				$results = $app->triggerEvent('onUserLogin', array($credentials, $options));

				$user = JFactory::getUser();

				if (in_array(false, $results, true) == false)
				{
					$app->triggerEvent('onUserAfterLogin', array($options));
				}
			}
			// User doesn't exist.
			else
			{
				$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_REGISTRATION_CONNECT_EXISTING', $this->pluginName));
			}

			$app->redirect(JRoute::_($returnUrl, false));
		}

		/*
		Now the authenciation is completed,
		we creat suggested username and store in the session
		together with email address and social network's info,
		then take user to registration page to complete the registration.
		*/
		$sessionData = $this->prepareData($fbUser);

		$app->setUserState('com_cmsocialconnect.register.data', $sessionData);
		$app->redirect(JRoute::_($returnUrl, false));

		return true;
	}

	/**
	 * Build HTML code for social network's button to display in module's login form.
	 * Add your button's HTML to $buttons array.
	 *
	 * @param   array  &$buttons  Array of the HTML of social network's buttons.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onPrepareModuleLoginButton(&$buttons)
	{
		$data = array('pluginId' => $this->pluginId, 'buttonClasses' => $this->buttonClasses);
		$layout = new JLayoutFile('module_login', $basePath = JPATH_PLUGINS . '/cmsocialconnect/' . $this->pluginId . '/layouts');
		$html = $layout->render($data);

		$buttons[] = $html;

		return true;
	}

	/**
	 * Build HTML code for social network's button to display in login form.
	 * Add your button's HTML to $buttons array.
	 *
	 * @param   array  &$buttons  Array of the HTML of social network's buttons.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onPrepareComponentLoginButton(&$buttons)
	{
		$data = array('pluginId' => $this->pluginId, 'buttonClasses' => $this->buttonClasses);
		$layout = new JLayoutFile('component_login', $basePath = JPATH_PLUGINS . '/cmsocialconnect/' . $this->pluginId . '/layouts');
		$html = $layout->render($data);

		$buttons[] = $html;

		return true;
	}

	/**
	 * Login with social network account.
	 *
	 * @param   string  $pluginId  The ID of the social network that is currently used.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onSocialLogin($pluginId)
	{
		// If we are not called, do nothing.
		if ($pluginId != $this->pluginId)
		{
			return true;
		}

		$app = JFactory::getApplication();

		$redirectUrl = JURI::base() . 'index.php?option=com_cmsocialconnect&task=login.socialLogin&network=' . $this->pluginId;

		$fbUser = $this->getUser($redirectUrl);

		if ($fbUser === false)
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_ERROR_GET_SOCIAL_NETWORK_USER_FAILURE', $this->pluginName));

			return false;
		}

		// Check if this social account was already connected by another user.
		$connection = CMSocialConnectHelper::getConnection($pluginId, $fbUser->id);

		if (!isset($connection->id) || (int) $connection->id <= 0)
		{
			$sessionData = $this->prepareData($fbUser);

			$app->setUserState('com_cmsocialconnect.register.data', $sessionData);

			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_LOGIN_NO_CONNECTIONS', $this->pluginName));

			$app->redirect(JRoute::_('index.php?option=com_users&view=registration', false));
		}

		// Check if user exists.
		$user = JFactory::getUser($connection->user_id);

		if ($user->get('guest'))
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_LOGIN_NO_USERS', $this->pluginName));

			return false;
		}

		// Log user in.
		$options = array('action' => 'core.login.site');

		$credentials = array(
			'username' => $user->get('username'),
		);

		JPluginHelper::importPlugin('user');
		$results = $app->triggerEvent('onUserLogin', array($credentials, $options));

		$user = JFactory::getUser();

		if (in_array(false, $results, true) == false)
		{
			// The user is successfully logged in. Run the after login events.
			$app->triggerEvent('onUserAfterLogin', array($options));

			CMSocialConnectHelper::updateLastLogin($user->get('id'), $this->pluginId, $fbUser->id);

			return true;
		}

		return false;
	}

	/**
	 * Connect with social network account.
	 *
	 * @param   string  $pluginId  The ID of the social network that is currently used.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onSocialConnect($pluginId)
	{
		// If we are not called, do nothing.
		if ($pluginId != $this->pluginId)
		{
			return true;
		}

		$app = JFactory::getApplication();

		$redirectUrl = JURI::base() . 'index.php?option=com_cmsocialconnect&task=connect.socialConnect&network=' . $this->pluginId;

		$user = JFactory::getUser();

		if ($user->get('guest'))
		{
			$app->enqueueMessage(JText::_('COM_CMSOCIALCONNECT_ERROR_NOT_LOGGED_IN'));

			return false;
		}

		$fbUser = $this->getUser($redirectUrl);

		if ($fbUser === false)
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_ERROR_GET_SOCIAL_NETWORK_USER_FAILURE', $this->pluginName));

			return false;
		}

		// Check if this social account was already connected.
		$connection = CMSocialConnectHelper::getConnection($pluginId, $fbUser->id);

		if (isset($connection->id) && $connection->user_id == $user->get('id'))
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_CONNECT_ALREADY_CONNECTED', $this->pluginName));

			return false;
		}
		elseif (isset($connection->id) && $connection->user_id != $user->get('id'))
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_CONNECT_ALREADY_CONNECTED_BY_OTHER_USER', $this->pluginName));

			return false;
		}

		$sessionData = array(
			'network_id'		=> $pluginId,
			'network_user_id'	=> $fbUser->id,
		);

		$app->setUserState('com_cmsocialconnect.connect.data', $sessionData);

		return true;
	}

	/**
	 * Authenticate and get social network account.
	 * Return false if failure or an object if successfully get the social network account.
	 *
	 * @param   string  $redirectUrl  The redirect URL used when authenciating.
	 *
	 * @return  mixed
	 *
	 * @since   1.0.0
	 */
	protected function getUser($redirectUrl)
	{
		jimport('joomla.facebook.facebook');
		jimport('joomla.facebook.oauth');
		jimport('joomla.facebook.user');

		$app = JFactory::getApplication();

		$appId = $this->params->get('app_id');
		$appSecret = $this->params->get('app_secret');

		$oauthOptions = new JRegistry;
		$oauthOptions->set('redirecturi', $redirectUrl);
		$oauthOptions->set('clientid', $appId);
		$oauthOptions->set('clientsecret', $appSecret);
		$oauthOptions->set('sendheaders', 1);

		$oauthParams = array(
			'scope' => 'public_profile, email'
		);

		$oauthOptions->set('requestparams', $oauthParams);

		$jFacebookOauth = new JFacebookOAuth($oauthOptions);

		// Authenticate. Will redirect to Facebook if there is no correct code.
		try
		{
			$jFacebookOauth->authenticate();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(500, $e->getMessage());

			return false;
		}

		// If we are here, then we have a correct token. Proceed to create a JFacebook object.
		$jFacebook = new JFacebook($jFacebookOauth);

		// Take the user information from facebook.
		$jFacebookUser = $jFacebook->user;

		$fbUser = $jFacebookUser->get('me?fields=id,name,email');

		return $fbUser;
	}

	/**
	 * Prepare data for registration form. Return an array.
	 * array(
	 *     'network_name'   => The name of the social network,
	 *     'network_id'     => The ID of the plugin/social network,
	 *     'network_user_id'=> The ID of the social network account,
	 *     'username'       => Generated username for user,
	 *     'email'          => User's email,
	 *     'name'           => User's name,
	 * );
	 *
	 * @param   object  $fbUser  The data of social network account.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function prepareData($fbUser)
	{
		$app = JFactory::getApplication();

		$data = array(
			'network_name'		=> $this->pluginName,
			'network_id'		=> $this->pluginId,
			'network_user_id'	=> $fbUser->id,
			'username'			=> '',
			'email'				=> '',
			'name'				=> $fbUser->name,
		);

		$email = $fbUser->email;

		// Email is already in use, notify user.
		if (CMSocialConnectHelper::isEmailInUse($email))
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_REGISTRATION_DUPLICATED_EMAIL', $email));
		}

		// Flag to check that we have a valid username for new user.
		$ready = false;

		// Firstly, use Facebook's name as Joomla!'s username.
		$username = JApplication::stringURLSafe($fbUser->name);

		if (CMSocialConnectHelper::isUsernameAvailable($username))
		{
			$ready = true;
		}

		// Secondly, we use Facebook's first name, middle name and last name as Joomla!'s username.
		if (!$ready)
		{
			$names = array();

			if (!empty($fbUser->first_name))
			{
				$names[] = $fbUser->first_name;
			}

			if (!empty($fbUser->last_name))
			{
				$names[] = $fbUser->last_name;
			}

			if (!empty($fbUser->middle_name))
			{
				$names[] = $fbUser->middle_name;
			}

			$username = implode(' ', $names);
			$username = JApplication::stringURLSafe(trim($username));

			if (CMSocialConnectHelper::isUsernameAvailable($username))
			{
				$ready = true;
			}
		}

		// Thirdly, we use the local part of Facebook's email as username.
		if (!$ready)
		{
			$username = JApplication::stringURLSafe(strstr($fbUser->email, '@', true));

			if (CMSocialConnectHelper::isUsernameAvailable($username))
			{
				$ready = true;
			}
		}

		// If the username is still in use,
		// we append a random number until the username is unique.
		if (!$ready)
		{
			$tempUsername = $username . rand(2, 5);

			while ($ready == false)
			{
				if (CMSocialConnectHelper::isUsernameAvailable($tempUsername))
				{
					$ready = true;
				}
			}

			$username = $tempUsername;
		}

		$data['email'] = $email;
		$data['username'] = $username;

		return $data;
	}
}
