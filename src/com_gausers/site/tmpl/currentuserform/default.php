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
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profileifmr', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profilertry', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profilebrb', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profileb4wdc', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profilebdgs', JPATH_ADMINISTRATOR);
$lang->load('plg_user_profilebecs', JPATH_ADMINISTRATOR);

$this->incl_partner = $this->params->get( 'incl_partner' );
$trg_group = $this->params->get('trg_group',0);
$wwc_group = $this->params->get('wwc_group',0);
$use_clubnumber = $this->params->get('use_clubnumber',0);
$fld_clubnumber = $this->params->get('fld_clubnumber',0);
$this->localProfile = $this->params->get('profile_suffix','');
$this->hideVax = $this->params->get('hide_vax',1);
$this->default_mship = $this->params->get('default_mship',1);

$user       = Factory::getApplication()->getIdentity();
$this->canMembers  = $user->authorise('core.members', 'com_gausers');
$this->canAdmin  = $user->authorise('core.admin', 'com_gausers');
$this->canAdmin = ($this->canMembers || $this->canAdmin);
$this->canCustFld  = $user->authorise('core.edit.value', 'com_users');

$this->canTrg  = $user->authorise('core.trgcerts', 'com_gausers');
$this->user_id  = $user->id;

$this->wwchildren = in_array($wwc_group, $user->groups);

// set up the plugin fields to ignore
$this->ignorArray = GaauditHelper::getProfileFieldsToIgnore('profile'.$this->localProfile);
$this->ignorStdArray = GaauditHelper::getProfileFieldsToIgnore('profile');
$this->useProfArray = GaauditHelper::getProfileFields('profile'.$this->localProfile);

if (!isset($this->item->mship_id) || empty($this->item->mship_id)) { $this->item->mship_id = $this->default_mship; }

$tabActive = $this->localProfile == 'b4wdc' ? 'contact' : 'details';

/*
GausersHelper::gaPrint(Factory::getApplication()->getUserState('com_gausers.test.data'));*/
?>


