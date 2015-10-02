<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_twitter
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

jimport('joomla.twitter.users');

/**
 * Class for interacting with a Twitter API instance.
 *
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_twitter
 * @since       1.0.0
 */
class CMTwitterUsers extends JTwitterUsers
{
	/**
	 * Method to get extended information of a given user, specified by ID or screen name as per the required id parameter.
	 *
	 * @param   mixed    $user      Either an integer containing the user ID or a string containing the screen name.
	 * @param   boolean  $entities  Set to true to return IDs as strings, false to return as integers.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   1.0.0
	 * @throws  RuntimeException
	 */
	public function getUser($user, $entities = null)
	{
		// Check the rate limit for remaining hits
		$this->checkRateLimit('users', 'show/:id');

		// Determine which type of data was passed for $user
		if (is_numeric($user))
		{
			$data['user_id'] = $user;
		}
		elseif (is_string($user))
		{
			$data['screen_name'] = $user;
		}
		else
		{
			// We don't have a valid entry
			throw new RuntimeException('The specified username is not in the correct format; must use integer or string');
		}

		// Set the API path
		$path = '/users/show.json';

		// Check if entities is specified
		if (!is_null($entities))
		{
			$data['include_entities'] = $entities;
		}

		// Send the request.
		return $this->sendRequest($path, 'GET', $data);
	}
}
