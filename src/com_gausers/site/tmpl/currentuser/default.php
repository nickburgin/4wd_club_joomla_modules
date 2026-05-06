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
use \Joomla\CMS\Date\Date;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Layout\LayoutHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;

// load any assets required
$this->document->getWebAssetManager()
    ->usePreset('com_gausers.gauserspreset');

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$user       = GausersHelper::getSpecificUser();
$canTrg = $user->authorise('core.trgcerts', 'com_gausers');
$canEdit = $user->authorise('core.edit', 'com_gausers');
if (!$canEdit && $user->authorise('core.edit.own', 'com_gausers')) {
	$canEdit = $user->id == $this->item->id;
}
$canMembers = $user->authorise('core.members','com_gausers');
$filePath = $this->params->get('invoice_loc', 'images/members/invoices');
$profileSuff = $this->params->get('profile_suffix', 'b4wdc');
$locProf = 'profile'.$profileSuff;
$mshipCalFin = $this->params->get('mship_period', 1);  // 1 = Financial, 2 = Calendar
$incPartner = $this->params->get('incl_partner', 0);  

$parentID = isset($this->item->profile->$locProf['fwdvic_no']) ? $this->item->profile->$locProf['fwdvic_no'] : 0;
$parentAssoc = $parentID ? ' ('.$parentID.')' : '';

// setup password button
$formLink = GausersHelper::getHTTPQuery(null, 'view', 'currentuserform', 'id', $this->item->id);
$formLink = GausersHelper::getHTTPQuery($formLink, null, null, 'tmpl', 'component');
$updPWLink = GausersHelper::getHTTPQuery($formLink, null, null, 'layout', 'modalpwupd');
$pwmodparams = array( 'url'        => 'index.php?'.http_build_query($updPWLink, '', '&amp;'),
		        'title'      => Text::_("COM_GAUSERS_PWUPD_DESC"), 'closeButton'=> true,
		        'modalWidth' => 60, 'bodyHeight' => 25, 'backdrop'   => 'static' );
$pwmodname = 'modal-myPWModal'.$this->item->id;
$pwhtml = '<a class="btn btn-primary pull-right" href="#'.$pwmodname.'" data-bs-toggle="modal">';
$pwhtml .= '<i class="fas fa-user-shield" title="'.Text::_('COM_GAUSERS_PWUPD_DESC').'"></i> '.Text::_('COM_GAUSERS_PWUPD_LBL').'</a>';

// action modal form
$actLink = GausersHelper::getHTTPQuery($formLink, null, null, 'layout', 'modalact');
$actmodparams = array( 'url'        => 'index.php?'.http_build_query($actLink, '', '&amp;'),
		        'title'      => Text::_("COM_GAUSERS_ACT_LBL").' for - '.$this->item->fullname, 'closeButton'=> true,
		        'modalWidth' => 60, 'bodyHeight' => 30, 'backdrop'   => 'static' );
$actmodname = 'modal-myActModal'.$this->item->id;
$acthtml = '<a class="btn btn-info" href="#'.$actmodname.'" data-bs-toggle="modal">';
$acthtml .= '<i class="fas fa-exclamation" title="'.Text::_('COM_GAUSERS_ACT_DESC').'"></i> '.Text::_('COM_GAUSERS_ACT_LBL').'</a>';

// set up dates
$regDate = !empty($this->item->registerDate) ? HtmlHelper::date($this->item->registerDate, Text::_('COM_GAUSERS_DISPLAY_DATE')) : '';
$visDate = !empty($this->item->lastvisitDate) ? HtmlHelper::date($this->item->lastvisitDate, Text::_('COM_GAUSERS_DISPLAY_DATE')) : 'Never';
$cntr = 0; // counter for invoices

