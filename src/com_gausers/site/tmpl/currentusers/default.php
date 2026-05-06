<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\User\User;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\User\UserFactoryInterface;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaauditHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;

// load any assets required
$wa = $this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$user       = GausersHelper::getSpecificUser();
$this->canMembers = $user->authorise('core.members','com_gausers');
$this->canCreate  = $user->authorise('core.create', 'com_gausers');
$this->canUpload = $user->authorise('core.attupload','com_gausers');
$this->user = $user;

$listOrder  = $this->state->get('list.ordering', 'a.name');
$listDirn   = $this->state->get('list.direction', 'asc');

$this->viewType = $this->params->get('view_type', 0);
$extract_members = $this->params->get('extract_members',false);
$trg_group = $this->params->get('trg_group',0);
$extract_number = $this->params->get('extract_number',0);
$extract_date = substr($this->params->get('extract_date') ?? '',0,10);
$allow_mbrdir = $this->params->get('allow_mbrdir',0);
$welcome_note = $this->params->get('welcome_note',0);
$mbr_compare  = $this->params->get( 'mbr_compare', 0 );
$send_mbrdir  = $this->params->get( 'send_mbrdir', 0 );
$mdir_file = 'images/members/MembersDirectory.pdf';
$allow_mbrlist = $this->params->get('allow_mbrlist',0);
$mlist_file = 'images/members/MembersList.pdf';
$addrlist = $this->params->get('addrlist',0);
$alist_file = 'images/members/AddressList.pdf';

$incl_years = $this->params->get('incl_years',0);
$indiv_inv = $this->params->get('indiv_inv',0);
$localProfile = $this->params->get('profile_suffix');
$this->localProf = $this->params->get('profile_suffix');
$this->profGroup = $this->params->get('profile_group');
$incl_partner  = $this->params->get( 'incl_partner' );
$mship_single  = $this->params->get( 'mship_single' );
$use_barcodes  = $this->params->get( 'use_barcodes', 0 );
$hide_vax  = $this->params->get( 'hide_vax', 1 );
$vax_field1  = $this->params->get( 'vax_field1', 0 );
$exempt_field1  = $this->params->get( 'exempt_field1', 0 );
$vax_field2  = $this->params->get( 'vax_field2', 0 );
$exempt_field2  = $this->params->get( 'exempt_field2', 0 );
$mchimplist  = $this->params->get( 'mchimplist', 0 );

$this->ignorArray = GaauditHelper::getProfileFieldsToIgnore('profile'.$localProfile);
$this->ignorStdArray = GaauditHelper::getProfileFieldsToIgnore('profile');

// setup new record button
$nuLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuserform.edit', 'id', 0);
$nuURL = 'index.php?'.http_build_query($nuLink, '', '&amp;');
$exLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.genextract', Session::getFormToken(), 1);
$exURL = 'index.php?'.http_build_query($exLink, '', '&amp;');
$listLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.genMembersList', Session::getFormToken(), 1);
$listURL = 'index.php?'.http_build_query($listLink, '', '&amp;');
$adlistLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.genAddressList', Session::getFormToken(), 1);
$adlistURL = 'index.php?'.http_build_query($adlistLink, '', '&amp;');
$mchimpLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.genMailChimpList', Session::getFormToken(), 1);
$mchimpURL = 'index.php?'.http_build_query($mchimpLink, '', '&amp;');
$mDirLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.genMembersDirectory', Session::getFormToken(), 1);
$mDirURL = 'index.php?'.http_build_query($mDirLink, '', '&amp;');

//$exLink = GausersHelper::getHTTPQuery($exLink, null, null, 'id', 0);

/*
echo '<pre>Test<br />';
print_r($this->viewType);
echo '</pre>';
print_r(Factory::getApplication()->getUserState('com_gausers.test.data'));
Factory::getApplication()->setUserState('com_gausers.test.data', $u);
*/
?>

<h2><?php echo Text::_('COM_GAUSERS_CURRENTUSERS_LIST_TITLE'); ?></h2>
<p><?php echo Text::_('COM_GAUSERS_CURRENTUSERS_LIST_MESSAGE'); ?></p>

