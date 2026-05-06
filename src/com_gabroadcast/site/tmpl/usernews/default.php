<?php
/**
 * @version     4.3.3
 * @package     com_gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gabroadcast\Administrator\Helper\GabroadcastHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gabroadcast.gabroadcastpreset');

//Load admin language file
Factory::getApplication()->getLanguage()->load('com_gabroadcast', JPATH_ADMINISTRATOR);

$today = Factory::getDate()->toSql();
$cycle_time = $this->params->get('cycle_time', 0);

$user       = Factory::getApplication()->getIdentity();
$listOrder  = $this->state->get('list.ordering', 'a.created_date');
$listDirn   = $this->state->get('list.direction', 'DESC');
$canCreate  = $user->authorise('core.create', 'com_gabroadcast');
$canEdit    = $user->authorise('core.edit', 'com_gabroadcast');
$canCheckin = $user->authorise('core.manage', 'com_gabroadcast');
$canChange  = $user->authorise('core.edit.state', 'com_gabroadcast');
$canDelete  = $user->authorise('core.delete', 'com_gabroadcast');
$lastbcast = GabroadcastHelper::getLastBroadcast();
$canBeSent = $today >= $lastbcast ? true : false;

$baseURL = 'index.php?';

//GabroadcastHelper::print_r2(Factory::getApplication()->getUserState('com_gabroadcast.test.data'));

// display heading for the list
if ($this->params->get('view_type', 1) == 5) {
    $heading = Text::_('COM_GABROADCAST_USERNEWS_PENDING');
} else {
    $heading = Text::_('COM_GABROADCAST_USERNEWS_LISTING');
}
?>

<?php if (isset($this->items[0]->state)): ?>
<h2><?php echo $heading; ?></h2>
<?php endif; ?>

<p><?php //echo Text::sprintf('COM_GABROADCAST_USERNEWS_LASTBCAST', $lastbcast, $next_bcast); ?></p>
<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

	<table class="table table-striped" id="usernewList">
		<thead>
		<tr>
			<?php if (isset($this->items[0]->state)): ?>
				<th width="5%">
					<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>

			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GABROADCAST_USERNEWS_NEWS_SUBJECT', 'a.news_subject', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GABROADCAST_USERNEWS_CAT_ID', 'a.cat_id', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GABROADCAST_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GABROADCAST_ID', 'a.id', $listDirn, $listOrder); ?>
			</th>

			<?php if ($canEdit || $canDelete): ?>
				<th class="center">
					<?php echo Text::_('COM_GABROADCAST_USERNEWS_ACTIONS'); ?>
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
                $canEdit = $user->authorise('core.edit', 'com_gabroadcast');
                if (!$canEdit && $user->authorise('core.edit.own', 'com_gabroadcast')) {
                    $canEdit = $user->id == $item->created_by;
                }

                $delNews = GabroadcastHelper::getHTTPQuery(null, 'task', 'usernew.publish', 'id', $item->id);
                $delNews = GabroadcastHelper::getHTTPQuery($delNews, null, null, 'state', -2);
                $delURL = $baseURL.\http_build_query($delNews, '', '&amp;');
                $sendNews = GabroadcastHelper::getHTTPQuery(null, 'task', 'usernew.sendOut', 'id', $item->id);
                $sendURL = $baseURL.\http_build_query($sendNews, '', '&amp;');
            ?>

			<tr class="row<?php echo $i % 2; ?>">

				<?php if (isset($this->items[0]->state)) : ?>
					<?php $class = ($canChange) ? 'active' : 'disabled'; ?>
					<td class="center">
						<a class="btn btn-micro <?php echo $class; ?>" href="<?php echo ($canChange) ? Route::_('index.php?option=com_gabroadcast&task=usernew.publish&id=' . $item->id . '&state=' . (($item->state + 1) % 2), false, 2) : '#'; ?>">
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
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'usernews.', $canCheckin); ?>
					<?php endif; ?>
					<a href="<?php echo Route::_('index.php?option=com_gabroadcast&view=usernew&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->news_subject); ?>
					</a>
				</td>
				<td>
					<?php echo $item->cat_id_name; ?>
				</td>
				<td>
					<?php echo $cdate = !empty($item->created_date) ? HtmlHelper::date($item->created_date, Text::_('COM_GABROADCAST_DISPLAY_DATE')) : ''; ?>
				</td>
				<td>
					<?php echo $item->id; ?>
				</td>
				<?php if ($canEdit || $canDelete): ?>
					<td class="center">
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_($delURL); ?>" class="btn btn-danger delete-button" type="button">
                                <i class="icon-trash" ></i>
                            </a>
						<?php endif; ?>
						<?php if ($canBeSent): ?>
							<a href="<?php echo Route::_($sendURL); ?>" class="btn btn-warning" type="button">
                                <i class="icon-envelope" ></i>
                            </a>
						<?php endif; ?>
					</td>
				<?php endif; ?>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

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
	
			if (!confirm("<?php echo Text::_('COM_GABROADCAST_DELETE_MESSAGE'); ?>")) {
				return false;
			}
		}
	</script>
<?php endif; ?>
