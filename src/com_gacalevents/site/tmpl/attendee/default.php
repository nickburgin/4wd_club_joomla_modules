<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gacalevents.gacaleventspreset');

// Load admin language file
Factory::getApplication()->getLanguage()->load('com_gacalevents', JPATH_ADMINISTRATOR);

$user = Factory::getApplication()->getIdentity();

$canEdit = $user->authorise('core.edit', 'com_gacalevents');

if (!$canEdit && $user->authorise('core.edit.own', 'com_gacalevents'))
{
	$canEdit = $user->id == $this->item->created_by;
}
$depart_day = HTMLHelper::_('date', $this->item->depart_date, Text::_('COM_GACALEVENTS_DISPLAY_DATEFULL'));
$return_day = HTMLHelper::_('date', $this->item->return_date, Text::_('COM_GACALEVENTS_DISPLAY_DATEFULL'));


?>

<div class="item_fields">

	<table class="table">
		

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_CREATED_DATE'); ?></th>
			<td><?php echo $this->item->created_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_MODIFIED_DATE'); ?></th>
			<td><?php echo $this->item->modified_date; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_ATTENDEE_ATTENDEE'); ?></th>
			<td><?php echo $this->item->attendee_name.' ('.$this->item->pub_name.')'; ?></td>
		</tr>
		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_ATTENDEE_QTY_ATT'); ?></th>
			<td><?php echo $this->item->qty_att; ?></td>
		</tr>
		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_ATTENDEE_EVENT'); ?></th>
			<td><?php echo $this->item->event_title.' <br />From: '.$depart_day.'<br />To: '.$return_day; ?></td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GACALEVENTS_FORM_LBL_COMMENT'); ?></th>
			<td><?php if ($this->item->comment) { echo nl2br($this->item->comment); } ?></td>
		</tr>

	</table>

</div>

<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gacalevents&view=attendees'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GACALEVENTS_RETURN"); ?>
</a>

<?php if($canEdit && $this->item->checked_out == 0): ?>

	<a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_gacalevents&task=attendee.edit&id='.$this->item->id); ?>">
		<i class="icon-edit"></i> <?php echo Text::_("COM_GACALEVENTS_EDIT_ITEM"); ?>
	</a>

<?php endif; ?>

<?php if ($user->authorise('core.delete','com_gacalevents.attendee.'.$this->item->id)) : ?>

	<a class="btn btn-danger" href="#deleteModal" role="button" data-toggle="modal">
		<i class="icon-trash"></i> <?php echo Text::_("COM_GACALEVENTS_DELETE_ITEM"); ?>
	</a>

	<div id="deleteModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="deleteModal" aria-hidden="true">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo Text::_('COM_GACALEVENTS_DELETE_ITEM'); ?></h3>
		</div>
		<div class="modal-body">
			<p><?php echo Text::sprintf('GACALEVENTS_DELETE_CONFIRM', $this->item->id); ?></p>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal">Close</button>
			<a href="<?php echo Route::_('index.php?option=com_gacalevents&task=attendee.remove&id=' . $this->item->id, false, 2); ?>" class="btn btn-danger">
				<?php echo Text::_('COM_GACALEVENTS_DELETE_ITEM'); ?>
			</a>
		</div>
	</div>

<?php endif; ?>
