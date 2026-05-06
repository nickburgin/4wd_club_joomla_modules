<?php
/**
 * @version     3.0.05
 * @package     com_gamerchandise
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com
 */

namespace GlennArkell\Component\Gamerchandise\Administrator\Helper;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\fpdf\fpdf;
use \GlennArkell\Component\Gamerchandise\Administrator\Helper\GamerchandiseHelper;

class GapdfHelper extends FPDF
{

    public function Header() {

		// get the current date-time based on timezone
		$date = Factory::getDate();
		//$config = Factory::getConfig();
		//$date->setTimezone(new DateTimeZone($config->get('offset')));
        $today = date_format($date, 'jS F Y');
        $lh = 5;
	    $app		= Factory::getApplication();
        $po_user  = $app->getUserState('com_gamerchandise.po_user.data');
        $user = GamerchandiseHelper::getSpecificUser($po_user);
        $profile = UserHelper::getProfile($user->id);

		$params  = ComponentHelper::getParams('com_gamerchandise');
        $rpt_header  = $params->get('rpt_header');
        $rpt_logo = HTMLHelper::cleanImageURL($rpt_header);
        if (isset($rpt_logo->url)) {
			$logo_img = $rpt_logo->url;
	    } else {
			$logo_img = $rpt_header;
		}
        $header_type  = $params->get('header_type');
        $site_phone  = $params->get('site_phone');
        $site_address  = $params->get('site_address');
        $site_suburb  = $params->get('site_suburb');
        $site_fax  = $params->get('site_fax');

        $merchemail	= $params->get('merchandise_email');
        $siteemail	= $app->get('mailfrom');       // system email address
        if ($merchemail > '') {$siteemail = $merchemail; }
        $sitename	= $app->get('sitename');
        $urllink = Uri::base();
        $order_no  = $app->getUserState('com_gamerchandise.po.data');

        if ($params->get('mship_single',0)) {
			$member = GamerchandiseHelper::breakdownNamesFromUserID($user->id);
			$name = GamerchandiseHelper::combineNames($member);
		} else {
			$name = $user->name;
		}

        $this->SetFont('Arial','B',14);
        $this->SetTextColor(0,100,148); // r,g,b
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(100, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(90, $lh, "Order", 0, 1, "R");
        $this->Cell(0, $lh, "", "T", 1, "C");
        $x = $this->GetX();
        $y = $this->GetY();
        if (isset($rpt_logo)) {
            $this->Image(JPATH_SITE.'/'.$logo_img, $x, $y, 0, 37, $header_type, $urllink);
            $this->Ln(39);
        } else {
            $this->Cell(100, $lh, $sitename, 0, 0, "L");
            $this->Ln(2);
        }

        // main header information
        $this->SetFont('Arial','',12);
        $this->Cell(100, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->SetTextColor(0,100,148); // r,g,b
        $this->Cell(90, $lh, $sitename, 0, 1, "L");
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(100, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(90, $lh, "Address: ".$site_address, 0, 1, "L");
        $this->Cell(115, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(75, $lh, $site_suburb, 0, 1, "L");
        $this->Cell(100, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(90, $lh, "Phone: ".$site_phone, 0, 1, "L");
        $this->Cell(100, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
		$this->Cell(90, $lh, "eMail: ".$siteemail, 0, 1, "L" );
        $this->Ln(2);
        $this->Cell(100, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(90, $lh, "#: ORD".str_pad($order_no, 8, '0', STR_PAD_LEFT), 0, 1, "L");
        $this->Cell(100, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(90, $lh, "Date: ".$today, 0, 1, "L");
        $this->Cell(0, $lh, "", "T", 1, "C");
        $this->Ln(2);

        $this->Cell(35, $lh, "Member: ", 0, 0, "R");
        $this->Cell(155, $lh, $name, 0, 1, "L");
        $this->Cell(35, $lh, "Address: ", 0, 0, "R");
        $this->Cell(155, $lh, $profile->profile['address1'], 0, 1, "L");
        $this->Cell(35, $lh, "", 0, 0, "R");
        $this->Cell(155, $lh, $profile->profile['city'].", ".$profile->profile['postal_code'] , 0, 1, "L");
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

