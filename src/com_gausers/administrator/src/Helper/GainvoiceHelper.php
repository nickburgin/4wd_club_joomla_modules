<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Data\DataObject;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Table\Table;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Date\Date;
use Joomla\CMS\User\User;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;

/**
 * Gausers helper.
 */
class GainvoiceHelper
{
    public static function getNextInv()
	{
		// get the users types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' max(id)+1 AS next_inv ');
		$query->from(' #__gausers_invoices ');
		$db->setQuery((string)$query);

	    try {
	        $result = $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Get Next Invoice Failed', 'danger');
	        $result = false;
	    }

        return $result;

    }

    public static function getUnpaidInvoices()
	{
		// get the users types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' * ');
		$query->from(' #__gausers_invoices ');
		$query->where(' state = '. (int) 1 );
		$db->setQuery((string)$query);

	    try {
	        $result = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Get All Invoices Failed', 'danger');
	        $result = false;
	    }

        return $result;
    }

    public static function setupFirstInvoice($userId)
	{
        return $userId;
    }

    /**
     * Setup the new end date for the next period of membership
     * @params $date object of the invoice_date from parameters
     * @params $defMship object of the default membership record
     * @return date string
     */
    public static function setupOldEndDate($params, $defMship)
	{
        $oldStartDate = new Date(strtotime($params->get('invoice_date') ?? ''));  //2025-07-01
        $oldEndDate = $oldStartDate->modify('+'.$defMship->mship_term.' '.$defMship->term_type);
        $oldEndDate = $oldEndDate->modify('-1 DAY');  //2025-06-30

        $oldExpDate = date_format($oldEndDate,'Y-m-d');  //2025-06-30

        return $oldExpDate;
    }

    /**
     * Setup the new end date for the next period of membership
     * @params $date object of the invoice_date from parameters
     * @params $defMship object of the default membership record
     * @return date string
     */
    public static function setupNewEndDate($params, $defMship)
	{
        $invoice_date = new Date(strtotime($params->get('invoice_date') ?? ''));
        $oldEndDate = $invoice_date->modify('+'.$defMship->mship_term.' '.$defMship->term_type);    //2025-07-01
        $oldEndDate = $oldEndDate->modify('-1 DAY');  //2025-06-30

        $newEndDate = $oldEndDate->modify('+'.$defMship->mship_term.' '.$defMship->term_type); //2025-06-30
        $newExpDate = date_format($newEndDate,'Y-m-d');

        return $newExpDate;
    }

    public static function getLastInvoiceMship($userId)
	{
		// get the users types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.*, m.title, m.subscrib_amt, m.discount_amt, m.joining_fee, m.term_type, m.mship_term ');
		$query->from(' #__gausers_invoices as a');
		$query->join('LEFT', '#__gausers_mshiptypes as m ON m.id = a.mship_id');
		$query->where(' a.user_id = '. (int) $userId );
		$query->where(' a.state = '. (int) 2 );
		$query->order(' a.id DESC LIMIT 1 ' );
		$db->setQuery((string)$query);

	    try {
	        $lastMship = $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Failed to Get Last Paid Invoice for Member', 'danger');
	        return false;
	    }

        if (!empty($lastMship)) {
            return $lastMship;
        } else {
            return 0;
        }
    }

    /**
     * Check the mship reference in case an update is required
     * @params $userId = the user id reference
     * @params $mshipId = mship id reference set in members form
     * @return boolean
     */
    public static function checkMshipType($userId, $mshipId)
	{
        $inv = self::getLastInvoiceMship($userId);
        if (!$inv) {
            Factory::getApplication()->enqueueMessage('No previous Invoice record', 'Warning');
            return true;
        } else {
            if ($inv->mship_id == $mshipId) {
                // do nothing
            } else {
                //update the invoice record
                $inv->mship_id = $mshipId;
        	    try {
        	        $result = Factory::getContainer()->get('DatabaseDriver')->updateObject('#__gausers_invoices', $inv, 'id');
        	    } catch (RuntimeException $e) {
        	        Factory::getApplication()->enqueueMessage($e->getMessage().' Updating MshipType in last Invoice Failed', 'danger');
        	        return false;
        	    }
            }
        }
        return $inv;
    }

