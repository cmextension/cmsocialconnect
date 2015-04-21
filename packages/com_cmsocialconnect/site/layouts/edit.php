<?php
/**
 * @package     CMSocialConnect
 * @subpackage  plg_user_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$networks = $displayData['networks'];

JFactory::getDocument()->addScript('components/com_cmsocialconnect/assets/js/cmsocialconnect.js');

$html = '';

if (!is_array($networks) || count($networks) == 0)
{
	return;
}

$return = CMSocialConnectHelper::getReturnUrl();
?>
<table class="table">
	<thead>
		<tr>
			<th><?php echo JText::_('COM_CMSOCIALCONNECT_SOCIAL_NETWORKS'); ?></th>
			<th><?php echo JText::_('COM_CMSOCIALCONNECT_STATUS'); ?></th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($networks as $network) : ?>
		<tr>
			<td><?php echo $network['name']; ?></td>
			<td>
			<?php if ($network['connected']) : ?>
				<?php echo JText::_('COM_CMSOCIALCONNECT_CONNECTED'); ?></span>
			<?php else : ?>
				<?php echo JText::_('COM_CMSOCIALCONNECT_DISCONNECTED'); ?></span>
			<?php endif; ?>
			</td>
			<td>
			<?php if ($network['connected']) : ?>
				<span class="btn btn-small btn-danger" onclick="socialDisconnect('<?php echo $network['id']; ?>')"><?php echo JText::_('COM_CMSOCIALCONNECT_DISCONNECT'); ?></span>
			<?php else : ?>
				<span class="btn btn-small btn-success" onclick="socialConnect('<?php echo $network['id']; ?>')"><?php echo JText::_('COM_CMSOCIALCONNECT_CONNECT'); ?></span>
			<?php endif; ?>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<input type="hidden" id="cmsc_network" name="cmsc_network" value="">
<input type="hidden" id="cmsc_task" name="cmsc_task" value="">
<input type="hidden" name="return" value="<?php echo $return; ?>">
