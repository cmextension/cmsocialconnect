<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;


$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'a.connected_date';
?>
<form action="<?php echo JRoute::_('index.php?option=com_cmsocialconnect&view=connections'); ?>" method="post" name="adminForm" id="adminForm">
<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
		<?php
		// Search tools bar
		echo JLayoutHelper::render('searchtools.connections', array('view' => $this));
		?>
		<?php if (empty($this->items)) : ?>
			<div class="alert alert-no-items">
				<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
			</div>
		<?php else : ?>
			<table class="table table-striped" >
				<thead>
					<tr>
						<th width="1%" class="center">
							<?php echo JHtml::_('grid.checkall'); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_CMSOCIALCONNECT_USER', 'user_name', $listDirn, $listOrder); ?>
						</th>
						<th>
							<?php echo JHtml::_('searchtools.sort', 'COM_CMSOCIALCONNECT_NETWORK', 'a.network_id', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_CMSOCIALCONNECT_CONNECTED_DATE', 'a.connected_date', $listDirn, $listOrder); ?>
						</th>
						<th class="hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'COM_CMSOCIALCONNECT_LAST_LOGIN_DATE', 'a.last_login_date', $listDirn, $listOrder); ?>
						</th>
						<th width="1%" class="hidden-phone">
							<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="6">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) : ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
							<?php echo $item->username; ?>
						</td>
						<td>
							<?php echo $item->network_id; ?>
						</td>
						<td class="hidden-phone">
							<?php echo $item->connected_date; ?>
						</td>
						<td class="hidden-phone">
							<?php echo $item->last_login_date; ?>
						</td>
						<td class="hidden-phone">
							<?php echo $item->id; ?>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