    /**
     * Check for need of invoices to be sent
     * @params $mshp = the membership object
     * @params $params = component parameters
     * @return boolean
     */
    public static function checkNewInvoicesDue($mship, $params)
	{
		$lastInvDate = strtotime($params->get('invoice_date'));
        $date = Factory::getDate();
        $prevDate = $lastInvDate->modify('+'.$mship->mship_term.' '.$mship->mship_type);
        $today = date_format($date,'Y-m-d H:i:s');

        // get the users types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' * ');
		$query->from(' #__gausers_invoices ');
		$query->where(' state = '. (int) 1 );
		$db->setQuery((string)$query);

	    try {
	        return (!empty($db->loadObjectList()) || $db->loadObjectList() > 0) ? true : false;
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' - Invoices check if required', 'danger');
	        return false;
	    }

    }

    /**
     * Create invoice record and pdf
     * This is triggered from Currentuserform Model for
     * individual invoices being generated and from Invoice Model
     * for each of the member records when renewals are triggered.
     * @params $id = this identifies if new member (0)
     * @params $u = the user object related to the new invoice
     * @params $params = component parameters
     * @return $attachfile = invoice pdf file
     */
    public static function mainInvoiceCreation($id, $u, $params)
	{
        Factory::getApplication()->setUserState('com_gausers.user.data', $u);
        
        // test for new user and load old expiry date
        if (!isset($u->oldExpDate)) {
            $oldStartDate = new Date(strtotime($params->get('invoice_date') ?? ''));
            $oldEndDate = $oldStartDate->modify('-1 DAY');
            $u->oldExpDate = date_format($oldEndDate,'Y-m-d');
        }

		// set up expiry date based on last invoice run and mship type
        if ($u->mship->subscrib_amt == 0.00) {
            // then treat this as a one off temporary membership
            $lastInvDate = new Date('NOW');
        } else {
            if (isset($u->mship->end_date)) {
                $lastInvDate = new Date(strtotime($u->mship->end_date));
            } else {
                $lastInvDate = new Date(strtotime($u->oldExpDate));
            }
        }

        $lastExpDate = date_format($lastInvDate,'Y-m-d');
        $eDate = $lastInvDate->modify('+'.$u->mship->mship_term.' '.$u->mship->term_type);
        $expDate = date_format($eDate,'Y-m-d');
        $amt = self::calcInvoiceAmt($id, $u, $lastExpDate, $params);

        // check if old member returning
        if (isset($u->mship->end_date) && $u->mship->end_date < $u->oldExpDate) {
            // set new expiry date to be the new expiry date and not there old exp date plus 1 year
            $expDate = $u->newExpDate;
            Factory::getApplication()->enqueueMessage('Past member returning - '.$u->name, 'notice');
        }
        
        Factory::getApplication()->enqueueMessage($amt .' - '. $u->name.' - joined: '.$u->regoDate . ' - lastExp: '.$lastExpDate . ' - newExp: '.$expDate, 'notice');

        $invRec = self::createNewInvoiceRec($u->id, $amt, $u->mship, $expDate);

	    $nextinv  = str_pad($invRec->id, 6, '0', STR_PAD_LEFT);
		Factory::getApplication()->setUserState('com_gausers.nextinv.data', $nextinv);

        $data = array();
		$data['nextinv'] = $nextinv;
		$data['invRec'] = $invRec;
		$data['mship'] = $u->mship;

        return self::createAdHocPDF($data, $id, $params);

    }

    /**
     * Calculate invoice amount due
     * @params $id = this identifies if new member (0)
     * @params $u = the user object related to the new invoice
     * @params $params = component parameters
     * @params $mship = the object for the membership type record
     * @return $amt = calculated amount of the invoice
     */
    public static function calcInvoiceAmt($id, $u, $lastExpDate, $params)
	{
        $minProrata  = $params->get('min_prorata', 0);
        $discAllow  = $params->get('discount_allowed', 0);
        $discSwitch  = $params->get('discount_switch', 0);
        $allowProrata  = $params->get('allow_prorata', 0);
        $yearProrata  = $params->get('year_prorata', 2);
        // financial year = 1 , 2 calendar year
        $mship_period  = $params->get('mship_period', 1);
        
        $oldProDate = new Date(strtotime($params->get('invoice_date') ?? ''));
        $oldProDate = date_format($oldProDate,'Y-m-d');

        $membership_dues  = $u->mship->subscrib_amt;

        // check for a discount switch
        if ($discAllow && (isset($u->$discSwitch) && $u->$discSwitch) ) {
			$membership_dues = $u->mship->discount_amt;
        }

        /* ---- go through the prorata process if necessary  ----- */
        if ($allowProrata) {
            // if existing user, then this is a renewal
            if ($id) {
                // check for second term for renewal to calc prorata
                if ($yearProrata == 2 && $u->regoDate > $oldProDate) {
                    $proRata = self::calcProRata($u, $oldProDate, $membership_dues);
                    if ($proRata->prorata_amount >= $minProrata) {
                        $membership_dues = $proRata->prorata_amount;
                    } else {
                        $membership_dues = $minProrata;
                    }
                }
            // else new user so full unless prorata set on first year
            } else {
                // check for first term
                if ($yearProrata == 1) {
                    $proRata = self::calcProRata($u, $lastExpDate, $membership_dues);
                    if ($proRata->prorata_amount >= $minProrata) {
                        $membership_dues = $proRata->prorata_amount;
                    } else {
                        $membership_dues = $minProrata;
                    }
                }
            }
        }


        // if new user add the joining fee
        if (!$id) {
            $membership_dues = $membership_dues + $u->mship->joining_fee;
        }

        return $membership_dues;
    }

	/*
	 *  Calculate Pro-rata amount for user
     *  query returns
	 * 		end_this_year,
	 *  	prorata_days,
	 *  	prorata_amount
	*/
	public static function calcProRata($u, $lastExpDate, $membership_dues = 0)
	{
        if ($u->mship->term_type == 'YEAR') {
           $multDays = 365 * $u->mship->mship_term;
        } elseif ($u->mship->term_type == 'MONTH') {
           $multDays = 30 * $u->mship->mship_term;
        } elseif ($u->mship->term_type == 'DAY') {
           $multDays = 7 * $u->mship->mship_term;
        } else {
           $multDays = 1;
        }

        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query	= $db->getQuery(true);
        $query->clear();
        $query->select(' DATE_ADD( '.$db->Quote($lastExpDate).', INTERVAL '.$u->mship->mship_term.' '.$u->mship->term_type.' ) as end_this_year  ');
        $query->select(' DATEDIFF(DATE_ADD( '.$db->Quote($lastExpDate).', INTERVAL '.$u->mship->mship_term.' '.$u->mship->term_type.' ), '.$db->Quote($u->registerDate).') as prorata_days  ');
        $query->select(' ROUND((( '.$db->Quote($membership_dues).' / '.$multDays.') * DATEDIFF(DATE_ADD( '.$db->Quote($lastExpDate).', INTERVAL '.$u->mship->mship_term.' '.$u->mship->term_type.' ), '.$db->Quote($u->registerDate).')),1) as prorata_amount  ');
        $db->setQuery((string)$query);

		try {
             $prorata = $db->loadObject();
             Factory::getApplication()->enqueueMessage('Calculating Pro-rata Amount for '.$u->name.' successful', 'message');
        } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Calculating Pro-rata Amount for '.$u->name.' failed', 'danger');
	        $prorata = false;
        }
		return $prorata;
	}

    /**
     * Gcreate a new invoice record
     * @params $id = user id
     * @params $amt = calculated amount of the invoice
     * @return object invoice record just created
     */
    public static function createNewInvoiceRec($id = 0, $amt = 0.00, $mship = null, $exp_date = null)
	{
		$user = Factory::getApplication()->getIdentity();
		$today = Factory::getDate()->toSql();

    	// load new invoice record
    	$new_inv = new \stdClass();
    	$new_inv->id = 0;
    	$new_inv->user_id = $id;
    	$new_inv->checked_out = null;
    	$new_inv->checked_out_time = null;
    	$new_inv->created_by = $user->id;
    	$new_inv->created_date = $today;
    	$new_inv->invoice_amt = $amt;
    	$new_inv->mship_id = $mship->mship_id;
    	$new_inv->end_date = $exp_date;
    	if ($amt === 0.00) {
            $new_inv->paid_date = $today;
            $new_inv->state = 2;
        } else {
            $new_inv->state = 1;
        }
	    try {
	        $result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__gausers_invoices', $new_inv, 'id');
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Saving New Invoice Data Failed', 'danger');
	        return false;
	    }
        return $new_inv;
        
    }

    public static function resendInv($data)
	{
        $params = ComponentHelper::getParams('com_gausers');
        $incl_std_text  = $params->get('incl_std_text');
        $inv_loc  = $params->get('invoice_loc', 'images/members/invoices');
        $sitename = Factory::getApplication()->get('fromname');

		// get the users types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.* ');
		$query->from(' #__gausers_invoices AS a ');
		$query->where(' a.id = '. (int) $data['id'] );
		$db->setQuery((string)$query);

        if (!$db->execute()) {
            throw new \Exception(500, $db->getErrorMsg());
        } else {
            $inv = $db->loadObject();
        }
        $inv_user = GausersHelper::getSpecificUser($inv->user_id);

		$path = Path::clean( JPATH_SITE . '/' . $inv_loc );
		$inv_file = 'Invoice'.str_pad($data['id'], 6, '0', STR_PAD_LEFT).'.pdf';
		$attachfile = $path.'/'.$inv_file;

        $subject = Text::_('COM_GAUSERS_INVOICE_EMAIL_RESENDSUBJECT');
        $body = Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SALUTATION',$inv_user->name);
        $body .= Text::_('COM_GAUSERS_INVOICE_EMAIL_BODY');
        $body .= Text::sprintf('COM_GAUSERS_INVOICE_EMAIL_SIGNOFF',$sitename);

		if (!empty($incl_std_text)) {
			$body	.= '<p> </p><p>'.$incl_std_text.'</p>';
		}

		$sentOK = GaemailHelper::sendEmail(array($inv_user->email), $body, $subject, $attachfile);
		Factory::getApplication()->enqueueMessage('Resent '.$attachfile, 'message');

        return $sentOK;
	}

	// * ------------------------   Invoice Creation of PDF   ----------------------------- * //
	/*
	 *  This is a mechanism to create an invoice for a new member
	 *  thus in addition to the membership fee there will generally be a joining fee
	 *  @params array() $data with some elements for this record
	 *  @params int $oldMbr 0 = new member 1 = old renewal member
	*/
	public static function createAdHocPDF($data, $oldMbr, $params)
	{
        if ($data['invRec']->invoice_amt == 0) { return false; }

        $today = $data['invRec']->created_date;
		$app = Factory::getApplication();
		$app->getLanguage()->load('com_gausers', JPATH_ADMINISTRATOR);

		$membership_desc  = $params->get('membership_desc');
		$bank_desc  = $params->get('bank_desc');
		$bank_bsb  = $params->get('bank_bsb');
		$bank_accnt  = $params->get('bank_accnt');
		$finance_accnt  = $params->get('finance_accnt');
		$pp_accnt  = $params->get('pp_accnt');
		$pp_img  = $params->get('pp_img');
		$pp_logo = HTMLHelper::cleanImageURL($pp_img);
		$ppExt = strtoupper(substr($pp_logo->url ?? '', -3));
		$payment_desc  = $params->get('payment_desc');
        $inv_loc  = $params->get('invoice_loc', 'images/members/invoices');
		$inv_foot_text  = $params->get('inv_foot_text');

        $membership_dues  = $data['mship']->subscrib_amt;
        $joining_fee  = $data['mship']->joining_fee;
        $discount_dues  = $data['mship']->discount_amt;
        $convert = isset($data['conversion']) && $data['conversion'] ? true : false;
        $tot_cost = $data['invRec']->invoice_amt;

        if (!$oldMbr) {
            if ($tot_cost < ($membership_dues + $joining_fee)) {
                $membership_desc = $membership_desc.' - discount applied';
            }
        } else {
            if ($tot_cost == ($membership_dues + $joining_fee)) {
                // reset oldmember as this is a re-generate of the pdf
                $oldMbr = 0;
            }
            if ($convert) {
                if ($tot_cost < ($membership_dues + $joining_fee)) {
                    $membership_desc = $membership_desc.' - discount applied (conversion of membership)';
                    $convDisc = ($membership_dues + $joining_fee - $tot_cost) * -1;
                }
            } else {
                if ($tot_cost < $membership_dues) {
                    $membership_dues = $tot_cost;
                    $membership_desc = $membership_desc.' - discount applied';
                }
            }
        }

		$app->setUserState('com_gausers.invoice_amt.data',$tot_cost);

        // set up family member names associated with this invoice
        $u =  $app->getUserState('com_gausers.user.data');
        if (isset($u->family_mbrs) && $u->family_mbrs) {
            $family = '(includes: ';
            foreach ($u->family_mbrs AS $fmbr) {
                $family .= GausersHelper::getSpecificUser($fmbr->id)->name .', ';
            }
            $family = substr($family,0,-2).')';
        } else {
            $family = '';
        }

		$lh = 4;
        //class instantiation
        //$pdf=new PDF("P","in","Letter"); // Legal Letter size
        //$pdf=new PDF("L","mm","A4");     // Landscape
        $pdf=new GapdfHelper("P","mm","A4");

		$pdf->SetMargins(10,10,10);

        $pdf->AddPage();

        $pdf->SetFont('Arial','B',8);
        $pdf->Cell(25, $lh, "Date", 0, 0, "L");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
        $pdf->Cell(100, $lh, "Invoice Description", 0, 0, "L");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
        $pdf->Cell(25, $lh, "Dues", 0, 0, "R");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
        $pdf->Cell(25, $lh, "Amount", 0, 1, "R");
        $pdf->SetFont('Arial','',8); // font-family, font-weight (B), font-size

		if (!$oldMbr) {
			$pdf->Cell(25, $lh, HTMLHelper::date($today, Text::_('COM_GAUSERS_DISPLAY_DATE')), 0, 0, "L");
			$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			$x = $pdf->GetX();
			$y = $pdf->GetY();
			$pdf->MultiCell(100, $lh, Text::_('COM_GAUSERS_JOINING_FEE_DESC'), 0, "L");
			$pdf->SetXY($x + 100, $y);
			$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			$pdf->Cell(25, $lh, number_format($joining_fee,2), 0, 0, "R");
			$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			$pdf->Cell(25, $lh, number_format($joining_fee,2), 0, 1, "R");
		} elseif ($convert) {
			$pdf->Cell(25, $lh, HTMLHelper::date($today, Text::_('COM_GAUSERS_DISPLAY_DATE')), 0, 0, "L");
			$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			$x = $pdf->GetX();
			$y = $pdf->GetY();
			$pdf->MultiCell(100, $lh, Text::_('COM_GAUSERS_JOINING_FEE_DESC'), 0, "L");
			$pdf->SetXY($x + 100, $y);
			$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			$pdf->Cell(25, $lh, number_format($joining_fee,2), 0, 0, "R");
			$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			$pdf->Cell(25, $lh, number_format($joining_fee,2), 0, 1, "R");
			$pdf->Cell(25, $lh, HTMLHelper::date($today, Text::_('COM_GAUSERS_DISPLAY_DATE')), 0, 0, "L");
			$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			$x = $pdf->GetX();
			$y = $pdf->GetY();
			$pdf->MultiCell(100, $lh, Text::_('COM_GAUSERS_CONVDISC_DESC'), 0, "L");
			$pdf->SetXY($x + 100, $y);
			$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			$pdf->Cell(25, $lh, number_format($convDisc,2), 0, 0, "R");
			$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
			$pdf->Cell(25, $lh, number_format($convDisc,2), 0, 1, "R");
		}

		// because we are using the created_date of the invoice record I'm using UTC so no change to the displayed date
        $pdf->Cell(25, $lh, HTMLHelper::date($today, Text::_('COM_GAUSERS_DISPLAY_DATE'), 'UTC'), 0, 0, "L");
		$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
		$x = $pdf->GetX();
		$y = $pdf->GetY();
		$pdf->MultiCell(100, $lh, $membership_desc, 0, "L");
		$pdf->SetXY($x + 100, $y);
		$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
		$pdf->Cell(25, $lh, number_format($membership_dues,2), 0, 0, "R");
		$pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets an spacer
		$pdf->Cell(25, $lh, number_format($membership_dues,2), 0, 1, "R");

		$pdf->Ln(4);

		$lh = 5;
		$pdf->SetFont('Arial','B',8);
		$pdf->Cell(160, $lh, "Total Cost", "T", 0, "R");
		$pdf->Cell(5, $lh, " ", "T", 0, "L"); // Sets an spacer
		$pdf->Cell(25, $lh, number_format($tot_cost,2), "T", 1, "R");
		
		if ($family > '') {
            $pdf->SetFont('Arial','',8);
    		$pdf->Cell(0, $lh, "", "T", 1, "C");
    		$lh = 4;
    		$pdf->MultiCell(0, $lh, $family, 0, "L");
    		$lh = 5;
        }

        $pdf->SetFont('Arial','',8);
		$pdf->Cell(0, $lh, "", "T", 1, "C");
		$lh = 4;
		$pdf->MultiCell(0, $lh, $inv_foot_text, 0, "L");
		$lh = 5;
		$pdf->SetFont('Arial','B',8);
		if (!$params->get('incl_finance', 0)) {
            $pdf->Cell(0, $lh, "EFT to: ".$bank_desc, 0, 1, "C");
    		$pdf->Cell(0, $lh, "BSB: ".$bank_bsb.' Accnt: '.$bank_accnt, 0, 1, "C");
		} else {
            //check if account is set
            if ($finance_accnt) {
                $finAccnt = GausersHelper::getRecord("#__gafinance_accounts", "id", $finance_accnt);
                $pdf->Cell(0, $lh, "EFT to: ".$finAccnt->accnt_name, 0, 1, "C");
        		$pdf->Cell(0, $lh, "BSB: ".$finAccnt->accnt_bsb.' Accnt: '.$finAccnt->accnt_number, 0, 1, "C");
            } else {
                $pdf->Cell(0, $lh, "EFT to: ".$bank_desc, 0, 1, "C");
        		$pdf->Cell(0, $lh, "BSB: ".$bank_bsb.' Accnt: '.$bank_accnt, 0, 1, "C");
            }
        }
		if ($params->get('pp_pay')) {
            $pdf->Cell(0, $lh, "PayPal payments can be made via this link.", 0, 1, "C");
    		$x = $pdf->GetX();
    		$y = $pdf->GetY();
            $pdf->Image(JPATH_SITE."/".$pp_logo->url, 69, $y, 70, 7, $ppExt, $pp_accnt);
            $pdf->Cell(0, 10, "", 0, 1, "C");
        }
		$pdf->Cell(0, $lh, $payment_desc, 0, 1, "C");
		$pdf->SetFont('Arial','B',10);
		$pdf->SetTextColor(0,100,148); // r,g,b
		$pdf->Cell(0, $lh, "Thank you", 0, 1, "C");
		$pdf->SetTextColor(64,64,64); // r,g,b

		$path = Path::clean( JPATH_SITE . '/' . $inv_loc );
        $pdf->Output($path . "/Invoice".$data['nextinv'].".pdf", "F");

		$attachfile = $path.'/Invoice'.$data['nextinv'].'.pdf';

        return $attachfile;

	}

}
