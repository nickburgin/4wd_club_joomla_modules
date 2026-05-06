<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
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
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gacalevents.gacaleventspreset');

// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gacalevents', JPATH_ADMINISTRATOR);

$user       = GacaleventsHelper::getSpecificUser();
$userId     = $user->id;
$listOrder  = $this->state->get('list.ordering');
$listDirn   = $this->state->get('list.direction');
$canCreate  = $user->authorise('core.create', 'com_gacalevents');
$canEdit    = $user->authorise('core.edit', 'com_gacalevents');
$canCheckin = $user->authorise('core.manage', 'com_gacalevents');
$canChange  = $user->authorise('core.edit.state', 'com_gacalevents');
$canDelete  = $user->authorise('core.delete', 'com_gacalevents');

?>

<h2><?php echo Text::_('COM_GACALEVENTS_TITLE_ATTENDEES'); ?></h2>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
        <div class="table-responsive">
	<table class="table table-striped" id="eventList">
		<thead>
		<tr>
			<?php if (isset($this->items[0]->state)): ?>
				<th width="5%">
					<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>

			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_EVENTS_USER_ID', 'user_id_name', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_EVENTS_TRAN_TYPE', 'a.tran_type', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_EVENTS_TRAN_DATE', 'a.tran_date', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_EVENTS_TRAN_AMOUNT', 'a.tran_amount', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_EVENTS_TRAN_DESC', 'a.tran_desc', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_EVENTS_TRAN_FILE', 'a.tran_file', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_EVENTS_ACCNT_ID', 'a.accnt_id', $listDirn, $listOrder); ?>
			</th>


			<?php if ($canEdit || $canDelete): ?>
				<th class="center">
					<?php echo Text::_('COM_GACALEVENTS_EVENTS_ACTIONS'); ?>
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
			<?php $canEdit = $user->authorise('core.edit', 'com_gacalevents'); ?>
			<?php if (!$canEdit && $user->authorise('core.edit.own', 'com_gacalevents')): ?>
					<?php $canEdit = $user->id == $item->created_by; ?>
			<?php endif; ?>

			<tr class="row<?php echo $i % 2; ?>">

				<?php if (isset($this->items[0]->state)) : ?>
					<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
					<td class="center">
						<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_gacalevents&task=event.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
						<?php if ($item->state == 1): ?>
							<i class="icon-publish"></i>
						<?php else: ?>
							<i class="icon-unpublish"></i>
						<?php endif; ?>
						</a>
					</td>
				<?php endif; ?>

				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'events.', $canCheckin); ?>
					<?php endif; ?>
					<a href="<?php echo Route::_('index.php?option=com_gacalevents&view=event&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->user_id_name); ?>
					</a>
				</td>
				<td>
					<?php echo $item->tran_type; ?>
				</td>
				<td>
					<?php echo $item->tran_date > 0 ? HTMLHelper::_('date', $item->tran_date, Text::_('DATE_FORMAT_LC4')) : '-'; ?>
				</td>
				<td>
					<?php echo $item->tran_amount; ?>
				</td>
				<td>
					<?php echo $item->tran_desc; ?>
				</td>
				<td>
					<?php if (!empty($item->tran_file) && file_exists($item->tran_file)) : ?>
					<a href="<?php echo Route::_(Uri::root() . $item->tran_file, false); ?>" target="_blank" title="Review the file"><i class="icon-search"></i></a>
					<?php endif; ?>
				</td>
				<td>
					<?php echo $item->accnt_id_name; ?>
				</td>


				<?php if ($canEdit || $canDelete): ?>
					<td class="center">
						<?php if ($canEdit): ?>
							<a href="<?php echo Route::_('index.php?option=com_gacalevents&task=event.edit&id=' . $item->id, false, 2); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_('index.php?option=com_gacalevents&task=eventform.remove&id=' . $item->id, false, 2); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
        </div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_('index.php?option=com_gacalevents&task=eventform.edit&id=0', false, 0); ?>"
		   class="btn btn-success btn-small"><i
				class="icon-plus"></i>
			<?php echo Text::_('COM_GACALEVENTS_ADD_ITEM'); ?></a>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<?php if($canDelete) : ?>
<script type="text/javascript">

	jQuery(document).ready(function () {
		jQuery('.delete-button').click(deleteItem);
	});

	function deleteItem() {

		if (!confirm("<?php echo Text::_('COM_GACALEVENTS_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
</script>
<?php endif; ?>
