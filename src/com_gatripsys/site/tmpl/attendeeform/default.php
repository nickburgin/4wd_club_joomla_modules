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
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_gatripsys');
$wa->useScript('keepalive')
	->useScript('form.validate');

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user       = GatripsysHelper::getSpecificUser();
$canAdmin  = $user->authorise('core.admin', 'com_gatripsys');
$canTrip  = $user->authorise('core.trip', 'com_gatripsys');
$admin_id = $this->params->get('admin_id');
$canAdmin  = ($user->id == $admin_id) ? 1 : $canAdmin;
$canLead = (($user->id == $this->item->leader) || $canTrip || $canAdmin) ? 1 : 0;
/*
echo '<pre>Test<br />';
print_r($user->authorise);
echo '</pre>';
*/
?>
<div class="trip-edit front-end-edit">
	<?php if (!empty($this->item->id)): ?>
		<h2>Edit Booking for <?php echo GatripsysHelper::getSpecificUser($this->item->user_id)->name; ?></h2>
	<?php else: ?>
		<h2>Add New Booking Entry</h2>
	<?php endif; ?>

	<form id="form-attendee"
		  action="<?php echo Route::_('index.php?option=com_gatripsys&task=attendeeform.save'); ?>"
		  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
		

		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GATRIPSYS_TITLE_ATTENDEE', true)); ?>
			<div class="row-fluid">
				<?php if ($canLead) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('user_id'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('user_id'); ?></div>
					</div>
				<?php else: ?>
					<?php if (!empty($this->item->user_id)): ?>
						<input type="hidden" name="jform[user_id]" value="<?php echo $this->item->user_id; ?>" />
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('user_name'); ?></div>
							<div class="controls"><input type="text" name="jform[user_name]" readonly="true" value="<?php echo GatripsysHelper::getSpecificUser($this->item->user_id)->name; ?>" /></div>
						</div>
					<?php else: ?>
						<input type="hidden" name="jform[user_id]" value="<?php echo $user->id; ?>" />
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('user_name'); ?></div>
							<div class="controls"><input type="text" name="jform[user_name]" readonly="true" value="<?php echo $user->name; ?>" /></div>
						</div>
					<?php endif; ?>
				<?php endif; ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('trip_id'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('trip_id'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('in_party'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('in_party'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('comment'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('comment'); ?></div>
				</div>
				<?php if ($canAdmin) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('approved_by'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('approved_by'); ?></div>
					</div>
				<?php endif; ?>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GATRIPSYS_TITLE_SYSINFO', true)); ?>
			<div class="row-fluid">
				<input type="hidden" name="jform[from_modal]" value="0" />
				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
				<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
				<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
				<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
				<input type="hidden" name="jform[modified_by]" value="<?php echo GatripsysHelper::getSpecificUser()->id; ?>" />

				<?php if($canAdmin) : ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('state'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('state'); ?></div>
					</div>
				<?php else: ?>
					<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
				<?php endif; ?>
				<?php if(empty($this->item->created_by)): ?>
					<input type="hidden" name="jform[created_by]" value="<?php echo GatripsysHelper::getSpecificUser()->id; ?>" />
				<?php else: ?>
					<input type="hidden" name="jform[created_by]" value="<?php echo $this->item->created_by; ?>" />
				<?php endif; ?>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('created_date'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('created_date'); ?></div>
				</div>
				<div class="control-group">
					<div class="control-label"><?php echo $this->form->getLabel('modified_date'); ?></div>
					<div class="controls"><?php echo $this->form->getInput('modified_date'); ?></div>
				</div>
			</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<div class="control-group">
			<div class="controls">

				<?php if ($this->canSave): ?>
					<button type="submit" class="validate btn btn-primary">
						<?php echo Text::_('JSUBMIT'); ?>
					</button>
				<?php endif; ?>
				<a class="btn"
				   href="<?php echo Route::_('index.php?option=com_gatripsys&task=attendeeform.cancel'); ?>"
				   title="<?php echo Text::_('JCANCEL'); ?>">
					<?php echo Text::_('JCANCEL'); ?>
				</a>
			</div>
		</div>

		<input type="hidden" name="option" value="com_gatripsys"/>
		<input type="hidden" name="task" value="attendeeform.save"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
