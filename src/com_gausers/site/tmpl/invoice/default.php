<?php
/**
 * @version    6.0.0
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$user       = Factory::getApplication()->getIdentity();
$canEdit = $user->authorise('core.edit', 'com_gausers');

if (!$canEdit && $user->authorise('core.edit.own', 'com_gausers')) {
	$canEdit = $user->id == $this->item->created_by;
}
?>

<div class="item_fields">

	<table class="table">
		

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_CREATED_DATE'); ?></th>
			<td><?php echo $this->item->created_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_INVOICE_USER_ID'); ?></th>
			<td><?php echo $this->item->user_id_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_CREATED_BY'); ?></th>
			<td><?php echo $this->item->created_by_name; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_INVOICE_PAID_DATE'); ?></th>
			<td><?php echo $this->item->paid_date; ?></td>
		</tr>

	</table>

</div>

<a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_gausers&task=invoice.cancel'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GAUSERS_RETURN"); ?>
</a>

<?php if ($user->authorise('core.delete','com_gausers.invoice.'.$this->item->id)) : ?>

	<a class="btn btn-danger pull-right" href="#deleteModal" role="button" data-toggle="modal">
		<i class="icon-trash"></i> <?php echo Text::_("COM_GAUSERS_DELETE_ITEM"); ?>
	</a>

	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo Text::_('COM_GAUSERS_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo Text::sprintf('COM_GAUSERS_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">Close</button>
			<a href="<?php echo Route::_('index.php?option=com_gausers&task=invoice.remove&id=' . $this->item->id, false, 2); ?>" class="btn btn-danger">
				<?php echo Text::_('COM_GAUSERS_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>

<?php endif; ?>
