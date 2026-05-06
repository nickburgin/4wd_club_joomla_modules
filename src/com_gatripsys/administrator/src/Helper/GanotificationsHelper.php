<?php

/**
 * @version    5.3.0
 * @package    Com_Gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2016 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Data\DataObject;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\Filesystem\Path;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GaemailHelper;

// Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

/**
 * Botifications helper.
 * @since  1.6
 */
class GanotificationsHelper
{
	/**
	* Set up Data object for mail template
	* @param trip record id
	* @return $item object
	*/
	public static function setupDataObject($trip_id = 0)
	{
		$sitename   = Factory::getApplication()->get('sitename');
		$params  = ComponentHelper::getParams('com_gatripsys');
		$tc_group = $params->get('notif_tc_group', 0);
		$tcs = Access::getUsersByGroup($tc_group);
		$bcc  = $params->get('bcc_users',0);
		$exclude_email = $params->get('exclude_email', 'noemail');
		$exLen = strlen($exclude_email);
		$notifMbrs = $params->get('notif_users', 1);
		$ignorMbrs = $params->get('ignore_mbrs', array());

		$trip = GatripsysHelper::getTripInformation($trip_id);

		$dept_date_disp = HtmlHelper::date($trip->dept_date, Text::_('COM_GATRIPSYS_DISPLAY_DATE'));
        $leader = GatripsysHelper::getSpecificUser($trip->leader);

        $tripCo = isset($tcs[0]) && $tcs[0] > '' ? $tcs[0] : 0;
        $trip_coord = GatripsysHelper::getSpecificUser($tripCo);

        $user = GatripsysHelper::getSpecificUser();
        $members = GatripsysHelper::getCurrentMembers();

        if ($params->get('mship_single',0)) {
	    	$member = GainvoiceHelper::breakdownNamesFromUserID($user->id);
			$member_name = GainvoiceHelper::combineNames($member);
	    	$tripCoord = GainvoiceHelper::breakdownNamesFromUserID($trip_coord->id);
			$coord_name = GainvoiceHelper::combineNames($tripCoord);
		} else {
			$member_name = $user->name;
			$coord_name = $trip_coord->name;
		}

    	foreach ($members as $m) {
            // test for ignore switch
            if (in_array($m->user_id, $ignorMbrs)) { continue; }

            if (substr($m->email,0,$exLen) != $exclude_email) {
                $mbrs[] = $m;
            }
            if (isset($m->altemail) && $m->inc_altemail && substr($m->altemail,0,$exLen) != $exclude_email) {
                $m->email = $m->altemail;
                $mbrs[] = $m;
            }
        }

        // use the template mail system
        $item = new \stdClass();
        $item->member_name = $member_name;
        $item->booker_name = $member_name;
        $item->leader_name = $trip->leader_name;
        $item->leader_email = $trip->leader_email;
        $item->leader = $leader;
        $item->trip_title = $trip->title;
        $item->trip_status = $trip->state;
        $item->dept_date_disp = $dept_date_disp;
        $item->coord_name = $coord_name;
        $item->tc_group = $tc_group;
        $item->sitename = $sitename;
        $item->user = $user;
        $item->tcs = $tcs;
        $item->bcc = $bcc;
        $item->trip_details = $trip;
        $item->members = $mbrs;

        return $item;
	}

	/**
	* Notify Users that a trip has been loaded as approved
	* @param trip record id
	* @return true
	*/
	public static function notifyUsersNewTrip($id, $tmpl)
	{
		$item = self::setupDataObject($id);

		$params  = ComponentHelper::getParams('com_gatripsys');

		$viewLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $id);
		$link = 'index.php?'.http_build_query($viewLink, '', '&amp;');

