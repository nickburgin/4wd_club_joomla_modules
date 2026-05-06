<?php
/**
 * @version    5.1.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$canEdit = GatripsysHelper::getSpecificUser()->authorise('core.edit', 'com_gatripsys');
if (!$canEdit && GatripsysHelper::getSpecificUser()->authorise('core.edit.own', 'com_gatripsys')) {
	$canEdit = GatripsysHelper::getSpecificUser()->id == $this->item->created_by;
}
?>
<?php if ($this->item) : ?>

	<div class="item_fields">
		<table class="table">
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_STATE'); ?></th>
				<td>
					<i class="icon-<?php echo ($this->item->state == 1) ? 'publish' : 'unpublish'; ?>"></i>
				</td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_CREATED_BY'); ?></th>
				<td><?php echo $this->item->created_by_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_CREATED_DATE'); ?></th>
				<td><?php echo $this->item->created_date; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_MODIFIED_BY'); ?></th>
				<td><?php echo $this->item->modified_by_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_UPDATE_DATE'); ?></th>
				<td><?php echo $this->item->modified_date; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_USER_ID'); ?></th>
				<td><?php echo $this->item->user_id_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_TRIP_ID'); ?></th>
				<td>id = <?php echo $this->item->trip_id; ?> name = <?php echo $this->item->trip_id_title; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_APPROVED_BY'); ?></th>
				<td><?php echo $this->item->approved_by_name; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_IN_PARTY'); ?></th>
				<td><?php echo $this->item->in_party; ?></td>
			</tr>
			<tr>
				<th><?php echo Text::_('COM_GATRIPSYS_FORM_LBL_ATTENDEE_COMMENT'); ?></th>
				<td><?php echo $this->item->comment; ?></td>
			</tr>

		</table>
	</div>
	<?php if($canEdit && $this->item->checked_out == 0): ?>
		<a class="btn" href="<?php echo Route::_('index.php?option=com_gatripsys&task=attendeeform.edit&id='.$this->item->id); ?>"><?php echo Text::_("COM_GATRIPSYS_EDIT_ITEM"); ?></a>
	<?php endif; ?>
	<?php if(GatripsysHelper::getSpecificUser()->authorise('core.admin','com_gatripsys')):?>
		<a class="btn" href="<?php echo Route::_('index.php?option=com_gatripsys&task=attendeeform.remove&id=' . $this->item->id, false); ?>"><?php echo Text::_("COM_GATRIPSYS_DELETE_ITEM"); ?></a>
	<?php endif; ?>
<?php else: ?>
	<?php echo Text::_('COM_GATRIPSYS_ITEM_NOT_LOADED'); ?>
<?php endif; ?>
