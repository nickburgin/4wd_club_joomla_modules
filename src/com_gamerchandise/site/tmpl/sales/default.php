<?php
/**
 * @version    4.0.7
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
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\Filesystem\Path;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

// load any assets required
$wa = $this->document->getWebAssetManager();
$wa->usePreset('com_gamerchandise.gamerchandisepreset');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gamerchandise', JPATH_ADMINISTRATOR);

$user       = GamerchandiseHelper::getSpecificUser();
$listOrder  = $this->state->get('list.ordering', 'prod.prod_name');
$listDirn   = $this->state->get('list.direction', 'asc');
$canCreate  = $user->authorise('core.create', 'com_gamerchandise');
$canEdit    = $user->authorise('core.edit', 'com_gamerchandise');
$canCheckin = $user->authorise('core.manage', 'com_gamerchandise');
$canChange  = $user->authorise('core.edit.state', 'com_gamerchandise');
$canDelete  = $user->authorise('core.delete', 'com_gamerchandise');
$canPurchase = false;
$cols = $canCheckin ? 6 : 5;

$path = 'images/merchandise/ORD';
$unconfirmedItems = false;
$baseURL = 'index.php?';

/*
echo '<pre>Test<br />';
print_r(Factory::getApplication()->getUserState('com_gamerchandise.test.data'));
echo '</pre>';
print_r($this->items);
print_r(Factory::getApplication()->getUserState('com_gamerchandise.test.data'));
*/
?>
<h2><?php echo Text::_('COM_GAMERCHANDISE_ITEMS_INCART'); ?></h2>
<form action="<?php echo Route::_('index.php?option=com_gamerchandise&view=sales'); ?>" 
	method="post" name="adminForm" id="adminForm">

	<?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>

	<table class="table table-striped" id="saleList">
		<thead>
			<tr>
				<th>
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAMERCHANDISE_SALES_PROD_ID', 'p.prod_name', $listDirn, $listOrder); ?>
				</th>
				<th>
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAMERCHANDISE_SALES_USER_ID', 'cust.name', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAMERCHANDISE_SALES_ORDER_QTY', 'a.order_qty', $listDirn, $listOrder); ?>
				</th>
				<th style="text-align: right;">
					<?php echo HTMLHelper::_('grid.sort',  'COM_GAMERCHANDISE_SALES_ORD_AMT', 'a.ord_amt', $listDirn, $listOrder); ?>
				</th>
				<th class="center">
					<?php echo Text::_('JSTATUS'); ?>
				</th>
				<th class="center">
					<?php echo Text::_('COM_GAMERCHANDISE_ACTIONS'); ?>
				</th>

			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : $cols; ?>">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) : ?>
			<?php 
				$canPurchase = ($item->state <= 1 || $canPurchase) ? true : false;
				$canEdit = $user->authorise('core.edit', 'com_gamerchandise');
				if (!$canEdit && $user->authorise('core.edit.own', 'com_gamerchandise')) {
					$canEdit = $user->id == $item->user_id;
					$canDelete = $user->id == $item->user_id;
				}
				$orderPDF  = $path . str_pad($item->order_ref, 8, '0', STR_PAD_LEFT) . '.pdf';
				
				if ($item->state == 2) { $status = 'Complete'; } 
				elseif ($item->state == 3) { $status =  'Paid'; } 
				elseif ($item->state == 4) { $status =  'Ordered'; }
				elseif ($item->state == 0) { $status =  'Created'; $unconfirmedItems = true;}
				else { $status =  'Unpaid'; }

                $confTran = GamerchandiseHelper::getHTTPQuery(null, 'task', 'sale.commitPurchases', 'id', $item->id);
                $confURL = $baseURL.\http_build_query($confTran, '', '&amp;');

			?>

			<tr class="row<?php echo $i % 2; ?>">

				<td>
					<?php if (isset($item->checked_out) && $item->checked_out) : ?>
						<?php echo HTMLHelper::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'sales.', $canCheckin); ?>
					<?php endif; ?>
					<a href="<?php echo Route::_('index.php?option=com_gamerchandise&view=sale&id='.(int) $item->id); ?>">
						<?php echo $this->escape($item->product); ?>
					</a>
				</td>
				<td>
					<?php echo $item->user_id_name; ?>
				</td>
				<td class='center'>
					<?php echo $item->order_qty; ?>
				</td>
				<td style="text-align: right;">
					<?php echo '$'.number_format($item->ord_amt,2); ?>
				</td>
				<td class='center'>
					<?php echo $status;  ?>
				</td>

				<td class='center'>
					<?php if ($canEdit || $canChange || $canDelete): ?>
						<?php if (($canEdit || $canChange) && $item->state <= 1): ?>
							<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=saleform.edit&id=' . $item->id . '&prod_id=' . $item->prod_id, false, 2); ?>"
								class="btn btn-warning" type="button" title="<?php echo Text::_('COM_GAMERCHANDISE_EDIT_ITEM'); ?>">
								<i class="icon-edit" ></i>
							</a>
						<?php endif; ?>
						<?php if ($canDelete && $item->state <= 1): ?>
							<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=sale.remove&id=' . $item->id, false, 2); ?>"
								class="btn btn-danger delete-button" type="button" title="<?php echo Text::_('COM_GAMERCHANDISE_DELETE_ORDER'); ?>"><i class="icon-trash" ></i>
							</a>
							<a href="<?php echo Route::_($confURL, false, 2); ?>" class="btn btn-primary" type="button"
								title="<?php echo Text::_('COM_GAMERCHANDISE_CONFIRM_PURCHASES'); ?>">
                                <i class="icon-flash" ></i>
							</a>
						<?php endif; ?>
						<?php if ($item->state == 5 && \file_exists($orderPDF)): ?>
							<a href="<?php echo Route::_($orderPDF); ?>" target="_blank"
								class="btn btn-info" type="button" title="<?php echo Text::_('COM_GAMERCHANDISE_VIEW_FILE'); ?>">
								<i class="icon-search" ></i>
							</a>
						<?php endif; ?>
						<?php if ($canCheckin): ?>
							<?php if ($item->state == 5): ?>
								<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=sale.markPaid&id=' . $item->id, false, 2); ?>"
									class="btn btn-primary" type="button" title="<?php echo Text::_('COM_GAMERCHANDISE_MARK_PAID'); ?>">
									<i class="icon-credit" ></i>
								</a>
							<?php endif; ?>
							<?php if ($item->state == 3): ?>
								<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=sale.markOrdered&id=' . $item->id, false, 2); ?>"
									class="btn btn-info" type="button" title="<?php echo Text::_('COM_GAMERCHANDISE_MARK_ORD'); ?>">
									<i class="icon-file-check" ></i>
								</a>
							<?php endif; ?>
							<?php if ($item->state == 3 || $item->state == 4): ?>
								<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=sale.markDelivered&id=' . $item->id, false, 2); ?>"
									class="btn btn-warning" type="button" title="<?php echo Text::_('COM_GAMERCHANDISE_MARK_DEL'); ?>">
									<i class="icon-thumbs-up" ></i>
								</a>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</td>

			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<?php if ($canCreate) : ?>
		<?php if ($canPurchase && $unconfirmedItems): ?>
			<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=sale.commitPurchases', false, 2); ?>"
				class="btn btn-primary" title="<?php echo Text::_('COM_GAMERCHANDISE_CONFIRM_MESSAGE'); ?>">
			    <i class="icon-flash"></i> <?php echo Text::_('COM_GAMERCHANDISE_CONFIRM_PURCHASES'); ?>
			</a>
		<?php endif; ?>
	<?php endif; ?>

	<input type="hidden" name="task" value=""/>
	<input type="hidden" name="boxchecked" value="0"/>
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>"/>
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>"/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>

<div style="margin: 10px 0;">
	<p class="small center">
		<span class="btn" ><i class="icon-publish"></i></span> <?php echo Text::_('COM_GAMERCHANDISE_GREENTICK'); ?><br />
		<span class="btn" ><i class="icon-unpublish"></i></span> <?php echo Text::_('COM_GAMERCHANDISE_REDCROSS'); ?>
	</p>
</div>

<?php if($canDelete) : ?>
	<script type="text/javascript">

		jQuery(document).ready(function () {
			jQuery('.delete-button').click(deleteItem);
		});

		function deleteItem() {
			if (!confirm("<?php echo Text::_('COM_GAMERCHANDISE_DELETE_MESSAGE'); ?>")) {
				return false;
			}
		}
	</script>
<?php endif; ?>
