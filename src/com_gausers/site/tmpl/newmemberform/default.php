<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaauditHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');

//Load admin language file
Factory::getApplication()->getLanguage()->load('com_gausers', JPATH_ADMINISTRATOR);

$newMember = $this->params->get('newMember',0);
$incPartner = $this->params->get('incl_partner',0);

$user       = Factory::getApplication()->getIdentity();
$this->form->setFieldAttribute('name', 'required', 'required');
$this->form->setFieldAttribute('email', 'required', 'required');
$this->form->setFieldAttribute('address1', 'required', 'required');
$this->form->setFieldAttribute('city', 'required', 'required');
$this->form->setFieldAttribute('postal_code', 'required', 'required');
$this->form->setFieldAttribute('phone', 'required', 'required');

// Try to identify IP address
$user_ip = GausersHelper::get_user_ip();

/*
echo "User IP Address is: " . $user_ip;
GausersHelper::gaPrint($ignoreMship);
GausersHelper::gaPrint(Factory::getApplication()->getUserState('com_gausers.test.data'));
*/
?>


<div class="usernew-edit front-end-edit">
    <?php if ($newMember) : ?>
        <h2>Apply for Membership</h2>
    
        <form id="form-gauser" action="<?php echo Route::_('index.php?option=com_gausers&task=newmemberform.newMbrApplication'); ?>"
            method="post" class="form-validate" enctype="multipart/form-data">
    
            <div class="span12 form-horizontal">
    
                <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'details')); ?>
    
    	            <?php /* *********************************** Name ***************************************************/ ?>
    				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_GAUSERS_MEMBER_MSHIP', true)); ?>
        	            <div class="row-fluid">
        	                <div class="span12 form-horizontal">
        	                
            	                <?php echo $this->form->renderField('mship_id'); ?>
            	                <?php echo $this->form->renderField('name'); ?>
            	                <?php if ($incPartner) : ?>
                	                <?php echo $this->form->renderField('partner'); ?>
                                <?php endif; ?>
                                <?php echo $this->form->renderField('email'); ?>
            	                <?php echo $this->form->renderField('address1'); ?>
            	                <?php echo $this->form->renderField('address2'); ?>
            	                <?php echo $this->form->renderField('city'); ?>
            	                <?php echo $this->form->renderField('postal_code'); ?>
            	                <?php echo $this->form->renderField('phone'); ?>
            	                <?php if ($this->captchaEnabled) : ?>
                                    <?php echo $this->form->renderField('captcha'); ?>
                                <?php endif; ?>


        			        </div>
        		        </div>
    	            <?php echo HTMLHelper::_('uitab.endTab'); ?>
    
    	        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    
            </div>
    
            <div class="clearfix"> </div>
    
            <div style="padding-top:30px;">
                <button type="submit" class="validate btn btn-primary"><span><?php echo Text::_('JSUBMIT'); ?></span></button>
                <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gausers&task=newmemberform.cancel'); ?>">
                	<i class="icon-undo"></i> <?php echo Text::_("COM_GAUSERS_CANCEL"); ?>
                </a>
    
                <input type="hidden" name="option" value="com_gausers" />
                <input type="hidden" name="task" value="newmemberform.newMbrApplication" />
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </form>
    <?php endif; ?>
</div>
