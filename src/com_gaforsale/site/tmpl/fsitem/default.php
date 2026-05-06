<?php
/**
 * @version    4.0.2
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2013. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaforsaleHelper;

$user = GaforsaleHelper::getSpecificUser();
$canEdit = $user->authorise('core.edit', 'com_gaforsale');
if (!$canEdit && $user->authorise('core.edit.own', 'com_gaforsale')) {
	$canEdit = $user->id == $this->item->created_by;
}
?>
<?php if ($this->item) : ?>

	<div class="item_fields">
		<table class="table">
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_ID'); ?></th>
				<td><?php echo $this->item->id; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_STATE'); ?></th>
				<td>
					<i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i>
				</td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_CREATED_BY'); ?></th>
				<td><?php echo $this->item->created_by_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_CREATED_DATE'); ?></th>
				<td><?php echo $this->item->created_date; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_MODIFIED_BY'); ?></th>
				<td><?php echo $this->item->modified_by_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_MODIFIED_DATE'); ?></th>
				<td><?php echo $this->item->update_date; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_USER_ID'); ?></th>
				<td><?php echo $this->item->user_id_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_ITEM_DESC'); ?></th>
				<td><?php echo $this->item->item_desc; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_ITEM_DETAILS'); ?></th>
				<td><?php echo $this->item->item_details; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_ITEM_PRICE'); ?></th>
				<td><?php echo $this->item->item_price; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_SELLER_CONTACT'); ?></th>
				<td><?php echo $this->item->seller_contact; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_FSITEM_SELLER_PHONE'); ?></th>
				<td><?php echo $this->item->seller_phone; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GAFORSALE_FORM_LBL_COMMENTS'); ?></th>
				<td><?php echo $this->item->comments; ?></td>
			</tr>

		</table>
	</div>
	<?php if($canEdit && $this->item->checked_out == 0): ?>
		<a class="btn" href="<?php echo Route::_('index.php?option=com_gaforsale&task=fsitem.edit&id='.$this->item->id); ?>"><?php echo Text::_("COM_GAFORSALE_EDIT_ITEM"); ?></a>
	<?php endif; ?>
	<?php if(Factory::getUser()->authorise('core.delete','com_gaforsale')):?>
		<a class="btn" href="<?php echo Route::_('index.php?option=com_gaforsale&task=fsitem.remove&id=' . $this->item->id, false, 2); ?>"><?php echo Text::_("COM_GAFORSALE_DELETE_ITEM"); ?></a>
	<?php endif; ?>
<?php
	else:
		echo Text::_('COM_GAFORSALE_ITEM_NOT_LOADED');
	endif;
?>