// setup address details
$contact_details = '';
if ($this->params->get('disp_address', 1)) {
    if (isset($this->item->profile->$locProf['use_post']) && $this->item->profile->$locProf['use_post']) {
        if (!$this->params->get('suburb_only', 0)) {
            $contact_details .= $this->item->profile->$locProf['postal_address1'].'<br />';
        }
        $contact_details .= $this->item->profile->$locProf['postal_city'].', '.$this->item->profile->$locProf['postal_post_code'].'<br />';
        $contact_details .= $this->item->profile->profile['phone'];
    } else {
        if (!$this->params->get('suburb_only', 0)) {
            $contact_details .= $this->item->profile->profile['address1'].'<br />';
        }
        $contact_details .= $this->item->profile->profile['city'].', '.$this->item->profile->profile['postal_code'].'<br />';
        $contact_details .= $this->item->profile->profile['phone'];
    }
}
/*
echo '<pre>Test<br />';
print_r($up);
echo '</pre>';
print_r(JPATH_SITE.'images/members/invoices/Invoice000459.pdf');
print_r(Factory::getApplication()->getUserState('com_gausers.test.data'));
print_r(Factory::getApplication()->getUserState('com_gausers.test.data'));
print_r($expDate);
print_r(Factory::getApplication()->getUserState('com_gausers.test.data'));
*/
?>

