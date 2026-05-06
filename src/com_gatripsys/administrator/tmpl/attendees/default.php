<?php
/**
 * @version     5.1.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

// no direct access
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

$user	= GatripsysHelper::getSpecificUser();
$userId	= $user->get('id');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$canOrder	= $user->authorise('core.edit.state', 'com_gatripsys');
$saveOrder	= $listOrder == 'a.ordering';
if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_gatripsys&task=attendees.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'attendeeList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}
$sortFields = $this->getSortFields();


/*
echo '<pre>Test<br />';
print_r($this->items);
echo '</pre>';
print_r(Factory::getApplication()->getUserState('com_gatripsys.test.data'));
*/

?>

<form action="<?php echo Route::_('index.php?option=com_gatripsys&view=attendees'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

			<div class="clearfix"></div>
		<table class="table table-striped" id="attendeeList">
			<thead>
				<tr>
                    <td class="w-1 text-center">
                        <?php echo HTMLHelper::_('grid.checkall'); ?>
                    </td>
                <?php if (isset($this->items[0]->ordering)): ?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
					</th>
                <?php endif; ?>
                <?php if (isset($this->items[0]->state)): ?>
					<th width="1%" class="nowrap center">
						<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
                <?php endif; ?>
                    
				<th class='left'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_ATTENDEES_USER_ID', 'a.user_name', $listDirn, $listOrder); ?>
				</th>
				<th class='left'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_ATTENDEES_TRIP_ID', 'trip_name', $listDirn, $listOrder); ?>
				</th>
				<th class='left'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_ATTENDEES_IN_PARTY', 'a.in_party', $listDirn, $listOrder); ?>
				</th>
				<th class='left'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRIPSYS_ATTENDEES_APPROVED_BY', 'a.approved_by', $listDirn, $listOrder); ?>
				</th>
				<th class='left'>
				<?php echo HTMLHelper::_('searchtools.sort',  'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
				</th>

                <?php if (isset($this->items[0]->id)): ?>
					<th width="1%" class="nowrap center hidden-phone">
						<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
                <?php endif; ?>
				</tr>
			</thead>
			<tfoot>
                <?php
                if(isset($this->items[0])){
                    $colspan = count(get_object_vars($this->items[0]));
                }
                else{
                    $colspan = 10;
                }
            ?>
			<tr>
				<td colspan="<?php echo $colspan ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$ordering   = ($listOrder == 'a.ordering');
                $canCreate	= $user->authorise('core.create',		'com_gatripsys');
                $canEdit	= $user->authorise('core.edit',			'com_gatripsys');
                $canCheckin	= $user->authorise('core.manage',		'com_gatripsys');
                $canChange	= $user->authorise('core.edit.state',	'com_gatripsys');
				?>
				<tr class="row<?php echo $i % 2; ?>">
                    
					<td >
						<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
					</td>
                <?php if (isset($this->items[0]->ordering)): ?>
					<td class="order nowrap center hidden-phone">
					<?php if ($canChange) :
						$disableClassName = '';
						$disabledLabel	  = '';
						if (!$saveOrder) :
							$disabledLabel    = Text::_('JORDERINGDISABLED');
							$disableClassName = 'inactive tip-top';
						endif; ?>
						<span class="sortable-handler hasTooltip <?php echo $disableClassName?>" title="<?php echo $disabledLabel?>">
							<i class="icon-menu"></i>
						</span>
						<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering;?>" class="width-20 text-area-order " />
					<?php else : ?>
						<span class="sortable-handler inactive" >
							<i class="icon-menu"></i>
						</span>
					<?php endif; ?>
					</td>
                <?php endif; ?>
                <?php if (isset($this->items[0]->state)): ?>
					<td class="center">
						<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'attendees.', $canChange, 'cb'); ?>
						<?php echo $item->state;?>
					</td>
                <?php endif; ?>
                    
				<td>
				<?php if (isset($item->checked_out) && $item->checked_out) : ?>
					<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'attendees.', $canCheckin); ?>
				<?php endif; ?>
				<?php if ($canEdit && $item->user_id) : ?>
					<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=attendee.edit&id='.(int) $item->id); ?>">
					<?php if ($item->user_id) { echo $this->escape($item->user_id); } else { echo $item->no_user; } ?>
                    </a>
				<?php else : ?>
					<?php if ($item->user_id) { echo $this->escape($item->user_id); } else { echo $item->no_user; } ?>
				<?php endif; ?>
				</td>
				<td>
					<?php echo $item->trip_name; ?>
				</td>
				<td>
					<?php echo $item->in_party; ?>
				</td>
				<td>
					<?php echo $item->approved_by; ?>
				</td>
				<td>
					<?php if ($item->state == 1) { echo 'Acepted'; } elseif ($item->state == -2) { echo 'Rejected'; }  elseif ($item->state == 2) { echo 'Cancelled'; } else { echo 'Pending'; } ?>
				</td>

                <?php if (isset($this->items[0]->id)): ?>
					<td class="center hidden-phone">
						<?php echo (int) $item->id; ?>
					</td>
                <?php endif; ?>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
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

