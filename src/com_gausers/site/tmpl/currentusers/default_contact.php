<?php

/**
 * @version    6.0.0
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Usage       This id used specifically for Four Wheel Drive Clubs using plugin "profileb4wdc"
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\HTML\HTMLHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GaauditHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GamodalHelper;
use GlennArkell\Component\Gausers\Administrator\Helper\GacustomfldsHelper;

//Load admin language file
Factory::getApplication()->getLanguage()->load('com_gausers', JPATH_ADMINISTRATOR);

$data = $displayData;
$itemlist = $data['view']->get('items');
$params = $data['view']->get('params');
$xtras = $data['view']->get('xtra');
$xtraCusts = $data['view']->get('xtraCusts');

$canAdmin = $data['view']->get('canAdmin');
$canMembers = $data['view']->get('canMembers');
$rptType = $data['view']->get('rpt_type');
$ignorArray = $data['view']->ignorArray;
$ignorStdArray = $data['view']->ignorStdArray;

// data['view']->user is the user object of member viewing the form
$user = $data['view']->user;
$today = Factory::getDate();

$localProfile = $params->get( 'profile_suffix' );
$incl_partner = $params->get( 'incl_partner', 0);
$incl_years = $params->get('incl_years',0);
$indiv_inv = $params->get('indiv_inv',0);
$mship_single  = $params->get( 'mship_single', 0);
$use_barcodes  = $params->get( 'use_barcodes', 0 );
$use_clubnumber = $params->get('use_clubnumber',0);
$fld_clubnumber = $params->get('fld_clubnumber',0);
$hide_vax  = $params->get( 'hide_vax', 1 );
$vax_field1  = $params->get( 'vax_field1', 0 );
$exempt_field1  = $params->get( 'exempt_field1', 0 );
$vax_field2  = $params->get( 'vax_field2', 0 );
$exempt_field2  = $params->get( 'exempt_field2', 0 );
$disp_address  = $params->get('disp_address', 0);
$suburb_only  = $params->get('suburb_only', 0);
$welcome_note = $params->get('welcome_note',0);
$default_mship = $params->get('default_mship',0);
$temp_mship = $params->get('temp_mship',0);
$dispExp = $params->get('disp_expiry',0);
$locProf = 'profile'.$localProfile;
$privacyType  = $params->get( 'privacy_type', 'Profile' );
$privacy_switchp  = $params->get( 'privacy_switchp', 0 );
$hideAddress = false;
$hideEmail = false;
$hidePartner = false;
$hidePhone = false;

if (!$disp_address || ($disp_address && $suburb_only)) {
    $addClass = 'gausermem';
} else {
    $addClass = 'gauseraddr';
}
if (!$canMembers) {
    $actClass = 'gausermem';
} else {
    $actClass = 'gauserphone';
}

?>
<table class="table table-striped" >
	<thead>
	<tr class="gausernone" >
        <th class="gauserhead">Name</th>

		<?php if ($localProfile != 'raf') : ?>
            <?php if ($disp_address || $canMembers) : ?>
    			<th class="<?php echo $addClass; ?>">Address</th>
    		<?php endif; ?>
    
            <th class="gauserhead">Phone</th>

            <th class="gauserhead">Mship</th>
        <?php endif; ?>

	    <?php if (isset($xtras) && !empty($xtras[0])) : ?>
	        <?php
				foreach ($xtras as $xtra) {
	                if ($xtra == 'memtype') {
						$xtra = 'Membership';
					} elseif (($xtra == 'aboutme' && $localProfile == 'ifmr') || ($xtra == 'rotaryclub' && $localProfile == 'rtry')) {
						$xtra = 'Rotary Club';
					} elseif (($xtra == 'favoritebook' && $localProfile == 'ifmr') || ($xtra == 'district' && $localProfile == 'rtry')) {
						$xtra = 'District';
					} elseif ($xtra == 'locgrp' && $localProfile == 'brb') {
						$xtra = 'Area';
					} else {
						$xtra = str_replace("_", " ", $xtra);
						$xtra = ucwords($xtra);
					}
	                echo '<th class="gauserhead">'.$xtra.'</th>';
	            }
	        ?>
	     <?php endif; ?>

	    <?php if (isset($xtraCusts) && \is_array($xtraCusts)) : ?>
	        <?php
				foreach ($xtraCusts as $cust) {
	                if ($cust == 1) {
						$custHead = 'Connection';
					} elseif ($cust == 2) {
						$custHead = 'Relationship';
                    }
	                echo '<th class="gauserhead">'.$custHead.'</th>';
	            }
	        ?>
	     <?php endif; ?>

         <?php if ($canMembers) : ?>
			 <th class="center <?php echo $actClass; ?>">Actions</th>
         <?php endif; ?>

    </tr>
	</thead>

	<tbody>
    <?php foreach ($itemlist as $item) : ?>

        <?php // set up all the reference and preference data

        $u = GausersHelper::getSpecificUser($item->id);

        // check if user in left group
        $showReturn = (is_array($u->groups) && in_array($params->get('leftclub_group'),$u->groups)) ? true : false;


        $mship = GainvoiceHelper::getLastInvoiceMship($item->id);
        // if no past mship invoice, set default details
        if (empty($mship)) {
            // if BECS test for default group and if not use set usergroup title
            if ($localProfile == 'becs') {
                $mship = GausersHelper::getMshiptypeID($temp_mship);
                $mship->memtype = $temp_mship;
            } else {
                $mship = GausersHelper::getMshiptypeID($default_mship);
                $mship->memtype = $default_mship;
            }
        }
        
        $mship->memtype = empty($mship->memtype) ? $mship->mship_id : $mship->memtype;

        $showConvert = !empty($mship->memtype) && $mship->memtype == $params->get('temp_mship') ? true : false;
        
        $item->mship = $mship;

        if (!empty($item->mship->end_date)) {
            $item->regTo = HtmlHelper::date($item->mship->end_date, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE'));
        } else {
            $item->regTo = '';
        }

        $item->regFr = HtmlHelper::date($item->registerDate, Text::_('COM_GAUSERS_ABRV_DISPLAY_DATE'));

        // remove quote marks from the profile data
        $item->privacy = isset($item->privacy) ? str_replace('"','',$item->privacy ?? '') : 0;
        $item->use_post = isset($item->use_post) ? str_replace('"','',$item->use_post ?? '') : 0;
        $item->phone = isset($item->phone) ? str_replace('"','',$item->phone ?? '') : '';
        $item->partner = isset($item->partner) ? str_replace('"','',$item->partner ?? '') : '';
        $item->fwdvic_no = isset($item->fwdvic_no) ? str_replace('"','',$item->fwdvic_no ?? '') : 0;
        $item->m_img = isset($item->m_img) ? str_replace('"','',$item->m_img ?? '') : '';
        $item->p_img = isset($item->p_img) ? str_replace('"','',$item->p_img ?? '') : '';

        if ($item->use_post) {
            $item->address = isset($item->postal_address1) ? str_replace('"','',$item->postal_address1 ?? '') : '';
            $item->city = isset($item->postal_city) ? str_replace('"','',$item->postal_city ?? '') : '';
            $item->postcode = isset($item->postal_post_code) ? str_replace('"','',$item->postal_post_code ?? '') : '';
            $item->region = isset($item->postal_region) ? str_replace('"','',$item->postal_region ?? '') : '';
        } else {
            $item->address = isset($item->address) ? str_replace('"','',$item->address ?? '') : '';
            $item->city = isset($item->city) ? str_replace('"','',$item->city ?? '') : '';
            $item->postcode = isset($item->postcode) ? str_replace('"','',$item->postcode ?? '') : '';
            $item->region = isset($item->region) ? str_replace('"','',$item->region ?? '') : '';
        }

        // Build the address for the member
        if (!in_array('region',$ignorStdArray)) {
            if ($suburb_only) {
                $mainAddress = $item->city.', '.$item->region.' '.$item->postcode ;
            } else {
                $mainAddress = $item->address.' '.$item->city.', '.$item->region.' '.$item->postcode ;
            }
        } else {
            if ($suburb_only) {
                $mainAddress = $item->city.', '.$item->postcode ;
            } else {
                $mainAddress = $item->address.' '.$item->city.', '.$item->postcode ;
            }
        }

        // set the privacy switch
        $markPrivate = 0;
		//if (!empty($privacy) && $privacy) {
		if (isset($item->privacy) && $item->privacy) {
            $markPrivate = $item->id == $user->id ? 0 : 1;
            $privSet = ' style="color:'.$params->get('privacy_color').';" ';
            if ($item->id != $user->id ) {
                if (!$canMembers ) { continue; } else { $markPrivate = 0; }
            }
        } else {
            $privSet = '';
        }

		if ($localProfile == 'becs') {
            $profDetails = UserHelper::getProfile($item->id);
            $lprof = 'profile'.$localProfile;
            $locParams = $profDetails->$lprof;

            $hideAddress = isset($locParams['anonaddress']) && $locParams['anonaddress'] ? true : false;
            $hideEmail = isset($locParams['anonemail']) && $locParams['anonemail'] ? true : false;
            $hidePartner = isset($locParams['anonpartner']) && $locParams['anonpartner'] ? true : false;
            $hidePhone = isset($locParams['anonphone']) && $locParams['anonphone'] ? true : false;
        }

        if ($hide_vax) {
			$vax_icons = '';
        } elseif (!$canMembers) {
			$vax_icons = '';
		} else {
    		// test for primary member vaccination
    		$vaccinated1 = GausersHelper::isUserVaccinated($item->id, $vax_field1, $exempt_field1);
    		// test for partner member vaccination
    		$vaccinated2 = GausersHelper::isUserVaccinated($item->id, $vax_field2, $exempt_field2);

			if ($vaccinated1) {
				$vax_icons = '<i class="icon-publish"></i> ';
			} else {
				$vax_icons = '<i class="icon-unpublish"></i> ';
			}
	        if ($mship_single) {
				if (isset($item->partner) && $item->partner > '')  {
					if ($vaccinated2) {
						$vax_icons .= '<i class="icon-publish"></i> ';
					} else {
						$vax_icons .= '<i class="icon-unpublish"></i> ';
					}
				}
			}
		}
		
        // setup the modal links for a member
		$lhtml = GamodalHelper::setupModalButton('view', 'currentuserform', 'id', $item->id, 'modalleft', 'myLeftModal', 'secondary', '', 'COM_GAUSERS_LEFTGROUP_DESC', 'fas fa-sign-out-alt', $u->name);
		$dhtml = GamodalHelper::setupModalButton('view', 'currentuserform', 'id', $item->id, 'modaldied', 'myDiedModal', 'secondary', '', 'COM_GAUSERS_PASSEDAWAY_DESC', 'fas fa-sad-tear', $u->name);
		$bhtml = GamodalHelper::setupModalButton('view', 'currentuser', 'id', $item->id, 'modalbcode', 'myBCModal', 'secondary', '', 'COM_GAUSERS_BARCODE_DESC', 'fas fa-barcode', $u->name);

		// set up the edit link of a member record
		$editLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuserform.edit', 'id', $item->id);
        $editURL = 'index.php?'.http_build_query($editLink, '', '&amp;');

		// set up the returned link of a member record
		$retLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuserform.markAsReturned', 'id', $item->id);
		$retLink = GausersHelper::getHTTPQuery($retLink, null, null, Session::getFormToken(), 1);
        $retURL = 'index.php?'.http_build_query($retLink, '', '&amp;');

		// set up the convert link of a member record
		$convLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.convertMship', 'id', $item->id);
		$convLink = GausersHelper::getHTTPQuery($convLink, null, null, Session::getFormToken(), 1);
        $convertURL = 'index.php?'.http_build_query($convLink, '', '&amp;');

		// set up the send welcome link of a member record
		$welcLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.sendwelcome', 'id', $item->id);
		$welcLink = GausersHelper::getHTTPQuery($welcLink, null, null, Session::getFormToken(), 1);
        $welcURL = 'index.php?'.http_build_query($welcLink, '', '&amp;');

		// set up the invoice link of a member record
		$invLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.genInvoice', 'id', $item->id);
		$invLink = GausersHelper::getHTTPQuery($invLink, null, null, Session::getFormToken(), 1);
        $invURL = 'index.php?'.http_build_query($invLink, '', '&amp;');

		// set up the view link of a member record
		$viewLink = GausersHelper::getHTTPQuery(null, 'view', 'currentuser', 'id', $item->id);
		$viewLink = GausersHelper::getHTTPQuery($viewLink, null, null, Session::getFormToken(), 1);
        $viewURL = 'index.php?'.http_build_query($viewLink, '', '&amp;');

		// set up the update link of a member record
		$updLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.update', 'id', $item->id);
		$updLink = GausersHelper::getHTTPQuery($updLink, null, null, Session::getFormToken(), 1);
        $updURL = 'index.php?'.http_build_query($updLink, '', '&amp;');

		if (!$incl_partner) { $item->partner = null; }

		// set up 4WDVic reference number if necessary
		//fa-exclamation-triangle
		if ($localProfile == 'b4wdc' && (isset($item->fwdvic_no) && $item->fwdvic_no == 0)) {
			$FWDVic = '<i class="fas fa-exclamation-triangle"></i>';
		} else {
			$FWDVic = '';
		}

		if ($item->block) {
            $icon = 'unpublish';
            $message = 'COM_GAUSERS_UNPAID_MESSAGE_DESC';
        } else {
            $icon = 'publish';
            $message = 'COM_GAUSERS_PAID_MESSAGE_DESC';
        }

		// get the last update date from audit trail
		$lastUpd = GaauditHelper::getLatestUpdate($item->id);
		if (!empty($lastUpd)) {
			$lastUpdDetails = 'Last Updated: '.$lastUpd->disp_date.' By: '.$lastUpd->created_by_name;
		} else {
	        $lastUpdDetails = 'No updates as yet';
		}

        if ($localProfile == 'food') {
			$fullname = $item->name;
	 	} else {
			// setup full name of membership
			if (!$incl_partner) {
			 	$fullname = $item->name;
			} elseif (!$mship_single) {
				$fullname = $item->name;
			} else {
				$mbr = GanamesHelper::breakdownNamesFromUserID($item->id);
                $fullname = GanamesHelper::combineNames($mbr);
			}
		}

        $regdate = new \DateTime($item->registerDate);
        $regyears = $regdate->diff($today);
        $regyears = $regyears->format('%y');

        // do a final check if canMember and if updating self
        if (!$canMembers && !$disp_address && $item->id == $user->id) { $showEdit = true; } else { $showEdit = false; }

        ?>

		<tr class="gausernone" >
	        <td class="gausername">
                <?php echo $vax_icons; ?>
				<span title="<?php echo $lastUpdDetails; ?>">
    				<?php if (!$canMembers && $hideEmail) : ?>
        				<span <?php echo $privSet; ?>><?php echo $fullname  ; ?></span>
    				<?php else : ?>
                        <a href="mailto:<?php echo $item->email; ?>" <?php echo $privSet; ?> ><?php echo $fullname  ; ?></a>
                    <?php endif; ?>
                </span> &nbsp;
				<?php echo $FWDVic; ?>
				<?php if ($item->m_img) : ?>
					<span style="float:right;"><img class="zoom" src="<?php echo $item->m_img; ?>" height="100" width="100" alt="" /></span>
				<?php endif; ?>
				<?php if ($item->p_img) : ?>
					<span style="float:right;"><img class="zoom" src="<?php echo $item->p_img; ?>" height="100" width="100" alt="" /></span>
				<?php endif; ?>
                <?php if ($markPrivate) : ?>
        			<span <?php echo $privSet; ?>>Private</span>
				<?php endif; ?>
			</td>
	        <?php if ($localProfile != 'raf') : ?>
                <?php if ($disp_address || $canMembers) : ?>
    				<td class="<?php echo $addClass; ?>">
                        <?php if (!$hideAddress || $canMembers) : ?>
                            <?php if ($canMembers || $item->id == $user->id) : ?>
        		                <span title="<?php echo Text::_('COM_GAUSERS_EDIT_MEMBER_DESC'); ?>">
        							<a href="<?php echo Route::_($editURL); ?>"<?php echo $privSet; ?>>
        		                        <?php echo $mainAddress; ?>
        		                    </a>
        		                </span>
        		            <?php else : ?>
        		                <span <?php echo $privSet; ?>><?php echo $mainAddress; ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                <?php endif; ?>
                <td class="gauserphone">
    				<?php if (!empty($item->phone) && !$hidePhone) : ?>
    					<a href="tel:<?php echo str_replace(" ","",$item->phone) ; ?>" <?php echo $privSet; ?> alt="">
    						<?php echo $item->phone ; ?>
    					</a>
    				<?php endif; ?>
    			</td>

                <td class="gauserphone">
    				<?php echo $item->mship->title; ?>
    				<?php if ($dispExp) { echo '<br /><br />'.$item->regTo; } ?>
    				<?php if ($incl_years) { echo '<br /><span class="small center">('.$regyears.' years)</span>'; } ?>
    				<?php if ($showEdit) : ?>
    					<a href="<?php echo Route::_($editURL); ?>"<?php echo $privSet; ?> class="btn btn-outline-warning btn-sm">
                            <i class="icon-edit"></i>
    		            </a>
    				<?php endif; ?>
    			</td>
	        <?php endif; ?>

	        <?php if (isset($xtras) && !empty($xtras[0])) : ?>
	            <?php
				    foreach ($xtras as $xtra) {
				        if (substr($item->$xtra ?? '',0,1) === '"') { $item->$xtra = substr($item->$xtra ?? '',1); }
				        if (substr($item->$xtra ?? '',-1) === '"') { $item->$xtra = substr($item->$xtra ?? '',0, -1); }
						if ($item->$xtra == 1) { $item->$xtra = 'Yes'; }
						echo '<td class="gausermem">'.$item->$xtra.'</td>';
				    }
				?>
	        <?php endif; ?>
	
	        <?php if (isset($xtraCusts) && \is_array($xtraCusts)) : ?>
	            <?php
				    $connections = GacustomfldsHelper::getCustomFields($u, 'com_users.user');
                    foreach ($xtraCusts as $xtraCust) {
                        foreach ($connections as $key => $connValue) {
                            if ($key == $xtraCust) {
        						if (substr($connValue,1,8) != '- Select') {
                                    echo '<td class="gausermem">'.$connValue.'</td>';
        						} else {
                                    echo '<td class="gausermem"> </td>';
                                }
                            }
						}
				    }
				?>
	        <?php endif; ?>

            <?php // --------------------------------------- action buttons for each member  ---------------  ?>
            <?php if ($canMembers) : ?>
	            <td class="center <?php echo $actClass; ?>">
                    <a href="<?php echo Route::_($updURL); ?>" class="btn btn-secondary">
                        <i class="icon-<?php echo $icon; ?>" title="<?php echo Text::_($message); ?>"></i>
                    </a>
                    <?php // ------------------------- conversion button  ---------------  ?>
                    <?php if ($showConvert) : ?>
						<a href="<?php echo Route::_($convertURL); ?>" class="btn btn-secondary">
                            <i class="fab fa-stack-overflow" title="<?php echo Text::_('COM_GAUSERS_CONVERT'); ?>"></i>
                        </a>
                    <?php endif; ?>
                    <?php // ------------------------- past mbr returned  ---------------  ?>
                    <?php if ($showReturn) : ?>
						<a href="<?php echo Route::_($retURL); ?>" class="btn btn-warning-light">
                            <i class="icon-undo" title="<?php echo Text::_('COM_GAUSERS_RETURN'); ?>"></i>
                        </a>
                    <?php else : ?>
                        <?php echo $lhtml; ?>
                    <?php endif; ?>

                    <?php // ------------------------- deceased  ---------------  ?>
                    <?php echo $dhtml; ?>

                    <?php // ------------------------- barcades  ---------------  ?>
                    <?php if ($use_barcodes) : ?>
						<?php echo $bhtml; ?>
                    <?php endif; ?>

                    <?php // ------------------------- Send welcome email  ---------------  ?>
                    <?php if ($welcome_note) : ?>
						<a href="<?php echo Route::_($welcURL); ?>" class="btn btn-secondary">
                            <i class="icon-mail" title="<?php echo Text::_('COM_GAUSERS_SEND_WELCOME_NOTE'); ?>"></i>
                        </a>
                    <?php endif; ?>

                    <?php // ------------------------- individual invoice  ---------------  ?>
                    <?php if ($indiv_inv) : ?>
						<a href="<?php echo Route::_($invURL); ?>" class="btn btn-secondary">
                            <i class="fas fa-file-invoice-dollar" title="<?php echo Text::_('COM_GAUSERS_GEN_INVOICE'); ?>"></i>
                        </a>
                    <?php endif; ?>

                    <?php // ------------------------- view record  ---------------  ?>
					<a href="<?php echo Route::_($viewURL); ?>" class="btn btn-secondary">
                        <i class="fas fa-search" title="<?php echo Text::_('COM_GAUSERS_VIEW_USER'); ?>"></i>
                    </a>
                </td>
             <?php endif; ?>
	    </tr>
	<?php endforeach; ?>
	</tbody>
</table>
