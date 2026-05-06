<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Layout\LayoutHelper;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

?>
<form
	action="<?php echo Route::_('index.php?option=com_gausers&layout=edit&id='.(int) $this->item->id); ?>" 
	method="post" enctype="multipart/form-data" name="adminForm" id="invoice-form" class="form-validate">

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAUSERS_TITLE_INVOICE', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="general" class="adminform">
					<legend><?php echo Text::_('COM_GAUSERS_LEGEND_INVOICE'); ?></legend>
					<?php echo $this->form->renderFieldset('general'); ?>
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

	<?php $this->ignore_fieldsets = array('general', 'extrainfo', 'sysinfo'); ?>
	<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
	
	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
