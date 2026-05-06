<?php
/**
 * @version    5.1.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Session\Session;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;

$tripRec = $displayData['view'];

//Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);
$lang->load('com_gatripsys', JPATH_SITE);
$user = GatripsysHelper::getSpecificUser();

$exEmail = strlen($tripRec->exclude_email);
$bookCntr = 0;
$persons = 0;
$wlHeader = 0;
$waitList = '';
$totBookings = count($tripRec->bookings);

?>
<table class="table table-striped" id="attendList">
	<thead>
		<tr>
			<th width="35%"><?php echo Text::_('COM_GATRIPSYS_ATTENDEES_USER_ID'); ?></th>
			<th width="8%" style="text-align: center;"><?php echo Text::_('# in Car'); ?></th>
			<th width="12%"><?php echo Text::_('COM_GATRIPSYS_ATTENDEES_STATUS'); ?></th>
			<th width="25%"><?php echo Text::_('COM_GATRIPSYS_ATTENDEES_CONTACT'); ?></th>
			<th width="20%"><?php echo Text::_('COM_GATRIPSYS_TRIPS_ACTIONS'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php // Booking status - 1 = approved and 0 = pending approval and 2 = cancelled and -2 = rejected ?>
		<?php foreach ($tripRec->bookings as $i => $booking) : ?>
			<?php
                // skip over cancelled bookings
                if ($booking->state == 2) { continue; }

                $bookCntr++;
                $persons = $persons + $booking->in_party;

                $canEditOwn = ($user->authorise('core.edit.own', 'com_gatripsys') && $user->id == $booking->user_id) ? 1 : 0;
                if (isset($booking->attend_email) && !empty($booking->attend_email)) {
                    if ((substr($booking->attend_email,0,$exEmail) != $tripRec->exclude_email)&&
    					$booking->state == 1) {
    					$tripRec->email_collection .= $booking->attend_email.',';
					}
				}

                if ($booking->inc_altemail &&
					($booking->altemail > '' && substr($booking->altemail,0,$exEmail) != $tripRec->exclude_email)&&
					$booking->state == 1) {
					$tripRec->email_collection .= $booking->altemail.',';
				}

				if ($tripRec->mship_single) {
					// make up new attend name and override
					$member = GainvoiceHelper::breakdownNamesFromUserID($booking->user_id);
					$booking->attend_name = GainvoiceHelper::combineNames($member);
				}

                // set the style to show attendee on a wait list
				if ( $tripRec->waitlist_avail ) {
                    if ( $tripRec->waitlist_all && $tripRec->max_no && $totBookings > $tripRec->max_no) {
                        $waitList = ' color:red;';
                    } else {
                        if (($tripRec->max_no && $bookCntr > $tripRec->max_no) || ($tripRec->max_people && $persons > $tripRec->max_people)) {
                            // set the style to show attendee on a wait list
                            $waitList = ' color:red;';
                            $wlHeader++;
                        }
                    }
                }

				$bookLink = GatripsysHelper::getHTTPQuery(null, 'task', 'attendeeform.edit', 'trip_id', $tripRec->id);
				$bookLink = GatripsysHelper::getHTTPQuery($bookLink, null, null, 'id', $booking->id);
				$bookLink = GatripsysHelper::getHTTPQuery($bookLink, null, null, 'tmpl', 'component');
				$bookLink = GatripsysHelper::getHTTPQuery($bookLink, null, null, 'layout', 'modal');
				$bookparams = array( 'url' => 'index.php?'.http_build_query($bookLink, '', '&amp;'),
				        'title' => Text::_("GABOOKING_EDIT_ATTENDEE"), 'closeButton'=> true,
				        'modalWidth' => 60, 'bodyHeight' => 50, 'backdrop'   => 'static' );
				// setup the actual link
				$bmodname = 'modal-myModal'.$booking->id;
				$bhtml = '<a class="btn btn-secondary btn-mini" href="#'.$bmodname.'" data-bs-toggle="modal">';
				$bhtml .= '<i class="icon-edit" title="'.Text::_('GABOOKING_EDIT_ATTENDEE').'"></i></a>';
			?>
			<?php if($waitList > '' && $wlHeader == 1) : ?>
				<tr class="row<?php echo $i; ?>">
					<td colspan="5" style="text-align:center;background-color:#ccc;color:red;"><?php echo Text::_('COM_GATRIPSYS_WAITLIST'); ?></td>
				</tr>
			<?php endif; ?>
			<tr class="row<?php echo $i % 2; ?>">
				<td style="<?php echo $waitList; ?>"><?php echo $booking->attend_name; ?></td>
				<td class="center" style="<?php echo $waitList; ?>"><?php echo $booking->in_party; ?></td>
				<td style="<?php echo $waitList; ?>"><?php echo $booking->status; ?></td>
				<td style="<?php echo $waitList; ?>"><?php echo str_replace('"','',$booking->primary_contact); ?></td>
				<td>
					<?php if(($tripRec->canLead || $tripRec->canTrip) && ($booking->state == 0 && $tripRec->state == 2)) : ?>
						<?php $attaccept = GatripsysHelper::getHTTPQuery(null, 'task', 'attendee.acceptAttendee', 'id', $booking->id); ?>
						<?php $attaccept = GatripsysHelper::getHTTPQuery($attaccept, null, null, 'trip_id', $tripRec->id); ?>
						<a class="btn btn-success btn-mini" href="<?php echo Route::_('index.php?'.http_build_query($attaccept, '', '&amp;'), false); ?>" 
                            title="<?php echo Text::_('GABOOKING_APPRV'); ?>"><i class="icon-publish" ></i>
						</a>
					<?php endif; ?>
					<?php if(($tripRec->canLead || $tripRec->canTrip) && ($tripRec->state == 2 && $booking->state == 1)) : ?>
						<?php $attunaccept = GatripsysHelper::getHTTPQuery(null, 'task', 'attendee.unacceptAttendee', 'id', $booking->id); ?>
						<?php $attunaccept = GatripsysHelper::getHTTPQuery($attunaccept, null, null, 'trip_id', $tripRec->id); ?>
						<a class="btn btn-secondary btn-mini" href="<?php echo Route::_('index.php?'.http_build_query($attunaccept, '', '&amp;'), false); ?>"
                            title="<?php echo Text::_('GABOOKING_UNAPPRV'); ?>"><i class="icon-unpublish" ></i>
						</a>
					<?php endif; ?>
					<?php if(($tripRec->canLead || $tripRec->canTrip) && ($tripRec->state == 0 || $booking->state == 1)) : ?>
						<?php $attreject = GatripsysHelper::getHTTPQuery(null, 'task', 'attendee.rejectAttendee', 'id', $booking->id); ?>
						<?php $attreject = GatripsysHelper::getHTTPQuery($attreject, null, null, 'trip_id', $tripRec->id); ?>
						<a class="btn btn-warning btn-mini" href="<?php echo Route::_('index.php?'.http_build_query($attreject, '', '&amp;'), false); ?>"
                            title="<?php echo Text::_('GABOOKING_REJCT'); ?>"><i class="icon-unpublish" ></i>
						</a>
					<?php endif; ?>
					<?php if(($canEditOwn || $tripRec->canLead || $tripRec->canTrip) && !$tripRec->finalExists) : ?>
						<?php if ($tripRec->trip_cost == '0.00' || ($booking->state == 0 && $tripRec->invOnApproval)) : ?>
							<?php echo $bhtml .= HTMLHelper::_('bootstrap.renderModal', $bmodname, $bookparams); ?>
						<?php endif; ?>
						<?php $attdel = GatripsysHelper::getHTTPQuery(null, 'task', 'attendee.removeAttendee', 'id', $booking->id); ?>
						<?php $attdel = GatripsysHelper::getHTTPQuery($attdel, null, null, 'trip_id', $tripRec->id); ?>
						<a class="btn btn-danger btn-mini pull-right" href="<?php echo Route::_('index.php?'.http_build_query($attdel, '', '&amp;'), false); ?>"
                            title="<?php echo Text::_('GABOOKING_CANCEL'); ?>"><i class="icon-trash"></i>
						</a>
					<?php endif; ?>
					<?php if($tripRec->trip_cost > '0.00' && $tripRec->canInvoice && $booking->inv_state == null && $tripRec->manual_inv) : ?>
						<?php $crInv = GatripsysHelper::getHTTPQuery(null, 'task', 'attendeeform.createInvoice', 'att_id', $booking->id); ?>
						<?php $crInv = GatripsysHelper::getHTTPQuery($crInv, null, null, Session::getFormToken(), 1); ?>
						<a class="btn btn-secondary btn-mini" href="<?php echo Route::_('index.php?'.http_build_query($crInv, '', '&amp;')); ?>"
                            title="<?php echo Text::_('GABOOKING_INV'); ?>"><i class="icon-16-money"></i>
						</a>
					<?php endif; ?>
				</td>
			</tr>

			<?php if($tripRec->show_comment && !empty($booking->comment)) : ?>
				<tr class="row<?php echo $i; ?>">
					<td>&nbsp;</td><td colspan="4"><span class="small"><?php echo $booking->comment; ?></span></td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	</tbody>
</table>

<?php if($tripRec->canLead && $tripRec->state == 2) : ?>
	<h2>
        <?php
    		/* email using the component phpMailer  */
            $emailLink = GatripsysHelper::getHTTPQuery(null, 'view', 'attendeeform', 'trip_id', $tripRec->id);
    		$emailLink = GatripsysHelper::getHTTPQuery($emailLink, null, null, 'tmpl', 'component');
    		$emailLink = GatripsysHelper::getHTTPQuery($emailLink, null, null, 'layout', 'modalemail');
    		$emailparams = array( 'url' => 'index.php?'.http_build_query($emailLink, '', '&amp;'),
    		        'title' => Text::_("COM_GATRIPSYS_SEND_EMAIL_DESC"), 'closeButton'=> true,
    		        'modalWidth' => 60, 'bodyHeight' => 60, 'backdrop'   => 'static' );
    		// setup the actual link
    		$emodname = 'modal-myEmailModal'.$tripRec->id;
    		$ehtml = '<a class="btn btn-primary" href="#'.$emodname.'" data-bs-toggle="modal">';
    		$ehtml .= '<i class="icon-mail"></i> '.Text::_('COM_GATRIPSYS_SEND_EMAIL').'</a>';
    		echo $ehtml .= HTMLHelper::_('bootstrap.renderModal', $emodname, $emailparams);

    	?>

    	<?php /* email using the individuals email package  */ ?>
        <a class="btn btn-tertiary" href="<?php echo $tripRec->email_collection; ?>" alt="" title="<?php echo Text::_('COM_GATRIPSYS_SEND_EMAIL_PERS'); ?>">
    		<i class="icon-mail" title="<?php echo Text::_('COM_GATRIPSYS_SEND_EMAIL_PERS_DESC'); ?>"></i> <?php echo Text::_('COM_GATRIPSYS_SEND_EMAIL_PERS'); ?>
    	</a> &nbsp; &nbsp;

        <?php if ($waitList > '') : ?>
            <?php /* don't display these buttons because some need to be weedled out before approving */ ?>
        <?php else : ?>
        	<?php
        		// set up the links for dis/approval of all attendees
                $apprvLink = GatripsysHelper::getHTTPQuery(null, 'task', 'attendee.approveAll', 'trip_id', $tripRec->id);
        		$unappLink = GatripsysHelper::getHTTPQuery(null, 'task', 'attendee.unapproveAll', 'trip_id', $tripRec->id);
        	?>
            <div style="float:right;">
        		<a class="btn btn-secondary" href="<?php echo Route::_('index.php?'.http_build_query($apprvLink, '', '&amp;')); ?>" alt=""
        			title="<?php echo Text::_('COM_GATRIPSYS_APPROVE_ALL_MESSAGE'); ?>" style="float:right;">
                    <i class="fas fa-thumbs-up"></i> <?php echo Text::_('COM_GATRIPSYS_APPROVE_ALL'); ?>
        		</a>
        	    <a class="btn btn-secondary" href="<?php echo Route::_('index.php?'.http_build_query($unappLink, '', '&amp;')); ?>" alt=""
        			title="<?php echo Text::_('COM_GATRIPSYS_UNAPPROVE_ALL'); ?>" style="float:right;">
                    <i class="fas fa-thumbs-down"></i> <?php echo Text::_('COM_GATRIPSYS_UNAPPROVE_ALL'); ?>
        		</a>&nbsp;
        	</div>
    	<?php endif; ?>
	</h2>
<?php endif; ?>

