<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_user_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

require_once JPATH_SITE . '/components/com_cmsocialconnect/helpers/cmsocialconnect.php';

/**
 * plg_user_cmsocialconnect's main class.
 *
 * @package     CMSocialConnect
 * @subpackage  plg_user_cmsocialconnect
 * @since       1.0.0
 */
class PlgUserCMSocialConnect extends JPlugin
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Runs on content preparation.
	 *
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareData($context, $data)
	{
		if (!in_array($context, array('com_users.profile', 'com_users.registration')))
		{
			return true;
		}

		$componentParams = JComponentHelper::getParams('com_cmsocialconnect');
		$showInProfile = (int) $componentParams->get('show_in_profile', 1);

		$app = JFactory::getApplication();

		// Registration.
		if ($context == 'com_users.registration')
		{
			$sessionData = $app->getUserState('com_cmsocialconnect.register.data', array(), 'array');

			if (!empty($sessionData))
			{
				if (empty($data->name) && !empty($sessionData['name']))
				{
					$data->name = $sessionData['name'];
				}

				if (empty($data->username) && !empty($sessionData['username']))
				{
					$data->username = $sessionData['username'];
				}

				if (empty($data->email1) && !empty($sessionData['email']))
				{
					$data->email1 = $sessionData['email'];
					$data->email2 = $sessionData['email'];
				}
			}
		}

		// Edit/view profile.
		if ($showInProfile && $context == 'com_users.profile')
		{
			if (!is_object($data))
			{
				return true;
			}

			$userId = isset($data->id) ? $data->id : 0;

			if (isset($data->cmsocialconnect) || $userId <= 0)
			{
				return true;
			}

			// Get available social networks.
			$plugins = array();
			$networks = array();

			JPluginHelper::importPlugin('cmsocialconnect');
			$app->triggerEvent('onSocialGetPlugins', array(&$plugins));

			if (!empty($plugins))
			{
				foreach ($plugins as $plugin)
				{
					if (!isset($networks[$plugin['id']]))
					{
						$networks[$plugin['id']] = array(
							'id'		=> $plugin['id'],
							'name'		=> $plugin['name'],
							'connected'	=> 0,
						);
					}
				}
			}

			$data->cmsocialconnect = array();
			$db = JFactory::getDbo();

			// Get user's connections.
			try
			{
				$query = $db->getQuery(true)
					->select('*')
					->from($db->qn('#__cmsocialconnect_connections'))
					->where($db->qn('user_id') . ' = ' . $db->q($userId));
				$connections = $db->setQuery($query)->loadObjectList();
			}
			catch (RuntimeException $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}

			if (!empty($connections))
			{
				foreach ($connections as $key => $connection)
				{
					if (isset($networks[$connection->network_id]))
					{
						$networks[$connection->network_id]['connected'] = 1;
					}
				}
			}

			$layout = $app->input->getString('layout', '');

			// Edit.
			if ($layout == 'edit')
			{
				if (!empty($networks))
				{
					$data->cmsocialconnect['socialnetworks'] = $networks;
				}
			}
			// View profile.
			else
			{
				$layoutData = array('networks' => $networks);
				$layout = new JLayoutFile('view', $basePath = __DIR__ . '/layouts');
				$data->cmsocialconnect['socialnetworks'] = $layout->render($layoutData);
			}
		}
	}

	/**
	 * Add social network fields to profile form.
	 *
	 * @param   JForm  $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm))
		{
			$this->_subject->setError('JERROR_NOT_A_FORM');

			return false;
		}

		$componentParams = JComponentHelper::getParams('com_cmsocialconnect');
		$showInProfile = (int) $componentParams->get('show_in_profile', 1);

		$name = $form->getName();

		if (!$showInProfile || $name != 'com_users.profile')
		{
			return true;
		}

		JFormHelper::addFieldPath(__DIR__ . '/fields');
		JForm::addFormPath(__DIR__ . '/profiles');
		$form->loadFile('cmsocialconnect', false);

		return true;
	}

	/**
	 * Save social network connection.
	 *
	 * @param   array    $data    Entered user data
	 * @param   boolean  $isNew   True if this is a new user
	 * @param   boolean  $result  True if saving the user worked
	 * @param   string   $error   Error message
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function onUserAfterSave($data, $isNew, $result, $error)
	{
		$app = JFactory::getApplication();
		$userId = JArrayHelper::getValue($data, 'id', 0, 'int');
		$user = JFactory::getUser($userId);
		$db = JFactory::getDbo();
		$params = JComponentHelper::getParams('com_users');

		// New user - registration.
		if ($isNew)
		{
			// Send emails.
			$config = JFactory::getConfig();
			$useractivation = $params->get('useractivation');
			$sendpassword = $params->get('sendpassword', 1);
			$query = $db->getQuery(true);

			// Compile the notification mail values.
			$data['fromname'] = $config->get('fromname');
			$data['mailfrom'] = $config->get('mailfrom');
			$data['sitename'] = $config->get('sitename');
			$data['siteurl'] = JUri::root();

			// Handle account activation/confirmation emails.
			if ($useractivation == 2)
			{
				// Set the link to confirm the user email.
				$uri = JUri::getInstance();
				$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
				$data['activate'] = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);

				$emailSubject = JText::sprintf(
					'COM_USERS_EMAIL_ACCOUNT_DETAILS',
					$data['name'],
					$data['sitename']
				);

				if ($sendpassword)
				{
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY',
						$data['name'],
						$data['sitename'],
						$data['activate'],
						$data['siteurl'],
						$data['username'],
						$data['password_clear']
					);
				}
				else
				{
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ADMIN_ACTIVATION_BODY_NOPW',
						$data['name'],
						$data['sitename'],
						$data['activate'],
						$data['siteurl'],
						$data['username']
					);
				}
			}
			elseif ($useractivation == 1)
			{
				// Set the link to activate the user account.
				$uri = JUri::getInstance();
				$base = $uri->toString(array('scheme', 'user', 'pass', 'host', 'port'));
				$data['activate'] = $base . JRoute::_('index.php?option=com_users&task=registration.activate&token=' . $data['activation'], false);

				$emailSubject = JText::sprintf(
					'COM_USERS_EMAIL_ACCOUNT_DETAILS',
					$data['name'],
					$data['sitename']
				);

				if ($sendpassword)
				{
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY',
						$data['name'],
						$data['sitename'],
						$data['activate'],
						$data['siteurl'],
						$data['username'],
						$data['password_clear']
					);
				}
				else
				{
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_WITH_ACTIVATION_BODY_NOPW',
						$data['name'],
						$data['sitename'],
						$data['activate'],
						$data['siteurl'],
						$data['username']
					);
				}
			}
			else
			{
				$emailSubject = JText::sprintf(
					'COM_USERS_EMAIL_ACCOUNT_DETAILS',
					$data['name'],
					$data['sitename']
				);

				if ($sendpassword)
				{
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_BODY',
						$data['name'],
						$data['sitename'],
						$data['siteurl'],
						$data['username'],
						$data['password_clear']
					);
				}
				else
				{
					$emailBody = JText::sprintf(
						'COM_USERS_EMAIL_REGISTERED_BODY_NOPW',
						$data['name'],
						$data['sitename'],
						$data['siteurl']
					);
				}
			}

			// Send the registration email.
			$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $data['email'], $emailSubject, $emailBody);

			// Send Notification mail to administrators
			if (($params->get('useractivation') < 2) && ($params->get('mail_to_admin') == 1))
			{
				$emailSubject = JText::sprintf(
					'COM_USERS_EMAIL_ACCOUNT_DETAILS',
					$data['name'],
					$data['sitename']
				);

				$emailBodyAdmin = JText::sprintf(
					'COM_USERS_EMAIL_REGISTERED_NOTIFICATION_TO_ADMIN_BODY',
					$data['name'],
					$data['username'],
					$data['siteurl']
				);

				// Get all admin users
				$query->clear()
					->select($db->quoteName(array('name', 'email', 'sendEmail')))
					->from($db->quoteName('#__users'))
					->where($db->quoteName('sendEmail') . ' = ' . 1);

				$db->setQuery($query);

				try
				{
					$rows = $db->loadObjectList();
				}
				catch (RuntimeException $e)
				{
					$this->_subject->setError(JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);

					return false;
				}

				// Send mail to all superadministrators id
				foreach ($rows as $row)
				{
					$return = JFactory::getMailer()->sendMail($data['mailfrom'], $data['fromname'], $row->email, $emailSubject, $emailBodyAdmin);

					// Check for an error.
					if ($return !== true)
					{
						$this->_subject->setError(JText::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'));

						return false;
					}
				}
			}

			// Check for an error.
			if ($return !== true)
			{
				$this->_subject->setError(JText::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'));

				// Send a system message to administrators receiving system mails
				$query->clear()
					->select($db->quoteName(array('name', 'email', 'sendEmail', 'id')))
					->from($db->quoteName('#__users'))
					->where($db->quoteName('block') . ' = ' . (int) 0)
					->where($db->quoteName('sendEmail') . ' = ' . (int) 1);
				$db->setQuery($query);

				try
				{
					$sendEmail = $db->loadColumn();
				}
				catch (RuntimeException $e)
				{
					$this->_subject->setError(JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);

					return false;
				}

				if (count($sendEmail) > 0)
				{
					$jdate = new JDate;

					// Build the query to add the messages
					foreach ($sendEmail as $userid)
					{
						$values = array(
							$db->quote($userid),
							$db->quote($userid),
							$db->quote($jdate->toSql()),
							$db->quote(JText::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT')),
							$db->quote(JText::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $return, $data['username']))
						);
						$query->clear()
							->insert($db->quoteName('#__messages'))
							->columns($db->quoteName(array('user_id_from', 'user_id_to', 'date_time', 'subject', 'message')))
							->values(implode(',', $values));
						$db->setQuery($query);

						try
						{
							$db->execute();
						}
						catch (RuntimeException $e)
						{
							$this->_subject->setError(JText::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 500);

							return false;
						}
					}
				}

				return false;
			}

			$socialData = $app->getUserState('com_cmsocialconnect.register.data');
			$now = JFactory::getDate()->toSql();

			if ($userId && $result && isset($socialData) && (count($socialData)))
			{
				try
				{
					// Check if this social account is already connected to a different Joomla! user.
					$query->clear()
						->select('COUNT(*)')
						->from($db->qn('#__cmsocialconnect_connections'))
						->where($db->qn('network_id') . ' = ' . $db->q($socialData['network_id']))
						->where($db->qn('network_user_id') . ' = ' . $db->q($socialData['network_user_id']));
					$count = $db->setQuery($query)->loadResult();
				}
				catch (RuntimeException $e)
				{
					$this->_subject->setError($e->getMessage());

					return false;
				}

				if ($count > 0)
				{
					$this->_subject->setError(JText::_('PLG_USER_CMSOCIALCONNECT_DUPLICATED_CONNECTION'));

					return false;
				}

				try
				{
					$query->clear()
						->insert($db->qn('#__cmsocialconnect_connections'))
						->columns(
							array(
								$db->qn('user_id'),
								$db->qn('network_id'),
								$db->qn('network_user_id'),
								$db->qn('connected_date'),
								$db->qn('last_login_date')
							)
						)->values(
							$db->q($userId) . ', '
								. $db->q($socialData['network_id']) . ', '
								. $db->q($socialData['network_user_id']) . ', '
								. $db->q($now) . ', '
								. $db->q($db->getNullDate())
						);

					$db->setQuery($query)->execute();
				}
				catch (RuntimeException $e)
				{
					$this->_subject->setError($e->getMessage());

					return false;
				}

				if ($useractivation == 1)
				{
					$app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_VERIFY'));
					$app->redirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
				}
				elseif ($useractivation == 2)
				{
					$app->enqueueMessage(JText::_('COM_USERS_REGISTRATION_COMPLETE_ACTIVATE'));
					$app->redirect(JRoute::_('index.php?option=com_users&view=registration&layout=complete', false));
				}
				else
				{
					$options = array('action' => 'core.login.site');

					// Actually we don't need password here.
					$response = array(
						'username'	=> $data['username'],
						'password'	=> $data['password1']
					);

					$result = $app->triggerEvent('onUserLogin', array($response, $options));

					if ($result)
					{
						// Update last login date.
						$socialNetworkId = $socialData['network_id'];
						$socialNetworkUserId = $socialData['network_user_id'];

						$app->setUserState('com_cmsocialconnect.register.data', null);

						CMSocialConnectHelper::updateLastLogin($userId, $socialNetworkId, $socialNetworkUserId);
					}

					$app->redirect('index.php');
				}
			}
		}
		// Logged-in user - edit profile.
		else
		{
			$network = $app->input->post->getString('cmsc_network', '');
			$task = $app->input->post->getString('cmsc_task', '');
			$return = $app->input->post->get('return', null, 'base64');

			if ($task == 'connect')
			{
				$app->setUserState('com_cmsocialconnect.connect.return', $return);
				$redirectUrl = 'index.php?option=com_cmsocialconnect&task=connect.socialConnect' .
				'&network=' . $network . '&return=' . $return;
				$app->redirect($redirectUrl);
			}
			elseif ($task == 'disconnect')
			{
				$app->setUserState('com_cmsocialconnect.disconnect.return', $return);
				$redirectUrl = 'index.php?option=com_cmsocialconnect&task=connect.socialDisconnect' .
				'&network=' . $network . '&return=' . $return;
				$app->redirect($redirectUrl);
			}
		}

		return true;
	}

	/**
	 * Delete connection.
	 *
	 * @param   array    $user     Holds the user data
	 * @param   boolean  $success  True if user was succesfully stored in the database
	 * @param   string   $msg      Message
	 *
	 * @return  boolean
	 */
	public function onUserAfterDelete($user, $success, $msg)
	{
		if (!$success)
		{
			return false;
		}

		$userId = JArrayHelper::getValue($user, 'id', 0, 'int');

		if ($userId)
		{
			try
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete($db->qn('#__cmsocialconnect_connections'))
					->where($db->qn('user_id') . ' = ' . $db->q($userId));
				$db->setQuery($query)->execute();
			}
			catch (Exception $e)
			{
				$this->_subject->setError($e->getMessage());

				return false;
			}
		}

		return true;
	}
}
