<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

?>

<form action="<?php echo Route::_('index.php?option=com_gausers&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="clubexec-form" class="form-validate">

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAUSERS_TITLE_CLUBEXEC', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">

        			<div class="control-group">
        				<div class="control-label"><?php echo $this->form->getLabel('pres_id'); ?></div>
        				<div class="controls"><?php echo $this->form->getInput('pres_id'); ?> &nbsp; <?php echo $this->form->getInput('pres_idp'); ?></div>
        			</div>
        			<?php echo $this->form->renderField('pres_name'); ?>
        			<div class="control-group">
        				<div class="control-label"><?php echo $this->form->getLabel('vpres_id'); ?></div>
        				<div class="controls"><?php echo $this->form->getInput('vpres_id'); ?> &nbsp; <?php echo $this->form->getInput('vpres_idp'); ?></div>
        			</div>
        			<?php echo $this->form->renderField('vpres_name'); ?>
        			<div class="control-group">
        				<div class="control-label"><?php echo $this->form->getLabel('secr_id'); ?></div>
        				<div class="controls"><?php echo $this->form->getInput('secr_id'); ?> &nbsp; <?php echo $this->form->getInput('secr_idp'); ?></div>
        			</div>
        			<?php echo $this->form->renderField('secr_name'); ?>
        			<div class="control-group">
        				<div class="control-label"><?php echo $this->form->getLabel('tres_id'); ?></div>
        				<div class="controls"><?php echo $this->form->getInput('tres_id'); ?> &nbsp; <?php echo $this->form->getInput('tres_idp'); ?></div>
        			</div>
        			<?php echo $this->form->renderField('tres_name'); ?>
        			<div class="control-group">
        				<div class="control-label"><?php echo $this->form->getLabel('edit_id'); ?></div>
        				<div class="controls"><?php echo $this->form->getInput('edit_id'); ?> &nbsp; <?php echo $this->form->getInput('edit_idp'); ?></div>
        			</div>
        			<?php echo $this->form->renderField('edit_name'); ?>
        			<div class="control-group">
        				<div class="control-label"><?php echo $this->form->getLabel('delg_id'); ?></div>
        				<div class="controls"><?php echo $this->form->getInput('delg_id'); ?> &nbsp; <?php echo $this->form->getInput('delg_idp'); ?></div>
        			</div>
        			<?php echo $this->form->renderField('delg_name'); ?>
        			<div class="control-group">
        				<div class="control-label"><?php echo $this->form->getLabel('regr_id'); ?></div>
        				<div class="controls"><?php echo $this->form->getInput('regr_id'); ?> &nbsp; <?php echo $this->form->getInput('regr_idp'); ?></div>
        			</div>
        			<?php echo $this->form->renderField('regr_name'); ?>
        			<div class="control-group">
        				<div class="control-label"><?php echo $this->form->getLabel('istc_id'); ?></div>
        				<div class="controls"><?php echo $this->form->getInput('istc_id'); ?> &nbsp; <?php echo $this->form->getInput('istc_idp'); ?></div>
        			</div>
        			<?php echo $this->form->renderField('istc_name'); ?>

        			<?php echo $this->form->renderField('end_term'); ?>

            </div>

        </div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'club', Text::_('COM_GAUSERS_TITLE_CLUB', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="extrainfo" class="adminform">
					<?php echo $this->form->renderFieldset('club'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'meeting', Text::_('COM_GAUSERS_TITLE_MEETING', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="extrainfo" class="adminform">
					<?php echo $this->form->renderFieldset('meeting'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GAUSERS_XTRAINFO', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="extrainfo" class="adminform">
					<?php echo $this->form->renderFieldset('extrainfo'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GAUSERS_SYSINFO', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="sysinfo" class="adminform">
					<?php echo $this->form->renderFieldset('sysinfo'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php $this->ignore_fieldsets = array('general', 'club', 'meeting', 'extrainfo', 'sysinfo'); ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
	
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
