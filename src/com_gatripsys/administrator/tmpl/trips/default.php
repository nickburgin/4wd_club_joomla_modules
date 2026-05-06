<?php
/**
 * @version    5.1.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user      = GatripsysHelper::getSpecificUser();
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_gatripsys');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_gatripsys&task=trips.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'tripList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
?>

<form action="<?php echo Route::_('index.php?option=com_gatripsys&view=trips'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="tripList">
					<thead>
					<tr>
                        <td class="w-1 text-center">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </td>
						<?php if (isset($this->items[0]->ordering)): ?>
							<th width="1%" class="nowrap center hidden-phone">
	                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
	                        </th>
						<?php endif; ?>
						<?php if (isset($this->items[0]->state)): ?>
							<th width="1%" class="nowrap center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>

						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_TRIPS_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_TRIPS_RATING', 'a.rating', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_TRIPS_LEADER', 'a.leader', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_TRIPS_MINMAX', 'a.max_no', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_TRIPS_DEPT_DATE', 'a.dept_date', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_TRIPS_RET_DATE', 'a.ret_date', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_TRIPS_DEPT_LOC', 'a.dept_loc', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_TRIPS_RET_LOC', 'a.ret_loc', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
							<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_ID', 'a.id', $listDirn, $listOrder); ?>
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
						$canCreate  = $user->authorise('core.create', 'com_gatripsys');
						$canEdit    = GatripsysHelper::canUserEdit($user, $item);
						$canCheckin = $user->authorise('core.manage', 'com_gatripsys');
						$canChange  = $user->authorise('core.edit.state', 'com_gatripsys');
						?>
						<tr class="row<?php echo $i % 2; ?>">

							<td >
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
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
										<span class="sortable-handler inactive"><i class="icon-menu"></i></span>
									<?php endif; ?>
								</td>
							<?php endif; ?>
							<?php if (isset($this->items[0]->state)): ?>
								<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'trips.', $canChange, 'cb'); ?>
								</td>
							<?php endif; ?>

							<td>
								<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'trips.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=trip.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</td>
							<td><?php echo $item->rating_name; ?></td>
							<td><?php echo $item->leader_name; ?></td>
							<td>
								<?php echo $max_no = $item->max_no == 0 ? Text::_('COM_GATRIPSYS_TRIPS_UNLIMTED') : $item->min_no.' - '.$item->max_no; ?>
							</td>
							<td>
								<?php $tdate = $item->dept_date;
									echo $tdate > 0 ? HTMLHelper::_('date', $tdate, Text::_('COM_GATRIPSYS_DISPLAY_DATETXT')) : '-'; ?>
							</td>
							<td>
								<?php $cdate = $item->ret_date;
									echo $cdate > 0 ? HTMLHelper::_('date', $cdate, Text::_('COM_GATRIPSYS_DISPLAY_DATETXT')) : '-'; ?>
							</td>
							<td><?php echo $item->dept_loc; ?></td>
							<td><?php echo $item->ret_loc; ?></td>
							<td><?php echo $item->id; ?></td>

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
<script>
    window.toggleField = function (id, task, field) {

        var f = document.adminForm, i = 0, cbx, cb = f[ id ];

        if (!cb) return false;

        while (true) {
            cbx = f[ 'cb' + i ];

            if (!cbx) break;

            cbx.checked = false;
            i++;
        }

        var inputField   = document.createElement('input');

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild(inputField);

        cb.checked = true;
        f.boxchecked.value = 1;
        window.submitform(task);

        return false;
    };
</script>
