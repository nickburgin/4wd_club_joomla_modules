<?php
/**
 * @version    4.3.3
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\HTML\HTMLHelper;

// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gabroadcast', JPATH_ADMINISTRATOR);

$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_gabroadcast');
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');


?>

<form
	action="<?php echo Route::_('index.php?option=com_gabroadcast&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="bcast-form" class="form-validate">

	<div class="form-horizontal">
		<?php echo HTMLHelper::_('bootstrap.startTabSet', 'myTab', array('active' => 'general')); ?>

		<?php echo HTMLHelper::_('bootstrap.addTab', 'myTab', 'general', Text::_('COM_GABROADCAST_TITLE_BCAST', true)); ?>
		<div class="row-fluid">
			<div class="span9 form-horizontal">
				<fieldset class="adminform">

					<?php echo $this->form->renderField('attach_lab'); ?>
					<?php echo $this->form->renderField('attach_dir'); ?>
					<?php echo $this->form->renderField('comment'); ?>

				</fieldset>
			</div>
			<div class="span2 form-horizontal">
				<fieldset class="adminform">

					<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
					<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
					<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
					<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	
					<?php echo $this->form->renderField('state'); ?>
					<?php echo $this->form->renderField('created_by'); ?>
					<?php echo $this->form->renderField('modified_by'); ?>
					<?php echo $this->form->renderField('created_date'); ?>
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
		<?php echo HTMLHelper::_('bootstrap.endTab'); ?>

		<?php echo HTMLHelper::_('bootstrap.endTabSet'); ?>

		<input type="hidden" name="task" value=""/>
		<?php echo HTMLHelper::_('form.token'); ?>

	</div>
</form>
