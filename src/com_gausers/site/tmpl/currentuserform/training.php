<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaauditHelper;

HTMLHelper::_('behavior.keepalive');
HTMLHelper::_('behavior.formvalidator');

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$incl_partner = $this->params->get( 'incl_partner' );
$trg_group = $this->params->get('trg_group',0);
$use_clubnumber = $this->params->get('use_clubnumber',0);
$fld_clubnumber = $this->params->get('fld_clubnumber',0);
$profileSuff = $this->params->get('profile_suffix', 'b4wdc');
$this->localProfile = $this->params->get('profile_suffix','b4wdc');

$user       = GausersHelper::getSpecificUser();
$this->user_id  = $user->id;
$this->canMembers  = $user->authorise('core.members', 'com_gausers');
$this->canTrg  = $user->authorise('core.trgcerts', 'com_gausers');
$this->canAdmin  = $user->authorise('core.admin', 'com_gausers');
$this->canAdmin = ($this->canMembers || $this->canAdmin);

if (isset($this->item->m_usi)) { $m_usi = $this->item->m_usi; } else { $m_usi = 'No USI Registered'; }
if (isset($this->item->p_usi)) { $p_usi = $this->item->p_usi; } else { $p_usi = 'No USI Registered'; }
// set up the plugin fields to ignore
$this->ignorArray = GaauditHelper::getProfileFieldsToIgnore('profile'.$this->localProfile);
$this->ignorStdArray = GaauditHelper::getProfileFieldsToIgnore('profile');

?>

