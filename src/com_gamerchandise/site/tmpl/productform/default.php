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
use \Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

// load any assets required
$wa = $this->document->getWebAssetManager();
$wa->usePreset('com_gamerchandise.gamerchandisepreset');
$wa->useScript('keepalive')
	->useScript('form.validate');


// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gamerchandise', JPATH_ADMINISTRATOR);

?>

<div class="product-edit front-end-edit">
	<h2><?php echo Text::_('COM_GAMERCHANDISE_TITLE_FORM_VIEW_PRODUCT'); ?></h2>

	<form id="form-product"
		  action="<?php echo Route::_('index.php?option=com_gamerchandise&task=product.save'); ?>"
		  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

		<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
	
		<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAMERCHANDISE_TITLE_PRODUCT', true)); ?>
			<div class="row-fluid">
				<div class="span10 form-horizontal">
					<fieldset name="general" class="adminform">
						<legend><?php echo Text::_('COM_GAMERCHANDISE_TITLE_PRODUCT'); ?></legend>
						<?php echo $this->form->renderFieldset('general'); ?>
					</fieldset>
				</div>
			</div>
		<?php echo HTMLHelper::_('uitab.endTab'); ?>
	
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

		<?php $this->ignore_fieldsets = array('general', 'extrainfo', 'sysinfo'); ?>
		<?php echo LayoutHelper::render('joomla.edit.params', $this); ?>

		<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

		<div class="control-group">
			<div class="controls">

				<?php if ($this->canSave): ?>
					<button type="submit" class="validate btn btn-primary">
						<span class="fas fa-check" aria-hidden="true"></span>
						<?php echo Text::_('JSUBMIT'); ?>
					</button>
				<?php endif; ?>
				<a class="btn btn-secondary"
				   href="<?php echo Route::_('index.php?option=com_gamerchandise&task=productform.cancel'); ?>"
				   title="<?php echo Text::_('JCANCEL'); ?>">
				   <span class="fas fa-times" aria-hidden="true"></span>
					<?php echo Text::_('JCANCEL'); ?>
				</a>
			</div>
		</div>

		<input type="hidden" name="option" value="com_gamerchandise"/>
		<input type="hidden" name="task" value="productform.save"/>
		<?php echo HTMLHelper::_('form.token'); ?>

	</form>
</div>
