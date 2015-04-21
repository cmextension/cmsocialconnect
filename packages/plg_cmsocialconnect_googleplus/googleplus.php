<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_googleplus
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Plugin to integrate with Google+ social network.
 *
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_googleplus
 * @since       1.0.0
 */
class PlgCMSocialConnectGooglePlus extends JPlugin
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
	protected $buttonClasses = array('cmsc-button', 'cmsc-google-plus');

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

		$this->pluginId = 'googleplus';
		$this->pluginName = JText::_('PLG_CMSOCIALCONNECT_GOOGLEPLUS_NAME');
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

		$gpUser = $this->getUser($redirectUrl);

		if ($gpUser === false)
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_ERROR_GET_SOCIAL_NETWORK_USER_FAILURE', $this->pluginName));
			$app->redirect(JRoute::_($returnUrl, false));
		}

		// Log user in if this social account was already connected.
		$connection = CMSocialConnectHelper::getConnection($pluginId, $gpUser->id);

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
		$sessionData = $this->prepareData($gpUser);

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

		$gpUser = $this->getUser($redirectUrl);

		if ($gpUser === false)
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_ERROR_GET_SOCIAL_NETWORK_USER_FAILURE', $this->pluginName));

			return false;
		}

		// Check if this social account was already connected by another user.
		$connection = CMSocialConnectHelper::getConnection($pluginId, $gpUser->id);

		if (!isset($connection->id) || (int) $connection->id <= 0)
		{
			$sessionData = $this->prepareData($gpUser);

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

			CMSocialConnectHelper::updateLastLogin($user->get('id'), $this->pluginId, $gpUser->id);

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

		$gpUser = $this->getUser($redirectUrl);

		if ($gpUser === false)
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_ERROR_GET_SOCIAL_NETWORK_USER_FAILURE', $this->pluginName));

			return false;
		}

		// Check if this social account was already connected by another user.
		$connection = CMSocialConnectHelper::getConnection($pluginId, $gpUser->id);

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
			'network_user_id'	=> $gpUser->id,
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
		$app = JFactory::getApplication();
		$clientId = $this->params->get('client_id');
		$clientSecret = $this->params->get('client_secret');

		// We get code after successful authentication.
		$code = $app->input->get('code', '', 'string');

		if (empty($code))
		{
			$authUrl = 'https://accounts.google.com/o/oauth2/auth';

			$params = array(
				'response_type'		=> 'code',
				'client_id'			=> $clientId,
				'redirect_uri'		=> $redirectUrl,
				'scope'				=> 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email',
			);

			$authUrl = $authUrl . '?' . http_build_query($params);

			header('Location:' . $authUrl);

			$app->close();
		}
		else
		{
			$authUrl = 'https://accounts.google.com/o/oauth2/token';

			$params = array(
				'code'			=> $code,
				'client_id'		=> $clientId,
				'client_secret'	=> $clientSecret,
				'redirect_uri'	=> $redirectUrl,
				'grant_type'	=> 'authorization_code'
			);

			$requestQuery = http_build_query($params);

			$curlOptions = array (
				CURLOPT_URL => $authUrl,
				CURLOPT_VERBOSE => 1,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_POST => 1,
				CURLOPT_POSTFIELDS => $requestQuery
			);

			$ch = curl_init();
			curl_setopt_array($ch, $curlOptions);

			$authResponse = curl_exec($ch);

			if (curl_errno($ch))
			{
				$app->enqueueMessage(curl_error($ch));
				curl_close($ch);
			}
			else
			{
				curl_close($ch);
			}

			// Convert response to object.
			$authData = json_decode($authResponse);

			if (!empty($authData->error))
			{
				$message = JText::_('PLG_CMSOCIALCONNECT_GOOGLEPLUS_CONNECT_ERROR');
				$message .= ' (' . JText::_('PLG_CMSOCIALCONNECT_GOOGLEPLUS_ERROR') . $authData->error;

				if (!empty($authData->error_description))
				{
					$message . ' - ' . $authData->error_description;
				}

				$message .= ')';
				$app->enqueueMessage($message);

				return false;
			}
			else
			{
				if (!empty($authData->access_token))
				{
					$url = 'https://www.googleapis.com/oauth2/v1/userinfo?access_token=' . $authData->access_token;

					$curlOptions = array (
						CURLOPT_URL => $url,
						CURLOPT_VERBOSE => 1,
						CURLOPT_SSL_VERIFYPEER => false,
						CURLOPT_RETURNTRANSFER => 1
					);

					$ch = curl_init();
					curl_setopt_array($ch, $curlOptions);

					$userResponse = curl_exec($ch);

					if (curl_errno($ch))
					{
						$app->enqueueMessage(curl_error($ch));
						curl_close($ch);
					}
					else
					{
						curl_close($ch);
					}

					// Convert response to object.
					$userData = json_decode($userResponse);

					if (empty($userResponse) || !empty($userData->error))
					{
						$message = JText::_('PLG_CMSOCIALCONNECT_GOOGLEPLUS_CONNECT_ERROR');

						if (!empty($userData->error))
						{
							$message .= ' (' . JText::_('PLG_CMSOCIALCONNECT_GOOGLEPLUS_ERROR') . $userData->error;

							if (!empty($userData->error_description))
							{
								$message . ' - ' . $userData->error_description;
							}

							$message .= ')';
						}

						$app->enqueueMessage($message);
					}
					else
					{
						return $userData;
					}
				}
			}
		}
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
	 * @param   object  $gpUser  The data of social network account.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	protected function prepareData($gpUser)
	{
		$app = JFactory::getApplication();

		$data = array(
			'network_name'		=> $this->pluginName,
			'network_id'		=> $this->pluginId,
			'network_user_id'	=> $gpUser->id,
			'username'			=> '',
			'email'				=> $gpUser->email,
			'name'				=> $gpUser->name,
		);

		// Email is already in use, notify user.
		if (CMSocialConnectHelper::isEmailInUse($gpUser->email))
		{
			$app->enqueueMessage(JText::sprintf('COM_CMSOCIALCONNECT_REGISTRATION_DUPLICATED_EMAIL', $email));
		}

		// Generate username.
		$ready = false;

		// Firstly, we use Google+ URL.
		if (strpos($gpUser->link, '+') !== false)
		{
			$parts = explode($gpUser->link, '+');

			if (!empty($parts[1]))
			{
				$username = JApplication::stringURLSafe($parts[1]);

				if (CMSocialConnectHelper::isUsernameAvailable($username))
				{
					$ready = true;
				}
			}
		}

		// Secondly, use Google+'s name as Joomla!'s username.
		$username = JApplication::stringURLSafe($gpUser->name);

		if (CMSocialConnectHelper::isUsernameAvailable($username))
		{
			$ready = true;
		}

		// Thirdly, we use the local part of email as username.
		if (!$ready)
		{
			$username = JApplication::stringURLSafe(strstr($gpUser->email, '@', true));

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
