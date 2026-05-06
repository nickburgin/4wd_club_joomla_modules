<?php
/**
 * @version    5.1.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user       = GatripsysHelper::getSpecificUser();
$userId     = $user->get('id');
$listOrder  = $this->state->get('list.ordering', 'a.user_id');
$listDirn   = $this->state->get('list.direction', 'asc');
$canCreate  = $user->authorise('core.create', 'com_gatripsys');
$canEdit    = $user->authorise('core.edit', 'com_gatripsys');
$canCheckin = $user->authorise('core.manage', 'com_gatripsys');
$canChange  = $user->authorise('core.edit.state', 'com_gatripsys');
$canDelete  = $user->authorise('core.delete', 'com_gatripsys');
$canAdmin  = $user->authorise('core.admin', 'com_gatripsys');
$canTrip  = $user->authorise('core.trip', 'com_gatripsys');

$tripId = $this->getState('list.trip', 0);
if ($tripId) {
	$leader = GatripsysHelper::getTripLeader($tripId);
	$pagehead = $this->params->get('page_heading'). ' - Incidents';
} else {
	$pagehead = $this->params->get('page_heading');
	$leader = 0;
}
$admin_id = $this->params->get('admin_id');
$canLead = ($user->id == $leader ) ? 1 : 0;
$canTrip = (!$canTrip && $user->id == $admin_id ) ? 1 : 0;

/*
echo '<pre>Test<br />';
print_r(Factory::getApplication()->getUserState('com_gatripsys.test.data'));
echo '</pre>';
Factory::getApplication()->setUserState('com_gatripsys.test.data',null);
*/
?>

<h2><?php echo $pagehead; ?></h2>

<form action="<?php echo Route::_('index.php?option=com_gatripsys&view=incidents'); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

	<table class="table table-striped" id="incidentList">
		<thead>
		<tr>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_INCIDENTS_USER_ID', 'a.user_id', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_INCIDENTS_TRIP_ID', 'a.trip_id', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_INCIDENTS_STATE', 'a.state', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_INCIDENTS_MAP_REF', 'a.map_ref', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRIPSYS_INCIDENTS_GPS_REF', 'a.gps_ref', $listDirn, $listOrder); ?>
			</th>

			<?php if ($canEdit || $canDelete): ?>
				<th class="center">
					<?php echo Text::_('COM_GATRIPSYS_INCIDENTS_ACTIONS'); ?>
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
			<?php $canEdit = $user->authorise('core.edit', 'com_gatripsys'); ?>

			<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gatripsys')): ?>
				<?php $canEdit = GatripsysHelper::getSpecificUser()->id == $item->created_by; ?>
			<?php endif; ?>

			<tr class="row<?php echo $i % 2; ?>">


				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'incidents.', $canCheckin); ?>
					<?php endif; ?>
					<!-- <a href="<?php //echo Route::_('index.php?option=com_gatripsys&view=incident&id='.(int) $item->id); ?>">  -->
					<?php echo $this->escape($item->user_id); ?>
					<!-- </a> -->
				</td>
				<td>
					<?php echo $item->trip_name; ?>
				</td>
				<td>
					<?php if ($item->state == 1) { echo 'Accepted'; } elseif ($item->state == 0) { echo 'Pending'; } else { echo 'Rejected'; } ?>
				</td>
				<td class="center">
					<?php echo $item->map_ref; ?>
				</td>
				<td>
					<?php echo $item->gps_ref; ?>
				</td>

				<td class="center">
					<?php if ($canLead || $canTrip): ?>
						<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=incidentform.edit&id=' . $item->id, false); ?>" class="btn btn-mini" type="button" title="Edit Incident">
							<i class="icon-edit" ></i>
						</a>
						<button data-item-id="<?php echo $item->id; ?>" class="btn btn-mini btn-danger delete-button pull-right" type="button" title="Delete Incident">
							<i class="icon-trash" ></i>
						</button>
					<?php endif; ?>
					<?php if (($canLead || $canTrip) && $item->state == 0): ?>
						<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=incidentform.attaccept&id=' . $item->id . '&trip_id=' . $item->trip_id, false); ?>" class="btn btn-mini" type="button" title="Approve Incident">
							<i class="icon-publish" ></i>
						</a>
					<?php endif; ?>
				</td>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ($canCreate && $this->trip_in_prog != 2) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=incidentform.edit&id=0', false); ?>" class="btn btn-success btn-small">
		   <i class="icon-plus"></i> <?php echo Text::_('COM_GATRIPSYS_ADD_ITEM'); ?>
		</a>
	<?php endif; ?>
	<a href="<?php echo Route::_('index.php?option=com_gatripsys&view=trips', false); ?>" class="btn btn-warning btn-small">
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
			window.location.href = '<?php echo Route::_('index.php?option=com_gatripsys&task=incidentform.remove&id=', false) ?>' + item_id;
		}
		<?php endif; ?>
	}
</script>


