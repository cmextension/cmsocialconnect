<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Model class for social network connections.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectModelConnection extends JModelLegacy
{
	/**
	 * Delete connection of a social network.
	 *
	 * @param   integer  $userId     The ID of the user.
	 * @param   string   $networkId  The ID of the social network.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function deleteConnection($userId, $networkId)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->qn('#__cmsocialconnect_connections'))
			->where($db->qn('user_id') . ' = ' . $db->q((int) $userId))
			->where($db->qn('network_id') . ' = ' . $db->q($networkId));
		$db->setQuery($query)->execute();

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);

			return false;
		}

		return true;
	}

	/**
	 * Save connection of a social network.
	 *
	 * @param   integer  $userId  The ID of the user.
	 * @param   array    $data    The data from the social network.
	 *                            array(
	 *                                'network_id' => The social network ID,
	 *                                'network_user_id' => The social account ID.
	 *                            );
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function saveConnection($userId, $data)
	{
		$now = JFactory::getDate()->toSql();
		$db = $this->getDbo();
		$query = $db->getQuery(true)
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
					. $db->q($data['network_id']) . ', '
					. $db->q($data['network_user_id']) . ', '
					. $db->q($now) . ', '
					. $db->q($db->getNullDate())
			);

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
