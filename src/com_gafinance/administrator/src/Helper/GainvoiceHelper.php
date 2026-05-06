<?php
/**
 * @version    5.1.0
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Data\DataObject;
use Joomla\CMS\Date\Date;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Installer\Installer;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\MVC\Model\ItemModel;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GafinanceHelper;
use \GlennArkell\Component\Gafinance\Administrator\Helper\GapdfHelper;

/**
 * Invoice helper.
 */
class GainvoiceHelper
{
    public static function getInvoice($id)
	{
        // get the invoice data
        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query	= $db->getQuery(true);
        $query->select(' a.client_id, a.invoice_cost, c.name, c.cont_name, c.email, c.address, c.phone, a.id, a.comment, a.invoice_desc ');
        $query->from(' #__gafinance_invoices as a ');
        $query->join('LEFT', '#__gafinance_patrons as c on a.client_id = c.id');
        $query->select(' accnt.accnt_name, accnt.accnt_bsb, accnt.accnt_number ');
        $query->join('LEFT', ' #__gafinance_accounts as accnt on accnt.id = a.accnt_id');
        $query->where(' a.id = '.(int) $id);
        $query->order(' c.name ' );
        $db->setQuery((string)$query);

	    try {
            return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

	}

    public static function createinv($pks)
	{
        $params         = ComponentHelper::getParams('com_gafinance');
        $inv_no_offset   = $params->get('inv_no_offset');
        $inv_prefix = $params->get('inv_prefix');
		$mess = 'Invoice Created For: ';

        foreach ($pks as $pk) {

            // test to see if invoice has already been created
            $nextinv = $pk + $inv_no_offset;
            $nextinv  = str_pad($nextinv, 6, '0', STR_PAD_LEFT);

            $parent = 'images/finances';
            $folder = 'invoices';
            $path = Path::clean( JPATH_SITE . '/' . $parent . '/' . $folder .'/'.$inv_prefix.''. $nextinv .'.pdf');

    		if (!is_dir($path) && !is_file($path)) {
                $inv_ok = self::generatePDF($nextinv, $pk);
            } else {
	            unlink($path);
                $mess .= $nextinv.' already existed so deleted and recreated<br />';
                $inv_ok = self::generatePDF($nextinv, $pk);
            }
            if ($inv_ok) {
	            $mess .= ' Done '.$nextinv.'<br />';
            } else {
	            $mess .= ' Failed '.$nextinv.'<br />';
            }

            Factory::getApplication()->enqueueMessage($mess, 'notice');

        }

        return true;
	}

    public static function generatePDF($nextinv = null, $pk = null)
	{
        $siteName = Factory::getApplication()->get('fromname');
        $params         = ComponentHelper::getParams('com_gafinance');
        $set_test   = $params->get('set_test', 0);
        $site_bank   = $params->get('site_bank');
        $site_aname   = $params->get('site_aname');
        $site_bsb   = $params->get('site_bsb');
        $site_accnt   = $params->get('site_accnt');
        $site_addr   = $params->get('site_addr');
        $site_sub   = $params->get('site_sub');
        $inv_prefix = $params->get('inv_prefix');
        $accnt_text = $params->get('payment_txt', '(Please include your business name in the transaction reference)');
        //$accnt_text = Text::_('COM_GAFINANCE_INVOICE_ACCOUNT_TEXT');
        $today  = Factory::getDate()->toSql();

        //$nextinv  = str_pad($nextinv, 5, '0', STR_PAD_LEFT);
        $parent = 'images/finances';
        $folder = 'invoices';
        $path = Path::clean( JPATH_SITE . '/' . $parent . '/' . $folder );
        $tot_cost = 0;
        $lh = 4;
        $lh1 = 1;
        $lh2 = 2;

        $client = self::getInvoice($pk);

        if (!$client) {
            Factory::getApplication()->enqueueMessage('Bad invoice selection ('.$pk.')', 'warning');
        } else {
            Factory::getApplication()->setUserState('com_gafinance.client.data', $client);

            //class instantiation
            $pdf=new GapdfHelper("P","mm","A4");
            $pdf->SetMargins(10,10,10);
            $pdf->AddPage();

            $pdf->SetFont('Arial','B',10);
            $pdf->Cell(10, $lh, "", 0, 0, "L");
            $pdf->Cell(20, $lh, "Quantity", 0, 0, "C");
            $pdf->Cell(120, $lh, "Description", 0, 0, "C");
            $pdf->Cell(20, $lh, "Unit Price", 0, 0, "R");
            $pdf->Cell(20, $lh, "Total", 0, 1, "R");
            $pdf->Cell(0, $lh1, "", 0, 1, "C");
            $pdf->Cell(0, $lh2, "", "T", 1, "C");

            $pdf->SetFont('Arial','',10);
            $pdf->Cell(10, $lh, "", 0, 0, "L");
            $pdf->Cell(20, $lh, "1", 0, 0, "C");
            $pdf->Cell(10, $lh, "", 0, 0, "L");
            $y = $pdf->GetY();
            $x = $pdf->GetX();
            $pdf->MultiCell(100, $lh, $client->invoice_desc, 0, "L");
            $newy = $pdf->GetY();
            $pdf->SetXY($x + 100, $y);
            $pdf->Cell(10, $lh, "", 0, 0, "L");
            $pdf->Cell(20, $lh, number_format($client->invoice_cost,2), 0, 0, "R");
            $pdf->Cell(20, $lh, number_format($client->invoice_cost,2), 0, 1, "R");
            $pdf->SetXY(0, $newy);
            $pdf->Cell(0, $lh1, "", 0, 1, "C");
            $pdf->Cell(0, $lh2, "", "T", 1, "C");
            $pdf->Cell(0, $lh2, "", 0, 1, "C");
            $pdf->Cell(170, $lh, "Sub Total", 0, 0, "R");
            $pdf->Cell(20, $lh, "$".number_format($client->invoice_cost,2), 0, 1, "R");
            $pdf->Cell(0, $lh2, "", 0, 1, "C");
            $pdf->Cell(170, $lh, "Sales Tax", 0, 0, "R");
            $pdf->Cell(20, $lh, "", 0, 1, "R");
            $pdf->Cell(0, $lh2, "", 0, 1, "C");
            $pdf->Cell(170, $lh, "Shipping & Handling", 0, 0, "R");
            $pdf->Cell(20, $lh, "", 0, 1, "R");
            $pdf->Cell(0, $lh1, "", 0, 1, "C");
            $pdf->Cell(0, $lh2, "", "T", 1, "C");
            $pdf->Cell(170, $lh, "Total Due", 0, 0, "R");
            $pdf->Cell(20, $lh, "$".number_format($client->invoice_cost,2), 0, 1, "R");
            $pdf->Cell(0, $lh1, "", "T", 1, "C");

            $pdf->Ln(4);

            $pdf->Cell(10, $lh, "", 0, 0, "L");
            $pdf->Cell(180, $lh, "Payment Options:", 0, 1, "L");
            $pdf->Cell(20, $lh, "", 0, 0, "L");
            $pdf->Cell(60, $lh, "1. Post your cheque to:", 0, 0, "L");
            $pdf->Cell(110, $lh, "The Treasurer,", 0, 1, "L");
            $pdf->Cell(80, $lh, "", 0, 0, "L");
            $pdf->Cell(110, $lh, $siteName, 0, 1, "L");
            $pdf->Cell(80, $lh, "", 0, 0, "L");
            $pdf->Cell(110, $lh, $site_addr, 0, 1, "L");
            $pdf->Cell(80, $lh, "", 0, 0, "L");
            $pdf->Cell(110, $lh, $site_sub, 0, 1, "L");
            $pdf->Cell(0, $lh2, "", 0, 1, "C");

            $pdf->Cell(20, $lh, "", 0, 0, "L");
            $pdf->Cell(60, $lh, "2. Transfer funds to:", 0, 0, "L");
            $pdf->Cell(110, $lh, $site_bank, 0, 1, "L");
            $pdf->Cell(80, $lh, "", 0, 0, "L");
            $pdf->Cell(110, $lh, "Account Name: ".$client->accnt_name, 0, 1, "L");
            $pdf->Cell(80, $lh, "", 0, 0, "L");
            $pdf->Cell(110, $lh, "Account BSB: ".$client->accnt_bsb, 0, 1, "L");
            $pdf->Cell(80, $lh, "", 0, 0, "L");
            $pdf->Cell(110, $lh, "Account No: ".$client->accnt_number, 0, 1, "L");
            $pdf->Cell(80, $lh, "", 0, 0, "L");
            $pdf->SetFont('Arial','',8);
			$pdf->MultiCell(110, $lh, $accnt_text, 0, "L");
            $pdf->Cell(0, $lh2, "", 0, 1, "C");

            $pdf->Ln(4);

            $pdf->SetFont('Arial','B',10);
            $pdf->SetTextColor(0,100,148);
            $pdf->Cell(0, $lh, "Thank you for your business", 0, 1, "C");

			$pdf->Output($path . "/".$inv_prefix."".$nextinv.".pdf", "F");

            $attachfile = $path.'/'.$inv_prefix.''.$nextinv.'.pdf';

            $subject = $inv_prefix.''.$nextinv;
            
        }

        // update invoice date field in invoice data
        $db		= Factory::getContainer()->get('DatabaseDriver');
        $query	= $db->getQuery(true);
        $query->update(' #__gafinance_invoices ');
        $query->set(' inv_date = '.$db->Quote($today));
        $query->set(' state = 4 ');
        $query->where(' id = '.(int) $pk);
        $db->setQuery((string)$query);
        if (!$db->execute()) {
           throw new Exception(500, $db->getErrorMsg());
        }

        return true;
	}

	public static function sendInvoices($pks, $smail = 0)
	{
        $mess = 'Invoice sent to ';
        
        // cycle through the selected entries to send out the invoices
        foreach ($pks as $pk) {

            $client = self::getInvoice($pk);

            $params         = ComponentHelper::getParams('com_gafinance');
            $inv_prefix = $params->get('inv_prefix');
            $inv_no_offset = $params->get('inv_no_offset');
		    $inv_no_offset = (empty($inv_no_offset) ? 0 : $inv_no_offset);

            $path = Path::clean( JPATH_SITE . '/images/finances/invoices' );
            $inv  = str_pad(($pk + $inv_no_offset), 6, '0', STR_PAD_LEFT);
            $attachfile = $path.'/'.$inv_prefix.''.$inv.'.pdf';
            $subject = 'Invoice - '.$inv;
            
            if (!is_file($attachfile)) {
                $mess .= $client->name.' NOT SENT - Invoice not available, '. $attachfile;
            } else {
                $mess .= $client->name.', ';
                if (!$smail) {
                    $inv_ok = self::sendEmail($attachfile, $subject, $client);
                } else {
                    $inv_ok = true;
                }

        		if ($inv_ok) {
                    // update invoice state
                    $db		= Factory::getContainer()->get('DatabaseDriver');
            		$query	= $db->getQuery(true);
                    $query->clear();
            		$query->update(' #__gafinance_invoices ');
                    $query->set(' state = 3 ');
                    $query->where(' id = '. (int) $pk );
            		$db->setQuery((string)$query);
                    if (!$db->execute()) {
                        throw new Exception(500, $db->getErrorMsg());
                    }
                }
            }
        }

        Factory::getApplication()->enqueueMessage($mess, 'notice');

        return $client->name;

	}

    public static function sendEmail($attachfile = null, $subject = "Invoice", $client = null)
	{

        $params = ComponentHelper::getParams('com_gafinance');
        $app = Factory::getApplication();
        $email_text = $params->get('email_text');
        $email_salute = $params->get('email_salute');
        
        if (!empty($client->email_comment)) {
            $email_text = $client->email_comment;
            $email_salute = '';
        }

        //$client = $app->getUserState('com_gafinance.client.data');
        // get the user requesting this update
        $mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name

        if ($client->email == $params->get('inv_dummyemail')) {
            $body = 'Client '.$client->cont_name.' has no email, so just a copy sent to Office.';
        } else {
            $body = 'Dear '.$client->cont_name.', ';
            $body .= " \r\n\r\n";
            $body .= $email_text;
            $body .= " \r\n";
            $body .= $email_salute;
            $body .= " \r\n\r\n";
            $body .= $fromname;
            $body .= " \r\n\r\n";
        }

        // Build the email and send
        $mail = Factory::getMailer();
		if ($client->email == $params->get('inv_dummyemail')) {
            $mail->addRecipient($mailfrom);
        } else {
            $mail->addRecipient($client->email);
    		$mail->addBCC($mailfrom);
        }
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if ($attachfile) {
            $mail->addAttachment($attachfile);
        }

		$sent = $mail->Send();

        return true;
	}

	public static function markInvoicesPaid($pks)
	{
        $params         = ComponentHelper::getParams('com_gafinance');
        $incl_finance   = $params->get('incl_finance');
        $inv_cat   = $params->get('inv_cat');
        $admin_id   = $params->get('admin_id');
        $user	= GafinanceHelper::getSpecificUser();
        $today  = Factory::getDate()->toSql();

        foreach ($pks as $pk) {
            
            // Get invoice record details
    		$db		= Factory::getContainer()->get('DatabaseDriver');
    		$query	= $db->getQuery(true);
    		$query->clear();
    		$query->select(' b.name AS name, c.invtype_name AS inv_type, a.* ');
    		$query->from( '#__gafinance_invoices AS a' );
    		$query->join( 'LEFT','#__gafinance_patrons AS b ON b.id = a.client_id' );
    		$query->join( 'LEFT','#__gafinance_invtypes AS c ON c.id = a.invoice_type' );
    		$query->where( ' a.id = '.(int) $pk );
        	//$query->where( ' a.paid_date = "0000-00-00" ');
            $db->setQuery((string)$query);

		    try {
		        $invoice = $db->loadObject();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage());
		        return false;
		    }

            //insert finance transaction record
			// Insert the object into the table.
			$trans = new \stdClass();
			$trans->id = 0;
			$trans->created_by = $user->id;
			$trans->created_date = $today;
			$trans->user_id = $user->id;
			$trans->tran_type = 'I';
			$trans->tran_desc = $invoice->inv_type;
			$trans->tran_date = $today;
			$trans->tran_amount = $invoice->invoice_cost;
			$trans->tran_ref = $invoice->name.' Inv:'.$invoice->id;
			$trans->cat_id = $inv_cat;
		    try {
				$finTrans = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__gafinance_transactions', $trans, 'id');
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage());
		        return false;
		    }

			// Update invoice record with paid date
            $invoice->paid_date = $today;
            $invoice->state = 2;
            $client = $invoice->name;
			unset($invoice->name);
            unset($invoice->inv_type);
		    try {
				$result = Factory::getContainer()->get('DatabaseDriver')->updateObject('#__gafinance_invoices', $invoice, 'id');
				Factory::getApplication()->enqueueMessage('Invoice Paid For: '.$client.' ( Finance Trans-'.$trans->id.') ', 'notice');
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage());
		        return false;
		    }
		}

        return $client;

	}

}
