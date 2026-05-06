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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gacalevents.gacaleventspreset');

// Load admin language file
Factory::getApplication()->getLanguage()->load('com_gacalevents', JPATH_ADMINISTRATOR);

$user       = Factory::getApplication()->getIdentity();
$listOrder  = $this->state->get('list.ordering', 'a.pub_name');
$listDirn   = $this->state->get('list.direction', 'ASC');
$canCreate  = $user->authorise('core.create', 'com_gacalevents');
$canEdit    = $user->authorise('core.edit', 'com_gacalevents');
$canCheckin = $user->authorise('core.manage', 'com_gacalevents');
$canChange  = $user->authorise('core.edit.state', 'com_gacalevents');
$canDelete  = $user->authorise('core.delete', 'com_gacalevents');

//GacaleventsHelper::gaPrint(Factory::getApplication()->getUserState('com_gacalevents.test.data'));

?>

<h2><?php echo Text::_('COM_GACALEVENTS_TITLE_ATTENDEES'); ?></h2>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm" class="com-content-category__articles">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="table-responsive">
	<table class="table table-striped" id="attendeeList">
		<thead>
		<tr>
			<?php if (isset($this->items[0]->state)): ?>
				<th width="5%">
					<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>

			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_ATTENDEES_ATTENDEE', 'a.pub_name', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_ATTENDEES_EVENT', 'event_title', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_ATTENDEES_QTY_ATT', 'a.qty_att', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GACALEVENTS_ID', 'a.id', $listDirn, $listOrder); ?>
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
			<?php 
                $canEdit = $user->authorise('core.edit', 'com_gacalevents');
    			if (!$canEdit && $user->authorise('core.edit.own', 'com_gacalevents')) {
    				$canEdit = $user->id == $item->created_by;
    			}
                $attLink = GacaleventsHelper::getHTTPQuery(null, 'task', 'attendeeform.edit', 'id', $item->id);
                $attLink = GacaleventsHelper::getHTTPQuery($attLink, null, null, 'event_id', $item->event);
                $attLink = GacaleventsHelper::getHTTPQuery($attLink, null, null, 'attendee', $item->attendee);
                $attURL = 'index.php?'.http_build_query($attLink, '', '&amp;');
                $remLink = GacaleventsHelper::getHTTPQuery(null, 'task', 'attendeeform.remove', 'id', $item->id);
                $remURL = 'index.php?'.http_build_query($remLink, '', '&amp;');

            ?>

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
					<a href="<?php echo Route::_('index.php?option=com_gacalevents&view=attendee&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->pub_name); ?>
					</a>
				</td>
				<td>
					<?php echo $item->event_title; ?>
				</td>
				<td>
					<?php echo $item->qty_att; ?>
				</td>
				<td>
					<?php echo $item->id; ?>
				</td>

				<?php if ($canEdit || $canDelete): ?>
					<td class="center">
						<?php if ($canEdit): ?>
							<a href="<?php echo Route::_($attURL); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_($remURL); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
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