<div class="usernew-edit front-end-edit">

    <h2><?php echo Text::sprintf('COM_GAUSERS_TRAINING_DETAILS', $this->item->fullname); ?></h2>

    <form id="form-gauser" action="<?php echo Route::_('index.php?option=com_gausers&task=currentuserform.save'); ?>" 
        method="post" class="form-validate" enctype="multipart/form-data">

        <div class="span12 form-horizontal">
			<?php if ($this->canTrg) : ?>

    	    <?php /* *********************************** Four Wheel Drive Clubs ***********************************/ ?>
			<?php if ($profileSuff == 'b4wdc') : ?>
            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'gausers_trg')); ?>

	            <?php /* *********************************** Member 1 ***************************************************/ ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gausers_trg', Text::_('COM_GAUSERS_MEMBER_TRAINING1', true)); ?>
    	            <div class="row-fluid">
    	                <div class="span12 form-horizontal">
                            <h2><?php echo $this->item->name; ?></h2>
                            <?php echo $this->form->renderFieldset('gausers_trg'); ?>
					    </div>
				    </div>
			    <?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gausers_cert', Text::_('COM_GAUSERS_MEMBER_CERT1', true)); ?>
    	            <div class="row-fluid">
    	                <div class="span12 form-horizontal">
                            <h2>
                                <?php echo $this->item->name; ?> &nbsp; <span class="small"><em>( USI: <?php echo $m_usi; ?> )</em></span>
                            </h2>
                            <input type="hidden" name="MAX_FILE_SIZE" value="250kb" />
                            <?php echo $this->form->renderFieldset('gausers_cert'); ?>
					    </div>
				    </div>
			    <?php echo HTMLHelper::_('uitab.endTab'); ?>

	            <?php if ($incl_partner && $this->item->partner > '') : ?>
                    <?php /* *********************************** Member 2 ***************************************************/ ?>
    				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gausers_trgp', Text::_('COM_GAUSERS_MEMBER_TRAINING2', true)); ?>
        	            <div class="row-fluid">
        	                <div class="span12 form-horizontal">
                                <h2><?php echo $this->item->partner; ?></h2>
                                <?php echo $this->form->renderFieldset('gausers_trgp'); ?>
    					    </div>
    				    </div>
    			    <?php echo HTMLHelper::_('uitab.endTab'); ?>
    				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gausers_certp', Text::_('COM_GAUSERS_MEMBER_CERT2', true)); ?>
        	            <div class="row-fluid">
        	                <div class="span12 form-horizontal">
                                <h2>
                                    <?php echo $this->item->partner; ?> &nbsp; <span class="small"><em>( USI: <?php echo $p_usi; ?> )</em></span>
                                </h2>
                                <input type="hidden" name="MAX_FILE_SIZE" value="250kb" />
                                <?php echo $this->form->renderFieldset('gausers_certp'); ?>

    					    </div>
    				    </div>
    			    <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php endif; ?>

                <?php /* *********************************** System Info ***************************************************/ ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GAUSERS_XTRAINFO', true)); ?>
    	            <div class="row-fluid">
    	                <div class="span12 form-horizontal">
                            <?php echo $this->form->renderFieldset('extrainfo'); ?>
                            <?php
                                $this->item->memtype= isset($this->item->memtype) ? $this->item->memtype : '';
                                $this->item->address2= isset($this->item->address2) ? $this->item->address2 : '';
                                $this->item->second_phone= isset($this->item->second_phone) ? $this->item->second_phone : '';
                                $this->item->second_email= isset($this->item->second_email) ? $this->item->second_email : '';
                            ?>
            				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
            				<input type="hidden" name="jform[name]" value="<?php echo $this->item->name; ?>" />
            				<input type="hidden" name="jform[email]" value="<?php echo $this->item->email; ?>" />
            				<input type="hidden" name="jform[address1]" value="<?php echo $this->item->address1; ?>" />
            				<input type="hidden" name="jform[address2]" value="<?php echo $this->item->address2; ?>" />
            				<input type="hidden" name="jform[city]" value="<?php echo $this->item->city; ?>" />
            				<input type="hidden" name="jform[postal_code]" value="<?php echo $this->item->postal_code; ?>" />
            				<input type="hidden" name="jform[use_post]" value="<?php echo $this->item->use_post; ?>" />
            				<input type="hidden" name="jform[postal_address1]" value="<?php echo $this->item->postal_address1; ?>" />
            				<input type="hidden" name="jform[postal_address2]" value="<?php echo $this->item->postal_address2; ?>" />
            				<input type="hidden" name="jform[postal_city]" value="<?php echo $this->item->postal_city; ?>" />
            				<input type="hidden" name="jform[postal_post_code]" value="<?php echo $this->item->postal_post_code; ?>" />
            				<input type="hidden" name="jform[phone]" value="<?php echo $this->item->phone; ?>" />
            
            				<input type="hidden" name="jform[memtype]" value="<?php echo $this->item->memtype; ?>" />
            				<input type="hidden" name="jform[fwdvic_no]" value="<?php echo $this->item->fwdvic_no; ?>" />
            				<input type="hidden" name="jform[m_img]" value="<?php echo $this->item->m_img; ?>" />
            				<input type="hidden" name="jform[kids]" value="<?php echo $this->item->kids; ?>" />
            				<input type="hidden" name="jform[altphone]" value="<?php echo $this->item->altphone; ?>" />
            				<input type="hidden" name="jform[altemail]" value="<?php echo $this->item->altemail; ?>" />
            				<input type="hidden" name="jform[2nd_phone]" value="<?php echo $this->item->second_phone; ?>" />
            				<input type="hidden" name="jform[2nd_email]" value="<?php echo $this->item->second_email; ?>" />
            				<input type="hidden" name="jform[inc_altemail]" value="<?php echo $this->item->inc_altemail; ?>" />
            				<input type="hidden" name="jform[emailnews]" value="<?php echo $this->item->emailnews; ?>" />
            				<input type="hidden" name="jform[vehicle_make]" value="<?php echo $this->item->vehicle_make; ?>" />
            				<input type="hidden" name="jform[vehicle_model]" value="<?php echo $this->item->vehicle_model; ?>" />
            				<input type="hidden" name="jform[vehicle_year]" value="<?php echo $this->item->vehicle_year; ?>" />
            				<input type="hidden" name="jform[vehicle_rego]" value="<?php echo $this->item->vehicle_rego; ?>" />
            				<input type="hidden" name="jform[vehicle_trans]" value="<?php echo $this->item->vehicle_trans; ?>" />
            				<input type="hidden" name="jform[vehicle_fuel]" value="<?php echo $this->item->vehicle_fuel; ?>" />
            				<input type="hidden" name="jform[camp_caravan]" value="<?php echo $this->item->camp_caravan; ?>" />
            				<input type="hidden" name="jform[camp_other]" value="<?php echo $this->item->camp_other; ?>" />
            				<input type="hidden" name="jform[hf_net]" value="<?php echo $this->item->hf_net; ?>" />
            				<input type="hidden" name="jform[hf_callsign]" value="<?php echo $this->item->hf_callsign; ?>" />
            				<input type="hidden" name="jform[hf_selcall]" value="<?php echo $this->item->hf_selcall; ?>" />
            				<input type="hidden" name="jform[hf2_net]" value="<?php echo $this->item->hf2_net; ?>" />
            				<input type="hidden" name="jform[hf2_callsign]" value="<?php echo $this->item->hf2_callsign; ?>" />
            				<input type="hidden" name="jform[hf2_selcall]" value="<?php echo $this->item->hf2_selcall; ?>" />
					    </div>
				    </div>
			    <?php echo HTMLHelper::_('uitab.endTab'); ?>

            <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    	    <?php /* *********************************** Beekeepers Clubs ***********************************/ ?>
			<?php elseif ($profileSuff == 'brb') : ?>
                <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'gausers_brb')); ?>

    	            <?php /* *********************************** Member 1 ***************************************************/ ?>
    				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gausers_brb', Text::_('COM_GAUSERS_BRBM_TRAINING_HEADER', true)); ?>
        	            <div class="row-fluid">
        	                <div class="span11 form-horizontal">
                                <h2><?php echo $this->item->name; ?></h2>
							    <?php echo LayoutHelper::render('default_trgbrb1', array('view' => $this), dirname(__FILE__)); ?>
    					    </div>
    				    </div>
    			    <?php echo HTMLHelper::_('uitab.endTab'); ?>

                    <?php /* *********************************** Member 2 ***************************************************/ ?>
    	            <?php if ($incl_partner && $this->item->partner > '') : ?>
        				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gausers_brbp', Text::_('COM_GAUSERS_BRBP_TRAINING_HEADER', true)); ?>
            	            <div class="row-fluid">
            	                <div class="span11 form-horizontal">
                                    <h2><?php echo $this->item->partner; ?></h2>
    							    <?php echo LayoutHelper::render('default_trgbrb2', array('view' => $this), dirname(__FILE__)); ?>
        					    </div>
        				    </div>
        			    <?php echo HTMLHelper::_('uitab.endTab'); ?>
                    <?php endif; ?>
                    <?php /* *********************************** System Info ***************************************************/ ?>
    				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'extrainfo', Text::_('COM_GAUSERS_XTRAINFO', true)); ?>
        	            <div class="row-fluid">
        	                <div class="span12 form-horizontal">
                                <?php echo $this->form->renderFieldset('extrainfo'); ?>
                				<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
                				<input type="hidden" name="jform[name]" value="<?php echo $this->item->name; ?>" />
                				<input type="hidden" name="jform[email]" value="<?php echo $this->item->email; ?>" />
                				<input type="hidden" name="jform[address1]" value="<?php echo $this->item->address1; ?>" />
                				<input type="hidden" name="jform[address2]" value="<?php echo $this->item->address2; ?>" />
                				<input type="hidden" name="jform[city]" value="<?php echo $this->item->city; ?>" />
                				<input type="hidden" name="jform[postal_code]" value="<?php echo $this->item->postal_code; ?>" />
                				<input type="hidden" name="jform[use_post]" value="<?php echo $this->item->use_post; ?>" />
                				<input type="hidden" name="jform[postal_address1]" value="<?php echo $this->item->postal_address1; ?>" />
                				<input type="hidden" name="jform[postal_address2]" value="<?php echo $this->item->postal_address2; ?>" />
                				<input type="hidden" name="jform[postal_city]" value="<?php echo $this->item->postal_city; ?>" />
                				<input type="hidden" name="jform[postal_post_code]" value="<?php echo $this->item->postal_post_code; ?>" />
                				<input type="hidden" name="jform[phone]" value="<?php echo $this->item->phone; ?>" />
                				<input type="hidden" name="jform[memtype]" value="<?php echo $this->item->memtype; ?>" />
                				<input type="hidden" name="jform[altphone]" value="<?php echo $this->item->altphone; ?>" />
                				<input type="hidden" name="jform[altemail]" value="<?php echo $this->item->altemail; ?>" />
                				<input type="hidden" name="jform[inc_altemail]" value="<?php echo $this->item->inc_altemail; ?>" />
                				<input type="hidden" name="jform[emailnews]" value="<?php echo $this->item->emailnews; ?>" />
    					    </div>
    				    </div>
    			    <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

    	    <?php /* *********************************** Others ***********************************/ ?>
			<?php else : ?>
                <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'gausers_trg')); ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gausers_trg', Text::_('COM_GAUSERS_MEMBER_TRAINING1', true)); ?>
    	            <div class="row-fluid">
    	                <div class="span12 form-horizontal">
                            <h2><?php echo $this->item->name; ?></h2>
                            <?php echo $this->form->renderField('m_usi'); ?>
                            <?php echo $this->form->renderField('trg_faid'); ?>
                            <?php echo $this->form->renderField('trg_faidc'); ?>
                            <?php echo $this->form->renderField('trg_food'); ?>
                            <?php echo $this->form->renderField('trg_foodc'); ?>
					    </div>
				    </div>
			    <?php echo HTMLHelper::_('uitab.endTab'); ?>

				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'gausers_wwc', Text::_('COM_GAUSERS_M_WWC_HEADER', true)); ?>
    	            <div class="row-fluid">
    	                <div class="span12 form-horizontal">
                            <h2><?php echo $this->item->name; ?></h2>
                            <?php echo $this->form->renderFieldset('gausers_wwc'); ?>
					    </div>
				    </div>
			    <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			<?php endif; ?>

			<?php else : ?>
				<h3 class="center">You are not authorised to update training details.</h3>
			<?php endif; ?>

        </div>

        <div class="clearfix"> </div>

        <div style="padding-top:30px;">
            <?php if ($this->canTrg) : ?>
				<button type="submit" class="validate btn btn-primary"><span><?php echo Text::_('JSUBMIT'); ?></span></button>
			<?php endif; ?>
            <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gausers&task=currentuser.cancel'); ?>">
            	<i class="icon-undo"></i> <?php echo Text::_("COM_GAUSERS_CANCEL"); ?>
            </a>

            <input type="hidden" name="option" value="com_gausers" />
            <input type="hidden" name="task" value="currentuserform.save" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </form>
</div>
