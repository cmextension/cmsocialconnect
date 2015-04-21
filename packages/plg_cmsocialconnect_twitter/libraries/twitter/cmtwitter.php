<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_twitter
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

jimport('joomla.twitter.twitter');

/**
 * Class for interacting with a Twitter API instance.
 *
 * @package     CMSocialConnect
 * @subpackage  plg_cmsocialconnect_twitter
 * @since       1.0.0
 */
class CMTwitter extends JTwitter
{
	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve
	 *
	 * @return  JTwitterObject  Twitter API object (statuses, users, favorites, etc.).
	 *
	 * @since   1.0.0
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = ($name != 'users') ? 'JTwitter' : 'CMTwitter';
		$class .= ucfirst($name);

		if (class_exists($class))
		{
			if (false == isset($this->$name))
			{
				$this->$name = new $class($this->options, $this->client, $this->oauth);
			}

			return $this->$name;
		}

		throw new InvalidArgumentException(sprintf('Argument %s produced an invalid class name: %s', $name, $class));
	}
}
