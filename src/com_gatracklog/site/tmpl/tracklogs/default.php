<?php
/**
 * @version    4.1.0
 * @package    com_gatracklog
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
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatracklog.gatracklogpreset');

// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatracklog', JPATH_ADMINISTRATOR);

$listOrder  = $this->state->get('list.ordering','a.name');
$listDirn   = $this->state->get('list.direction','asc');

$user       = GatracklogHelper::getSpecificUser();
$canCreate  = $user->authorise('core.create', 'com_gatracklog');
$canEdit    = $user->authorise('core.edit', 'com_gatracklog');
$canCheckin = $user->authorise('core.manage', 'com_gatracklog');
$canChange  = $user->authorise('core.edit.state', 'com_gatracklog');
$canDelete  = $user->authorise('core.delete', 'com_gatracklog');

$baseURL = 'index.php?';
$createTran = GatracklogHelper::getHTTPQuery(null, 'task', 'tracklog.edit', 'id', 0);
$createURL = $baseURL.\http_build_query($createTran, '', '&amp;');

/*
echo '<pre>Test<br />';
print_r(Factory::getApplication()->getUserState('com_gatracklogs.test.data'));
echo '</pre>';
*/

// Display a heading for the page
if ($this->params->get('page_title', '') > '') {
	echo '<h1>'.$this->params->get('page_title').'</h1>';
} else { 
	echo '<h1>'.Text::_('COM_GATRACKLOG_TITLE_TRACKLOGS').'</h1>';
}
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm" class="com-content-category__articles">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

    <div class="table-responsive com-contact-categories categories-list">
	<table class="table table-striped" id="tracklogList">
		<thead>
		<tr>
			<?php if (isset($this->items[0]->state)): ?>
				<th width="5%">
					<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>

			<th class='left'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_TRACKLOGS_NAME', 'a.name', $listDirn, $listOrder); ?>
			</th>
			<th class='left'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_TRACKLOGS_RATING', 'a.rating', $listDirn, $listOrder); ?>
			</th>
			<th class='left'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_TRACKLOGS_TRACK_ZONE', 'a.track_zone', $listDirn, $listOrder); ?>
			</th>
			<th class='left'>
				<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_TRACKLOGS_SEASON_CLOSE', 'a.season_close', $listDirn, $listOrder); ?>
			</th>


			<?php if ($canEdit || $canDelete): ?>
				<th class="center">
					<?php echo Text::_('COM_GATRACKLOG_ACTIONS'); ?>
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
            
                $canEdit = GatracklogHelper::canUserEdit($user, $item);
                $editTran = GatracklogHelper::getHTTPQuery(null, 'task', 'tracklog.edit', 'id', $item->id);
                $editURL = $baseURL.\http_build_query($editTran, '', '&amp;');
                $delTran = GatracklogHelper::getHTTPQuery(null, 'task', 'tracklog.remove', 'id', $item->id);
                $delURL = $baseURL.\http_build_query($delTran, '', '&amp;');

            ?>

			<tr class="row<?php echo $i % 2; ?>">

				<?php if (isset($this->items[0]->state)) : ?>
					<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
					<td class="center">
						<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_gatracklog&task=tracklog.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
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
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'tracklogs.', $canCheckin); ?>
					<?php endif; ?>
					<a href="<?php echo Route::_('index.php?option=com_gatracklog&view=tracklog&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->name); ?>
					</a>
				</td>
				<td>
					<?php echo $item->rating_name; ?>
				</td>
				<td>
					<?php echo $item->track_zone_name; ?>
				</td>
				<td>
					<?php echo $item->season_close_name; ?>
				</td>


				<?php if ($canEdit || $canDelete): ?>
					<td class="center">
						<?php if ($canEdit): ?>
							<a href="<?php echo Route::_($editURL); ?>" class="btn btn-mini" type="button"><i class="icon-edit" ></i></a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_($delURL); ?>" class="btn btn-mini delete-button" type="button"><i class="icon-trash" ></i></a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
        </div>
	<?php if ($canCreate) : ?>
		<a href="<?php echo Route::_($createURL); ?>" class="btn btn-success btn-small">
            <i class="icon-plus"></i> <?php echo Text::_('COM_GATRACKLOG_ADD_ITEM'); ?>
        </a>
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

		if (!confirm("<?php echo Text::_('COM_GATRACKLOG_DELETE_MESSAGE'); ?>")) {
			return false;
		}
	}
</script>
<?php endif; ?>
