<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Connection table.
 *
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @since       1.0.0
 */
class CMSocialConnectTableConnection extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabaseDriver  &$db  Database driver object.
	 *
	 * @since   1.0.0
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__cmsocialconnect_connections', 'id', $db);
	}
}