        if ($params->get('tmpl_email',0)) {
            $sent = GaemailHelper::sendEmailTmpl('com_gatripsys.'.$tmpl, $item, $link);
        } else {
            $recipients = GaemailHelper::getRecipients($item->members, $params);
            $subject = Text::sprintf('COM_GATRIPSYS_NOTIFY_MEMBERS_SUBJ',$item->leader_name);
            $body = GaemailHelper::setupEmailContent($item);
            $sent = GaemailHelper::sendEmail($recipients, $body, $subject, null);
        }

	}

	/**
	* Notify the Trip Leader
	* @param   array $data        Submitted data from attendee form
	* @param   string  $tmpl      Template identifier
	* @param   int   $booking_id  Attendee record id - 0 means a new booking else cancelation
	* @return true
	*/
	public static function notifyLeader($data, $tmpl, $booking_id = 0)
	{

		if ($data['trip_id']) {

			$item = self::setupDataObject($data['trip_id']);

			$viewLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $data['trip_id']);
			$link = 'index.php?'.http_build_query($viewLink, '', '&amp;');
    		$tripLink = '<a href="'.Uri::base().$link.'">Link to Trip - '.$item->trip_title.'</a>';

			$params  = ComponentHelper::getParams('com_gatripsys');
			
			// test if this is relating to a trip approval or attendee
            if (isset($data['not_attendee']) && $data['not_attendee']) {
                // its approval of a trip only so setup object
                $new_rec = new \stdClass();
                $new_rec->name = $item->leader_name;
                $new_rec->leader_name = $item->leader_name;
                $new_rec->email = $item->leader_email;
                $new_rec->leader_email = $item->leader_email;
                $recips = array($new_rec);
            } else {
                $booker = GatripsysHelper::getSpecificUser($data['booked_by']);
    			$attendee = GatripsysHelper::getSpecificUser($data['user_id']);

                // check config and setup names
    			if ($params->get('mship_single',0)) {
    	    		$att = GainvoiceHelper::breakdownNamesFromUserID($data['user_id']);
    				$item->att_name = GainvoiceHelper::combineNames($att);
    	    		$book = GainvoiceHelper::breakdownNamesFromUserID($data['booked_by']);
    				$item->book_name = GainvoiceHelper::combineNames($book);
    			} else {
    				$item->book_name = $booker->name;
    				$item->att_name = $attendee->name;
    			}

    			if ($data['booked_by'] != $data['user_id']) {
    				$item->madeby = ' Booking made by '.$item->book_name.'.';
    			}

                $attRec = new \stdClass();
                $attRec->name = $item->att_name;
                $attRec->email = $attendee->email;
                $attRec->leader_name = $item->leader_name;
                $attRec->leader_email = $item->leader_email;
                $leadRec = new \stdClass();
                $leadRec->att_name = $item->att_name;
                $leadRec->att_email = $attendee->email;
                $leadRec->name = $item->leader_name;
                $leadRec->email = $item->leader_email;
                $recips = array($attRec, $leadRec);
            }

            if ($params->get('tmpl_email', 0)) {
                // use the template mail system
                $item->members = $recips;
                GaemailHelper::sendEmailTmpl('com_gatripsys.'.$tmpl, $item, $link, null, null);
            } else {

                $recipients = array();
    			if (substr($attendee->email,0,$exLen) != $exclude_email) {
    				$recipients[] = $attendee->email;
    			}
    			if ($trip->leader && substr($trip->leader_email,0,$exLen) != $exclude_email) {
    				$recipients[] = $trip->leader_email;
    			}
    			if (substr($booker->email,0,$exLen) != $exclude_email) {
    				$recipients[] = $booker->email;
    			}
    
    			if ($trip->inc_altemail && isset($trip->altemail) && $trip->altemail > '' && substr($trip->altemail,0,7) != $exclude_email) {
    				$recipients[] = $trip->altemail;
    			}
    
    			if (!empty($recipients)) {
    				$subject = Text::_('COM_GATRIPSYS_TRIP_BOOKING_SUBJECT').$item->att_name;
    				$body = '<p> Dear '.$item->leader_name.',</p>';
    				if ($data['new_booking']) {
    					$body .= '<p>'.Text::sprintf('COM_GATRIPSYS_ATT_BOOKING_BODY',$item->att_name, $item->trip_title, $item->madeby);
    				} else {
    					$body .= '<p>'.Text::sprintf('COM_GATRIPSYS_ATT_CANCELLATION_BODY',$item->att_name, $item->trip_title, $item->madeby);
    				}
    				$body .= '<p> </p>';
    				$body .= '<p>'.$sitename.'</p>';
    				
    				GaemailHelper::sendEmail($recipients, $body, $subject, 0, 0, 0);
    				Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_TL_NOTIFIED_MESSAGE'), 'notice');
    			} else {
    				Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NOTL_NOTIFIED_MESSAGE'), 'warning');
    			}
			}
		} else {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NO_TRIPID_MESSAGE'), 'warning');
        }


		return true;
	}

	/**
	* Notify the Trip Leader of the booking or trip approval
	* @param   array   $data passed by booking screen
	* @param   string  $tmpl  mandatory (template ext)
	* @param   integer $id booking reference if existing
	* @return true
	*/
	public static function notifyTripLeader($data, $tmpl, $booking_id = 0)
	{
		if ($data['id']) {
    		$params  = ComponentHelper::getParams('com_gatripsys');
    		
    		$trip = GatripsysHelper::getTripFromAttend($data['id']);

    		// setup the data to include in email
    		$data['coord_name'] = null;
    		$data['leader_name'] = $trip->leader_name;
    		$data['trip_title'] = $trip->title;
    		$data['member_name'] = $trip->attend_name;
    		$data['dept_date'] = HtmlHelper::date($trip->dept_date, Text::_('COM_GATRIPSYS_DISPLAY_DATE'));

            $data['recips'] = array(array('email'=>$trip->leader_email, 'name'=>$trip->leader_name));
    		$data['cc_recips'] = array(array('email'=>$trip->attend_email, 'name'=>$trip->attend_name));

			$viewLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $trip->id);
			$link = 'index.php?'.http_build_query($viewLink, '', '&amp;');

            if ($params->get('tmpl_email', 0)) {
                GaemailHelper::sendEmailTemplate('com_gatripsys.'.$tmpl, $data, $link, null, null);
            }
		} else {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NO_ATTID_MESSAGE'), 'warning');
        }

		return true;
	}

	/**
	* Notify the Trip Leader of the trip being created
	* @param   array   $data passed by booking screen
	* @param   string  $tmpl  mandatory (template ext)
	* @return true
	*/
	public static function notifyLeaderNewTrip($data, $tmpl)
	{
		if ($data['trip_id']) {
    		$params  = ComponentHelper::getParams('com_gatripsys');
    		
    		$trip = GatripsysHelper::getTripInformation($data['trip_id']);

    		// setup the data to include in email
    		$data['coord_name'] = null;
    		$data['leader_name'] = $trip->leader_name;
    		$data['trip_title'] = $trip->title;
    		$data['dept_date'] = HtmlHelper::date($trip->dept_date, Text::_('COM_GATRIPSYS_DISPLAY_DATE'));

            $data['recips'] = array(array('email'=>$trip->leader_email, 'name'=>$trip->leader_name));

			$viewLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $trip->id);
			$link = 'index.php?'.http_build_query($viewLink, '', '&amp;');

            if ($params->get('tmpl_email', 0)) {
                GaemailHelper::sendEmailTemplate('com_gatripsys.'.$tmpl, $data, $link, null, null);
            }
		} else {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NO_TRIPSET_MESSAGE'), 'warning');
        }

		return true;
	}

	/**
	* Notify the Trip Co-ordinator of the trip that has been proposed
	* @param trip record id
	* @return true
	*/
	public static function notifyTripCoord($id = 0)
	{

		if ($id) {
    	    /* tripnew, tripaprv, booktrip, bookaprv, bookcan, tripcan */

    		$params  = ComponentHelper::getParams('com_gatripsys');
			$item = self::setupDataObject($id);

			// In case no trip co-ord group set, use admin id
            if (!isset($item->tcs) || empty($item->tcs)) {
                $admin_id = $params->get('admin_id', 0);
                $item->tcs = array($admin_id);
                $tmpl = 'tripnewad';
            } else {
                $tmpl = 'tripnewtc';
            }

			// cycle through the TC array of ids
			$tcs = array();
			$tc_names = '';
			foreach ($item->tcs as $tc) {
                $tripCoord = GatripsysHelper::getSpecificUser($tc);
                $tc_names .= $tripCoord->name.', ';
                $tcs[] = $tripCoord;
            }
            $tc_names = substr($tc_names,0,-2);
            $item->members = $tcs;

			$viewLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $id);
			$link = 'index.php?'.http_build_query($viewLink, '', '&amp;');
    		$tripLink = '<a href="'.Uri::base().$link.'">Link to Trip - '.$item->trip_title.'</a>';

            if ($params->get('tmpl_email', 0)) {
                $item->coord_name = $tc_names;
                if ($item->trip_status == 1) {
                    GaemailHelper::sendEmailTmpl('com_gatripsys.'.$tmpl, $item, $link, null, null);
                } elseif ($item->trip_status == 2) {
                    GaemailHelper::sendEmailTmpl('com_gatripsys.tripaprv', $item, $link, null, null);
                } elseif ($item->trip_status == 5) {
                    GaemailHelper::sendEmailTmpl('com_gatripsys.tripcantc', $item, $link, null, null);
                } elseif ($item->trip_status == 4) {
                    GaemailHelper::sendEmailTmpl('com_gatripsys.tripclose', $item, $link, null, null);
                }
            } else {

                $recipients = GaemailHelper::getRecipients($item->members, $params);

    			if ($item->tc_group) {
                    $subject = Text::sprintf('COM_GATRIPSYS_TC_NOTIF_SUBJECT', $item->member_name);
    				$body = Text::sprintf('COM_GATRIPSYS_PROPOSED_TRIP_BODY',$item->trip_title, $tripLink);

    				GaemailHelper::sendEmail($recipients, $body, $subject, 0, 0, 0);
    				Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_TC_NOTIFIED_MESSAGE'), 'message');
    			} else {
    				Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NOTC_NOTIFIED_MESSAGE'), 'danger');
    			}
			}
        }


		return true;
	}


	/**
	* Notify Users attendance on trip is approved
	* @param   integer $id        Trip record id
	* @param   integer $user_id   User record id
	* @param   string  $tmpl      Template identifier
	* @return true
	*/
	public static function notifyMember($id, $user_id, $tmpl)
	{
		if ($id) {

			$item = self::setupDataObject($id);
			$attendee = GatripsysHelper::getSpecificUser($user_id);
			$params  = ComponentHelper::getParams('com_gatripsys');

			$viewLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $id);
			$link = 'index.php?'.http_build_query($viewLink, '', '&amp;');

            if ($params->get('tmpl_email', 0)) {
                $item->members = array($attendee);
                GaemailHelper::sendEmailTmpl('com_gatripsys.'.$tmpl, $item, $link, null, null);
            }
        }
	}

	/**
	* Notify Users attendance on trip is approved
	* @param   integer $id     Trip record id
	* @param   string  $tmpl   Template identifier
	* @return true
	*/
	public static function notifyMembers($id, $tmpl)
	{
		if ($id) {

			$item = self::setupDataObject($id);
			$item->members = GatripsysHelper::getTripAttendees($id);

			$params  = ComponentHelper::getParams('com_gatripsys');

			$viewLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $id);
			$link = 'index.php?'.http_build_query($viewLink, '', '&amp;');

            if ($params->get('tmpl_email', 0)) {
                GaemailHelper::sendEmailTmpl('com_gatripsys.'.$tmpl, $item, $link, null, null);
            }
        }
	}

	/**
	* Notify the Trip Co-ordinator of the trip that has been finalised
	* @param trip object
	* @return true
	*/
	public static function finaliseTripCoord($trip = null)
	{

		if ($trip->id) {
			$app        = Factory::getApplication();
			$sitename   = $app->get('sitename');

			$params  = ComponentHelper::getParams('com_gatripsys');
			$tc_group = $params->get('notif_tc_group');

			if ($tc_group) {

    			$tcs = Access::getUsersByGroup($tc_group);
    			$user = GatripsysHelper::getSpecificUser();
    
                if ($params->get('mship_single',0)) {
    	    		$member = GainvoiceHelper::breakdownNamesFromUserID($user->id);
    				$member_name = GainvoiceHelper::combineNames($member);
    			} else {
    				$member_name = $user->name;
    			}
    			$leader = GatripsysHelper::getSpecificUser($trip->leader);
    
    	        $path = Path::clean( JPATH_SITE . '/images/trips' );
    	        $tripno = str_pad($trip->id, 6, '0', STR_PAD_LEFT);
    			$attachfile = $path . "/Trip".$tripno."_final.pdf";
    			$filename = "Trip".$tripno."_final.pdf";

//                 if ($params->get('tmpl_email', 1)) {
//                     // use the template system
//             		$viewLink = GatripsysHelper::getHTTPQuery(null, 'view', 'trip', 'id', $trip->id);
//             		$link = 'index.php?'.http_build_query($viewLink, '', '&amp;');
// 
//                     $item = new \stdClass();
//                     $mrbs = array();
// 
//         			foreach ($tcs as $tc) {
//         				$tcUser = GatripsysHelper::getSpecificUser($tc);
//         				$mrbs[] = $tcUser;
//         			}
//                     $mrbs[] = $leader;
// 
//                     $item->member_name = $member_name;
//                     $item->booker_name = $member_name;
//                     $item->leader_name = $leader->name;
//                     $item->leader_email = $leader->email;
//                     $item->leader = $leader;
//                     $item->trip_title = $trip->title;
//                     $item->trip_status = $trip->state;
//                     $item->dept_date_disp = $trip->dept_date_disp;
//                     $item->sitename = $sitename;
//                     $item->user = $user;
//                     $item->tcs = $tcs;
//                     $item->bcc = $bcc;
//                     $item->trip_details = $trip;
//                     $item->members = $mbrs;
// 
//                     GaemailHelper::sendEmailTmpl('com_gatripsys.tripfinal', $item, $link, $filename, $attachfile);
//                 } else {
        			$recipients = array();
        			$recipients[] = $user->email;
        			foreach ($tcs as $tc) {
        				$recipients[] = GatripsysHelper::getSpecificUser($tc)->email;
        			}

    				$subject = 'Finalised Trip by '.$member_name;
    				$sitename = '<a href="'.Uri::base().'index.php?option=com_gatripsys&view=trip&id='.$trip->id.'">'.$sitename.'</a>';
    				$body = '<p>'.Text::_('COM_GATRIPSYS_FINALISED_TRIP_BODY');
    				$body .= '</p><p> </p><p>'.$sitename.'</p>';
    				$body .= '<p>Be sure to be logged in to the website before clicking the link.</p>';
    				GaemailHelper::sendEmail($recipients, $body, $subject, $attachfile, 0, 0);
//				}
				Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_TC_NOTIFIED_MESSAGE'), 'message');
			} else {
				Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_NOTC_NOTIFIED_MESSAGE'), 'danger');
			}
        }


		return true;
	}

}
