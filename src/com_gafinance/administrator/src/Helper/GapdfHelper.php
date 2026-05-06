<?php
/**
 * @version    5.2.3
 * @package    Com_Gafinance
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2020 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gafinance\Administrator\Helper;

defined('_JEXEC') or die;

require_once ('fpdf/fpdf.php');

use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use \GlennArkell\Component\Gafinance\Administrator\Helper\fpdf\fpdf;

class GapdfHelper extends FPDF
{

    public function Header() {

        $today = date('jS F Y');
        $lh = 5;
        $lh1 = 1;
        $lh2 = 2;

	    $app		= Factory::getApplication();
        $params         = ComponentHelper::getParams('com_gafinance');
        $inv_no_offset   = $params->get('inv_no_offset');
        $inv_prefix   = $params->get('inv_prefix');
        $site_abn   = $params->get('site_abn');
        $site_phone   = $params->get('site_phone');
        $site_fax   = $params->get('site_fax');
        $site_addr   = $params->get('site_addr');
        $site_sub   = $params->get('site_sub');
        $site_bank   = $params->get('site_bank');
        $site_aname   = $params->get('site_aname');
        $site_bsb   = $params->get('site_bsb');
        $site_accnt   = $params->get('site_accnt');
        $inv_himage   = $params->get('inv_himage');
        // prepare image from new media manager
        $inv_himage = HTMLHelper::cleanImageURL($inv_himage);

        $img_type   = strtoupper($params->get('img_type'));
        $client  = $app->getUserState('com_gafinance.client.data');
        $app->setUserState('com_gafinance.client.data', null);

        $nextinv = $client->id + $inv_no_offset;
        $nextinv  = str_pad($nextinv, 6, '0', STR_PAD_LEFT);

        $site_email	= $app->get('mailfrom');       // system email address
        $site_name	= $app->get('sitename');
        $urllink = Uri::root();

        // main title line
        //enter filename: phpjabber logo, x position: (page width/2)-half the picture size,
        //y position: rough estimate, width, height, filetype, link: click it!
        //    $this->Image("logo.jpg", (8.5/2)-1.5, 9.8, 3, 1, "JPG", "http://www.glennarkell.com.au");
        if (!empty($inv_himage->url)) {
            $this->Image(JPATH_SITE."/".$inv_himage->url, 10, 10, 190, 40, $img_type, $urllink);
        }

        $this->SetY(50);

        $this->SetTextColor(64,64,64); // r,g,b
        $this->SetFont('Arial','B',12);
        $this->Cell(190, $lh, $site_name, 0, 1, "C");
        $this->Cell(190, $lh, $site_abn, 0, 1, "C");
        $this->Cell(190, $lh, $site_addr, 0, 1, "C");
        $this->Cell(190, $lh, $site_sub, 0, 1, "C");
        $this->Cell(10, $lh, "", 0, 0, "L"); // Sets an indent
        $this->Cell(85, $lh, "TO:", 0, 0, "L");
        $this->SetTextColor(0,100,148); // r,g,b
        $this->SetFont('Arial','B',14);
        $this->Cell(95, $lh, Text::_('COM_GAFINANCE_INVOICE_PDF_TITLE'), 0, 1, "R");
        $this->SetTextColor(64,64,64); // r,g,b
        $this->SetFont('Arial','',12);
        $this->Cell(20, $lh, "", 0, 0, "L"); // Sets an indent
        $this->Cell(75, $lh, $client->name, 0, 0, "L");
        $this->Cell(95, $lh, "Ref: ".$inv_prefix.$nextinv, 0, 1, "R");
        $this->Cell(20, $lh, "", 0, 0, "L"); // Sets an indent
        $this->Cell(75, $lh, "Attn: ".$client->cont_name, 0, 0, "L");
        $this->Cell(95, $lh, "Date: ".$today, 0, 1, "R");
        $this->Cell(20, $lh, "", 0, 0, "L"); // Sets an indent
        $this->MultiCell(170, $lh, $client->address , 0, "L", false);
        $this->Cell(0, $lh2, "", "", 1, "C");
        $this->Cell(0, $lh1, "", "T", 1, "C");

    }

    public function Footer() {
        //This is the footer; it's repeated on each page.
        $lh = 5;

        $this->SetY(-15);
        $this->SetFont('Arial','',6);
        $this->SetTextColor(64,64,64); // r,g,b
        $this->Cell(0, $lh, "", 'T', 1, "R");
        $this->Cell(0, $lh, "Page - ".$this->PageNo(), 0, 1, "R");
    }

}

