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
use \GlennArkell\Component\Gausers\Administrator\Helper\fpdf\fpdf;

class GambrapplicpdfHelper extends FPDF
{

    public function Header() {

        $today = date('jS F Y');
        $lh = 5;

	    $app		= Factory::getApplication();
	    $app->getLanguage()->load('com_gausers', JPATH_ADMINISTRATOR);
	    $params = ComponentHelper::getParams('com_gausers');
        $site_address  = $params->get('site_address');
        $site_suburb  = $params->get('site_suburb');
        $site_phone  = $params->get('site_phone');
        $md_header  = $params->get('newmbr_header');
        $heading  = $params->get('heading', 'Membership Application');

        $siteemail	= $app->get('mailfrom');       // system email address
        $sitename	= $app->get('sitename');
        $urllink = Uri::base();

        $this->SetFont('Arial','B',10);
        $this->SetTextColor(0,100,148); // r,g,b  (powder blue)
        $x = $this->GetX();
        $y = $this->GetY();

        if (!empty($md_header)) {
            $md_header = HTMLHelper::cleanImageURL($md_header);
            $fileType = substr($md_header->url,-3);
            $this->Image(JPATH_SITE."/".$md_header->url, $x, $y, 0, 30, $fileType);
            $this->Ln(35);
        } else {
            $this->SetFont('Arial','B',24);
			$this->Cell(190, $lh, $sitename, 0, 1, "L");
			$this->SetFont('Arial','',10);
        }

        // main header information
        $this->Cell(190, $lh, "", 0, 1, "L");
        $this->SetFont('Arial','B',18);
        $this->Cell(190, 12, $heading, 0, 1, "C");
        $this->SetFont('Arial','B',12);
        $this->Cell(190, $lh, $sitename, 0, 1, "R");
        $this->Cell(190, $lh, $site_address, 0, 1, "R");
        $this->Cell(190, $lh, $site_suburb, 0, 1, "R");
        $this->Cell(190, $lh, "Date: ".$today, 0, 1, "R");

        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(0, $lh, "", "T", 1, "C");

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