<div class="item_fields">
    <h2>Details for <?php echo $this->item->fullname; ?></h2>
	<table class="table">

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_CURRENTUSER_REGISTEREDDATE'); ?></th>
			<td>
                <?php echo $regDate ?><br />
                <?php echo '<span style="font-size:0.7em;">Last Login: '.$visDate.'</span>'; ?>
            </td>
			<td> </td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_CURRENTUSER_ID'); ?></th>
			<td><?php echo $this->item->id . $parentAssoc; ?></td>
			<td> </td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_CURRENTUSER_MEMTYPE'); ?></th>
			<td>
            <?php echo $mshipTitle = $this->item->invoices ? $this->item->invoices[0]->title : 'No Invoices'; ?>
            </td>
			<td> </td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_GAUSER_EMAIL'); ?></th>
			<td><?php echo $this->item->email; ?></td>
			<td> </td>
		</tr>

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_GAUSER_NAME'); ?></th>
			<td><?php echo $this->item->fullname; ?></td>
			<td><?php
                    if (isset($this->item->profile->$locProf['m_img']) && $this->item->profile->$locProf['m_img'] > '') {
                        echo '<img style="max-width:120px;" src="'.$this->item->profile->$locProf['m_img'].'" alt="Member" title="Member Image" />';
                    }
                    if (isset($this->item->profile->$locProf['p_img']) && $this->item->profile->$locProf['p_img'] > '') {
                        echo '<img style="max-width:120px;" src="'.$this->item->profile->$locProf['p_img'].'" alt="Partner" title="Partner Image" />';
                    }
                ?>
            </td>
		</tr>

		<?php if ($profileSuff == 'docs') : ?>
            <tr>
    			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_CERT_NAME'); ?></th>
    			<td><?php echo $this->item->profile->$locProf['cert_name']; ?></td>
    			<td> </td>
    		</tr>
            <tr>
    			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_PRIMARY_MBR'); ?></th>
    			<td><?php echo $this->item->profile->$locProf['prime_mbr'] == 1 ? 'Yes' : 'No'; ?></td>
    			<td> </td>
    		</tr>
		<?php endif; ?>

		<?php if ($profileSuff == 'brb') : ?>
            <tr>
    			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_GAUSER_LOCGRP'); ?></th>
    			<td><?php echo $this->item->profile->$locProf['locgrp']; ?></td>
    			<td> </td>
    		</tr>
            <tr>
    			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_GAUSER_REGONO'); ?></th>
    			<td><?php echo $this->item->profile->$locProf['regono']; ?></td>
    			<td> </td>
    		</tr>
		<?php endif; ?>

		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_GAUSER_ADDRESS'); ?></th>
			<td><?php echo $contact_details; ?></td>
			<td> </td>
		</tr>
        <tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_GAUSER_WWK'); ?></th>
			<td>
                <?php if (!empty($this->item->profile->$locProf['wwk_reg'])) : ?>
                    <?php echo $this->item->profile->$locProf['wwk_reg']; ?>
                    <?php echo !empty($this->item->profile->$locProf['wwk_exp']) ? ' (exp: '.HTMLHelper::_('date', $this->item->profile->$locProf['wwk_exp'], Text::_('COM_GAUSERS_DISPLAY_DATE')).')' : ''; ?>
                <?php endif; ?>
                <?php if ($incPartner && !empty($this->item->profile->$locProf['wwk_regp'])) : ?>
                    <?php echo '<br />'.$this->item->profile->$locProf['wwk_regp']; ?>
                    <?php echo !empty($this->item->profile->$locProf['wwk_expp']) ? ' (exp: '.HTMLHelper::_('date', $this->item->profile->$locProf['wwk_expp'], Text::_('COM_GAUSERS_DISPLAY_DATE')).')' : ''; ?>
                <?php endif; ?>
            </td>
			<td> </td>
		</tr>

		<?php if ($profileSuff == 'brb') : ?>
		<tr>
			<th><?php echo Text::_('COM_GAUSERS_BRB_TRAINING'); ?></th>
			<td colspan="2">
                <?php
                    if (isset($this->item->profile->$locProf['trg_b2bc']) && $this->item->profile->$locProf['trg_b2bc'] > '') {
                        $b2bImg = $this->item->profile->$locProf['trg_b2bc'];
                        $b2bDate = $this->item->profile->$locProf['trg_b2b'];
                        $b2bLink = '<a href="'.$b2bImg.'" alt="" target="_blank">';
                        if (substr($b2bImg, -3) == 'pdf') { $b2bImg = 'media/com_gausers/images/pdf.png'; }
                        $b2bLink .= '<img style="max-width:120px;" src="'.$b2bImg.'" alt="" title="Certificate Dated: '.$b2bDate.'" /></a> &nbsp;';
                        echo $b2bLink;
                    }
                    if (isset($this->item->profile->$locProf['trg_introc']) && $this->item->profile->$locProf['trg_introc'] > '') {
                        $intImg = $this->item->profile->$locProf['trg_introc'];
                        $intDate = $this->item->profile->$locProf['trg_intro'];
                        $intLink = '<a href="'.$intImg.'" alt="" target="_blank">';
                        if (substr($intImg, -3) == 'pdf') { $intImg = 'media/com_gausers/images/pdf.png'; }
                        $intLink .= '<img style="max-width:120px;" src="'.$intImg.'" alt="" title="Certificate Dated: '.$intDate.'" /></a> &nbsp;';
                        echo $intLink;
                    }
                    if (isset($this->item->profile->$locProf['trg_bioc']) && $this->item->profile->$locProf['trg_bioc'] > '') {
                        $bioImg = $this->item->profile->$locProf['trg_bioc'];
                        $bioDate = $this->item->profile->$locProf['trg_bio'];
                        $bioLink = '<a href="'.$bioImg.'" alt="" target="_blank">';
                        if (substr($bioImg, -3) == 'pdf') { $bioImg = 'media/com_gausers/images/pdf.png'; }
                        $bioLink .= '<img style="max-width:120px;" src="'.$bioImg.'" alt="" title="Certificate Dated: '.$bioDate.'" /></a> &nbsp;';
                        echo $bioLink;
                    }
                ?>
            </td>
		</tr>
		<?php endif; ?>

	</table>

</div>

<a class="btn btn-secondary" href="<?php echo Route::_('index.php?option=com_gausers&task=currentuser.cancel'); ?>">
	<i class="icon-undo"></i> <?php echo Text::_("COM_GAUSERS_CANCEL"); ?>
</a>

<?php if($canEdit): ?>
	<a class="btn btn-primary" href="<?php echo Route::_('index.php?option=com_gausers&task=currentuser.edit&id='.$this->item->id.'&frm=1&layout=edit'); ?>">
		<i class="icon-edit"></i> <?php echo Text::_("COM_GAUSERS_EDIT_ITEM"); ?>
	</a>
<?php endif; ?>
<?php if($canTrg): ?>
    <a class="btn btn-warning" href="<?php echo Route::_('index.php?option=com_gausers&task=currentuser.edit&id='.$this->item->id.'&layout=training&frm=1'); ?>">
		<i class="icon-edit"></i> <?php echo Text::_("COM_GAUSERS_EDIT_TRG"); ?>
	</a>
