<?php
/**
 * @version    3.0.09
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2013. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;


HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.core');
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');


?>
<form
	action="<?php echo Route::_('index.php?option=com_gaforsale&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="account-form" class="form-validate form-horizontal">

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAFORSALE_TITLE_FSITEM', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="general" class="adminform">
					<legend><?php echo Text::_('COM_GAFORSALE_TITLE_FSITEM'); ?></legend>
					<?php echo $this->form->renderFieldset('general'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GAFORSALE_XTRAINFO', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="extrainfo" class="adminform">
					<?php echo $this->form->renderFieldset('extrainfo'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GAFORSALE_SYSINFO', true)); ?>
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
