<?php
/**
 * @version    4.1.2
 * @package    Com_Gamerchandise
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

// load any assets required
$wa = $this->document->getWebAssetManager();
$wa->usePreset('com_gamerchandise.gamerchandisepreset');
$wa->useScript('keepalive')
	->useScript('form.validate');


// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gamerchandise', JPATH_ADMINISTRATOR);

$user       = GamerchandiseHelper::getSpecificUser();
$canManage = $user->authorise('core.manage', 'com_gamerchandise');
$prod_id = Factory::getApplication()->getUserState('com_gamerchandise.edit.prod.id');
$hide_colour = false;
$hide_size = false;
$notify_qm = $this->params->get('notify_qm', 0);
$ignore_cat = $this->params->get('ignore_cat', 0);
if (is_array($ignore_cat)) {
	$hide_colour = in_array($this->item->product->cat_id, $ignore_cat);
	$hide_size = in_array($this->item->product->cat_id, $ignore_cat);
}
if($hide_size) {
    $this->form->setFieldAttribute('cat_size_id', 'type', 'hidden');
    $this->form->setFieldAttribute('cat_size_id', 'default', 0);
}
if($hide_colour) {
    $this->form->setFieldAttribute('cat_colour_id', 'type', 'hidden');
    $this->form->setFieldAttribute('cat_colour_id', 'default', 0);
}

$this->form->setFieldAttribute('user_id', 'default', $user->id);
if(!$canManage) {
    $this->form->setFieldAttribute('user_id', 'readonly', 'true');
    $this->form->setFieldAttribute('prod_id', 'type', 'hidden');
    $this->form->setFieldAttribute('user_id', 'type', 'hidden');
    $this->form->setFieldAttribute('price', 'type', 'hidden');
    $this->form->setFieldAttribute('ord_paid', 'type', 'hidden');
    $this->form->setFieldAttribute('id', 'type', 'hidden');
    $this->form->setFieldAttribute('order', 'type', 'hidden');
    $this->form->setFieldAttribute('state', 'type', 'hidden');
    $this->form->setFieldAttribute('date_delivered', 'type', 'hidden');
    $this->form->setFieldAttribute('part_qty', 'type', 'hidden');
    $this->form->setFieldAttribute('part_delivered', 'type', 'hidden');
    $this->form->setFieldAttribute('price', 'type', 'hidden');
    $this->form->setFieldAttribute('ord_amt', 'type', 'hidden');
    $this->form->setFieldAttribute('ord_paid', 'type', 'hidden');
    $this->form->setFieldAttribute('paid_date', 'type', 'hidden');
    $this->form->setFieldAttribute('ordering', 'type', 'hidden');
    $this->form->setFieldAttribute('version_note', 'type', 'hidden'); 
}

?>

<div class="sale-edit front-end-edit">
	<h2><?php echo Text::sprintf('COM_GAMERCHANDISE_CREATE_ORDER', $this->item->product->prod_name); ?></h2>

	<form id="form-sale"
		  action="<?php echo Route::_('index.php?option=com_gamerchandise&task=saleform.save'); ?>"
		  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
	
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAMERCHANDISE_TITLE_SALE', true)); ?>
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset name="general" class="adminform">
						<legend><?php echo Text::_('COM_GAMERCHANDISE_TITLE_SALE'); ?></legend>
						<?php echo $this->form->renderFieldset('general'); ?>
						<?php if(!$canManage): ?>
    						<?php echo $this->form->renderFieldset('special'); ?>
						<?php endif; ?>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
	
	    <?php if($canManage): ?>
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'special', Text::_('COM_GAMERCHANDISE_UPDATE_SALES', true)); ?>
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset name="special" class="adminform">
						<legend><?php echo Text::_('COM_GAMERCHANDISE_UPDATE_SALES'); ?></legend>
						<?php echo $this->form->renderFieldset('special'); ?>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
	    <?php endif; ?>

		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GAMERCHANDISE_XTRAINFO', true)); ?>
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset name="extrainfo" class="adminform">
						<?php echo $this->form->renderFieldset('extrainfo'); ?>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
	
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GAMERCHANDISE_SYSINFO', true)); ?>
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
					<button type="submit" class="validate btn btn-primary"> <?php echo Text::_('COM_GAMERCHANDISE_ADD_TO_CART'); ?> </button>
				<?php endif; ?>
				<a class="btn" href="<?php echo Route::_('index.php?option=com_gamerchandise&task=saleform.cancel'); ?>" title="<?php echo Text::_('JCANCEL'); ?>">
					<?php echo Text::_('JCANCEL'); ?>
				</a>
			</div>
		</div>

		<input type="hidden" name="option" value="com_gamerchandise"/>
		<input type="hidden" name="task" value="saleform.save"/>
		<?php echo HTMLHelper::_('form.token'); ?>
	</form>
</div>
