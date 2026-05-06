<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;


use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
/** This places the column filter button */
$wa->useScript('table.columns')
    ->useScript('multiselect');
$wa->usePreset('com_gacalevents.gacaleventspreset');

$user      = Factory::getApplication()->getIdentity();
$listOrder = $this->state->get('list.ordering', 'a.depart_date');
$listDirn  = $this->state->get('list.direction', 'DESC');
$canOrder  = $user->authorise('core.edit.state', 'com_gacalevents');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_gacalevents&task=events.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'eventList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>

<form action="<?php echo Route::_('index.php?option=com_gacalevents&view=events'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="eventList">
					<thead>
					<tr>
						<?php if (isset($this->items[0]->ordering)): ?>
							<th width="1%" class="nowrap center hidden-phone">
	                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
	                        </th>
						<?php endif; ?>
						<th width="1%" >
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<?php if (isset($this->items[0]->state)): ?>
							<th width="1%" class="nowrap center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>

						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GACALEVENTS_EVENTS_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GACALEVENTS_EVENTS_DEPART_DATE', 'a.depart_date', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GACALEVENTS_EVENTS_RETURN_DATE', 'a.return_date', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GACALEVENTS_EVENTS_LEADER', 'a.leader', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GACALEVENTS_ID', 'a.id', $listDirn, $listOrder); ?>
						</th>

						
					</tr>
					</thead>
					<tfoot>
					<tr>
						<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
							<?php echo $this->pagination->getListFooter(); ?>
						</td>
					</tr>
					</tfoot>
					<tbody>
					<?php foreach ($this->items as $i => $item) :
						$ordering   = ($listOrder == 'a.ordering');
						$canCreate  = $user->authorise('core.create', 'com_gacalevents');
						$canEdit    = $user->authorise('core.edit', 'com_gacalevents');
						$canCheckin = $user->authorise('core.manage', 'com_gacalevents');
						$canChange  = $user->authorise('core.edit.state', 'com_gacalevents');
						?>
						<tr class="row<?php echo $i % 2; ?>">

							<?php if (isset($this->items[0]->ordering)) : ?>
								<td class="order nowrap center hidden-phone">
									<?php if ($canChange) :
										$disableClassName = '';
										$disabledLabel    = '';

										if (!$saveOrder) {
											$disabledLabel    = Text::_('JORDERINGDISABLED');
											$disableClassName = 'inactive tip-top';
										}
									?>
										<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>"
											  title="<?php echo $disabledLabel ?>">
											<i class="icon-menu"></i>
										</span>
										<input type="text" style="display:none" name="order[]" size="5"
											   value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
									<?php else : ?>
										<span class="sortable-handler inactive">
											<i class="icon-menu"></i>
										</span>
									<?php endif; ?>
								</td>
							<?php endif; ?>
							<td >
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<?php if (isset($this->items[0]->state)): ?>
								<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'events.', $canChange, 'cb'); ?>
								</td>
							<?php endif; ?>

							<td>
								<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'events.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo Route::_('index.php?option=com_gacalevents&task=event.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php
								$ddate = $item->depart_date;
								echo $ddate > 0 ? HTMLHelper::_('date', $ddate, Text::_('COM_GACALEVENTS_DISPLAY_DATE_ABRV')) : '-';
								?>
							</td>
							<td>
								<?php
								$rdate = $item->return_date;
								echo $rdate > 0 ? HTMLHelper::_('date', $rdate, Text::_('COM_GACALEVENTS_DISPLAY_DATE_ABRV')) : '-';
								?>
							</td>
							<td>
								<?php echo $item->leader; ?>
							</td>
							<td>
								<?php echo $item->id; ?>
							</td>

						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
	            <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
