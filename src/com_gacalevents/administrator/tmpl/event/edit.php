<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
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
$wa->useScript('keepalive')
	->useScript('form.validate');

if (!$this->state->params->get('formal', 0)) {
    $this->form->setFieldAttribute('formal_event', 'type', 'hidden');
}

?>

<form
	action="<?php echo Route::_('index.php?option=com_gacalevents&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="event-form" class="form-validate form-horizontal">

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
    					<?php echo $this->form->renderField('event_details'); ?>
    				</fieldset>
    			</div>
    		</div>
    	<?php echo HTMLHelper::_('uitab.endTab'); ?>

    	<?php if ($this->state->params->get('charge_event')) : ?>
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

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'sysinfo', Text::_('COM_GACALEVENTS_SYSINFO', true)); ?>
    		<div class="row-fluid">
    			<div class="span10 form-horizontal">
    				<fieldset class="adminform">
    					<?php echo $this->form->renderFieldset('sysinfo'); ?>
    				</fieldset>
    			</div>
    		</div>
    	<?php echo HTMLHelper::_('uitab.endTab'); ?>

    	<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GACALEVENTS_XTRAINFO', true)); ?>
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
