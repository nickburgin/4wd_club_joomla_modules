<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
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
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('table.columns')
    ->useScript('multiselect');
$wa->usePreset('com_gafinance.gafinancepreset');

$user      = GafinanceHelper::getSpecificUser();
$listOrder = $this->state->get('list.ordering', 'a.tran_date');
$listDirn  = $this->state->get('list.direction', 'DESC');
$canOrder  = $user->authorise('core.edit.state', 'com_gafinance');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder)
{
	$saveOrderingUrl = 'index.php?option=com_gafinance&task=transactions.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'transactionList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>

<form action="<?php echo Route::_('index.php?option=com_gafinance&view=transactions'); ?>" method="post"
	  name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="transactionList">
					<thead>
					<tr>
						<?php if (isset($this->items[0]->ordering)): ?>
							<th width="1%" class="nowrap center hidden-phone">
	                            <?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
	                        </th>
						<?php endif; ?>
						<th width="1%" >
							<input type="checkbox" name="checkall-toggle" value=""
								   title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
						</th>
						<?php if (isset($this->items[0]->state)): ?>
							<th width="1%" class="nowrap center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
						<?php endif; ?>

						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAFINANCE_TRANSACTIONS_USER_ID', 'user_id_name', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAFINANCE_TRANSACTIONS_TRAN_TYPE', 'a.tran_type', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAFINANCE_TRANSACTIONS_TRAN_DATE', 'a.tran_date', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAFINANCE_TRANSACTIONS_TRAN_AMOUNT', 'a.tran_amount', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAFINANCE_TRANSACTIONS_TRAN_FILE', 'a.tran_file', $listDirn, $listOrder); ?>
						</th>
						<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAFINANCE_ID', 'a.id', $listDirn, $listOrder); ?>
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
						$canCreate  = $user->authorise('core.create', 'com_gafinance');
						$canEdit    = $user->authorise('core.edit', 'com_gafinance');
						$canCheckin = $user->authorise('core.manage', 'com_gafinance');
						$canChange  = $user->authorise('core.edit.state', 'com_gafinance');
						?>
						<tr class="row<?php echo $i % 2; ?>">

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
							<td >
								<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
							</td>
							<?php if (isset($this->items[0]->state)): ?>
								<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'transactions.', $canChange, 'cb'); ?>
								</td>
							<?php endif; ?>

							<td>
								<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
									<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'transactions.', $canCheckin); ?>
								<?php endif; ?>
								<?php if ($canEdit) : ?>
									<a href="<?php echo Route::_('index.php?option=com_gafinance&task=transaction.edit&id='.(int) $item->id); ?>">
									<?php echo $this->escape($item->user_id_name); ?></a>
								<?php else : ?>
									<?php echo $this->escape($item->user_id_name); ?>
								<?php endif; ?>
							</td>
							<td>
								<?php echo $item->tran_type; ?>
							</td>
							<td>
								<?php echo $item->tran_date > 0 ? HTMLHelper::_('date', $item->tran_date, Text::_('COM_GAFINANCE_DISPLAY_DATE')) : '-'; ?>
							</td>
							<td>
								<?php echo $item->tran_amount; ?>
							</td>
							<td>
								<?php
									if (!empty($item->tran_file)) {
										$tran_fileArr = explode(',', $item->tran_file);
										foreach ($tran_fileArr as $fileSingle) :
											if (!is_array($fileSingle)) :
												echo '<a href="' . Route::_(Uri::root() . $fileSingle, false) . '" target="_blank" title="See the tran_file">View File</a>';
											endif;
										endforeach;
									}
								?>
							</td>
							<td>
								<?php echo $item->id; ?>
							</td>

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