<form action="<?php echo Route::_('index.php?option=com_gausers&view=currentusers'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="span12">

        <?php echo LayoutHelper::render('default_filter', array('view' => $this), dirname(__FILE__)); ?>
    
    	<div class="clearfix"> </div>
    
        <?php if ($this->viewType == 1) : ?>
            <?php echo LayoutHelper::render('default_training', array('view' => $this), dirname(__FILE__)); ?>
        <?php else : ?>
            <?php echo LayoutHelper::render('default_contact', array('view' => $this), dirname(__FILE__)); ?>
        <?php endif; ?>
    
        <div class="clearfix"> </div>
        <?php if ($localProfile == 'b4wdc') : ?>
    		<p><i class="fas fa-exclamation-triangle"></i> <span style="color:red;">means that the 4WDVic reference number is missing from profile.</span></p>
        <?php endif; ?>
    
        <div class="pagination">
            <p class="counter"><?php echo $this->pagination->getPagesCounter(); ?></p>
            <?php echo $this->pagination->getPagesLinks(); ?>
        </div>
        <div style="margin: 0 auto; text-align: center;">
            <span class="small center">Current Membership Count: <?php echo $this->pagination->total; ?></span>
        </div>

    	<?php if ($this->canMembers) : ?>
    		<a href="<?php echo Route::_($nuURL); ?>" class="btn btn-success">
    		   <i class="icon-plus"></i> <?php echo Text::_('COM_GAUSERS_ADD_NEW_USER'); ?>
    		</a>
    	<?php endif; ?>
    	<?php if ($this->canUpload && $mbr_compare) : ?>
    		<a href="<?php echo Route::_('index.php?option=com_gausers&task=currentuserform&layout=memload&id=0', false, 0); ?>"
    			class="btn btn-primary">
    		   <i class="icon-shuffle" title="<?php echo Text::_('COM_GAUSERS_VERIFY_USER_DATA'); ?>"></i>
    		</a>
    	<?php endif; ?>
    
    	<?php if ($this->canMembers) : ?>
    	    <?php if ($extract_members): ?>
    	        <a href="<?php echo Route::_($exURL); ?>" class="btn btn-warning">
                    <i class="icon-mail-2" title="<?php echo Text::_('COM_GAUSERS_EXTRACT_DESC'); ?>"></i>
    	        </a> &nbsp; <span class="small" style="padding-left:20px;">Last extract: <?php echo $extract_date.' ('.$extract_number.')'; ?></span>
    	    <?php endif; ?>
        <?php endif; ?>
    	<?php if ($allow_mbrdir): ?>
    		<a href="<?php echo Route::_($mDirURL); ?>"
    			class="btn btn-success pull-right"><i class="fas fa-file-pdf" title="<?php echo Text::_('COM_GAUSERS_MBRDIR_DESC'); ?>"></i>
    		</a>
    		<?php if (!$send_mbrdir && \file_exists($mdir_file)): ?>
    			<a href="<?php echo $mdir_file; ?>" target="_blank" alt="" class="btn btn-info pull-right">
    				<i class="icon-search" title="<?php echo Text::_('COM_GAUSERS_MBRDIR_REVIEW_DESC'); ?>"></i>
    			</a> &nbsp; &nbsp;
    		<?php endif; ?>
    	<?php endif; ?>
    
    	<?php if ($allow_mbrlist): ?>
    		<a href="<?php echo Route::_($listURL); ?>" class="btn btn-success pull-right">
                <i class="fas fa-file-pdf" title="<?php echo Text::_('COM_GAUSERS_MBRLIST_DESC'); ?>"></i>
    		</a>
    		<?php if (\file_exists($mlist_file)): ?>
    			<a href="<?php echo $mlist_file; ?>" target="_blank" alt="" class="btn btn-info pull-right">
    				<i class="icon-search" title="<?php echo Text::_('COM_GAUSERS_MBRLIST_REVIEW_DESC'); ?>"></i>
    			</a> &nbsp; &nbsp;
    		<?php endif; ?>
    	<?php endif; ?>
    
    	<?php if ($addrlist): ?>
    		<a href="<?php echo Route::_($adlistURL); ?>" class="btn btn-success pull-right">
                <i class="fas fa-file-pdf" title="<?php echo Text::_('COM_GAUSERS_ADDRESSLIST_DESC'); ?>"></i>
    		</a>
    		<?php if (\file_exists($alist_file)): ?>
    			<a href="<?php echo $alist_file; ?>" target="_blank" alt="" class="btn btn-info pull-right">
    				<i class="icon-search" title="<?php echo Text::_('COM_GAUSERS_ADDLIST_REVIEW_DESC'); ?>"></i>
    			</a> &nbsp; &nbsp;
    		<?php endif; ?>
    	<?php endif; ?>
    
    	<?php if ($mchimplist): ?>
    		<a href="<?php echo Route::_($mchimpURL); ?>" class="pull-right">
                <i class="fab fa-mailchimp" title="<?php echo Text::_('COM_GAUSERS_MAILCHIMP_SETTINGS_DESC'); ?>"></i>
    		</a>
    	<?php endif; ?>
    </div>
</form>
<?php if($this->canMembers) : ?>
	<script type="text/javascript">

		jQuery(document).ready(function () {
			jQuery('.extract-button').click(extractItem);
		});

		function extractItem() {
	
			if (!confirm("<?php echo Text::_('COM_GAUSERS_EXTRACT_DESC'); ?>")) {
				return false;
			}
		}
	</script>
<?php endif; ?>
