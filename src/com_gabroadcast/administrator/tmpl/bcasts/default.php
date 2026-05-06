<?php
/**
 * @version    4.3.3
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
/** This places the column filter button */
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user      = Factory::getApplication()->getIdentity();
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_gabroadcast');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_gabroadcast&task=bcasts.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'bcastList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();
?>

<form action="<?php echo Route::_('index.php?option=com_gabroadcast&view=bcasts'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

			<div class="clearfix"></div>
			<table class="table table-striped" id="bcastList">
				<thead>
				<tr>
					<?php if (isset($this->items[0]->ordering)): ?>
						<th width="1%" class="nowrap center hidden-phone">
                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.`ordering`', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
                        </th>
					<?php endif; ?>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value=""
							   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<?php if (isset($this->items[0]->state)): ?>
						<th width="1%" class="nowrap center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.`state`', $listDirn, $listOrder); ?>
						</th>
					<?php endif; ?>

					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GABROADCAST_BCASTS_ATTACH_LAB', 'a.`attach_lab`', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GABROADCAST_BCASTS_ATTACH_DIR', 'a.`attach_dir`', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GABROADCAST_CREATED_DATE', 'a.`created_date`', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GABROADCAST_ID', 'a.`id`', $listDirn, $listOrder); ?>
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
					$canCreate  = $user->authorise('core.create', 'com_gabroadcast');
					$canEdit    = $user->authorise('core.edit', 'com_gabroadcast');
					$canCheckin = $user->authorise('core.manage', 'com_gabroadcast');
					$canChange  = $user->authorise('core.edit.state', 'com_gabroadcast');
					?>
					<tr class="row<?php echo $i % 2; ?>">

						<?php if (isset($this->items[0]->ordering)) : ?>
							<td class="order nowrap center hidden-phone">
								<?php if ($canChange) : ?>
									<?php $disableClassName = ''; ?>
									<?php $disabledLabel    = ''; ?>

									<?php if (!$saveOrder) : ?>
										<?php $disabledLabel    = Text::_('JORDERINGDISABLED'); ?>
										<?php $disableClassName = 'inactive tip-top'; ?>
									<?php endif; ?>

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
						<td class="hidden-phone">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<?php if (isset($this->items[0]->state)): ?>
							<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'bcasts.', $canChange, 'cb'); ?>
							</td>
						<?php endif; ?>

						<td>
						<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
							<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'bcasts.', $canCheckin); ?>
						<?php endif; ?>
						<?php if ($canEdit) : ?>
							<a href="<?php echo Route::_('index.php?option=com_gabroadcast&task=bcast.edit&id='.(int) $item->id); ?>">
							<?php echo $this->escape($item->attach_lab); ?></a>
						<?php else : ?>
							<?php echo $this->escape($item->attach_lab); ?>
						<?php endif; ?>
		
						</td>
						<td>
							<?php echo $item->attach_dir; ?>
						</td>
						<td>
							<?php echo substr($item->created_date,0,10); ?>
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
