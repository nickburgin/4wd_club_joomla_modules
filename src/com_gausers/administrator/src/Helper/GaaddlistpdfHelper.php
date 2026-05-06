<?php
/**
 * @version 5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2012. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by Glenn Arkell - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\User\UserHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\fpdf\fpdf;

class GaaddlistpdfHelper extends FPDF
{

    public function Header() {

        $today = \date('jS F Y');
        $this->SetFont('Arial','B',10);
		$this->Cell(150, 5, $today, "B", 1, "C");
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

