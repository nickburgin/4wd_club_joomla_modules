<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
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
use \Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->useScript('keepalive')
    ->useScript('form.validate')
    ->usePreset('com_gacalevents.gacaleventspreset');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gacalevents', JPATH_ADMINISTRATOR);

$user    = GacaleventsHelper::getSpecificUser();
$canManage = $user->authorise('core.manage', 'com_gacalevents');
$canEdit = GacaleventsHelper::canUserEdit($this->item, $user);

$event_id = Factory::getApplication()->getUserState('com_gacalevents.edit.attendee.event_id');
if (!isset($event_id)) { $event_id = $this->state->get('attendee.event_id'); }

$attendee = Factory::getApplication()->getUserState('com_gacalevents.edit.attendee.attendee');
if (!isset($attendee)) { $attendee = $this->state->get('attendee.attendee'); }

$attName    = GacaleventsHelper::getSpecificUser($attendee)->name;
$event_details = GacaleventsHelper::getEvent($event_id);

if (isset($event_details)) {
	$public_rego = $event_details->formal_event;
	$event_charge = ($event_details->mbr_cost > 0 || $event_details->pub_cost > 0) ? true : false;
} else {
	$public_rego = 0;
	$event_charge = 0;
}
$this->form->setFieldAttribute('event', 'default', $event_id);
$this->form->setFieldAttribute('event', 'type', 'hidden');
if ($attendee) {
    $this->form->setFieldAttribute('attendee', 'type', 'hidden');
    $this->form->setFieldAttribute('attendee', 'default', $attendee);
}
?>

<div class="event-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new \Exception(Text::_('COM_GACALEVENTS_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h2><?php echo Text::sprintf('COM_GACALEVENTS_EDIT_ITEM_TITLE', $this->item->pub_name); ?></h2>
		<?php else: ?>
			<h2><?php echo Text::_('COM_GACALEVENTS_ADD_ITEM_TITLE'); ?></h2>
			<?php echo '<h3>'.$event_details->title.'</h3>'; ?>
		<?php endif; ?>

		<form id="form-event"
			  action="<?php echo Route::_('index.php?option=com_gacalevents&task=attendeeform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

			<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GACALEVENTS_TAB_EVENT', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">

				<?php if ($attendee) : ?>
					<h4><?php echo Text::sprintf('COM_GACALEVENTS_ADD_ATTENDEE', $attName); ?></h4>
				<?php endif; ?>
                <?php echo $this->form->renderFieldset('general'); ?>

				<?php if ($event_charge && $canManage): ?>
					<?php echo $this->form->renderFieldset('eventpayment'); ?>
				<?php endif; ?>

			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php if ($public_rego && $user_id == 0) : ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'formal', Text::_('COM_GAEVENTS_TITLE_FORMAL', true)); ?>
					<div class="row-fluid">
						<div class="span10 form-horizontal">
							<fieldset class="adminform">
								<?php echo $this->form->renderFieldset('publicatt'); ?>
							</fieldset>
						</div>
					</div>
				<?php echo HTMLHelper::_('uitab.endTab'); ?>
			<?php endif; ?>

			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-secondary"
					   href="<?php echo Route::_('index.php?option=com_gacalevents&task=attendeeform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gacalevents"/>
			<input type="hidden" name="task"
				   value="attendeeform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
