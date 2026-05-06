<?php
/**
 * @version     5.1.6                                                     
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Mail\MailTemplate;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GambrapplicpdfHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;

/**
 * Gausers helper.
 */
class GambrapplicHelper
{

	public static function newMemberForm($item, $params)
	{
		$date = GausersHelper::getTodaysDate();
		$today = date_format($date,'YmdHis');
		//$params = ComponentHelper::getParams('com_gausers');
        $footText  = $params->get('footText', '');
        $headText  = $params->get('headText', '');
        $sigRequired  = $params->get('sig_required', 0);
        $ignoreMships  = $params->get('ignoreMship', array());
        $pp_accnt  = $params->get('pp_accnt', 0);
        $incPartner  = $params->get('incl_partner', 0);
        if ($incPartner && $item->partner > '') {
            $item->name = $item->name.' & '.$item->partner;
        }    

		$mships = GausersHelper::getAllMshiptypes();
		$cntMships = count($mships);

        $lh = 8;
        //class instantiation
        //$pdf=new PDF("P","in","Letter"); // Legal Letter size
        //$pdf=new PDF("L","mm","A4");     // Landscape
        $pdf=new GambrapplicpdfHelper("P","mm","A4");

		$pdf->SetMargins(10,10,10);

        $pdf->AddPage();

        $pdf->SetFont('Arial','B',10); // font-family, font-weight (B), font-size
        $pdf->Cell(60, $lh, "Name: ", 0, 0, "L");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(125, $lh, "    ".$item->name, 1, 1, "L");
        $pdf->Cell(190, 3, "", 0, 1, "L");

        $pdf->SetFont('Arial','B',10); // font-family, font-weight (B), font-size
        $pdf->Cell(60, $lh, "eMail: ", 0, 0, "L");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(125, $lh, "    ".$item->email, 1, 1, "L");
        $pdf->Cell(190, 3, "", 0, 1, "L");

        $pdf->SetFont('Arial','B',10); // font-family, font-weight (B), font-size
        $pdf->Cell(60, $lh, "Phone: ", 0, 0, "L");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(125, $lh, "    ".$item->phone, 1, 1, "L");
        $pdf->Cell(190, 3, "", 0, 1, "L");

        $pdf->SetFont('Arial','B',10); // font-family, font-weight (B), font-size
        $pdf->Cell(60, $lh, "Address: ", 0, 0, "L");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(125, $lh, "    ".$item->address1." ".$item->address2, "L,T,R", 1, "L");
        //$pdf->Cell(65, 3, "", 0, 0, "L");
        //$pdf->Cell(125, 3, "", "L,R", 1, "L");

        $pdf->SetFont('Arial','B',10); // font-family, font-weight (B), font-size
        $pdf->Cell(60, $lh, "", 0, 0, "L");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(125, $lh, "    ".$item->city." ".$item->postal_code, "L,B,R", 1, "L");
        $pdf->Cell(190, 4, "", 0, 1, "L");

        $pdf->Cell(190, 2, "", "T", 1, "L");
        $pdf->Cell(190, 6, "Tick which membership option you wish.", 0, 1, "C");
        $pdf->Cell(190, 2, "", "B", 1, "L");
        $pdf->Cell(190, 2, "", 0, 1, "L");

        // set up column headers
        $pdf->SetFont('Arial','B',12); // font-family, font-weight (B), font-size
        $pdf->Cell(80, $lh, "Membership Type", 0, 0, "L");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
        $pdf->Cell(30, $lh, "Joining Fee", 0, 0, "L");
        $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
        $pdf->Cell(30, $lh, "Subscription", 0, 0, "L");
        $pdf->Cell(25, $lh, " ", 0, 0, "L"); // Sets a spacer
        $pdf->Cell(15, $lh, " ", 0, 1, "L");
        $pdf->Cell(190, 4, "", "B", 1, "L");
        $pdf->Cell(190, 2, "", 0, 1, "L");

        foreach ($mships as $ms) {
            $showMship = $ms->subscrib_amt == 0 ? false : true;
            $showMship = !$showMship && $ms->joining_fee == 0 ? false : true;
            if (in_array($ms->id, $ignoreMships)) { $showMship = false; }
            if ($showMship) {
                $pdf->SetFont('Arial','B',10); // font-family, font-weight (B), font-size
                $pdf->Cell(80, $lh, "    ".$ms->title, 0, 0, "L");
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
                $pdf->Cell(30, $lh, $ms->joining_fee, 0, 0, "L");
                $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
                $pdf->Cell(30, $lh, $ms->subscrib_amt, 0, 0, "L");
                $pdf->Cell(25, $lh, " ", 0, 0, "L"); // Sets a spacer
                $pdf->SetFont('Arial','',12);
                $pdf->Cell(15, $lh, " ", 1, 1, "L");
                $pdf->Cell(190, 4, "", 0, 1, "L");
            }
        }

        if (!empty($headText) && $headText > '') {
            $pdf->SetFont('Arial','',12);
            $pdf->Cell(5, 4, "", 0, 0, "L");
            $pdf->MultiCell(180, 4, $headText, 0, "L");
            $pdf->Cell(5, 4, "", 0, 1, "L");
        }

        if ($sigRequired) {
            $pdf->Cell(190, $lh, "", 0, 1, "L");
            $pdf->SetFont('Arial','B',10); // font-family, font-weight (B), font-size
            $pdf->Cell(40, $lh, "Signature: ", 0, 0, "L");
            $pdf->Cell(5, $lh, " ", 0, 0, "L"); // Sets a spacer
            $pdf->SetFont('Arial','',12);
            $pdf->Cell(145, $lh, "    .........................................................................................", 0, 1, "L");
            $pdf->Cell(190, 4, "", "B", 1, "L");
        }

        if (!empty($footText) && $footText > '') {
            $pdf->SetFont('Arial','',12);
            $pdf->Cell(190, $lh, "", "T", 1, "L");
            $pdf->Cell(5, 4, "", 0, 0, "L");
            $pdf->MultiCell(180, 4, $footText, 0, "L");
            $pdf->Cell(5, 4, "", 0, 1, "L");
        }

//         if (!empty($pp_accnt)) {
//             $pp_img  = $params->get('pp_img', 'images/general/paypal.jpg');
//             $pp_img = HTMLHelper::cleanImageURL($pp_img);
//             $fileType = substr($pp_img->url,-3);
//             $pdf->Cell(80, 4, "", 0, 0, "L");
//             $x = $pdf->GetX();
//             $y = $pdf->GetY();
//             $pdf->Image(JPATH_SITE."/".$pp_img->url, $x, $y, 0, 10, $fileType, $pp_accnt);
//             $pdf->Cell(5, 4, "", 0, 1, "L");
//         }

		$path = Path::clean( JPATH_SITE . '/images/members/applics' );
        $pdf->Output($path . "/".$item->name."-".$today.".pdf", "F");

		$attachfile = $path.'/'.$item->name.'-'.$today.'.pdf';
		
		$filename = $item->name.'-'.$today.'.pdf';
		Factory::getApplication()->setUserState('com_gausers.file.newmember.name', $filename);

        return $attachfile;

	}

	public static function checkEmailExists($item)
	{
		// count user records
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->clear()
			->select(' count(id) ')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('email') . ' = ' . $db->Quote($item->email) );

		$db->setQuery($query);

		try {
			$result = $db->loadResult();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_EMAILCHECK_FAILED'), 'warning');
			$result = 0;
		}

		return $result;
	}

}
