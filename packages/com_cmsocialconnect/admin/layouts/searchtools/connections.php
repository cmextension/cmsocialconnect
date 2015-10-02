<?php
/**
 * @package     CMSocialConnect
 * @subpackage  com_cmsocialconnect
 * @copyright   Copyright (C) 2015 CMExtension Team http://www.cmext.vn/
 * @license     GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

// Set some basic options
$customOptions = array(
	'filtersHidden'       => isset($data['options']['filtersHidden']) ? $data['options']['filtersHidden'] : empty($data['view']->activeFilters),
	'defaultLimit'        => isset($data['options']['defaultLimit']) ? $data['options']['defaultLimit'] : JFactory::getApplication()->get('list_limit', 20),
	'searchFieldSelector' => '#filter_search',
	'orderFieldSelector'  => '#list_fullordering'
);

$data['options'] = array_merge($customOptions, $data['options']);

$formSelector = !empty($data['options']['formSelector']) ? $data['options']['formSelector'] : '#adminForm';

// Load search tools.
JHtml::_('searchtools.form', $formSelector, $data['options']);

$script = array();
$script[] = 'function clearFilters() {';
$script[] = "	document.getElementById('filter_user_id').value='';";
$script[] = "	document.getElementById('filter_user_id_id').value='';";
$script[] = "	document.getElementById('filter_network_id').value='';";
$script[] = "	document.adminForm.submit();";
$script[] = "}";

JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
?>
<div class="js-stools clearfix">
	<div class="clearfix">
		<div class="js-stools-container-bar">
			<?php echo JLayoutHelper::render('joomla.searchtools.default.filters', $data); ?>
			<div class="btn-wrapper btn-group">
				<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
					<i class="icon-search"></i>
				</button>
				<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="clearFilters();">
					<i class="icon-delete"></i>
				</button>
			</div>
		</div>
		<div class="js-stools-container-list hidden-phone hidden-tablet">
			<?php echo JLayoutHelper::render('joomla.searchtools.default.list', $data); ?>
		</div>
	</div>
</div>
