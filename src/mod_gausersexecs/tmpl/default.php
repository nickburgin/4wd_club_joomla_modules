<?php
/*
# ------------------------------------------------------------------------
# @version     5.1.6
# @copyright   Copyright (C) 2014. All rights reserved.
# @license     GNU General Public License version 2 or later; see LICENSE.txt
# Author:      Glenn Arkell
# Websites:    http://www.glennarkell.com.au
# ------------------------------------------------------------------------
*/
// no direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Date\Date;

$moduleclass_sfx	= $params->get('moduleclass_sfx', '');
$ht = $params->get('header_tag');
$hc = $params->get('header_class');
$set_listheight = $params->get('set_listheight');
$list_height = $params->get('list_height');
$intro_text = $params->get('intro_text');
$show_past = $params->get('show_past', 0);
$show_title = $params->get('showtitle', 0);
$show_pos = $params->get('show_pos', 0);
$show_exec = $params->get('show_exec', 0);
$cntr = 0;

$jdate = new Date($execs[0]->end_term);
$date_display = $jdate->format(Text::_('DATE_FORMAT_LC5'));
$club_address = $execs[0]->club_address;
$club_suburb = $execs[0]->club_suburb;
$club_pcode = $execs[0]->club_pcode;
$club_phone = $execs[0]->club_phone;
$club_email = $execs[0]->club_email;
$meeting_time = $execs[0]->meeting_time;
$meeting_info = $execs[0]->meeting_info;
$meeting_address = $execs[0]->meeting_address;
$comment = $execs[0]->comment;

if (!$show_pos) {
    // this means all data shown so get the club info as well
} else {
    // this means only show the positions information - NOT club data
}


// if (!$show_past) {
// 	$jdate = new Date($execs[0]->end_term);
// 	$date_display = $jdate->format(Text::_('DATE_FORMAT_LC5'));
// 	$club_address = $execs[0]->club_address;
// 	$club_suburb = $execs[0]->club_suburb;
// 	$club_pcode = $execs[0]->club_pcode;
// 	$club_phone = $execs[0]->club_phone;
// 	$club_email = $execs[0]->club_email;
// 	$meeting_time = $execs[0]->meeting_time;
// 	$meeting_info = $execs[0]->meeting_info;
// 	$meeting_address = $execs[0]->meeting_address;
// 	$comment = $execs[0]->comment;
// 	if (empty($execs[0]->pres_name) || $execs[0]->pres_name == '') {
// 		if ($execs[0]->pres_idp) { $president = $execs[0]->pres_partner; } else { $president = $execs[0]->pres_member; }
// 	} else {
// 		$president = $execs[0]->pres_name;
// 	}
// 	if (empty($execs[0]->vpres_name) || $execs[0]->vpres_name == '') {
// 		if ($execs[0]->vpres_idp) { $vpresident = $execs[0]->vpres_partner; } else { $vpresident = $execs[0]->vpres_member; }
// 	} else {
// 		$vpresident = $execs[0]->vpres_name;
// 	}
// 	if (empty($execs[0]->secr_name) || $execs[0]->secr_name == '') {
// 		if ($execs[0]->secr_idp) { $secretary = $execs[0]->secr_partner; } else { $secretary = $execs[0]->secr_member; }
// 	} else {
// 		$secretary = $execs[0]->secr_name;
// 	}
// 	if (empty($execs[0]->tres_name) || $execs[0]->tres_name == '') {
// 		if ($execs[0]->tres_idp) { $treasurer = $execs[0]->tres_partner; } else { $treasurer = $execs[0]->tres_member; }
// 	} else {
// 		$treasurer = $execs[0]->tres_name;
// 	}
// } else {
//     foreach ($execs AS $exec) { 
// 		$cntr++;
// 		if ($cntr > 1) { continue; }
// 		$jdate = new Date($exec->end_term);
// 		$date_display = $jdate->format(Text::_('DATE_FORMAT_LC5'));
// 		$club_address = $exec->club_address;
// 		$club_suburb = $exec->club_suburb;
// 		$club_pcode = $exec->club_pcode;
// 		$club_phone = $exec->club_phone;
// 		$club_email = $exec->club_email;
// 		$meeting_time = $exec->meeting_time;
// 		$meeting_info = $exec->meeting_info;
// 		$meeting_address = $exec->meeting_address;
// 		$comment = $exec->comment;
// 		if (empty($exec->pres_name) || $exec->pres_name == '') {
// 			if ($exec->pres_idp) { $president = $exec->pres_partner; } else { $president = $exec->pres_member; }
// 		} else {
// 			$president = $exec->pres_name;
// 		}
// 		if (empty($exec->vpres_name) || $exec->vpres_name == '') {
// 			if ($exec->vpres_idp) { $vpresident = $exec->vpres_partner; } else { $vpresident = $exec->vpres_member; }
// 		} else {
// 			$vpresident = $exec->vpres_name;
// 		}
// 		if (empty($exec->secr_name) || $exec->secr_name == '') {
// 			if ($exec->secr_idp) { $secretary = $exec->secr_partner; } else { $secretary = $exec->secr_member; }
// 		} else {
// 			$secretary = $exec->secr_name;
// 		}
// 		if (empty($exec->tres_name) || $exec->tres_name == '') {
// 			if ($exec->tres_idp) { $treasurer = $exec->tres_partner; } else { $treasurer = $exec->tres_member; }
// 		} else {
// 			$treasurer = $exec->tres_name;
// 		}
// 	}
// }

