<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Connections model.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectModelConnections extends JModelList
{
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the parameters.
		$params = JFactory::getApplication()->getParams();
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.connected_date', 'desc');
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.0.0
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$user = JFactory::getUser();

		$query->select(
				$db->qn(
					array(
						'a.id',
						'a.user_id',
						'a.network_id',
						'a.network_user_id',
						'a.connected_date',
						'a.last_login_date'
					)
				)
			)
			->from($db->qn('#__cmsocialconnect_connections') . ' AS a');

		$query->where($db->qn('a.user_id') . ' = ' . $db->q($user->get('id')));

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.connected_date');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
