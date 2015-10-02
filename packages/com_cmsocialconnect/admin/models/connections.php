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
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   1.0.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'user_id', 'a.user_id',
				'network_id', 'a.network_id',
				'network_user_id', 'a.network_user_id',
				'connected_date', 'a.connected_date',
				'last_login_date', 'a.last_login_date',
				'user_name',
			);
		}

		parent::__construct($config);
	}

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
		// Load the filter state.
		$userId = $this->getUserStateFromRequest($this->context . '.filter.user_id', 'filter_user_id');
		$this->setState('filter.user_id', $userId);

		$networkId = $this->getUserStateFromRequest($this->context . '.filter.network_id', 'filter_network_id');
		$this->setState('filter.network_id', $networkId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_cmsocialconnect');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('a.connected_date', 'desc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 * 
	 * @since   1.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.user_id');
		$id .= ':' . $this->getState('filter.network_id');

		return parent::getStoreId($id);
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
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
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

		// Join over the users for the checked out user.
		$query->select($db->qn('u.username') . ' AS username')
			->join('LEFT',
				$db->qn('#__users') . ' AS u ON ' . $db->qn('u.id') . ' = ' . $db->qn('a.user_id')
			);

		// Filter by user ID.
		$userId = (int) $this->getState('filter.user_id');

		if ($userId > 0)
		{
			$query->where($db->qn('a.user_id') . ' = ' . $db->q($userId));
		}

		// Filter by network ID.
		$networkId = $this->getState('filter.network_id');

		if (!empty($networkId))
		{
			$query->where($db->qn('a.network_id') . ' = ' . $db->q($networkId));
		}

		// Add the list ordering clause.
		$orderCol = $this->state->get('list.ordering', 'a.connected_date');
		$orderDirn = $this->state->get('list.direction', 'DESC');
		$query->order($db->escape($orderCol . ' ' . $orderDirn));

		return $query;
	}
}
