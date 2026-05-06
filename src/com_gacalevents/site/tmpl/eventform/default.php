<?php
/**
 * @version    3.3.1
 * @package    Com_Gacalevents
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
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gacalevents.gacaleventspreset');
$wa->useScript('keepalive')
	->useScript('form.validate');

// Load admin language file
Factory::getApplication()->getLanguage()->load('com_gacalevents', JPATH_ADMINISTRATOR);

$user    = Factory::getApplication()->getIdentity();
$canEdit = GacaleventsHelper::canUserEdit($this->item, $user);

$canLink = GacaleventsHelper::getHTTPQuery(null, 'task', 'eventform.cancel', null, null);
$canURL = 'index.php?'.http_build_query($canLink, '', '&amp;');

$public_rego = $this->params->get('public_rego');
$charge_event = $this->params->get('charge_event', 0);
if (!$public_rego) {
    $this->form->setFieldAttribute('pub_name', 'type', 'hidden');
    $this->form->setFieldAttribute('pub_email', 'type', 'hidden');
    $this->form->setFieldAttribute('pub_phone', 'type', 'hidden');
    $this->form->setFieldAttribute('pub_from', 'type', 'hidden');
    $this->form->setFieldAttribute('pub_name', 'type', 'hidden');
}
if (isset($event_details) && $this->item->formal_event) {
    $this->form->setFieldAttribute('pub_name', 'type', 'hidden');
    $this->form->setFieldAttribute('pub_name', 'default', 0);
    $this->form->setFieldAttribute('formal_event', 'default', 1);
}
if (!$charge_event) {
    $this->form->setFieldAttribute('paid', 'type', 'hidden');
    $this->form->setFieldAttribute('paid_amt', 'type', 'hidden');
    $this->form->setFieldAttribute('att_cat', 'type', 'hidden');
}

?>

<div class="event-edit front-end-edit">
	<?php if (!$canEdit) : ?>
		<h3>
			<?php throw new \Exception(Text::_('COM_GACALEVENTS_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
		</h3>
	<?php else : ?>
		<?php if (!empty($this->item->id)): ?>
			<h1><?php echo Text::sprintf('COM_GACALEVENTS_EDIT_ITEM_TITLE', $this->item->title); ?></h1>
		<?php else: ?>
			<h1><?php echo Text::_('COM_GACALEVENTS_ADD_ITEM_TITLE'); ?></h1>
		<?php endif; ?>

		<form id="form-event"
			  action="<?php echo Route::_('index.php?option=com_gacalevents&task=eventform.save'); ?>"
			  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">

			<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

    			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GACALEVENTS_TITLE_EVENT', true)); ?>
    				<div class="row-fluid">
    					<div class="span10 form-horizontal">
    						<fieldset class="adminform">
    							<?php echo $this->form->renderFieldset('general'); ?>
    						</fieldset>
    					</div>
    				</div>
    			<?php echo HTMLHelper::_('uitab.endTab'); ?>

    			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_GACALEVENTS_EVENT_DETAILS', true)); ?>
    				<div class="row-fluid">
    					<div class="span10 form-horizontal">
    						<fieldset class="adminform">
    							<?php echo $this->form->renderFieldset('details'); ?>
    						</fieldset>
    					</div>
    				</div>
    			<?php echo HTMLHelper::_('uitab.endTab'); ?>

            	<?php if ($charge_event) : ?>
                    <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'costings', Text::_('COM_GACALEVENTS_COSTINGS', true)); ?>
                		<div class="row-fluid">
                			<div class="span10 form-horizontal">
                				<fieldset class="adminform">
                					<?php echo $this->form->renderFieldset('costings'); ?>
                				</fieldset>
                			</div>
                		</div>
                	<?php echo HTMLHelper::_('uitab.endTab'); ?>
            	<?php endif ; ?>

    			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GACALEVENTS_XTRAINFO', true)); ?>
    				<div class="row-fluid">
    					<div class="span10 form-horizontal">
    						<fieldset class="adminform">
    							<?php echo $this->form->renderFieldset('extrainfo'); ?>
    						</fieldset>
    					</div>
    				</div>
    			<?php echo HTMLHelper::_('uitab.endTab'); ?>

    			<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GACALEVENTS_SYSINFO', true)); ?>
    				<div class="row-fluid">
    					<div class="span10 form-horizontal">
    						<fieldset class="adminform">
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
							<span class="fas fa-check" aria-hidden="true"></span> <?php echo Text::_('JSUBMIT'); ?>
						</button>
					<?php endif; ?>
					<a class="btn btn-secondary" href="<?php echo Route::_($canURL); ?>" title="<?php echo Text::_('JCANCEL'); ?>">
					   <i class="fas fa-times"></i> <?php echo Text::_('JCANCEL'); ?>
					</a>
				</div>
			</div>

			<input type="hidden" name="option" value="com_gacalevents"/>
			<input type="hidden" name="task" value="eventform.save"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</form>
	<?php endif; ?>
</div>
