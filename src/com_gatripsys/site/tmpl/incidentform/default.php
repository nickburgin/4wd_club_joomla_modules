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

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

$user       = GatripsysHelper::getSpecificUser();
$canAdmin  = $user->authorise('core.admin', 'com_gatripsys');
$canTrip  = $user->authorise('core.trip', 'com_gatripsys');
$admin_id = $this->params->get('admin_id');
$canAdmin  = ($user->id == $admin_id) ? 1 : $canAdmin;

if (!$canAdmin) {
    // set user to readonly
    $this->form->setFieldAttribute('user_id', 'type', 'hidden');
    $this->form->setFieldAttribute('user_name', 'default', $user->name);
} else {
    $this->form->setFieldAttribute('user_name', 'type', 'hidden');
}

$trip = GatripsysHelper::getTripInformation($this->item->trip_id);

/*
echo '<pre>Test<br />';
print_r($user->authorise);
echo '</pre>';
*/
?>

<div class="trip-edit front-end-edit">
	<?php if (!empty($this->item->id)): ?>
		<h2>Edit Incident provided by <?php echo GatripsysHelper::getSpecificUser($this->item->user_id)->name; ?></h2>
	<?php else: ?>
		<h2>Add Incident Entry</h2>
	<?php endif; ?>

	<form id="form-incident"
		  action="<?php echo Route::_('index.php?option=com_gatripsys&task=incidentform.save'); ?>"
		  method="post" class="form-validate form-horizontal" enctype="multipart/form-data" target="_parent">

			<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_GATRIPSYS_TITLE_INCIDENT', true)); ?>
				<div class="row-fluid">
					<div class="span12 form-horizontal">
						<?php echo $this->form->renderFieldset('general'); ?>
					</div>
				</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'location', Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_LOCATION', true)); ?>
				<div class="row-fluid">
					<div class="span12 form-horizontal">
						<?php echo $this->form->renderFieldset('location'); ?>
					</div>
				</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'witnesses', Text::_('COM_GATRIPSYS_FORM_LBL_INCIDENT_WITNESSES', true)); ?>
				<div class="row-fluid">
					<div class="span12 form-horizontal">
						<?php echo $this->form->renderFieldset('witnesses'); ?>
					</div>
				</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'extrainfo', Text::_('COM_GATRIPSYS_TITLE_EXTRAINFO', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="extrainfo" class="adminform">
							<?php echo $this->form->renderFieldset('extrainfo'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
		
			<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'sysinfo', Text::_('COM_GATRIPSYS_TITLE_SYSINFO', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="sysinfo" class="adminform">
							<?php echo $this->form->renderFieldset('sysinfo'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('bootstrap.endTab'); ?>
	
			<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-danger"
					   href="<?php echo Route::_('index.php?option=com_gatripsys&task=incidentform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

		<input type="hidden" name="option" value="com_gatripsys"/>
		<input type="hidden" name="task" value="incidentform.save"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