<?php endif; ?>
<?php if($canMembers): ?>
    <?php echo $acthtml .= HTMLHelper::_('bootstrap.renderModal', $actmodname, $actmodparams); ?>
<?php endif; ?>
<?php echo $pwhtml .= HTMLHelper::_('bootstrap.renderModal', $pwmodname, $pwmodparams); ?>

<?php if($this->item->invoices): ?>

    <h2><?php echo Text::_("COM_GAUSERS_MEMBERS_PAST_INVS"); ?></h2>
	<table class="table">
		<thead>
		<tr>
			<th><?php echo Text::_('COM_GAUSERS_FORM_LBL_ID'); ?></th>
			<th><?php echo Text::_('COM_GAUSERS_FINANCIAL_YEAR'); ?></th>
			<th class="center"><?php echo Text::_('COM_GAUSERS_FORM_LBL_INVOICE_MSHIP_ID'); ?></th>
			<th><?php echo Text::_('JSTATUS'); ?></th>
			<th style="text-align:right;"><?php echo Text::_('COM_GAUSERS_FORM_LBL_INVOICE_INVOICE_AMT'); ?></th>
			<th class="center"><?php echo Text::_('COM_GAUSERS_FORM_LBL_INVOICE_PAID_DATE'); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($this->item->invoices as $inv) : ?>
			<?php 
                $FinYear = (!empty($inv->end_date)) ? HtmlHelper::date($inv->end_date, Text::_('COM_GAUSERS_DISPLAY_DATE')) : '';
            ?>
            <tr>
				<?php if(\file_exists($filePath.'/'.$inv->inv_no.'.pdf') && ($canMembers || $canEdit)) : ?>
					<td><?php echo '<a href="'.$filePath.'/'.$inv->inv_no.'.pdf" target="_blank">'.$inv->inv_no.'</a>'; ?></td>
				<?php else : ?>
					<td><?php echo $inv->inv_no; ?></td>
				<?php endif; ?>
				<td><?php echo $FinYear; ?></td>
				<td class="center"><?php echo $inv->title; ?></td>
				<td><?php echo $inv->status; ?></td>
				<td style="text-align:right;"><?php echo '$'.number_format($inv->invoice_amt,2); ?></td>
				<td class="center"><?php echo $pdate = (!empty($inv->paid_date)) ? HtmlHelper::date($inv->paid_date, Text::_('COM_GAUSERS_DISPLAY_DATE')) : ''; ?></td>
			</tr>
		<?php endforeach; ?>
		</thead>
	</table>
<?php endif; ?>

<?php if($canMembers): ?>

    <h2><?php echo Text::_("COM_GAUSERS_MEMBERS_HISTORY"); ?></h2>
	
	<?php if($this->item->actions): ?>
        <table class="table">
    		<thead>
    		<tr>
    			<th><?php echo Text::_('COM_GAUSERS_CREATED_DATE'); ?></th>
    			<th><?php echo Text::_('COM_GAUSERS_CREATED_BY'); ?></th>
    			<th class="center"><?php echo Text::_('COM_GAUSERS_MEMBER_DETAILS'); ?></th>
    		</tr>
    		</thead>
    		<tbody>
    		<?php foreach ($this->item->actions as $act) : ?>
                <tr class="border-top border-success">
    				<td><?php echo $act->created_date ? HtmlHelper::date($act->created_date, Text::_('COM_GAUSERS_DISPLAY_DATE')) : ''; ?></td>
    				<td><?php echo GausersHelper::getSpecificUser($act->created_by)->name; ?></td>
    				<td><?php echo $act->act_name; ?></td>
    			</tr>
                <tr class="border-bottom border-success border-2">
    				<td colspan="3"><?php echo str_replace("\r\n", "<br />", $act->comment); ?></td>
    			</tr>
    		<?php endforeach; ?>
    		</thead>
    	</table>
	<?php else : ?>
        <p><?php echo Text::_("COM_GAUSERS_MEMBERS_HISTORY_NONE"); ?></p>
	<?php endif; ?>
<?php endif; ?>
