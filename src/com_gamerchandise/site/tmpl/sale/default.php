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

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gamerchandise', JPATH_ADMINISTRATOR);

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gamerchandise.gamerchandisepreset');

$user       = GamerchandiseHelper::getSpecificUser();
$canEdit = $user->authorise('core.edit', 'com_gamerchandise');
if (!$canEdit && $user->authorise('core.edit.own', 'com_gamerchandise')) {
	$canEdit = $user->id == $this->item->created_by;
}
$canAdmin = $user->authorise('core.manage', 'com_gamerchandise');
$canDelete = $user->authorise('core.delete', 'com_gamerchandise');

?>
<?php if ($this->item) : ?>

	<div class="item_fields">
		<table class="table">
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_STATE'); ?></th>
				<td><i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_CREATED_DATE'); ?></th>
				<td><?php echo $this->item->created_date; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_SALE_PROD_ID'); ?></th>
				<td><?php echo $this->item->product; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_SALE_ORDER_QTY'); ?></th>
				<td><?php echo $this->item->order_qty; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_SALE_DATE_DELIVERED'); ?></th>
				<td><?php echo $this->item->date_delivered; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_SALE_PART_QTY'); ?></th>
				<td><?php echo $this->item->part_qty; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_SALE_PART_DELIVERED'); ?></th>
				<td><?php echo $this->item->part_delivered; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_SALE_ORD_AMT'); ?></th>
				<td><?php echo $this->item->ord_amt; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_SALE_ORD_PAID'); ?></th>
				<td><i class="icon-<?php echo ($this->item->ord_paid == 1) ? 'publish' : 'unpublish'; ?>"></i></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_SALE_PAID_DATE'); ?></th>
				<td><?php echo $this->item->paid_date; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAMERCHANDISE_FORM_LBL_COMMENT'); ?></th>
				<td><?php echo $this->item->comment; ?></td>
			</tr>

		</table>
	</div>

	<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gamerchandise&view=sales', false, 2); ?>">
		<i class="icon-undo"></i> <?php echo Text::_("COM_GAMERCHANDISE_RETURN"); ?>
	</a>
	<?php if($canAdmin || ($canEdit && $this->item->checked_out == 0 && $this->item->state <= 1)): ?>
		<a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=sale.edit&id='.$this->item->id); ?>"><?php echo Text::_("COM_GAMERCHANDISE_EDIT_ITEM"); ?></a>
	<?php endif; ?>

	<?php if($this->item->state == 1): ?>
		<a href="<?php echo Route::_('index.php?option=com_gamerchandise&task=sale.commitPurchase&id='.$this->item->id); ?>"
			class="btn btn-primary" title="<?php echo Text::_('COM_GAMERCHANDISE_CONFIRM_ITEM_MESSAGE'); ?>">
		    <i class="icon-credit"></i> <?php echo Text::_('COM_GAMERCHANDISE_CONFIRM_PURCHASES'); ?>
		</a>
	<?php endif; ?>

	<?php if($canDelete):?>
		<a class="btn btn-danger right" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=sale.remove&id=' . $this->item->id, false, 2); ?>"><?php echo Text::_("COM_GAMERCHANDISE_DELETE_ORDER"); ?></a>
	<?php endif; ?>
<?php else : ?>
	<?php echo Text::_('COM_GAMERCHANDISE_ITEM_NOT_LOADED'); ?>
<?php endif; ?>
