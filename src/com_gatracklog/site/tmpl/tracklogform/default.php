<?php
/**
 * @version    4.2.0
 * @subpackage com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GamodalHelper;

// load any assets required
$this->getDocument()->getWebAssetManager()
    ->usePreset('com_gatracklog.gatracklogpreset')
    ->useScript('keepalive')
	->useScript('form.validate');

// Load admin language file
Factory::getApplication()->getLanguage()->load('com_gatracklog', JPATH_ADMINISTRATOR, 'en-GB', true);

$user    = Factory::getApplication()->getIdentity();
$canEdit = GatracklogHelper::canUserEdit($user, $this->item);
$canTran = GamodalHelper::getHTTPQuery(null, 'task', 'tracklogform.cancel', 'id', $this->item->id);
$canURL = 'index.php?'.\http_build_query($canTran, '', '&amp;');

?>

<div class="tracklog-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new \Exception(Text::_('COM_GAGATRACKLOG_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo Text::sprintf('COM_GAGATRACKLOG_EDIT_ITEM_TITLE', $this->item->id); ?></h1>
		<?php else: ?>
			<h1><?php echo Text::_('COM_GAGATRACKLOG_ADD_ITEM_TITLE'); ?></h1>
		<?php endif; ?>

		<form id="form-tracklog"
			  action="<?php echo Route::_('index.php?option=com_gatracklog&task=tracklogform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

			<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GAGATRACKLOG_TAB_TRACKLOG', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="general" class="adminform">
							<legend><?php echo Text::_('COM_GAGATRACKLOG_TAB_TRACKLOG'); ?></legend>
							<?php echo $this->form->renderFieldset('general'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>

			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'loginfo', Text::_('COM_GATRACKLOG_TITLE_LOGINFO', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="loginfo" class="adminform">
							<?php echo $this->form->renderFieldset('loginfo'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GAGATRACKLOG_XTRAINFO', true)); ?>
				<div class="row-fluid">
					<div class="span10 form-horizontal">
						<fieldset name="extrainfo" class="adminform">
							<?php echo $this->form->renderFieldset('extrainfo'); ?>
						</fieldset>
					</div>
				</div>
			<?php echo HTMLHelper::_('uitab.endTab'); ?>
		
			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GAGATRACKLOG_SYSINFO', true)); ?>
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
						<button type="submit" class="validate btn btn-primary">
							<span class="fas fa-check" aria-hidden="true"></span>
							<?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-danger" href="<?php echo Route::_($canURL); ?>" title="<?php echo Text::_('JCANCEL'); ?>">
					   <span class="fas fa-times" aria-hidden="true"></span> <?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gatracklog"/>
			<input type="hidden" name="task" value="tracklogform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
