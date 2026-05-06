<?php
/**
 * @version    4.2.0
 * @subpackage com_gatracklog
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
use Joomla\CMS\Language;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GamodalHelper;

// load any assets required
$this->getDocument()->getWebAssetManager()
    ->usePreset('com_gatracklog.gatracklogpreset');

// Load admin language file
Factory::getApplication()->getLanguage()->load('com_gatracklog', JPATH_ADMINISTRATOR, 'en-GB', true);

$listOrder  = $this->state->get('list.ordering','a.track_zone');
$listDirn   = $this->state->get('list.direction','ASC');

$user       = Factory::getApplication()->getIdentity();
$canCreate  = $user->authorise('core.create', 'com_gatracklog');
$canEdit    = $user->authorise('core.edit', 'com_gatracklog');
$canCheckin = $user->authorise('core.manage', 'com_gatracklog');
$canChange  = $user->authorise('core.edit.state', 'com_gatracklog');
$canEditOwn  = $user->authorise('core.edit.own', 'com_gatracklog');
$canDelete  = $user->authorise('core.delete', 'com_gatracklog');
$this->canMembers  = $user->authorise('core.members', 'com_gatracklog');

$baseURL = 'index.php?';
$createTran = GamodalHelper::getHTTPQuery(null, 'task', 'tracklog.edit', 'id', 0);
$createURL = $baseURL.\http_build_query($createTran, '', '&amp;');

/*
GatracklogHelper::print_r2(Factory::getApplication()->getUserState('com_gatracklogs.test.data'))
*/

// Display a heading for the page
if ($this->params->get('page_title', '') > '') {
	echo '<div class="page-header"><h1 itemprop="headline">'.$this->params->get('page_title').'</h1></div>';
} else { 
	echo '<div class="page-header"><h1 itemprop="headline">'.Text::_('COM_GATRACKLOG_TITLE_TRACKLOGS').'</h1></div>';
}
?>

<form action="<?php echo htmlspecialchars(Uri::getInstance()->toString()); ?>" method="post"
      name="adminForm" id="adminForm" class="com-content-category__articles">

	<?php
        // display standard joomla filter files
        //echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
        // display component view specific filter files
        echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__));
    ?>

    <div class="table-responsive com-contact-categories categories-list">
	<table class="table table-striped" id="tracklogList">
		<thead>
		<tr>
			<?php if (isset($this->items[0]->state)): ?>
				<th width="5%">
					<?php echo HTMLHelper::_('grid.sort', 'JPUBLISHED', 'a.state', $listDirn, $listOrder); ?>
				</th>
			<?php endif; ?>

			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRACKLOG_TRACKLOGS_NAME', 'a.name', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRACKLOG_TRACKLOGS_RATING', 'r.title', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRACKLOG_TRACKLOGS_TRACK_ZONE', 't.title', $listDirn, $listOrder); ?>
			</th>
			<th class=''>
				<?php echo HTMLHelper::_('grid.sort',  'COM_GATRACKLOG_TRACKLOGS_SEASON_CLOSE', 's.title', $listDirn, $listOrder); ?>
			</th>


			<?php if ($canEditOwn || $canCheckin || $canDelete): ?>
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

                $linkTran = GamodalHelper::getHTTPQuery(null, 'view', 'tracklog', 'id', $item->id);
                $appURL = $baseURL.\http_build_query($linkTran, '', '&amp;');

                $editTran = GamodalHelper::getHTTPQuery(null, 'task', 'tracklog.edit', 'id', $item->id);
                $editURL = $baseURL.\http_build_query($editTran, '', '&amp;');

                $removeTran = GamodalHelper::getHTTPQuery(null, 'task', 'tracklog.remove', 'id', $item->id);
                $removeURL = $baseURL.\http_build_query($removeTran, '', '&amp;');

                $archTran = GamodalHelper::getHTTPQuery(null, 'task', 'tracklog.publish', 'id', $item->id);
                $archTran = GamodalHelper::getHTTPQuery($archTran, null, null, 'state', 2);
                $archURL = $baseURL.\http_build_query($archTran, '', '&amp;');

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
					<a href="<?php echo Route::_($appURL); ?>">
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
				<td>
					<?php echo $item->id; ?>
				</td>


				<?php if ($canEditOwn || $canCheckin || $canDelete): ?>
					<td class="center">
						<a href="<?php echo Route::_($archURL); ?>" class="btn btn-info"
                            type="button" title="<?php echo Text::_('COM_GATRACKLOG_MARK_ARCHIVED'); ?>">
                            <i class="icon-archive" ></i>
                        </a>
						<?php if ($canEdit): ?>
							<a href="<?php echo Route::_($editURL); ?>" class="btn btn-warning" type="button">
                                <i class="icon-edit" ></i>
                            </a>
						<?php endif; ?>
						<?php if ($canDelete): ?>
							<a href="<?php echo Route::_($removeURL); ?>" class="btn btn-danger delete-button" type="button">
                                <i class="icon-trash" ></i>
                            </a>
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
