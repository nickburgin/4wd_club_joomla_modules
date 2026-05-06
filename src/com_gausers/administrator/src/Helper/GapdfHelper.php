<?php
/**
 * @version     6.0.0
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\User\UserHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\fpdf\fpdf;
use \GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;

class GapdfHelper extends FPDF
{

    public function Header() 
    {
        $today = date('jS F Y');
        $lh = 5;

	    $app		= Factory::getApplication();
	    $params = ComponentHelper::getParams('com_gausers');
        $site_address  = $params->get('site_address');
        $site_suburb  = $params->get('site_suburb');
        $site_phone  = $params->get('site_phone');
        $inv_logo  = $params->get('inv_logo');
        $inv_logo = HTMLHelper::cleanImageURL($inv_logo);
        $logoExt = strtoupper(substr($inv_logo->url ?? '', -3));
        $incl_partner  = $params->get('incl_partner');
        $mship_single  = $params->get('mship_single');
        $profile_suffix  = $params->get('profile_suffix');
        $localprof = 'profile'.$profile_suffix;

        $siteemail	= $app->get('mailfrom');       // system email address
        $sitename	= $app->get('sitename');
        $urllink = Uri::base();
        $nextinv  = $app->getUserState('com_gausers.nextinv.data');
        $u =  $app->getUserState('com_gausers.user.data');
        $use_post = str_replace('"','',$u->use_post);
        $address1 = $use_post ? str_replace('"','',$u->postal_address1) : str_replace('"','',$u->address1);
        $address2 = $use_post ? str_replace('"','',$u->postal_address2) : str_replace('"','',$u->address2);
        $suburb = $use_post ? str_replace('"','',$u->postal_suburb) : str_replace('"','',$u->suburb);
        $region = $use_post ? str_replace('"','',$u->postal_region) : str_replace('"','',$u->region);
        $pcode = $use_post ? str_replace('"','',$u->postal_pcode) : str_replace('"','',$u->pcode);
        $partner = str_replace('"','',$u->partner);

        $this->SetFont('Arial','B',10);
        $this->SetTextColor(0,100,148); // r,g,b
        $this->SetTextColor(64,64,64); // r,g,b
        if (!empty($inv_logo->url)) {
            $this->Image(JPATH_SITE."/".$inv_logo->url, 10, 10, 190, 34, $logoExt); // from invoice header
            $this->Ln(35);
        } else {
            $this->Cell(120, $lh, $sitename, 0, 1, "L");
        }
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 120mm
        $this->Cell(70, $lh, "Invoice/Tax Receipt", 0, 1, "R");
        $this->Cell(0, $lh, "", "T", 1, "C");
        //$x = $this->GetX();
        //$y = $this->GetY();

        // main header information
        $this->SetFont('Arial','',8);
        $this->Cell(130, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->SetTextColor(0,100,148); // r,g,b
        $this->Cell(60, $lh, $sitename, 0, 1, "R");
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(130, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(60, $lh, $site_address, 0, 1, "R");

        // client header information
        $this->SetFont('Arial','B',12);
        $this->Cell(30, $lh, "Member To:", 0, 0, "L"); // Sets an indent of 20mm
		if (!$incl_partner) {
	        $this->Cell(100, $lh, $u->name, 0, 0, "L");
        } elseif (!$mship_single && (isset($partner) && $partner > '')) {
	        $this->Cell(100, $lh, $u->name.' & '.$partner, 0, 0, "L");
        } elseif (!$mship_single) {
	        $this->Cell(100, $lh, $u->name, 0, 0, "L");
        } else {
			$member = GanamesHelper::breakdownNamesFromUserID($u->id);
			$mshipname = GanamesHelper::combineNames($member);
			$this->Cell(100, $lh, $mshipname, 0, 0, "L");
        }
        $this->SetFont('Arial','',8);
        $this->Cell(60, $lh, $site_suburb, 0, 1, "R");
        $this->SetFont('Arial','B',12);

        if ($address2) {
	        $this->Cell(30, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
	        $this->Cell(100, $lh, $address1, 0, 1, "L");
	        $this->SetFont('Arial','B',12);
            $this->Cell(30, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
            $this->Cell(100, $lh, $address2, 0, 0, "L");
	        $this->SetFont('Arial','',8);
	        $this->Cell(60, $lh, "Phone: ".$site_phone, 0, 1, "R");
	        $this->SetFont('Arial','B',12);
	        $this->Cell(30, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
	        $this->Cell(100, $lh, $suburb." ".$region." ".$pcode, 0, 0, "L");
	        $this->SetFont('Arial','',8);
	        $this->Cell(60, $lh, "eMail: ".$siteemail, 0, 1, "R" );
        } else {
	        $this->Cell(30, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
	        $this->Cell(100, $lh, $address1, 0, 0, "L");
	        $this->SetFont('Arial','',8);
	        $this->Cell(60, $lh, "Phone: ".$site_phone, 0, 1, "R");
	        $this->SetFont('Arial','B',12);
	        $this->Cell(30, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
	        $this->Cell(100, $lh, $suburb." ".$region." ".$pcode, 0, 0, "L");
	        $this->SetFont('Arial','',8);
	        $this->Cell(60, $lh, "eMail: ".$siteemail, 0, 1, "R" );
        }

        $this->Cell(130, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(45, $lh, "Invoice/Receipt #: ", 0, 0, "R");
        $this->SetFont('Arial','B',10);
        $this->Cell(15, $lh, $nextinv, 0, 1, "R");
        $this->SetFont('Arial','',8);
        $this->Cell(130, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(60, $lh, "Date: ".$today, 0, 1, "R");
        $this->Cell(0, $lh, "", 0, 1, "C");
        $this->Cell(0, $lh, "", 0, 1, "C");
        $this->Cell(0, $lh, "", 0, 1, "C");
        $this->Cell(0, $lh, "", "T", 1, "C");
        $this->Cell(0, $lh, "", 0, 1, "C");

    }

    public function Footer() {
        //This is the footer; it's repeated on each page.
        //enter filename: phpjabber logo, x position: (page width/2)-half the picture size,
        //y position: rough estimate, width, height, filetype, link: click it!
        //    $this->Image("logo.jpg", (8.5/2)-1.5, 9.8, 3, 1, "JPG", "http://www.glennarkell.com");
        $lh = 5;

        $this->SetY(-15);
        $this->SetFont('Arial','',6);
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(0, $lh, "", 'T', 1, "R");
        $this->Cell(0, $lh, "Page - ".$this->PageNo(), 0, 1, "R");
    }

}

