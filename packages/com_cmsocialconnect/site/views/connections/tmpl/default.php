<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$layoutData = array('networks' => $this->networks);
$layout = new JLayoutFile('edit', $basePath = JPATH_SITE . '/components/com_cmsocialconnect/layouts');

$html = $layout->render($layoutData);
?>
<div class="cmsocialconnect<?php echo $this->pageclass_sfx?>">
	<?php if ($this->params->get('show_page_heading')) : ?>
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php endif; ?>

	<form method="post" action="index.php?option=com_cmsocialconnect&task=connections.manage">
		<?php echo $html; ?>
	</form>
</div>
