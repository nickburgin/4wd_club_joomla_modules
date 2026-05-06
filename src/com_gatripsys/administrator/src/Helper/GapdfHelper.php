<?php
/**
 * @version    5.1.0
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
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\fpdf\fpdf;

class GapdfHelper extends FPDF
{

    public function Header() {

		// get the current date-time based on timezone
		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
        $today = date_format($date, 'jS F Y');
        $lh = 5;

	    $app		= Factory::getApplication();
		$params  = ComponentHelper::getParams('com_gatripsys');
        $rpt_header  = $params->get('rpt_header');
        $rpt_header = HTMLHelper::cleanImageURL($rpt_header);

        $profile_prefix  = $params->get('prof_pref');
        $header_type  = $params->get('header_type');

        if ($profile_prefix) {
            $profpref = 'profile'.$profile_prefix;
        } else {
            $profpref = 'profile';
        }

        $siteemail	= $app->get('mailfrom');       // system email address
        $sitename	= $app->get('sitename');
        $urllink = Uri::base();
        $trip  = $app->getUserState('com_gatripsys.trip.data');
        $pageHeader  = $app->getUserState('com_gatripsys.triprpt.header');
        $up = UserHelper::getProfile( $trip->leader );
        $trip->leader_phone = $up->profile['phone'];
        $localprof = $up->$profpref;
        $trip->leader_img = $localprof['m_img'];

        $this->SetFont('Arial','B',12);
        $this->SetTextColor(0,100,148); // r,g,b
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, $pageHeader, 0, 1, "R");
        $this->Cell(0, $lh, "", "T", 1, "C");
        $x = $this->GetX();
        $y = $this->GetY();
        if (!empty($rpt_header)) {
            if (\file_exists(JPATH_SITE.'/'.$rpt_header->url)) {
                $img_type = substr($rpt_header->url,-3);
                $this->Image(JPATH_SITE.'/'.$rpt_header->url, $x, $y, 0, 37, $img_type, $urllink);
            }
        } else {
            $this->Cell(120, $lh, $sitename, 0, 0, "L");
        }
        $this->Ln(38);
        $x = $this->GetX();
        $y = $this->GetY();
        if (!empty($trip->leader_img)) {
			$leader_img = HTMLHelper::cleanImageURL($trip->leader_img);
            if (\file_exists(JPATH_SITE.'/'.$leader_img->url)) {
                $mimg_type = substr($leader_img->url,-3);
                $this->Image(JPATH_SITE.'/'.$leader_img->url, $x, $y, 0, 30, $mimg_type, $urllink);
            }
        }
        // main header information
        $this->SetFont('Arial','',10);
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->SetTextColor(0,100,148); // r,g,b
        $this->Cell(70, $lh, $sitename, 0, 1, "L");
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, "Leader: ".$trip->leader_name, 0, 1, "L");
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, "Phone: ".$trip->leader_phone, 0, 1, "L");
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, "eMail: ".$trip->leader_email, 0, 1, "L" );
        $this->Ln(2);
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, "Trip #: ".$trip->id, 0, 1, "L");
        $this->Cell(120, $lh, "", 0, 0, "L"); // Sets an indent of 20mm
        $this->Cell(70, $lh, "Report Date: ".$today, 0, 1, "L");
        $this->SetFont('Arial','',14);
        $this->Ln(2);
        $this->MultiCell(190, $lh, $trip->title, 0, "C");
        $this->Ln(2);
        $this->Cell(0, $lh, "", "T", 1, "C");
        //$this->Ln(2);

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

//     public function NbLines($w,$txt) {
//         //Computes the number of lines a MultiCell of width w will take
//         $cw =&$this->CurrentFont['cw'];
// 
//         if($w==0)
//             $w=$this->w-$this->rMargin-$this->x;
//             $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
//             $s=str_replace("\r",'',$txt);
//             $nb=strlen($s);
// 
//         if($nb>0 and $s[$nb-1]=="\n")
//             $nb--;
//             $sep=-1;
//             $i=0;
//             $j=0;
//             $l=0;
//             $nl=1;
//             while($i<$nb)
//             {
//                 $c=$s[$i];
//                 if($c=="\n")
//                 {
//                 $i++;
//                 $sep=-1;
//                 $j=$i;
//                 $l=0;
//                 $nl++;
//                 continue;
//             }
//         if($c==' ')
//             $sep=$i;
//             $l+=$cw[$c];
//         if($l>$wmax)
//         {
//             if($sep==-1)
//             {
//                 if($i==$j)
//                     $i++;
//                 }
//                 else
//                     $i=$sep+1;
//                     $sep=-1;
//                     $j=$i;
//                     $l=0;
//                     $nl++;
//         }
//         else
//             $i++;
//         }
//         return $nl;
//     }
// 
//     public function Section($ar1)
//     {
//         for($i=0; $i<count($ar1); $i++)
//         {
//         # bring the array element back to a local variable f1, f2, f3
//         $f1 = $ar1[$i][0];
//         $f2 = $ar1[$i][1];
//         $f3 = $ar1[$i][2];
//         # the following will return the number of lines for the
//         # text field f1, f2, and f3
//         $nb1 = $this->NbLines($f1,25);
//         $nb2 = $this->NbLines($f2,35);
//         $nb3 = $this->NbLines($f3,20);
//         # variable hx will yeild the amount of Y consummed by this
//         # line of text
//         $hx = max($nb1, $nb2, $nb3) * 6;
//         # now going to check to see if the text in f1 will cause
//         # a page break
//         if($this->GetY() + $hx > $this->PageBreakTrigger)
//         {
//         # going to add a new page
//         $this->AddPage($this->CurOrientation);
//         # add the page header
//         $this->Header();
//         # space the start of the text 5 mm below the end of the header
//         $this->Ln(5);
//         # reset the font to what is needed for the text display (content)
//         $this->SetFont('Arial', '', 9);
// 
// 
//     }

}