/*
echo '<pre>Test<br />';
print_r($execs);
echo '</pre>';
print_r(JFactory::getApplication()->getUserState('mod_gausers.test.data'));
*/
?>
<?php if ($set_listheight) : ?>
    <style>
        #execs<?php echo $module->id; ?> {
		max-height: <?php echo $list_height; ?>px;
		overflow:hidden;
		overflow-y:scroll;
		width: 98%;
	}
    </style>
<?php endif ; ?>

<div class="mod_gausers_execs<?php echo $moduleclass_sfx; ?>">
    <div id="execs<?php echo $module->id; ?>">
		<?php echo $intro_text; ?>

		<?php if (!empty($execs) && !$show_pos) : ?>
			<div class="item_fields">
				<table class="table events-inner">
                    <tr>
						<td width="98%">
                            <?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_CLUB_ADDRESS') . '</strong><br />' . $execs[0]->club_address . '<br />' . $execs[0]->club_suburb . ', ' . $execs[0]->club_pcode; ?>
                        </td>
					</tr>
					<tr>
						<td width="98%">
    						<?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_CLUB_PHONE') . ':</strong> ' . $execs[0]->club_phone; ?>
                        </td>
					</tr>
					<tr>
						<td width="98%">
    						<?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_CLUB_EMAIL') . '</strong><br />' . $execs[0]->club_email; ?>
                        </td>
					</tr>
					<?php if (isset($execs[0]->meeting_time) && $execs[0]->meeting_time > '00:00:00') : ?>
    					<tr>
    						<td width="98%">
        						<td><?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_MEETING_TIME') . ' - </strong>' . $execs[0]->meeting_time; ?>
                            </td>
    					</tr>
					<?php endif; ?>
					<?php if (isset($execs[0]->meeting_info) && $execs[0]->meeting_info > '') : ?>
    					<tr>
    						<td width="98%">
        						<?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_MEETING_INFO') . '</strong><br />' . $execs[0]->meeting_info; ?>
                            </td>
    					</tr>
					<?php endif; ?>
					<?php if (isset($execs[0]->meeting_address) && $execs[0]->meeting_address > '') : ?>
    					<tr>
    						<td width="98%">
        						<?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_MEETING_ADDRESS') . '</strong><br />' . $execs[0]->meeting_address; ?>
                            </td>
    					</tr>
					<?php endif; ?>
					<?php if (isset($execs[0]->comment) && $execs[0]->comment > '') : ?>
    					<tr>
    						<td width="98%">
        						<?php echo '<strong>'.Text::_('MOD_GAUSERSEXECS_COMMENT') . '</strong><br />' . nl2br($execs[0]->comment); ?>
                            </td>
    					</tr>
					<?php endif; ?>
					<tr>
						<th width="98%">
							Term End Date: <?php echo $execs[0]->disp_edate; ?>
						</th>
					</tr>
					<tr>
						<td width="98%">
							<strong>President: </strong> &nbsp; <?php echo $execs[0]->pres_name; ?><br />
							<strong>Vice Pres: </strong> &nbsp; <?php echo $execs[0]->vpres_name; ?><br />
							<strong>Secretary: </strong> &nbsp; <?php echo $execs[0]->secr_name; ?><br />
							<strong>Treasurer: </strong> &nbsp; <?php echo $execs[0]->tres_name; ?><br />
							<?php if (!$show_exec) : ?>
    							<strong>Editor: </strong> &nbsp; <?php echo $execs[0]->edit_name; ?><br />
    							<strong>Delegate: </strong> &nbsp; <?php echo $execs[0]->delg_name; ?><br />
    							<strong>Regional Rep: </strong> &nbsp; <?php echo $execs[0]->regr_name; ?><br />
    							<strong>Instructor Coord: </strong> &nbsp; <?php echo $execs[0]->istc_name; ?><br />
							<?php endif; ?>
						</td>
					</tr>
				</table>
			</div>
		<?php elseif (!empty($execs) && $show_pos) : ?>
			<div class="item_fields">
    			<table class="table events-inner">
                    <?php if ($show_past) : ?>
                        <?php foreach ($execs AS $i => $exec) : ?>
        					<tr>
        						<th width="98%">
        							Term End Date: <?php echo $exec->disp_edate; ?>
        						</th>
        					</tr>
        					<tr>
        						<td width="98%">
        							<strong>President: </strong> &nbsp; <?php echo $exec->pres_name; ?><br />
        							<strong>Vice Pres: </strong> &nbsp; <?php echo $exec->vpres_name; ?><br />
        							<strong>Secretary: </strong> &nbsp; <?php echo $exec->secr_name; ?><br />
        							<strong>Treasurer: </strong> &nbsp; <?php echo $exec->tres_name; ?><br />
        							<?php if (!$show_exec) : ?>
            							<strong>MagEditor: </strong> &nbsp; <?php echo $exec->edit_name; ?><br />
            							<strong>Delegate : </strong> &nbsp; <?php echo $exec->delg_name; ?><br />
            							<strong>Regl Rep : </strong> &nbsp; <?php echo $exec->regr_name; ?><br />
            							<strong>InstCoord: </strong> &nbsp; <?php echo $exec->istc_name; ?><br />
        							<?php endif; ?>
        						</td>
        					</tr>
    					<?php endforeach; ?>
        			<?php else : ?>
    					<tr>
    						<th width="98%">
    							Term End Date: <?php echo $execs[0]->disp_edate; ?>
    						</th>
    					</tr>
    					<tr>
    						<td width="98%">
    							<strong>President: </strong> &nbsp; <?php echo $execs[0]->pres_name; ?><br />
    							<strong>Vice Pres: </strong> &nbsp; <?php echo $execs[0]->vpres_name; ?><br />
    							<strong>Secretary: </strong> &nbsp; <?php echo $execs[0]->secr_name; ?><br />
    							<strong>Treasurer: </strong> &nbsp; <?php echo $execs[0]->tres_name; ?><br />
    							<?php if (!$show_exec) : ?>
        							<strong>MagEditor: </strong> &nbsp; <?php echo $execs[0]->edit_name; ?><br />
        							<strong>Delegate : </strong> &nbsp; <?php echo $execs[0]->delg_name; ?><br />
        							<strong>Regl Rep : </strong> &nbsp; <?php echo $execs[0]->regr_name; ?><br />
        							<strong>InstCoord: </strong> &nbsp; <?php echo $execs[0]->istc_name; ?><br />
    							<?php endif; ?>
    						</td>
    					</tr>
        			<?php endif; ?>
				</table>
			</div>
			
        <?php else : ?>
	        <p>No data present for the club.</p>
        <?php endif; ?>
	</div>
</div>
