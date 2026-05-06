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
use Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');

$public_rego = $this->state->params->get('public_rego');
$charge_event = $this->state->params->get('charge_event', 0);
$event_details = GacaleventsHelper::getEvent($this->item->event);
if (!$public_rego) {
    $this->form->setFieldAttribute('pub_name', 'type', 'hidden');
    $this->form->setFieldAttribute('pub_email', 'type', 'hidden');
    $this->form->setFieldAttribute('pub_phone', 'type', 'hidden');
    $this->form->setFieldAttribute('pub_from', 'type', 'hidden');
    $this->form->setFieldAttribute('pub_name', 'type', 'hidden');
}
if (isset($event_details) && $event_details->formal_event) {
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

<form
	action="<?php echo Route::_('index.php?option=com_gacalevents&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="attendee-form" class="form-validate form-horizontal">

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'general')); ?>

    	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_GACALEVENTS_TAB_ATTENDEE', true)); ?>
    		<div class="row-fluid">
    			<div class="span10 form-horizontal">
    				<fieldset class="adminform">
    					<?php echo $this->form->renderFieldset('general'); ?>
    					<?php echo $this->form->renderFieldset('publicinfo'); ?>
    				</fieldset>
    			</div>
    		</div>
    	<?php echo HTMLHelper::_('uitab.endTab'); ?>
    
    	<?php if (isset($event_details) && $event_details->formal_event) : ?>
        	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'formalinfo', Text::_('COM_GAEVENTS_TITLE_FORMAL', true)); ?>
        		<div class="row-fluid">
        			<div class="span10 form-horizontal">
        				<fieldset class="adminform">
        					<?php echo $this->form->renderFieldset('formalinfo'); ?>
        				</fieldset>
        			</div>
        		</div>
        	<?php echo HTMLHelper::_('uitab.endTab'); ?>
    	<?php endif; ?>
    
    	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GACALEVENTS_EVENT_SYSINFO', true)); ?>
    		<div class="row-fluid">
    			<div class="span10 form-horizontal">
    				<fieldset class="adminform">
    					<?php echo $this->form->renderFieldset('extrainfo'); ?>
    
    					<?php if ($this->state->params->get('save_history', 1)) : ?>
    						<?php echo $this->form->renderField('version_note'); ?>
    					<?php endif; ?>
    				</fieldset>
    			</div>
    		</div>
    	<?php echo HTMLHelper::_('uitab.endTab'); ?>

	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo HTMLHelper::_('form.token'); ?>

</form>
