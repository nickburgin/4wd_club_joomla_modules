<?php
/**
 * @version    4.2.0
 * @package    pkg_mypackage
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
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');
$wa->usePreset('com_gatracklog.gatracklogpreset');

$user      = Factory::getApplication()->getIdentity();
$listOrder = $this->state->get('list.ordering', 't.title');
$listDirn  = $this->state->get('list.direction', 'ASC');
$saveOrder = $listOrder == 'a.tran_date';
$version = GatracklogHelper::getComponentVersion();

if ($saveOrder && !empty($this->items)) {
	$saveOrderingUrl = 'index.php?option=com_gatracklog&task=tracklogs.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
    HTMLHelper::_('sortablelist.sortable', 'tracklogList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

/*
GatracklogHelper::print_r2($componentXML);
*/
?>

<form action="<?php echo Route::_('index.php?option=com_gatracklog&view=tracklogs'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

                <?php if (empty($this->items)) : ?>
                    <?php echo LayoutHelper::render('emptystate', array('view' => $this)); ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
    				<table class="table table-striped" id="tracklogList">
        				<caption id="captionTable">
        					<?php echo Text::_('Version: '.$version); ?>
        				</caption>
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
    						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_TRACKLOGS_NAME', 'a.name', $listDirn, $listOrder); ?>
    						</th>
    						<th class='left'>
    						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_TRACKLOGS_RATING', 'a.rating', $listDirn, $listOrder); ?>
    						</th>
    						<th class='left'>
    						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_TRACKLOGS_TRACK_ZONE', 't.title', $listDirn, $listOrder); ?>
    						</th>
    						<th class='left'>
    						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_TRACKLOGS_SEASON_CLOSE', 's.title', $listDirn, $listOrder); ?>
    						</th>
    						<th class='left'>
    						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_CREATED_DATE', 'a.created_date', $listDirn, $listOrder); ?>
    						</th>
    						<th class='left'>
    						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GATRACKLOG_ID', 'a.id', $listDirn, $listOrder); ?>
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
    						$canEdit    = GatracklogHelper::canUserEdit($user, $item);
    						$canCreate  = $user->authorise('core.create', 'com_gatracklog');
    						$canCheckin = $user->authorise('core.manage', 'com_gatracklog');
    						$canChange  = $user->authorise('core.edit.state', 'com_gatracklog');
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
    										<span class="sortable-handler inactive">
    											<i class="icon-menu"></i>
    										</span>
    									<?php endif; ?>
    								</td>
    							<?php endif; ?>
    							<?php if (isset($this->items[0]->state)): ?>
    								<td class="center">
    								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'tracklogs.', $canChange, 'cb'); ?>
    								</td>
    							<?php endif; ?>
    
    							<td>
    								<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
    									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'tracklogs.', $canCheckin); ?>
    								<?php endif; ?>
    								<?php if ($canEdit) : ?>
    									<a href="<?php echo Route::_('index.php?option=com_gatracklog&task=tracklog.edit&id='.(int) $item->id); ?>">
    									<?php echo $this->escape($item->name); ?></a>
    								<?php else : ?>
    									<?php echo $this->escape($item->name); ?>
    								<?php endif; ?>
    							</td>

    							<td><?php echo $item->rating_name; ?></td>

    							<td><?php echo $item->track_zone_name; ?></td>

    							<td><?php echo $item->season_close_name; ?></td>

    							<td>
    								<?php $cdate = $item->created_date;
    									echo $cdate > 0 ? HTMLHelper::_('date', $cdate, Text::_('COM_GATRACKLOG_DISPLAY_DATETIME')) : '-'; ?>
    							</td>

    							<td><?php echo $item->id; ?></td>
    
    						</tr>
    					<?php endforeach; ?>
    					</tbody>
    				</table>
                <?php endif; ?>


                <?php // Load the batch processing form. ?>
                <?php
                    if (
                        $user->authorise('core.create', 'com_gatracklog')
                        && $user->authorise('core.edit', 'com_gatracklog')
                        && $user->authorise('core.edit.state', 'com_gatracklog')
                    ) :
                ?>
                    <template id="joomla-dialog-batch"><?php echo $this->loadTemplate('batch_body'); ?></template>
                <?php endif; ?>

				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
	            <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
