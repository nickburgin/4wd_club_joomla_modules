<?php
/**
 * @version    4.2.2
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
use \Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaforsaleHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gaforsale.gaforsalepreset');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gaforsale', JPATH_ADMINISTRATOR);

$user = GaforsaleHelper::getSpecificUser();

if($this->item->state == 1){
	$state_string = 'Publish';
	$state_value = 1;
} else {
	$state_string = 'Unpublish';
	$state_value = 0;
}
$canAdmin = $user->authorise('core.admin','com_gaforsale');
$canState = $user->authorise('core.edit.state','com_gaforsale');
$canDelete = ($user->authorise('core.edit.own','com_gaforsale') && $user->id == $this->item->user_id) ? true : false;
$canEdit = GaforsaleHelper::canUserEdit($this->item, $user);

$templt = isset($this->item->id) && $this->item->id == 0 ? 'fsitems' : '';
$this->form->setFieldAttribute('templt', 'type', 'hidden');
$this->form->setFieldAttribute('templt', 'default', $templt);
$this->form->setFieldAttribute('id', 'type', 'hidden');

//GaforsaleHelper::gaPrint($this->item);
/*
GaforsaleHelper::gaPrint(Factory::getApplication()->getUserState('com_gaforsale.test.data'));
print_r(Factory::getApplication()->getUserState('com_garesearch.test.data'));
*/
?>

<div class="fsitem-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new \Exception(Text::_('COM_GAFORSALE_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo Text::sprintf('COM_GAFORSALE_EDIT_ITEM_TITLE', $this->item->item_desc); ?></h1>
		<?php else: ?>
			<h1><?php echo Text::_('COM_GAFORSALE_ADD_ITEM'); ?></h1>
		<?php endif; ?>

	<form id="form-fsitem"
		  action="<?php echo Route::_('index.php?option=com_gaforsale&task=fsitemform.save'); ?>"
		  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

			<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAFORSALE_FSITEM_DETAILS', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="general" class="adminform">
							<legend><?php echo Text::_('COM_GAFORSALE_FSITEM_DETAILS'); ?></legend>
							<?php if ($canAdmin) : ?>
								<?php echo $this->form->renderField('user_id'); ?>
                            <?php else: ?>
								<?php echo $this->form->renderField('user_id_name'); ?>
								<input type="hidden" name="jform[user_id]" value="<?php echo $this->item->user_id; ?>" />
								<?php echo $this->form->renderField('id'); ?>
                            <?php endif; ?>

							<?php echo $this->form->renderFieldset('general'); ?>

						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'imageinfo', Text::_('COM_GAFORSALE_IMAGEINFO', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="imageinfo" class="adminform">
							<?php if (isset($this->item->item_image) && !empty($this->item->item_image)) : ?>
								<div class="control-group">
									<div class="control-label"><?php echo $this->form->getLabel('item_image_txt'); ?></div>
									<div class="controls"><?php echo $this->form->getInput('item_image_txt'); ?>
										<a class="btn btn-danger btn-mini pull-left"
											href="<?php echo Route::_('index.php?option=com_gaforsale&task=fsitem.removeFile&id='.$this->item->id); ?>"
											title="<?php echo Text::_('GAREMOVE_FILE'); ?>">
											<i class="icon-trash"></i>
										</a>
									</div>
							    </div>
							    <input type="hidden" name="jform[item_image]" value="" />
							<?php else: ?>
								<?php echo $this->form->renderField('item_image'); ?>
								<input type="hidden" name="jform[item_image_txt]" value="" />
							<?php endif; ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php if ($canAdmin) : ?>
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
    							<?php echo $this->form->renderField('templt'); ?>
    						</fieldset>
    					</div>
    				</div>
    			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		    <?php endif; ?>

			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

			<div class="control-group" >
				<div class="controls">

					<?php if ($this->canSave): ?>
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-danger"
					   href="<?php echo Route::_('index.php?option=com_gaforsale&task=fsitemform.cancel'); ?>"
					   title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span> <?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gaforsale"/>
			<input type="hidden" name="task" value="fsitemform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
