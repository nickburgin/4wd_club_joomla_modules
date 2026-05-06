<?php
/**
 * @version     5.1.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\Data\DataObject;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Installer\Installer;
use \Joomla\CMS\User\User;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GanotificationsHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GapdfinvHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GaemailHelper;

//Load admin language file
$lang = Factory::getApplication()->getLanguage();
$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

/**
 * Invoice helper.
 */
class GainvoiceHelper
{
    /**
     * Method to check if an invoice is required for an attendance.
     * @param   obj   $user     User record object performing this check.
     * @param   array   $data     Attendee submitted data.
     * @param   int   $att_id     An attendee record id.
     * @param   obj   $params     Component parameters.
     * @return  boolean    True on success, false on failure
     */
    public static function checkInvoiceReq($user, $data, $att_id, $params)
	{
		$charge_trip = $params->get('charge_trip', 0);
		$inv_approval = $params->get('inv_approval', 1);
		$paid_apprv = $params->get('paid_apprv', 1);

		$trip = GatripsysHelper::getTripInformation($data['trip_id']);
		$trip->in_party = $data['in_party'];
		$trip->att_id = $att_id;

		if ($charge_trip && $trip->trip_cost > '0.00') {

    		$u  = GatripsysHelper::getSpecificUser($data['user_id']);
			if ($inv_approval) {
				if ($data['state'] == 1) {
					$inv = self::createInvoice($u, $trip);
					if ($inv) {
						$ok = GaemailHelper::sendEmail(array($u->email), 'Invoice Attached', 'Booking Invoice', $inv);
					}
					return $inv;
				}
			} else {
				if ($data['state'] == 0) {
					$inv = self::createInvoice($u, $trip);
					if ($inv) {
						$ok = GaemailHelper::sendEmail(array($u->email), 'Invoice Attached', 'Booking Invoice', $inv);
					}
					return $inv;
				}
			}

		}
		
		return true;
	}

