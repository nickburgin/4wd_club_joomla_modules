<?php
/**
 * @version     5.1.0
 * @package     com_gatripsys
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
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_gatripsys');
$wa->useScript('keepalive')
	->useScript('form.validate');

?>
<form
	action="<?php echo Route::_('index.php?option=com_gatripsys&layout=edit&id='.(int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="invoice-form" class="form-validate">

	<div class="form-horizontal">
		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GATRIPSYS_LEGEND_INVOICE', true)); ?>
		<div class="row-fluid">
			<div class="span9 form-horizontal">
				<fieldset class="adminform">

					<h3><?php echo $this->item->trip->title; ?></h3>
					<?php echo $this->form->renderField('user_id'); ?>
					<?php echo $this->form->renderField('invoice_amt'); ?>
					<?php echo $this->form->renderField('paid_date'); ?>
					<?php echo $this->form->renderField('comment'); ?>

				</fieldset>
			</div>
			<div class="span3">
				<fieldset class="adminform">
					<?php echo $this->form->renderField('id'); ?>
					<?php echo $this->form->renderField('state'); ?>
					<?php echo $this->form->renderField('created_by'); ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('created_date'); ?></div>
						<div class="controls"><?php echo $this->item->created_date; ?></div>
					</div>

					<?php if(empty($this->item->modified_by)) : ?>
						<input type="hidden" name="jform[modified_by]" value="<?php echo GatripsysHelper::getSpecificUser()->id; ?>" />
					<?php else : ?>
						<input type="hidden" name="jform[modified_by]" value="<?php echo $this->item->modified_by; ?>" />
					<?php endif; ?>
					<?php echo $this->form->renderField('modified_date'); ?>

					<?php if ($this->state->params->get('save_history', 1)) : ?>
						<div class="control-group">
							<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
							<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
						</div>
					<?php endif; ?>
				</fieldset>
			</div>
		</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>

		

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token'); ?>

	</div>
</form>
