<?php
/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user = GatripsysHelper::getSpecificUser();
$canEdit = $user->authorise('core.edit', 'com_gatripsys');

if (!$canEdit && $user->authorise('core.edit.own', 'com_gatripsys')) {
	$canEdit = $user->id == $this->item->created_by;
}
?>

<div class="item_fields">

	<table class="table">
		

		<tr>
			<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INVOICE_CREATED_DATE'); ?></th>
			<td><?php echo $this->item->created_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INVOICE_USER_ID'); ?></th>
			<td><?php echo $this->item->user_id_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INVOICE_CREATED_BY'); ?></th>
			<td><?php echo $this->item->created_by_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_INVOICE_PAID_DATE'); ?></th>
			<td><?php echo $this->item->paid_date; ?></td>
		</tr>

	</table>

</div>

<a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_gatripsys&task=invoice.cancel'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GATRIPSYS_RETURN"); ?>
</a>

<?php if ($user->authorise('core.delete','com_gatripsys.invoice.'.$this->item->id)) : ?>

	<a class="btn btn-danger pull-right" href="#deleteModal" role="button" data-bs-toggle="modal">
		<i class="icon-trash"></i> <?php echo Text::_("COM_GATRIPSYS_DELETE_ITEM"); ?>
	</a>

	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo Text::_('COM_GATRIPSYS_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo Text::sprintf('COM_GATRIPSYS_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-bs-dismiss="modal">Close</button>
			<a href="<?php echo Route::_('index.php?option=com_gatripsys&task=invoice.remove&id=' . $this->item->id, false, 2); ?>" class="btn btn-danger">
				<?php echo Text::_('COM_GATRIPSYS_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>

<?php endif; ?>
