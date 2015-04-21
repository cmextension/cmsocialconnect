<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_twitter
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Plugin to integrate with Twitter social network.
 *
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_twitter
 * @since       1.0.0
 */
class PlgCMSocialConnectTwitter extends JPlugin
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
	protected $buttonClasses = array('cmsc-button', 'cmsc-twitter');

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

		$this->pluginId = 'twitter';
		$this->pluginName = JText::_('PLG_CMSOCIALCONNECT_TWITTER_NAME');
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

		$twtUser = $this->getUser($redirectUrl);

		if ($twtUser === false)
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_ERROR_GET_SOCIAL_NETWORK_USER_FAILURE', $this->pluginName));
			$app->redirect(JRoute::_($returnUrl, false));
		}

		// Log user in if this social account was already connected.
		$connection = CMSocialConnectHelper::getConnection($pluginId, $twtUser->id);

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
		$sessionData = $this->prepareData($twtUser);

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

		$twtUser = $this->getUser($redirectUrl);

		if ($twtUser === false)
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_ERROR_GET_SOCIAL_NETWORK_USER_FAILURE', $this->pluginName));

			return false;
		}

		// Check if this social account was already connected by another user.
		$connection = CMSocialConnectHelper::getConnection($pluginId, $twtUser->id);

		if (!isset($connection->id) || (int) $connection->id <= 0)
		{
			$sessionData = $this->prepareData($twtUser);

			$app->setUserState('com_cmsocialconnect.register.data', $sessionData);

			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_LOGIN_NO_CONNECTIONS', $this->pluginName));

			$app->redirect(JRoute::_('index.php?option=com_users&view=registration', false));
		}

		// Check if user exists.
		$user = JFactory::getUser($connection->user_id);

		if ($user->get('guest'))
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_LOGIN_NO_USERS', $this->pluginName));
			$app->redirect(JRoute::_($returnUrl, false));
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

			CMSocialConnectHelper::updateLastLogin($user->get('id'), $this->pluginId, $twtUser->id);

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

		$twtUser = $this->getUser($redirectUrl);

		if ($twtUser === false)
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_ERROR_GET_SOCIAL_NETWORK_USER_FAILURE', $this->pluginName));

			return false;
		}

		// Check if this social account was already connected by another user.
		$connection = CMSocialConnectHelper::getConnection($pluginId, $twtUser->id);

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
			'network_user_id'	=> $twtUser->id,
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
		require_once __DIR__ . '/libraries/twitter/cmtwitter.php';
		require_once __DIR__ . '/libraries/twitter/cmusers.php';
		jimport('joomla.twitter.oauth.php');
		jimport('joomla.twitter.profile');

		$app = JFactory::getApplication();
		$appId = $this->params->get('consumer_key');
		$appSecret = $this->params->get('consumer_secret');

		$oauthOptions = new JRegistry;
		$oauthOptions->set('callback', $redirectUrl);
		$oauthOptions->set('consumer_key', $appId);
		$oauthOptions->set('consumer_secret', $appSecret);
		$oauthOptions->set('sendheaders', 1);

		// We use authenticateURL for one-time authentication.
		$oauthOptions->set('authoriseURL', 'https://api.twitter.com/oauth/authenticate');

		$jTwitterOauth = new JTwitterOAuth($oauthOptions);

		// Authenticate. Will redirect to Twitter if there is no correct code.
		try
		{
			$jTwitterOauth->authenticate();
		}
		catch (RuntimeException $e)
		{
			$app->enqueueMessage(500, $e->getMessage());

			return false;
		}

		$cmTwitter = new CMTwitter($jTwitterOauth);

		$jTwitterProfile = $cmTwitter->__get('profile');

		$settings = $jTwitterProfile->getSettings();

		$screenName = $settings->screen_name;

		$twtUser = $cmTwitter->users->getUser($screenName);

		return $twtUser;
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
	 * @param   object  $twtUser  The data of social network account.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function prepareData($twtUser)
	{
		$app = JFactory::getApplication();

		$data = array(
			'network_name'		=> $this->pluginName,
			'network_id'		=> $this->pluginId,
			'network_user_id'	=> $twtUser->id,
			'username'			=> '',
			'email'				=> '',
			'name'				=> $twtUser->name,
		);

		// Use Twitter's screen name as Joomla!'s username.
		$username = JApplication::stringURLSafe($twtUser->screen_name);

		if (CMSocialConnectHelper::isUsernameAvailable($username))
		{
			$ready = true;
		}

		// Try Twitter's name as Joomla!'s username.
		if (!$ready)
		{
			$username = JApplication::stringURLSafe($twtUser->name);

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

		$data['username'] = $username;

		return $data;
	}
}
