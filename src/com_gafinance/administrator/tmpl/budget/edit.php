<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
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

$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_gafinance');
$wa->useScript('keepalive')
	->useScript('form.validate');

?>
<form
	action="<?php echo Route::_('index.php?option=com_gafinance&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="budget-form" class="form-validate form-horizontal">

	
	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAFINANCE_BUDGET_DETAILS', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="general" class="adminform">
					<legend><?php echo Text::_('COM_GAFINANCE_BUDGET_DETAILS'); ?></legend>
					<?php echo $this->form->renderFieldset('general'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GAFINANCE_XTRAINFO', true)); ?>
		<div class="row-fluid">
			<div class="span10 form-horizontal">
				<fieldset name="extrainfo" class="adminform">
					<?php echo $this->form->renderFieldset('extrainfo'); ?>
				</fieldset>
			</div>
		</div>
	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GAFINANCE_SYSINFO', true)); ?>
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