    public static function getInvoiceForAttendee($att_id)
	{
		// get the users types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' i.*, u.name AS att_name, u.email AS att_email ');
		$query->select(' t.title AS trip_title ');
		$query->from(' #__gatripsys_invoices AS i ');
		$query->join('LEFT', ' #__gatripsys_attendees AS a ON a.id = i.att_id');
		$query->join('LEFT', ' #__users AS u ON u.id = i.user_id');
		$query->join('LEFT', ' #__gatripsys_trips AS t ON t.id = a.trip_id');
		$query->where(' i.state IN (1,2) ' );
		$query->where(' i.att_id = '. (int) $att_id );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        return false;
	    }

	}

    public static function getInvoice($id)
	{
		// get the users types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' i.* ');
		$query->select(' u.name AS att_name, u.email AS att_email ');
		$query->select(' t.title AS trip_title ');
		$query->from(' #__gatripsys_invoices AS i ');
		$query->join('LEFT', ' #__gatripsys_attendees AS a ON a.id = i.att_id');
		$query->join('LEFT', ' #__users AS u ON u.id = i.user_id');
		$query->join('LEFT', ' #__gatripsys_trips AS t ON t.id = a.trip_id');
		$query->where(' i.id = '. (int) $id );
		$db->setQuery((string)$query);

	    try {
	        $inv = $db->loadObject();
	    } catch (RuntimeException $e) {
	        return false;
	    }
	    
	    return $inv;

	}

    public static function sendInvoiceReminder()
	{
        $params = ComponentHelper::getParams('com_gatripsys');
        $incl_std_text  = $params->get('incl_std_text');
        $inv_pref  = $params->get('inv_pref');
        $sitename = Factory::getApplication()->get('fromname');

		// get the unpaid invoices from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.*, u.name, u.email ');
		$query->from(' #__gatripsys_invoices AS a ');
		$query->join('LEFT', ' #__users AS u ON u.id = a.user_id');
		$query->where(' a.state = 1 ' );
		$db->setQuery((string)$query);
	    try {
	        $invoices = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        return false;
	    }

		foreach ($invoices AS $inv) {
	        //$inv_user = GatripsysHelper::getSpecificUser($inv->user_id);
	        if ($params->get('mship_single',0)) {
	    		$inv_user = self::breakdownNamesFromUserID($inv->user_id);
				$member_name = self::combineNames($inv_user);
			} else {
	            $member_name = $inv->name;
			}
	
			$trip = GatripsysHelper::getTripFromAttend($inv->att_id);

			$body = '<p>Dear '.$member_name.',</p>';
			$body .= '<p>'.TEXT::sprintf('COM_GATRIPSYS_REMINDER_BODY',$trip->title);
	
			$body .= '</p><p> </p><p>'.$sitename .'</p>';
			$subject = 'Reminder of Outstanding Trip Invoice';
			$recipients = array();
			$recipients[] = $inv->email;
	
			$sentOK = GaemailHelper::sendEmail($recipients, $body, $subject, 0, 0, 0);
			if ($sentOK) {
				Factory::getApplication()->enqueueMessage('Reminder sent to '.$member_name, 'notice');
			}
		}


        return $sentOK;
	}

    public static function resendInv($data)
	{
        $params = ComponentHelper::getParams('com_gatripsys');
        $incl_std_text  = $params->get('incl_std_text');
        $inv_pref  = $params->get('inv_pref');
        $sitename = Factory::getApplication()->get('fromname');

		// get the users types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.* ');
		$query->from(' #__gatripsys_invoices AS a ');
		$query->where(' a.id = '. (int) $data['id'] );
		$db->setQuery((string)$query);

        if (!$db->execute()) {
            throw new \Exception(500, $db->getErrorMsg());
        } else {
            $inv = $db->loadObject();
        }
        $inv_user = GatripsysHelper::getSpecificUser($inv->user_id);
        if ($params->get('mship_single',0)) {
    		$inv_user = self::breakdownNamesFromUserID($inv->user_id);
			$member_name = self::combineNames($inv_user);
		}
		
		$trip = GatripsysHelper::getTripFromAttend($inv->att_id);

		$path = Path::clean( JPATH_SITE . '/images/trips/invoices' );
		$inv_file = $inv_pref.str_pad($data['id'], 6, '0', STR_PAD_LEFT).'.pdf';
		$attachfile = $path.'/'.$inv_file;

		$body = '<p>Dear '.$member_name.',</p>';
		$body .= '<p>'.TEXT::sprintf('COM_GATRIPSYS_INV_RESEND_BODY',$trip->title);

		$body .= '</p><p> </p><p>'.$sitename .'</p>';
		if (!empty($incl_std_text)) {
			$body	.= '<p> </p><p>'.$incl_std_text.'</p>';
		}
		$subject = 'Resending Invoice';
		$recipients = array();
		$recipients[] = $inv_user->email;

		$sentOK = GaemailHelper::sendEmail($recipients, $body, $subject, $attachfile, 0, 0);
		Factory::getApplication()->enqueueMessage('Resent '.$attachfile, 'notice');

        return $sentOK;
	}

	// * ------------------------   Invoice Creation processes   ----------------------------- * //

	public static function createInvoice($u = null, $trip = null)
	{

        $params = ComponentHelper::getParams('com_gatripsys');

        if ($trip) {
            $bank_desc  = $params->get('bank_desc');
            $bank_bsb  = $params->get('bank_bsb');
            $bank_accnt  = $params->get('bank_accnt');
            $payment_desc  = $params->get('payment_desc');
            $inv_foot_text  = $params->get('inv_foot_text');
            $inv_pref  = $params->get('inv_pref', 'TRIP');
            $mship_single  = $params->get('mship_single');

            // get the date
            $app		= Factory::getApplication();
			$tz = Factory::getConfig()->get('offset');
			$date = Factory::getDate('now', $tz);
			$today = date_format($date,'Y-m-d H:i:s');
			$inv_date = date_format($date,'Y-m-d');

            $user	= GatripsysHelper::getSpecificUser();

    		// load new invoice record
    		$att_cost = $trip->trip_cost * $trip->in_party;
    		$new_inv = new \stdClass();
    		$new_inv->id = 0;
    		$new_inv->state = 1;
    		$new_inv->user_id = $u->id;
    		$new_inv->att_id = $trip->att_id;
    		$new_inv->created_by = $user->id;
    		$new_inv->created_date = $today;
    		$new_inv->invoice_amt = $att_cost;
    		$new_inv->comment = $trip->title;

		    try {
		        // If it fails, it will throw a RuntimeException
		        $result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__gatripsys_invoices', $new_inv, 'id');
		        Factory::getApplication()->enqueueMessage('New Invoice for '.$u->name.' successful', 'notice');
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage().' New Invoice Failed', 'danger');
		        return false;
		    }

            if ($result) {
                $nextinv = $new_inv->id;
                $nextinv  = str_pad($nextinv, 6, '0', STR_PAD_LEFT);
                $app->setUserState('com_gatripsys.nextinv.data', $inv_pref.$nextinv);
                $app->setUserState('com_gatripsys.user.data', $u);
                $tot_cost = 0;
                $lh = 4;

                $profile = UserHelper::getProfile($u->id);
                //class instantiation
                //$pdf=new PDF("P","in","Letter"); // Legal Letter size
                //$pdf=new PDF("L","mm","A4");     // Landscape
                $pdf=new GapdfinvHelper("P","mm","A4");

                $pdf->SetMargins(10,10,10);

                $pdf->AddPage();

		        // client header information
                $pdf->SetFont('Arial','B',10);
                $pdf->SetTextColor(0,100,148); // r,g,b
		        $pdf->SetFont('Arial','B',10);
		        $pdf->Cell(30, $lh, "To:", 0, 0, "L"); // Sets an indent of 20mm
		        $pdf->SetFont('Arial','',12);
		        if ($mship_single) {
					$member = self::breakdownNamesFromUserID($u->id);
					$mshipname = self::combineNames($member);
					$pdf->Cell(160, $lh, $mshipname, 0, 1, "L");
				} else {
					$pdf->Cell(160, $lh, $u->name, 0, 1, "L");
				}
		        $pdf->Cell(30, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
		        $pdf->Cell(160, $lh, $profile->profile['address1'], 0, 1, "L");
		        if ($profile->profile['address2']) {
		            $pdf->Cell(30, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
		            $pdf->Cell(160, $lh, $profile->profile['address2'], 0, 1, "L");
		        }
		        $pdf->Cell(30, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
		        $pdf->Cell(160, $lh, $profile->profile['city'].", ".$profile->profile['postal_code'], 0, 1, "L");
		        $pdf->Cell(0, $lh, "", 0, 1, "L");
		        $pdf->Cell(0, $lh, "", 0, 1, "L");
		        $pdf->Cell(0, $lh, "", 0, 1, "L");
		        $pdf->Cell(0, $lh, "", 0, 1, "L");
		        $pdf->Cell(0, $lh, "", "T", 1, "C");

                $pdf->SetTextColor(0,0,0); // r,g,b
                $pdf->SetFont('Arial','B',8);
                $pdf->Cell(25, $lh, "Date", 0, 0, "L");
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(90, $lh, "Invoice Description", 0, 0, "L");
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(5, $lh, "Attendees", 0, 0, "C");
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(25, $lh, "Cost Each", 0, 0, "R");
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(25, $lh, "Amount", 0, 1, "R");
                $pdf->SetFont('Arial','',8); // font-family, font-weight (B), font-size


				$tot_cost = $tot_cost + $att_cost;
                $pdf->Cell(25, $lh, $inv_date, 0, 0, "L");
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
                $x = $pdf->GetX();
                $y = $pdf->GetY();
                $pdf->MultiCell(90, $lh, $trip->title, 0, "L");
                $pdf->SetXY($x + 90, $y);
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(5, $lh, $trip->in_party, 0, 0, "C");
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(25, $lh, number_format($trip->trip_cost,2), 0, 0, "R");
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(25, $lh, number_format($att_cost,2), 0, 1, "R");

                $pdf->Ln(4);

                $lh = 5;
				$pdf->SetFont('Arial','B',8);
                $pdf->Cell(160, $lh, "Total Cost", "T", 0, "R");
                $pdf->Cell(5, $lh, " ", "T", 0, "L"); // Sets an spacer
                $pdf->Cell(25, $lh, number_format($tot_cost,2), "T", 1, "R");

                $pdf->SetFont('Arial','',8);
                $pdf->Cell(0, $lh, "", "T", 1, "C");
                $lh = 4;
                $pdf->MultiCell(0, $lh, $inv_foot_text, 0, "L");
                $lh = 5;
                $pdf->SetFont('Arial','B',8);
                $pdf->Cell(0, $lh, "EFT to: ".$bank_desc, 0, 1, "C");
                $pdf->Cell(0, $lh, "BSB: ".$bank_bsb.' Accnt: '.$bank_accnt, 0, 1, "C");
                $pdf->Cell(0, $lh, $payment_desc, 0, 1, "C");
                $pdf->SetFont('Arial','B',10);
                $pdf->SetTextColor(0,100,148); // r,g,b
                $pdf->Cell(0, $lh, "Thank you", 0, 1, "C");
                $pdf->SetTextColor(64,64,64); // r,g,b

                $path = Path::clean( JPATH_SITE . '/images/trips/invoices' );
                $pdf->Output($path . "/".$inv_pref.$nextinv.".pdf", "F");

                $attachfile = $path.'/'.$inv_pref.$nextinv.'.pdf';

                return $attachfile;
		    } else {
				Factory::getApplication()->enqueueMessage('Result from Invoice Insert FALSE ', 'danger');
		        return false;
		    }

        }

	}

    /**
     * Method to cancel an invoice for an attendance.
     * @param   int   $id     An attendance record id.
     * @return  boolean    True on success, false on failure
     */
    public static function cancelInvoice($id)
	{
		$u	= GatripsysHelper::getSpecificUser();
		$today	= GatripsysHelper::getTodaysDate();
		$inv = self::getInvoice($id);

		if ($inv->state == 2) {
			// trigger refund notification because invoice is in paid state
			$refundReq = self::refundNotif($inv);
			Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_REFUND_NOTIFICATION'),'notice');
			$state = 3;
			$refund = ' - Refund notification Sent.';
	    } else {
			$state = -2;
			$refund = ' - No refund necessary (unpaid).';
	    }

        $inv->modified_by = $u->id;
        $inv->modified_date = $today;
        $inv->comment = $inv->comment."\r\nCancelled by ".$u->name.$refund;
        $inv->state = $state;
        
        // remove extra fields for the save
        unset($inv->att_email);
        unset($inv->att_name);
        unset($inv->trip_title);

	    try {
	        $result = Factory::getContainer()->get('DatabaseDriver')->updateObject('#__gatripsys_invoices', $inv, 'id');
	        Factory::getApplication()->enqueueMessage('Invoice record updated successfully','notice');
	        return true;
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage('Update Invoice Record Failed - '.$e->getMessage(),'danger');
	        return false;
	    }

	}

    /**
     * Method to notify paid attendee of a trip that refund is required due to cancellation of an attendance.
     * @param   obj   $inv     Invoice for Attendance record object.
     * @return  boolean    True on success, false on failure
     */
    public static function refundNotif($inv)
	{
        $today = GatripsysHelper::getTodaysDate();
		$user = GatripsysHelper::getSpecificUser();
		$params = ComponentHelper::getParams('com_gatripsys');
		$refund_notif  = $params->get('refund_notif', null);
		$finance_cat  = $params->get('finance_cat',0);
		$mship_single  = $params->get('mship_single',0);

		$incl_finance  = $params->get('incl_finance', 0);

		if ($incl_finance) {
			// setup a finance trans for the refund
			$fin_trans = new \stdClass();
			$fin_trans->id = 0;
			$fin_trans->state = 0;
			$fin_trans->cat_id = $finance_cat;
			$fin_trans->created_by = $user->id;
			$fin_trans->created_date = $today;
			$fin_trans->tran_date = $today;
			$fin_trans->user_id = $inv->user_id;
			$fin_trans->tran_amount = ($inv->invoice_amt * -1);
			$fin_trans->tran_ref = 'Trip Invoice Refund '.$inv->id;
			$fin_trans->tran_desc = $inv->trip_title.' - Refund';
			$fin_trans->tran_type = 'E';
			$fin_trans->comment = 'Refund from Trip Invoice';
		    try {
		        Factory::getContainer()->get('DatabaseDriver')->insertObject('#__gafinance_transactions', $fin_trans);
		        Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_REFUND_TRANS_SUCC'),'notice');
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_REFUND_TRANS_FAIL'),'notice');
		    }
		}

		// get names of attendee using the user_id from the attendance record
		if ($mship_single) {
			$attendee = self::breakdownNamesFromUserID($inv->user_id);
			$attend_name = self::combineNames($attendee);
		} else {
			$attend_name = $inv->att_name;
		}

		$recipients = array($inv->att_email, $refund_notif);
		$subject = Text::_('COM_GATRIPSYS_REFUND_SUBJECT');
		$body = Text::sprintf('COM_GATRIPSYS_REFUND_BODY',$attend_name, $inv->trip_title);
		$sentOK = GaemailHelper::sendEmail($recipients, $body, $subject, null, 0, 0);
		
		if ($sentOK) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_REFUND_SENT_LABEL'),'notice');
		} else {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_REFUND_SENTFAILED_LABEL'),'notice');
		}
        return true;
	}

	/**
	 * Break up the name fields and form a display name version
	 * @param   object  $member  data including name, partner and the breakup firstnames and surnames.
	 * @return  string	combined name for the membership display
	 */
    public static function combineNames($member = null)
	{
        if (!$member) {
			$result = 'No Member Set';
		} else {
			$member->partner = str_replace('"','',$member->partner ?? '');
	        $member->surnamep = str_replace('"','',$member->surnamep ?? '');
	        $member->firstnamep = str_replace('"','',$member->firstnamep ?? '');
			if (empty($member->partner) || $member->partner == '' || $member->partner == ' ') {
				$result = isset($member->name) ? $member->name : 'Name Unavailable';
			} else {
				if (trim($member->surname) == trim($member->surnamep)) {
					$result = trim($member->firstname). ' & ' .trim($member->firstnamep) . ' ' . trim($member->surname);
				} else {
					$result = trim($member->name). ' & ' .trim($member->partner);
				}
			}
		}

		return $result;

	}

	/**
	 * Get all the name fields as an object
	 * @param   id  $user id.
	 * @return  object	membership names object
	 */
    public static function breakdownNamesFromUserID($id = null)
	{
		$params = ComponentHelper::getParams('com_gatripsys');
		$profsuf  = $params->get( 'prof_pref' );

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ' );
        $query->select(' substr(name, 1, LOCATE(" ",name)) AS firstname ');
        $query->select(' if(substr(name, (LOCATE(" ",name)+1), 1)="&",substr(name, LOCATE(" ",name,(LOCATE(" ",name)+3))+1),substr(name, LOCATE(" ",name)+1)) AS surname ');
        $query->select(' if(h.profile_value IS NULL, "", h.profile_value) AS partner ');
        $query->select(' substr(h.profile_value, 1, LOCATE(" ",h.profile_value)) AS firstnamep ');
        $query->select(' substr(h.profile_value, LOCATE(" ",h.profile_value)+1) AS surnamep ');
        $query->select(' if(j.profile_value IS NULL, "", j.profile_value) AS inc_altemail ');
        $query->select(' if(k.profile_value IS NULL, "", k.profile_value) AS altemail ');
        $query->from('#__users AS a');
        $query->join('LEFT', ' #__user_profiles AS h ON a.id = h.user_id AND h.profile_key = "profile'.$profsuf.'.partner" ');
        $query->join('LEFT', ' #__user_profiles AS j ON a.id = j.user_id AND j.profile_key = "profile'.$profsuf.'.inc_altemail" ');
        $query->join('LEFT', ' #__user_profiles AS k ON a.id = k.user_id AND k.profile_key = "profile'.$profsuf.'.altemail" ');
        $query->where('a.id = ' . (int) $id );
        $db->setQuery($query);
		try {
			$member =  $db->loadObject();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			$member = false;
		}

		return $member;

	}
}
