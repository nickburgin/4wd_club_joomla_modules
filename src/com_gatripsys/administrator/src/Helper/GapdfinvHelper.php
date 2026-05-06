<?php
/**
 * @version    4.0.0
 * @package     com_gatripsys
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Helper;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\fpdf\fpdf;

class GapdfinvHelper extends FPDF
{

    public function Header() {

		// get the current date-time based on timezone
		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
        $today = date_format($date, 'jS F Y');
        $lh = 5;

	    $app		= Factory::getApplication();
		$params  = ComponentHelper::getParams('com_gatripsys');
        $rpt_header  = $params->get('inv_logo');
        $header_type  = $params->get('inv_logo_type','JPG');
        $nxtinv = $app->getUserState('com_gatripsys.nextinv.data');
        $rpt_header = HTMLHelper::cleanImageURL($rpt_header);

        $siteemail	= $app->get('mailfrom');       // system email address
        $sitename	= $app->get('sitename');
        $urllink = Uri::base();

        $this->SetFont('Arial','B',12);
        $this->SetTextColor(0,100,148); // r,g,b
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, "Trip Invoice", 0, 1, "R");
        $this->Cell(0, $lh, "", "T", 1, "C");
        $x = $this->GetX();
        $y = $this->GetY();
        if (!empty($rpt_header)) {
            $this->Image(JPATH_SITE.'/'.$rpt_header->url, $x, $y, 0, 21, $header_type, $urllink);
        } else {
            $this->Cell(120, $lh, $sitename, 0, 0, "L");
        }
        $this->Ln(12);
        $x = $this->GetX();
        $y = $this->GetY();
        // main header information
        $this->SetFont('Arial','',10);
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->SetTextColor(0,100,148); // r,g,b
        $this->Cell(70, $lh, $sitename, 0, 1, "L");
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, "Trip Attendance Invoice", 0, 1, "L");
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, "Invoice Date: ".$today, 0, 1, "L");
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, "Invoice Number: ".$nxtinv, 0, 1, "L");
        $this->Ln(2);
        $this->Cell(0, $lh, "", "T", 1, "C");

    }

    public function Footer() {
        //This is the footer; it's repeated on each page.
        //enter filename: phpjabber logo, x position: (page width/2)-half the picture size,
        //y position: rough estimate, width, height, filetype, link: click it!
        //    $this->Image("logo.jpg", (8.5/2)-1.5, 9.8, 3, 1, "JPG", "http://www.glennarkell.com");
        $lh = 5;

        $this->SetY(-15);
        $this->SetFont('Arial','',8);
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(0, $lh, "", 'T', 1, "R");
        $this->Cell(0, $lh, "Page - ".$this->PageNo(), 0, 1, "R");
    }

}

