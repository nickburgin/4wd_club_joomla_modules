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
use \GlennArkell\Component\Gausers\Administrator\Helper\fpdf\fpdf;

class GalistmembersPDFHelper extends FPDF
{

    public function Header() {

        $today = date('jS F Y');
        $lh = 5;

	    $app		= Factory::getApplication();
        $sitename	= $app->get('sitename');

        $this->SetFont('Arial','B',24);
        $this->SetTextColor(0,100,148); // r,g,b
        $this->Cell(190, 10, $sitename, 0, 1, "C");
        $this->SetFont('Arial','B',10);
        $this->SetTextColor(64,64,64); // r,g,b

        $this->Cell(130, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(60, $lh, "Date: ".$today, 0, 1, "R");
        //$this->Cell(0, $lh, "", 0, 1, "C");
        $this->Cell(0, 3, "", "T", 1, "C");

        $this->Cell(20, $lh, "Attend", "B", 0, "C");
        $this->Cell(5, $lh, "", "B", 0, "C");
        $this->Cell(65, $lh, "Member", "B", 0, "L");
        $this->Cell(10, $lh, "", "B", 0, "C");
        $this->Cell(20, $lh, "Attend", "B", 0, "C");
        $this->Cell(5, $lh, "", "B", 0, "C");
        $this->Cell(65, $lh, "Member", "B", 1, "L");
        $this->Cell(0, 3, "", "", 1, "C");

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

