<?php
/**
 * @version    6.0.0
 * @package    com_gausers
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
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');

$user      = Factory::getApplication()->getIdentity();
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_gausers');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_gausers&task=mshiptypes.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'mshiptypeList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>

<form action="<?php echo Route::_('index.php?option=com_gausers&view=mshiptypes'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="mshiptypeList">
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
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_MSHIPTYPES_TITLE', 'a.title', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_MSHIPTYPES_JOINING_FEE', 'a.joining_fee', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_MSHIPTYPES_SUBSCRIB_AMT', 'a.subscrib_amt', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_MSHIPTYPES_TERM_TYPE', 'a.term_type', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAUSERS_ID', 'a.id', $listDirn, $listOrder); ?>
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
						$canCreate  = $user->authorise('core.create', 'com_gausers');
						$canEdit    = GausersHelper::canUserEdit($user, $item);
						$canCheckin = $user->authorise('core.manage', 'com_gausers');
						$canChange  = $user->authorise('core.edit.state', 'com_gausers');
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
										<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
									<?php else : ?>
										<span class="sortable-handler inactive">
											<i class="icon-menu"></i>
										</span>
									<?php endif; ?>
								</td>
							<?php endif; ?>
							<?php if (isset($this->items[0]->state)): ?>
								<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'mshiptypes.', $canChange, 'cb'); ?>
								</td>
							<?php endif; ?>

							<td>
								<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'mshiptypes.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo Route::_('index.php?option=com_gausers&task=mshiptype.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->title); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->title); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php echo $item->joining_fee; ?>
							</td>
							<td>
								<?php echo $item->subscrib_amt.' (Disc: '.$item->discount_amt.')'; ?>
							</td>
							<td>
								<?php echo $item->term_type . ' ('. $item->mship_term . ')'; ?>
							</td>

							<td><?php echo $item->id; ?></td>

						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" value=""/>
				<input type="hidden" name="boxchecked" value="0"/>
	            <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
