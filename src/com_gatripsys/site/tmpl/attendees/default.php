<?php
/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user       = GatripsysHelper::getSpecificUser();
$listOrder  = $this->state->get('list.ordering', 'a.user_id');
$listDirn   = $this->state->get('list.direction', 'asc');
$canCreate  = $user->authorise('core.create', 'com_gatripsys');
$canEdit    = $user->authorise('core.edit', 'com_gatripsys');
$canCheckin = $user->authorise('core.manage', 'com_gatripsys');
$canChange  = $user->authorise('core.edit.state', 'com_gatripsys');
$canDelete  = $user->authorise('core.delete', 'com_gatripsys');
$canAdmin  = $user->authorise('core.admin', 'com_gatripsys');
$canTrip  = $user->authorise('core.trip', 'com_gatripsys');

$coOrdGp = $this->params->get('trip_coord', 8);
$admin_id = $this->params->get('admin_id');
$showAll = in_array($coOrdGp, $user->groups) || $user->id == $admin_id ? true : false;

/*
echo '<pre>Test<br />';
print_r(Factory::getApplication()->getUserState('com_gatripsys.test.data'));
echo '</pre>';
Factory::getApplication()->setUserState('com_gatripsys.test.data',null);
*/
?>

<h2><?php echo $this->params->get('page_heading'); ?></h2>

<form action="<?php echo Route::_('index.php?option=com_gatripsys&view=attendees'); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

	<table class="table table-striped" id="attendeeList">
		<thead>
		<tr>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_ATTENDEES_USER_ID', 'u.name', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_ATTENDEES_TRIP_ID', 't.title', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_STATE', 'a.state', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_TRIPS_TRIP_COST', 't.trip_cost', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_INVOICES_PAID_DATE', 'i.paid_date', $listDirn, $listOrder); ?>
			</th>

			<?php if ($canEdit || $canDelete): ?>
				<th class="center">
					<?php echo Text::_('COM_GATRIPSYS_ATTENDEES_ACTIONS'); ?>
				</th>
			<?php endif; ?>

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
		<?php foreach ($this->items as $i => $item) : ?>
			<?php 
                $canEdit = $user->authorise('core.edit', 'com_gatripsys');
                if (!$canEdit && $user->authorise('core.edit.own', 'com_gatripsys')) {
                    $canEdit = GatripsysHelper::getSpecificUser()->id == $item->created_by;
                }
            ?>

            <tr class="row<?php echo $i % 2; ?>">

				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'attendees.', $canCheckin); ?>
					<?php endif; ?>
					<!-- <a href="<?php //echo Route::_('index.php?option=com_gatripsys&view=attendee&id='.(int) $item->id); ?>">  -->
					<?php echo $this->escape($item->user_name); ?>
					<!-- </a> -->
				</td>
				<td>
					<?php echo $item->trip_name.' ('.$item->dept_date_disp.')'; ?>
				</td>
				<td>
					<?php if ($item->state == 1) { echo 'Accepted'; } elseif ($item->state == 0) { echo 'Pending'; } else { echo 'Rejected'; } ?>
				</td>
				<td class="right" style="padding-right:30px;">
					<?php echo $item->trip_cost; ?>
				</td>
				<td class="center">
					<?php echo $item->paid_date_disp; ?>
				</td>
				<td class="center">
					<?php if ($showAll): ?>
						<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=attendeeform.edit&id=' . $item->id, false); ?>" class="btn btn-mini" type="button" title="Edit Attendee">
							<i class="icon-edit" ></i>
						</a>
						<button data-item-id="<?php echo $item->id; ?>" class="btn btn-mini btn-danger delete-button pull-right" type="button" title="Delete Attendee">
							<i class="icon-trash" ></i>
						</button>
					<?php endif; ?>
				</td>

			</tr>

		<?php endforeach; ?>
		</tbody>
	</table>

	<a href="<?php echo Route::_('index.php?option=com_gatripsys&view=trips', false); ?>" class="btn btn-secondary btn-small">
	   <i class="icon-undo-2"></i> <?php echo Text::_('COM_GATRIPSYS_RETURN'); ?>
	</a>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {
		var item_id = jQuery(this).attr('data-item-id');
		<?php if($canDelete) : ?>
		if (confirm("<?php echo Text::_('COM_GATRIPSYS_DELETE_MESSAGE'); ?>")) {
			window.location.href = '<?php echo Route::_('index.php?option=com_gatripsys&task=attendee.remove&id=', false) ?>' + item_id;
		}
		<?php endif; ?>
	}
</script>


