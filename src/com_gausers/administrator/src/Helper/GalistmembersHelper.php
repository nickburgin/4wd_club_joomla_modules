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
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GalistmembersPDFHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GanamesHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GainvoiceHelper;

class GalistmembersHelper
{


	/**
	* Get current user records with partner and kids names
	* @return array of objects on success, boolean on fail
	*/
	public static function getCurrentMembers($params)
	{
        $inclGroup  = $params->get('sendto_group', 0);
        $exclMbrs  = $params->get('extract_excludes', array());
        $exMbrs = \implode(',', $exclMbrs);
		$prof_pref  = $params->get('profile_suffix', 'b4wdc');
		$part_key = 'profile'.$prof_pref.'.partner';

        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
        // primary member name
        $query->select(' id, name, SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 1), \' \', -1) AS first_name ');
        $query->select(' If( length(name) - length(replace(name, \' \', \'\'))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 2), \' \', -1) ,NULL) as middle1_name ');
        $query->select(' If( If( length(name) - length(replace(name, \' \', \'\'))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 3), \' \', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 4), \' \', -1), null, If( length(name) - length(replace(name, \' \', \'\'))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 3), \' \', -1) ,NULL)) as middle2_name ');
        $query->select(' SUBSTRING_INDEX(SUBSTRING_INDEX(name, \' \', 4), \' \', -1) AS last_name ');
        // partners name
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS first_namep " );
        $query->select(" If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 2), ' ', -1) ,NULL) as middle1_namep " );
        $query->select(" If( If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1), null, If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL)) as middle2_namep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS last_namep " );
        $query->select(" h.profile_value AS partner " );
		$query->from(' #__users ');
		$query->join('LEFT', '#__user_profiles AS h ON h.user_id = id AND h.profile_key = '.$db->Quote($part_key));
        $query->where(' block = 0 ' );
        if (!empty($exclMbrs)) {
            $query->where(' id NOT IN ('.$exMbrs.')' );
        }
		$query->order(' last_name ASC ' );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

	}

	/**
	 * Break up the name fields and form a display name version
	 * @param   object  $member  data including name, partner and the breakup firstnames and surnames.
	 * @return  string	combined name for the membership display
	 */
    public static function combineNames($member = null)
	{
        if (isset($member->partner)) {
            $member->partner = str_replace('"','',$member->partner);
            $member->first_namep = trim(str_replace('"','',$member->first_namep));
            $member->last_namep = trim(str_replace('"','',$member->last_namep));
        }

        if (isset($member->partner) && !empty($member->partner) && $member->partner != ' ') {
			if (trim($member->last_name) == trim($member->last_namep)) {
				$result = trim($member->last_name) . ', ' . trim($member->first_name). ' & ' .trim($member->first_namep);
			} else {
				$result = trim($member->last_name) . ', ' . trim($member->first_name). ' & ' .trim($member->partner);
			}
		} else {
            $result = trim($member->last_name) . ', ' . trim($member->first_name);
        }

		return $result;

	}

	// * ------------------------   Creation of PDF   ----------------------------- * //
	/*
	 *  This is a mechanism to create a PDF list of members
	 *
	*/
	public static function createPDF($finstatus = null, $mship = null)
	{
		Factory::getLanguage()->load('com_gausers', JPATH_ADMINISTRATOR);
		$params = ComponentHelper::getParams('com_gausers');
		$joint_mship  = $params->get('mship_single', 0);

        $members = self::getCurrentMembers($params);

		$lh = 5;
		$cntr = 0;

        //class instantiation
        //$pdf=new PDF("P","in","Letter"); // Legal Letter size
        //$pdf=new PDF("L","mm","A4");     // Landscape
        $pdf=new GalistmembersPDFHelper("P","mm","A4");

		$pdf->SetMargins(10,10,10);

        $pdf->AddPage();
        
        // 40 lines per page

        $pdf->SetFont('Arial','',10); // font-family, font-weight (B), font-size
        $data = array();
        foreach ($members AS $m) {
            // get last inv
            $lastInv = GainvoiceHelper::getLastInvoiceMship($m->id);

            // test if member needs to be included
            if ($finstatus == 1) {
                if (!isset($lastInv) || !$lastInv || $lastInv === null) { continue; }
            }
            if ($mship) {
                if ($mship != $lastInv->mship_id) { continue; }
            }

            if ($joint_mship) {
                $name = self::combineNames($m);
            } else {
                $name = $m->name;
            }
            if ($m->id == 531) {
            $data['mbr'] = $m;
            $data['inv'] = $lastInv;
            Factory::getApplication()->setUserState('com_gausers.test.data', $data);
            }
            $cntr++;
            if ($cntr == 1) { $m1 = $name; continue; }
            if ($cntr == 2) {
                $cntr = 0;
                $pdf->Cell(10, $lh, "", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(5, $lh, "", "TRBL", 0, "C");
                $pdf->Cell(10, $lh, "", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(65, $lh, $m1, 0, 0, "L");
                $pdf->Cell(10, $lh, "", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(10, $lh, "", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(5, $lh, "", "TRBL", 0, "C");
                $pdf->Cell(10, $lh, "", 0, 0, "L"); // Sets an spacer
                $pdf->Cell(65, $lh, $name, 0, 1, "L");
                $pdf->Cell(190, 1, "", 0, 1, "L"); // Sets an spacer
            }
        }

        // clean up
        if ($cntr == 1) {
            $pdf->Cell(10, $lh, "", 0, 0, "L"); // Sets an spacer
            $pdf->Cell(5, $lh, "", "TRBL", 0, "L");
            $pdf->Cell(10, $lh, "", 0, 0, "L"); // Sets an spacer
            $pdf->Cell(40, $lh, $m1, 0, 0, "L");
        }

		$path = Path::clean( JPATH_SITE . '/images/members' );
        $pdf->Output($path . "/MembersList.pdf", "F");

		$attachfile = $path.'/MembersList.pdf';

        return $attachfile;

	}

}

