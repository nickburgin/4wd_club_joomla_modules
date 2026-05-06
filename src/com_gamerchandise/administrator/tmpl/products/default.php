<?php
/**
 * @version    4.1.2
 * @package    Com_Gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
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
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');

$user      = GamerchandiseHelper::getSpecificUser();
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_gamerchandise');
$saveOrder = $listOrder == 'a.ordering';

if ($saveOrder) {
	$saveOrderingUrl = 'index.php?option=com_gamerchandise&task=products.saveOrderAjax&tmpl=component';
	HTMLHelper::_('sortablelist.sortable', 'productList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

?>

<form action="<?php echo Route::_('index.php?option=com_gamerchandise&view=products'); ?>" 
	method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">

            	<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
		<table class="table table-striped" id="productList">
			<thead>
				<tr>
					<?php if (isset($this->items[0]->ordering)): ?>
						<th width="1%" class="nowrap center hidden-phone">
							<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING'); ?>
						</th>
					<?php endif; ?>
					<th width="1%" class="hidden-phone">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					<?php if (isset($this->items[0]->state)): ?>
						<th width="1%" class="nowrap center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
						</th>
					<?php endif; ?>
	
					<th>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAMERCHANDISE_PRODUCTS_PROD_NAME', 'a.prod_name', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAMERCHANDISE_PRODUCTS_PROD_CODE', 'a.prod_code', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAMERCHANDISE_PRODUCTS_PRICE', 'a.price', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAMERCHANDISE_PRODUCTS_SOH', 'a.soh', $listDirn, $listOrder); ?>
					</th>
					<th class='left'>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAMERCHANDISE_PRODUCTS_CAT_ID', 'a.cat_id', $listDirn, $listOrder); ?>
					</th>
					<th>
						<?php echo HTMLHelper::_('searchtools.sort',  'COM_GAMERCHANDISE_ID', 'a.id', $listDirn, $listOrder); ?>
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
					$canCreate  = $user->authorise('core.create', 'com_gamerchandise');
					$canEdit    = $user->authorise('core.edit', 'com_gamerchandise');
					$canCheckin = $user->authorise('core.manage', 'com_gamerchandise');
					$canChange  = $user->authorise('core.edit.state', 'com_gamerchandise');
					?>
					<tr class="row<?php echo $i % 2; ?>">
	
						<?php if (isset($this->items[0]->ordering)) : ?>
							<td class="order nowrap center hidden-phone">
								<?php if ($canChange) :
									$disableClassName = '';
									$disabledLabel    = '';
	
									if (!$saveOrder) :
										$disabledLabel    = Text::_('JORDERINGDISABLED');
										$disableClassName = 'inactive tip-top';
									endif; 
								?>
									<span class="sortable-handler hasTooltip <?php echo $disableClassName ?>" title="<?php echo $disabledLabel ?>"><i class="icon-menu"></i></span>
									<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order "/>
								<?php else : ?>
									<span class="sortable-handler inactive"><i class="icon-menu"></i></span>
								<?php endif; ?>
							</td>
						<?php endif; ?>
						<td class="hidden-phone">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						<?php if (isset($this->items[0]->state)): ?>
							<td class="center">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'products.', $canChange, 'cb'); ?>
							</td>
						<?php endif; ?>
	
						<td>
							<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
								<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'products.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=product.edit&id='.(int) $item->id); ?>">
								<?php echo $this->escape($item->prod_name); ?></a>
							<?php else : ?>
								<?php echo $this->escape($item->prod_name); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php echo $item->prod_code; ?>
						</td>
						<td>
							<?php echo $item->price; ?>
						</td>
						<td>
							<?php echo $item->soh; ?>
						</td>
						<td>
							<?php echo $item->cat_id; ?>
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
<script>
    window.toggleField = function (id, task, field) {

        var f = document.adminForm, i = 0, cbx, cb = f[ id ];

        if (!cb) return false;

        while (true) {
            cbx = f[ 'cb' + i ];

            if (!cbx) break;

            cbx.checked = false;
            i++;
        }

        var inputField   = document.createElement('input');

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild(inputField);

        cb.checked = true;
        f.boxchecked.value = 1;
        window.submitform(task);

        return false;
    };
</script>
