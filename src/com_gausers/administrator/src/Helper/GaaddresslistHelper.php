<?php
/**
 * @version     5.1.6
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
use \Joomla\CMS\Access\Access;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaaddlistpdfHelper;

class GaaddresslistHelper
{

    public static function createNameList()
	{
		$params  = ComponentHelper::getParams('com_gausers');
		$profsuf  = $params->get('profile_suffix', 'docs');
		$incl_partner  = $params->get('incl_partner', 0);
		$mship_single  = $params->get('mship_single', 0);
		$emaillist  = $params->get('emaillist', 0);

		$exclemail  = $params->get('exclude_email_pref', 'noemail');
		$address_group  = $params->get('address_group', 0);
        $usersInGroup = $address_group ? Access::getUsersByGroup($address_group) : array();

		$members = GausersHelper::getMembersDetails($params, 1);

		foreach ($members AS $m) {
            $m->altemail = isset($m->altemail) ? str_replace('"','',$m->altemail) : 0;
            $m->inc_altemail = isset($m->inc_altemail) ? str_replace('"','',$m->inc_altemail) : 0;
            $m->address1 = isset($m->address1) ? str_replace('"','',$m->address1) : '';
            $m->address2 = isset($m->address2) ? str_replace('"','',$m->address2) : '';
            $m->suburb = isset($m->suburb) ? str_replace('"','',$m->suburb) : '';
            $m->region = isset($m->region) ? str_replace('"','',$m->region) : '';
            $m->pcode = isset($m->pcode) ? str_replace('"','',$m->pcode) : '';
            $m->country = isset($m->country) ? str_replace('"','',$m->country) : 'Australia';
            $m->use_post = isset($m->use_post) ? str_replace('"','',$m->use_post) : 0;
            if ($m->use_post) {
                $m->address1 = isset($m->postal_address1) ? str_replace('"','',$m->postal_address1) : '';
                $m->address2 = isset($m->postal_address2) ? str_replace('"','',$m->postal_address2) : '';
                $m->suburb = isset($m->postal_suburb) ? str_replace('"','',$m->postal_suburb) : '';
                $m->region = isset($m->postal_region) ? str_replace('"','',$m->postal_region) : '';
                $m->pcode = isset($m->postal_pcode) ? str_replace('"','',$m->postal_pcode) : '';
                $m->country = isset($m->postal_country) ? str_replace('"','',$m->postal_country) : 'Australia';
            }

            $includeMbr = self::checkEmail($params, $m->email);
            
            if (!$includeMbr && in_array($m->id, $usersInGroup)) {
                $includeMbr = true;
            }

            if ($includeMbr) {
                if ($incl_partner) {
                    $mName = GanamesHelper::breakdownNamesFromUserID($m->id);
                    $m->name = GanamesHelper::combineNames($mName);
                }
                $mbrAddresses[] = $m;
            }
        }
        
        $addressList = self::createPDF($mbrAddresses);

        if ($emaillist) {
            // get user and email
            $user = GausersHelper::getSpecificUser();
            $sentOK = GaemailHelper::sendEmail(array($user->email), "Attached is the address list.", "Address List", $addressList, false, false);
        }

        return true;

	}

    public static function checkEmail($params, $email)
	{
		$exclemail  = $params->get('exclude_email_pref', 'noemail');
		$len  = strlen($exclemail);

        if (substr($email, 0, $len) == $exclemail) {
            return true;
        } else {
            return false;
        }
	}

    public static function createPDF($members)
	{
		$date = GausersHelper::getTodaysDate();
		$today = date_format($date,'Y-m-d H:i:s');

        $pdf = new GaaddlistpdfHelper("P","mm","A4");
        $lh = 6;
    	$perpage = 16;
    	$pagecntr = 0;
    	$collectedName = '';

        $pdf->SetMargins(30,20,30);

        $pdf->AddPage();

        $pdf->SetTextColor(64,64,64); // r,g,b
        $cntr = 0;
        //insert a gap
        $pdf->Cell(150, $lh, "", 0, 1, "L");

		foreach ($members as $mbr) {
            $pagecntr++;
            $cntr++;
            if ($cntr == 1) {
                $collectedName = $mbr;
                continue;
            } else {
                // name
                $pdf->Cell(72, $lh, $collectedName->name, 0, 0, "L");
                $pdf->Cell(5, $lh, "", 0, 0, "L");
                $pdf->Cell(72, $lh, $mbr->name, 0, 1, "L");
                // address
                $pdf->Cell(72, $lh, $collectedName->address1, 0, 0, "L");
                $pdf->Cell(5, $lh, "", 0, 0, "L");
                $pdf->Cell(72, $lh, $mbr->address1, 0, 1, "L");
                // suburb
                $pdf->Cell(72, $lh, $collectedName->suburb.", ".$collectedName->region." ".$collectedName->pcode, 0, 0, "L");
                $pdf->Cell(5, $lh, "", 0, 0, "L");
                $pdf->Cell(72, $lh, $mbr->suburb.", ".$mbr->region." ".$mbr->pcode, 0, 1, "L");
                // country
                $pdf->Cell(72, $lh, $collectedName->country, 0, 0, "L");
                $pdf->Cell(5, $lh, "", 0, 0, "L");
                $pdf->Cell(72, $lh, $mbr->country, 0, 1, "L");

                $pdf->Cell(150, $lh, "", 0, 1, "L");

                $collectedName = '';
                $cntr = 0;
            }
            
            if ($pagecntr === $perpage) {
                $pagecntr = 0;
                $pdf->AddPage();
                $pdf->Cell(150, $lh, "", 0, 1, "L");
            }
        }

		$path = Path::clean( JPATH_SITE . '/images/members' );
        $pdf->Output($path . "/AddressList.pdf", "F");

		$attachfile = $path.'/AddressList.pdf';
		
		$filename = 'AddressList.pdf';
		Factory::getApplication()->setUserState('com_gausers.file.addresslist', $filename);

        return $attachfile;
	}

}