<div class="usernew-edit front-end-edit">
    <?php if (!empty($this->item->id)): $frmScrn = $this->item->id; ?>
        <h2>Details for <?php echo $this->item->fullname; ?></h2>
    <?php else: $frmScrn = 0;  ?>
        <h2>Create New Membership</h2>
    <?php endif; ?>
    
    <?php 
        if (!$frmScrn) {
            $tabActive = $this->localProfile == 'b4wdc' ? 'contact' : 'details';
        }
    ?>

    <form id="form-gauser" action="<?php echo Route::_('index.php?option=com_gausers&task=currentuserform.save'); ?>"
        method="post" class="form-validate" enctype="multipart/form-data">

        <div class="span12 form-horizontal">

            <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => $tabActive)); ?>

	            <?php /* *********************************** Name ***************************************************/ ?>
				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_GAUSERS_MEMBER_MSHIP', true)); ?>
    	            <div class="row-fluid">
    	                <div class="span12 form-horizontal">
    					<?php if ($this->localProfile == 'b4wdc') : ?>
    						<?php echo LayoutHelper::render('default_nameb4wdc', array('view' => $this), dirname(__FILE__)); ?>
    					<?php elseif ($this->localProfile == 'becs') : ?>
    						<?php echo LayoutHelper::render('default_namebecs', array('view' => $this), dirname(__FILE__)); ?>
    					<?php elseif ($this->localProfile == 'ifmr' || $this->localProfile == 'rtry') : ?>
    						<?php echo LayoutHelper::render('default_nameifmr', array('view' => $this), dirname(__FILE__)); ?>
    					<?php elseif ($this->localProfile == 'brb') : ?>
    						<?php echo LayoutHelper::render('default_namebrb', array('view' => $this), dirname(__FILE__)); ?>
    					<?php elseif ($this->localProfile == 'raf') : ?>
    						<?php echo LayoutHelper::render('default_nameraf', array('view' => $this), dirname(__FILE__)); ?>
    					<?php else : ?>
    						<?php echo LayoutHelper::render('default_name', array('view' => $this), dirname(__FILE__)); ?>
    					<?php endif; ?>
    			        </div>
    		        </div>
	            <?php echo HTMLHelper::_('uitab.endTab'); ?>

	            <?php /* *********************************** Medical ***************************************************/ ?>
				<?php if (!$this->hideVax) : ?>
    				<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'medical', Text::_('COM_GAUSERS_MEMBER_MEDICAL', true)); ?>
    	            <div class="row-fluid">
    	                <div class="span12 form-horizontal">
    						<?php echo LayoutHelper::render('default_medical', array('view' => $this), dirname(__FILE__)); ?>
    			        </div>
    		        </div>
    	            <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php endif; ?>

	            <?php /* *********************************** Bee Keepers ***************************************************/ ?>
	            <?php if ($this->localProfile == 'brb') : ?>
					<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'contbrb', Text::_('COM_GAUSERS_MEMBER_DETAILS', true)); ?>
		            <div class="row-fluid">
		                <div class="span12 form-horizontal">
							<?php echo LayoutHelper::render('default_contbrb', array('view' => $this), dirname(__FILE__)); ?>
				        </div>
			        </div>
		            <?php echo HTMLHelper::_('uitab.endTab'); ?>
                <?php endif; ?>

	            <?php /* *********************************** Food ***************************************************/ ?>
	            <?php if ($this->localProfile == 'food') : ?>
					<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'food', Text::_('COM_GAUSERS_PROFILEFOOD_SLIDER_LABEL', true)); ?>
    		            <div class="row-fluid">
    		                <div class="span12 form-horizontal">
    							<?php echo LayoutHelper::render('default_food', array('view' => $this), dirname(__FILE__)); ?>
    				        </div>
    			        </div>
		            <?php echo HTMLHelper::_('uitab.endTab'); ?>

					<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'contact', Text::_('COM_GAUSERS_MEMBER_CONTACT', true)); ?>
    		            <div class="row-fluid">
    		                <div class="span12 form-horizontal">
    							<?php echo LayoutHelper::render('default_contact', array('view' => $this), dirname(__FILE__)); ?>
    				        </div>
    			        </div>
		            <?php echo HTMLHelper::_('uitab.endTab'); ?>
	            <?php else : ?>
		            <?php /* *********************************** Contact ***************************************************/ ?>
					<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'contact', Text::_('COM_GAUSERS_MEMBER_CONTACT', true)); ?>
    		            <div class="row-fluid">
    		                <div class="span12 form-horizontal">
    						<?php if ($this->localProfile == 'b4wdc') : ?>
    							<?php echo LayoutHelper::render('default_contb4wdc', array('view' => $this), dirname(__FILE__)); ?>
    						<?php elseif ($this->localProfile == 'becs') : ?>
    							<?php echo LayoutHelper::render('default_contbecs', array('view' => $this), dirname(__FILE__)); ?>
    						<?php elseif ($this->localProfile == 'ifmr' || $this->localProfile == 'rtry') : ?>
    							<?php echo LayoutHelper::render('default_contifmr', array('view' => $this), dirname(__FILE__)); ?>
    						<?php else : ?>
    							<?php echo LayoutHelper::render('default_contact', array('view' => $this), dirname(__FILE__)); ?>
    						<?php endif; ?>
    				        </div>
    			        </div>
		            <?php echo HTMLHelper::_('uitab.endTab'); ?>

    	            <?php /* *********************************** DOCs Emergency ***************************************************/ ?>
    	            <?php if ($this->localProfile == 'docs') : ?>
    					<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'emergency', Text::_('COM_GAUSERS_MEMBER_EMERGENCY', true)); ?>
    		            <div class="row-fluid">
    		                <div class="span12 form-horizontal">
    							<?php echo LayoutHelper::render('default_emergency', array('view' => $this), dirname(__FILE__)); ?>
    				        </div>
    			        </div>
    		            <?php echo HTMLHelper::_('uitab.endTab'); ?>
                    <?php endif; ?>

		            <?php /* *********************************** 4WD Others ***************************************************/ ?>
		            <?php if ($this->localProfile == 'b4wdc') : ?>
						<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'vehicle', Text::_('COM_GAUSERS_MEMBER_VEHICLE', true)); ?>
    			            <div class="row-fluid">
    			                <div class="span12 form-horizontal">
    							    <?php echo LayoutHelper::render('default_vehicle', array('view' => $this), dirname(__FILE__)); ?>
    					        </div>
    				        </div>
                        <?php echo HTMLHelper::_('uitab.endTab'); ?>

			            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'training1', Text::_('COM_GAUSERS_MEMBER_TRAINING1', true)); ?>
    			            <div class="row-fluid">
    			                <div class="span12 form-horizontal">
    							    <?php echo LayoutHelper::render('default_training1', array('view' => $this), dirname(__FILE__)); ?>
    					        </div>
    				        </div>
			            <?php echo HTMLHelper::_('uitab.endTab'); ?>

			            <?php if ($this->incl_partner) : ?>
							<?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'training2', Text::_('COM_GAUSERS_MEMBER_TRAINING2', true)); ?>
    				            <div class="row-fluid">
    				                <div class="span12 form-horizontal">
    								    <?php echo LayoutHelper::render('default_training2', array('view' => $this), dirname(__FILE__)); ?>
    						        </div>
    					        </div>
				            <?php echo HTMLHelper::_('uitab.endTab'); ?>
			            <?php endif; ?>

			            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'wwc', Text::_('COM_GAUSERS_MEMBER_WWC', true)); ?>
    			            <div class="row-fluid">
    			                <div class="span12 form-horizontal">
    							    <?php echo LayoutHelper::render('default_wwc', array('view' => $this), dirname(__FILE__)); ?>
    					        </div>
    				        </div>
			            <?php echo HTMLHelper::_('uitab.endTab'); ?>

		            <?php /* *********************************** BRB Keepers ***************************************************/ ?>
		            <?php elseif ($this->localProfile == 'brb') : ?>
			            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'brbkeep', Text::_('COM_GAUSERS_MEMBER_BRBKEEP', true)); ?>
    			            <div class="row-fluid">
    			                <div class="span12 form-horizontal">
    							    <?php echo LayoutHelper::render('default_brbkeep', array('view' => $this), dirname(__FILE__)); ?>
    					        </div>
    				        </div>
			            <?php echo HTMLHelper::_('uitab.endTab'); ?>

			            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'trgbrb1', Text::_('COM_GAUSERS_BRBM_TRAINING_HEADER', true)); ?>
    			            <div class="row-fluid">
    			                <div class="span12 form-horizontal">
    							    <?php echo LayoutHelper::render('default_trgbrb1', array('view' => $this), dirname(__FILE__)); ?>
    					        </div>
    				        </div>
			            <?php echo HTMLHelper::_('uitab.endTab'); ?>

			            <?php if ($this->incl_partner) : ?>
                            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'trgbrb2', Text::_('COM_GAUSERS_BRBP_TRAINING_HEADER', true)); ?>
        			            <div class="row-fluid">
        			                <div class="span12 form-horizontal">
        							    <?php echo LayoutHelper::render('default_trgbrb2', array('view' => $this), dirname(__FILE__)); ?>
        					        </div>
        				        </div>
    			            <?php echo HTMLHelper::_('uitab.endTab'); ?>
			            <?php endif; ?>

                        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'wwc', Text::_('COM_GAUSERS_MEMBER_WWC', true)); ?>
    			            <div class="row-fluid">
    			                <div class="span12 form-horizontal">
    							    <?php echo LayoutHelper::render('default_wwc', array('view' => $this), dirname(__FILE__)); ?>
    					        </div>
    				        </div>
			            <?php echo HTMLHelper::_('uitab.endTab'); ?>
		            <?php /* *********************************** BECs Shed Others ***************************************************/ ?>
		            <?php elseif ($this->localProfile == 'becs') : ?>
			            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'anon', Text::_('COM_GAUSERS_MEMBER_ANON', true)); ?>
    			            <div class="row-fluid">
    			                <div class="span12 form-horizontal">
    							    <?php echo LayoutHelper::render('default_anon', array('view' => $this), dirname(__FILE__)); ?>
    					        </div>
    				        </div>
			            <?php echo HTMLHelper::_('uitab.endTab'); ?>
		            <?php /* *********************************** IFMR Others ***************************************************/ ?>
		            <?php elseif ($this->localProfile == 'ifmr' || $this->localProfile == 'rtry') : ?>
			            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'ifmr', Text::_('COM_GAUSERS_MEMBER_IFMR', true)); ?>
    			            <div class="row-fluid">
    			                <div class="span12 form-horizontal">
    							    <?php echo LayoutHelper::render('default_ifmr', array('view' => $this), dirname(__FILE__)); ?>
    					        </div>
    				        </div>
			            <?php echo HTMLHelper::_('uitab.endTab'); ?>
		            <?php /* *********************************** BDGS ***************************************************/ ?>
		            <?php elseif ($this->localProfile == 'bdgs') : ?>
                        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'wwc', Text::_('COM_GAUSERS_MEMBER_WWC', true)); ?>
    			            <div class="row-fluid">
    			                <div class="span12 form-horizontal">
    							    <?php echo LayoutHelper::render('default_wwc', array('view' => $this), dirname(__FILE__)); ?>
    					        </div>
    				        </div>
			            <?php echo HTMLHelper::_('uitab.endTab'); ?>
		            <?php endif; ?>
                <?php endif; ?>
	        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>

        </div>

        <div class="clearfix"> </div>

        <div style="padding-top:30px;">
            <button type="submit" class="validate btn btn-primary"><span><?php echo Text::_('JSUBMIT'); ?></span></button>
            <a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gausers&task=currentuser.cancel'); ?>">
            	<i class="icon-undo"></i> <?php echo Text::_("COM_GAUSERS_CANCEL"); ?>
            </a>

            <input type="hidden" name="option" value="com_gausers" />
            <input type="hidden" name="task" value="currentuserform.save" />
            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </form>
</div>
