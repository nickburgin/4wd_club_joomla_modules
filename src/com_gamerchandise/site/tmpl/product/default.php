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

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gamerchandise', JPATH_ADMINISTRATOR);

$user = GamerchandiseHelper::getSpecificUser();
$canManage = $user->authorise('core.manage', 'com_gamerchandise');
$canEdit = $user->authorise('core.edit', 'com_gamerchandise');
if (!$canEdit && $user->authorise('core.edit.own', 'com_gamerchandise')) {
	$canEdit = $user->id == $this->item->created_by;
}
$emailPO = $this->params->get('poemail');
$total_cost = 0;

$path = Path::clean( JPATH_SITE . '/images/merchandise' );
$order  = str_pad($this->item->id, 8, '0', STR_PAD_LEFT);
$pofile = $path.'/ORD'.$order.'.pdf';
$fileExists = \file_exists($pofile);
$link_file = 'images/merchandise/ORD'.$order.'.pdf';

?>

<?php if ($this->item) : ?>
	<h2>Details of <?php echo $this->item->prod_name; ?></h2>
	<div class="item_fields">
		<table class="table">
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_PRODUCT_STATE'); ?></th>
				<td><i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_PRODUCT_CAT_ID'); ?></th>
				<td><?php echo $this->item->cat_id_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_PRODUCT_PROD_NAME'); ?></th>
				<td><?php echo $this->item->prod_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_PRODUCT_PROD_CODE'); ?></th>
				<td><?php echo $this->item->prod_code; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_PRODUCT_PRICE'); ?></th>
				<td><?php echo '$'.number_format($this->item->price,2); ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_PRODUCT_PROD_DESC'); ?></th>
				<td><?php echo $this->item->prod_desc; ?></td>
			</tr>

		</table>
	</div>

	<table class="table" id="saleList">
		<thead>
			<tr>
				<th class='left'><?php echo Text::_('COM_GAMERCHANDISE_SALES_USER_ID'); ?></th>
				<th class="center"><?php echo Text::_('COM_GAMERCHANDISE_SALES_ORDER_QTY'); ?></th>
				<th class="right"><?php echo Text::_('COM_GAMERCHANDISE_SALES_ORD_AMT'); ?></th>
				<th class="center"><?php echo Text::_('COM_GAMERCHANDISE_SALES_ORD_PAID'); ?></th>
				<th class="center"><?php echo Text::_('COM_GAMERCHANDISE_PRODUCTS_ACTIONS'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->item->sales as $i => $sale) : ?>
				<?php if ($canManage || $user->id == $sale->user_id) : ?>
					<tr>
						<td><?php echo $sale->user_id_name; ?></td>
						<td class="center"><?php echo $sale->order_qty; ?></td>
						<td style="text-align:right;"><?php echo '$'.number_format($sale->ord_amt,2); ?></td>
						<td class="center"><?php if ($sale->ord_paid) {echo '<i class="icon-ok"></i>';} else {echo '<i class="icon-cancel-2"></i>';} ?></td>
						<td class="center">
							<?php if($canEdit && $this->item->checked_out == 0): ?>
								<a class="btn btn-mini" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=saleform.edit&id='.$sale->id); ?>" title="Edit line item">
									<i class="icon-edit"></i>
								</a>
							<?php endif; ?>
							<?php if($canManage): ?>
								<a class="btn btn-mini btn-danger right" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=sale.remove&id=' . $sale->id, false, 2); ?>" title="Delete line item">
									<i class="icon-trash"></i>
								</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php endforeach; ?>
		</tbody>
	</table>


	<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gamerchandise&view=products', false, 2); ?>">
		<i class="icon-undo"></i> <?php echo Text::_("COM_GAMERCHANDISE_RETURN"); ?>
	</a>

	<?php if($canEdit && $this->item->checked_out == 0): ?>
		<a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=product.edit&id='.$this->item->id); ?>">
			<i class="icon-edit"></i> <?php echo Text::_("COM_GAMERCHANDISE_EDIT_ITEM"); ?>
		</a>
	<?php endif; ?>
	<?php if($canManage): ?>
		<a class="btn btn-success" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=saleform&layout=edit&id=0&prod_id='.$this->item->id); ?>">
			<i class="icon-plus"></i> <?php echo Text::_("COM_GAMERCHANDISE_ADD_SALE"); ?>
		</a>
		<?php if ($fileExists) : ?>
			<a class="btn btn-info" href="<?php echo $link_file; ?>" alt="View PDF for Printing" target="_blank">
				<i class="icon-print"></i> <?php echo Text::_("COM_GAMERCHANDISE_PRINT_ITEM"); ?>
			</a>
		<?php else : ?>
			<a class="btn btn-info" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=product.genPDF&id=' . $this->item->id, false, 2); ?>">
				<i class="icon-print"></i> <?php echo Text::_("COM_GAMERCHANDISE_PDF_ITEM"); ?>
			</a>
		<?php endif; ?>
		<?php if ($emailPO && $fileExists) : ?>
			<a class="btn btn-incident" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=product.sendPO&id=' . $this->item->id, false, 2); ?>">
				<i class="icon-mail-2"></i> <?php echo Text::_("COM_GAMERCHANDISE_SEND_ITEM"); ?>
			</a>
		<?php endif; ?>
		<a class="btn btn-danger right" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=product.remove&id=' . $this->item->id, false, 2); ?>">
			<i class="icon-trash"></i> <?php echo Text::_("COM_GAMERCHANDISE_DELETE_ITEM"); ?>
		</a>
		<?php if ($fileExists) : ?>
			<a class="btn btn-inverse right" style="margin-right: 5px;" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=product.deletePDF&id=' . $this->item->id, false, 2); ?>">
				<i class="icon-trash"></i> <?php echo Text::_("COM_GAMERCHANDISE_DELETE_PDF"); ?>
			</a>
		<?php endif; ?>
	<?php endif; ?>
<?php else : ?>
	<?php echo Text::_('COM_GAMERCHANDISE_ITEM_NOT_LOADED'); ?>
<?php endif; ?>
