<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;
?>
<?php if (!empty( $this->sidebar)) : ?>
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
<?php else : ?>
<div id="j-main-container">
<?php endif;?>
	<legend>CM Social Connect</legend>
	<ul>
		<li><strong>Version</strong>: 1.0.0</li>
		<li><strong>Released</strong>: April 22, 2015</li>
		<li><strong>Author</strong>: <a href="http://www.cmext.vn/" target="_blank">CMExtension team</a></li>
		<li><strong>License</strong>: <a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">GNU General Public License version 2</a> (or, at your option, any later version)</li>
		<li><strong>Special thanks to</strong>:
			<ul>
				<li>The developers and contributors of Joomla! CMS</li>
				<li>The integrated social networks</li>
				<li><a href="http://fontawesome.io" target="_blank">Font Awesome</a></li></li>
			</ul>
		</li>
	</ul>
</div>
