<?php

/**
 * @version    5.1.6
 * @package    Com_Gausers
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * Usage       This id used specifically for Four Wheel Drive Clubs using plugin "profileb4wdc"
 */

defined('JPATH_BASE') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaauditHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GamodalHelper;

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gausers', JPATH_ADMINISTRATOR);

$data = $displayData;
$itemlist = $data['view']->get('items');
$params = $data['view']->get('params');
$xtras = $data['view']->get('xtra');

$canAdmin = $data['view']->get('canAdmin');
$canMembers = $data['view']->get('canMembers');
$rptType = $data['view']->get('rpt_type');
$ignorArray = $data['view']->ignorArray;
$ignorStdArray = $data['view']->ignorStdArray;

// data['view'] is the user id of member viewing the form
$user = $data['view']->user;
$today = GausersHelper::getTodaysDate();

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
$dispExp = $params->get('disp_expiry',0);
$hideAddress = false;
$hideEmail = false;
$hidePartner = false;
$hidePhone = false;

if (!$disp_address || ($disp_address && $suburb_only)) {
    $addClass = 'gausermem';
} else {
    $addClass = 'gauseraddr';
}

/*
echo '<pre>Test<br />';
print_r($locParams);
echo '</pre>';
*/
?>
<table class="table table-striped" >
	<thead>
	<tr class="gausernone" >
        <th class="gauserhead">Name</th>

		<?php if ($disp_address) : ?>
			<th class="gauserhead">Address</th>
		<?php endif; ?>

        <th class="gauserhead">Phone</th>

        <th class="gauserhead">Mship</th>

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
	
         <?php if ($canMembers) : ?>
			 <th class="gauserhead">Actions</th>
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
        if (empty($mship)) {
            // if no past mship invoice, set default details
            $mship = GausersHelper::getMshiptypeID($default_mship);
            $mship->memtype = $default_mship;
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

        // get all the privacy switches
		if (isset($item->privacy) && !empty($item->privacy) && $item->privacy) {
            if ($canMembers || $item->id == $user->id) {
                $privSet = ' style="color:'.$params->get('privacy_color').';" ';
            } else {
                continue;
            }
        } else {
            $privSet = '';
        }
		if ($localProfile == 'becs') {
            $profDetails = UserHelper::getProfile($item->id);
            $locParams = $profDetails->get('profile'.$localProfile);

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

		// set up the edit link of a member record
		$retLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuserform.markAsReturned', 'id', $item->id);
		$retLink = GausersHelper::getHTTPQuery($retLink, null, null, Session::getFormToken(), 1);
        $retURL = 'index.php?'.http_build_query($retLink, '', '&amp;');

		// set up the edit link of a member record
		$convLink = GausersHelper::getHTTPQuery(null, 'task', 'currentuser.convertMship', 'id', $item->id);
		$convLink = GausersHelper::getHTTPQuery($convLink, null, null, Session::getFormToken(), 1);
        $convertURL = 'index.php?'.http_build_query($convLink, '', '&amp;');

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
    				<?php if ($hideEmail) : ?>
        				<?php echo $fullname  ; ?>
    				<?php else : ?>
                        <a href="mailto:<?php echo $item->email; ?>" ><?php echo $fullname  ; ?></a>
                    <?php endif; ?>
                </span> &nbsp;
				<?php echo $FWDVic; ?>
				<?php if ($item->m_img) : ?>
					<span style="float:right;"><img class="zoom" src="<?php echo $item->m_img; ?>" height="100" width="100" alt="" /></span>
				<?php endif; ?>
				<?php if ($item->p_img) : ?>
					<span style="float:right;"><img class="zoom" src="<?php echo $item->p_img; ?>" height="100" width="100" alt="" /></span>
				<?php endif; ?>
                <?php if ($disp_address) : ?>
        			<span <?php echo $privSet; ?>>Private</span>
				<?php endif; ?>
			</td>
	        <?php if ($disp_address) : ?>
				<td class="<?php echo $addClass; ?>">
                    <?php if (!$hideAddress) : ?>
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
					<a href="tel:<?php echo str_replace(" ","",$item->phone) ; ?>" alt="">
						<span <?php echo $privSet; ?>><?php echo $item->phone ; ?></span>
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
	
            <?php if ($canMembers) : ?>
	            <td class="gausermem">
                    <a href="<?php echo Route::_('index.php?option=com_gausers&task=currentuser.update&id='.$item->id.'&'. Session::getFormToken() .'=1'); ?>"
                        class="btn btn-secondary"><i class="icon-<?php echo $icon; ?>" title="<?php echo Text::_($message); ?>"></i>
                    </a>
                    <?php if ($showConvert) : ?>
						<a href="<?php echo Route::_($convertURL); ?>"
							class="btn btn-secondary"><i class="fab fa-stack-overflow" title="<?php echo Text::_('COM_GAUSERS_CONVERT'); ?>"></i>
                        </a>
                    <?php else : ?>
                        <?php if ($showReturn) : ?>
    						<a href="<?php echo Route::_($retURL); ?>"
    							class="btn btn-warning-light"><i class="icon-undo" title="<?php echo Text::_('COM_GAUSERS_RETURN'); ?>"></i>
                            </a>
                        <?php else : ?>
                            <?php echo $lhtml; ?>
                        <?php endif; ?>
                        <?php echo $dhtml; ?>
                        <?php if ($use_barcodes) : ?>
    						<?php echo $bhtml; ?>
                        <?php endif; ?>
                        <?php if ($welcome_note) : ?>
    						<a href="<?php echo Route::_('index.php?option=com_gausers&task=currentuser.sendwelcome&id='.$item->id.'&'. Session::getFormToken() .'=1'); ?>"
    							class="btn btn-secondary"><i class="icon-mail" title="<?php echo Text::_('COM_GAUSERS_SEND_WELCOME_NOTE'); ?>"></i>
                            </a>
                        <?php endif; ?>
                        <?php if ($indiv_inv) : ?>
    						<a href="<?php echo Route::_('index.php?option=com_gausers&task=currentuser.genInvoice&id='.$item->id.'&'. Session::getFormToken() .'=1'); ?>"
                                class="btn btn-secondary"><i class="fas fa-file-invoice-dollar" title="<?php echo Text::_('COM_GAUSERS_GEN_INVOICE'); ?>"></i>
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>
					<a href="<?php echo Route::_('index.php?option=com_gausers&view=currentuser&id='.$item->id.'&'. Session::getFormToken() .'=1'); ?>"
                        class="btn btn-secondary"><i class="fas fa-search" title="<?php echo Text::_('COM_GAUSERS_VIEW_USER'); ?>"></i>
                    </a>
                </td>
             <?php endif; ?>
	    </tr>
	<?php endforeach; ?>
	</tbody>
</table>
