<?php
/**
 * @version    5.1.0
 * @package    com_gatripsys
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
use \Joomla\CMS\Component\ComponentHelper;

$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_gatripsys');
$wa->useScript('keepalive')
	->useScript('form.validate');

$params = ComponentHelper::getParams('com_gatripsys');
$charge_trip = $params->get('charge_trip',0);

?>
<form
	action="<?php echo Route::_('index.php?option=com_gatripsys&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="trip-form" class="form-validate form-horizontal">

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GATRIPSYS_TITLE_TRIP', true)); ?>

		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="general" class="adminform">
					<legend><?php echo Text::_('COM_GATRIPSYS_FIELDSET_TRIP'); ?></legend>
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
					<?php echo $this->form->renderFieldset('plans'); ?>
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

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
