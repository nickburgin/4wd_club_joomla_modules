<?php
/**
 * @version    5.3.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gatripsys.gatripsyspreset');

// Load language file
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR, 'en-GB', true);
$lang->load('com_gatripsys', JPATH_SITE, 'en-GB', true);

$user    = GatripsysHelper::getSpecificUser();
$canEdit = GatripsysHelper::canUserEdit($user, $this->item);
$charge_trip = $this->params->get('charge_trip', 0);
$admin_id = $this->params->get('admin_id');

$canTrip  = $user->authorise('core.trip', 'com_gatripsys');
$canTrip = ($canTrip || $admin_id == $user->id) ? 1 : 0;

if (!isset($this->item->id) || empty($this->item->id) || $this->item->id == 0) {
    // set leader as user by default
    $this->form->setFieldAttribute('leader', 'default', $user->id);
}
if (!$canTrip) {
    $this->form->setFieldAttribute('id', 'type', 'hidden');
    $this->form->setFieldAttribute('ordering', 'type', 'hidden');
    $this->form->setFieldAttribute('state', 'type', 'hidden');
}
?>

<div class="trip-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new \Exception(Text::_('COM_GATRIPSYS_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo Text::sprintf('COM_GATRIPSYS_EDIT_TRIP', $this->item->title); ?></h1>
		<?php else: ?>
			<h1><?php echo Text::_('COM_GATRIPSYS_ADD_NEW_TRIP'); ?></h1>
		<?php endif; ?>

		<form id="form-trip"
			  action="<?php echo Route::_('index.php?option=com_gatripsys&task=tripform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

			<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GATRIPSYS_TITLE_TRIP_BASICS', true)); ?>
		
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="general" class="adminform">
							<?php echo $this->form->renderFieldset('general'); ?>
						</fieldset>
						<?php if ($charge_trip): ?>
							<?php echo $this->form->renderField('trip_cost'); ?>
						<?php endif; ?>
					</div>
				</div>

			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'depart', Text::_('COM_GATRIPSYS_TITLE_TRIP_DEPARTURE', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="depart" class="adminform">
							<?php echo $this->form->renderFieldset('depart'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'where', Text::_('COM_GATRIPSYS_TITLE_WHERE', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="where" class="adminform">
							<?php echo $this->form->renderFieldset('where'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'what', Text::_('COM_GATRIPSYS_TITLE_WHAT', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="what" class="adminform">
							<?php echo $this->form->renderFieldset('what'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'equip', Text::_('COM_GATRIPSYS_TITLE_EQUIP', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="equip" class="adminform">
							<?php echo $this->form->renderFieldset('equip'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_GATRIPSYS_TITLE_DETAILS', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="details" class="adminform">
							<?php echo $this->form->renderFieldset('details'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'plans', Text::_('COM_GATRIPSYS_TITLE_TRIPPLANS', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="plans" class="adminform">

                            <?php if (!isset($this->item->trip_img_disp)) : ?>
								<?php echo $this->form->renderField('trip_img'); ?>
							<?php else : ?>
								<?php $this->form->setFieldAttribute('trip_img_disp', 'class', ' inclButton'); ?>
								<?php echo $this->form->renderField('trip_img_disp'); ?>
								<a class="btn btn-danger"
									href="<?php echo Route::_('index.php?option=com_gatripsys&task=trip.removeTripFile&tripfile=i&id='.$this->item->id); ?>"
									title="<?php echo Text::_('JREMOVE_FILE_DESC'); ?>">
									<i class="icon-trash"></i> <?php echo Text::_('JREMOVE_FILE'); ?>
								</a>
							<?php endif; ?>

							<?php echo $this->form->renderField('trip_plan'); ?>
							<?php echo $this->form->renderField('trip_plan_new'); ?>

						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GATRIPSYS_TITLE_EXTRAINFO', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="extrainfo" class="adminform">
							<?php echo $this->form->renderFieldset('extrainfo'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GATRIPSYS_TITLE_SYSINFO', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="sysinfo" class="adminform">
							<?php echo $this->form->renderFieldset('sysinfo'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

			<div class="control-group">
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-danger"
					   href="<?php echo Route::_('index.php?option=com_gatripsys&task=tripform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span>
						<?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gatripsys"/>
			<input type="hidden" name="task" value="tripform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
