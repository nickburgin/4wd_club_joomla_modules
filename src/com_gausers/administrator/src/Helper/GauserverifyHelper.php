<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\Data\DataObject;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Installer\Installer;
use \Joomla\CMS\User\User;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Plugin\PluginHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;

/**
 * Gausers helper.
 */
class GauserverifyHelper
{

	/**
	 * Verify data from file - triggered from admin side
	 * @return  boolean true on success
	 */
    public static function adminVerifyFile()
	{
        $params = ComponentHelper::getParams('com_gausers');
        $verify_file  = $params->get( 'verify_file', 0);
        
        if ($verify_file) {
			$path = Path::clean( JPATH_SITE . '/images/members/import/');
			$destfile = $path.'/'.$verify_file.'.csv';
            self::processAdminData($destfile, $params);
        }

		return $cntr;
    }

	/**
	 * Process the file
	 * @param   array  $mbr.
	 * @return  boolean true on successful comparison of data
	 */
    public static function processAdminData($destfile, $params)
	{
		// import data from csv file
		$csv_file = file_get_contents($destfile);
		$mbrs = explode(PHP_EOL, $csv_file);

        $data = array();
		foreach ($mbrs as $key => $m) {
			// ignore header row
            if ($key > 0) {
                $data[] = str_getcsv($m);
			}
		}

		$cntr = 0;
	    $mbr_ids = '';
        /*
            0 Name,                    	    0 = <club_id>
            1 First Names,          		1 = <club_name>
            2 Member Id,					2 = <fwdv_no>
            3 Family Id,					3 = <name>
            4 Member Type,					4 = <email>
            5 Addressline1,					5 = <address1>
            6 Addressline2,	    			6 = <address2>
            7 Addressline3,	    			7 = <suburb>
            8 PostCode,	    				8 = <region>
            9 Email,	    				9 = <pcode>
            10 Email2,	    				10 = <phone>
            11 Home,	    				11 = <partner_name>
            12 Work,	    				12 = <partner_phone>
            13 Mobile,	    				13 = <partner_email>
            14 Sex, 	    				14 = <club_mship_id>
            15 Birth date,
            16 Status,    if this is pending ignore
            17 Fees,      if this is unpaid ignore
            18 Renewal Date,
            19 Join Date, 
            20 Other Id No,
            21 Privacy,
            22 Club email, 
            23 Ext email,
            24 Registration Number,
            25 Number of Hives,
            26 Town where Bees kept,
            27 Badge,
            28 Name for badge?,
            29 Area,
            30 Not in use,
            31 Not in use,
            32 Not in use,
            33 Not in use,
            34 Notes
		*/
		$newData = array();
		$itemData = array();
		foreach ($data as $mbr) {
            // ignore family member records
            if ($mbr[3] > '') { continue; }
            if ($mbr[16] == 'Pending') { continue; }
            if ($mbr[17] == 'Unpaid') { continue; }
            $itemData[0] = 0;
            $itemData[1] = 'BRB';
            $itemData[2] = 0;
            $itemData[3] = $mbr[1].' '.$mbr[0];
            $itemData[4] = str_replace('"', '', $mbr[9]);
            // address fields - address 1
            $itemData[5] = str_replace('"', '', $mbr[5]);
            $add2 = str_replace('"', '', $mbr[6]);
            $add3 = str_replace('"', '', $mbr[7]);
            // address fields - address 2 & suburb
            if ($add2 > '' && $add3 > '') {
                $itemData[6] = $add2;
                $itemData[7] = $add3;
            } elseif ($add2 == '' && $add3 > '') {
                $itemData[6] = '';
                $itemData[7] = $add3;
            } else {
               $itemData[7] = $add2;
            }
            // address fields - region
            $itemData[8] = 'VIC';
            // address fields - postcode
            $itemData[9] = $mbr[8];
            // phone
            $itemData[10] = str_replace('"', '', $mbr[13]);
            
            $itemData[11] = '';
            $itemData[12] = '';
            $itemData[13] = '';
            $itemData[14] = str_replace('"', '', $mbr[2]);
            $itemData[15] = str_replace('"', '', $mbr[24]);
            $itemData[16] = str_replace('"', '', $mbr[29]);
            $itemData[17] = str_replace('"', '', $mbr[19]);

            $newData[] = $itemData;
        }

        foreach ($newData as $m) {
			$cntr++;
			$mbr_id = self::verifyMemberData($m, $params);

			if ($mbr_id) {
				$mbr_ids .= $mbr_id.',';
			}

		}
		//$mbr_ids = substr($mbr_ids,0, -1);


				
		// now get all other enabled members to pickup new ones not on list from State Association
	    //$newMembers = self::getMember(0, $mbr_ids, $params);
	            
	    //foreach ($newMembers as $new_mbr) {
			//Factory::getApplication()->enqueueMessage(Text::_('New Member Needs to be added - '.$new_mbr->name), 'notice');
		//}
	
		return $cntr;
    }

	/**
	 * Compare data from CSV file to database
	 * @param   array  $mbr.
	 * @return  boolean true on successful comparison of data
	 */
    public static function verifyMemberData($mbr, $params)
	{   /*
	    0 = <club_id>
	    1 = <club_name>
	    2 = <fwdv_no>
	    3 = <name>
	    4 = <email>
	    5 = <address1>
	    6 = <address2>
	    7 = <suburb>
	    8 = <region>
	    9 = <pcode>
	    10 = <phone>
	    11 = <partner_name>
	    12 = <partner_phone>
	    13 = <partner_email>
	    14 = <club_mship_id>
		*/

		$profsuf  = $params->get( 'profile_suffix', 'b4wdc');
		$mship_single  = $params->get( 'mship_single', 0);
		$allow_compare  = $params->get( 'allow_compare', 0);
		$sendto_group  = $params->get( 'sendto_group', 0);

		$member = self::getMember($mbr, 0, $params);

		if ($mship_single) {
			$full_name = GausersHelper::combineNames($member);
		} else {
			$full_name = $member->name;
		}

		// compare uploaded data with data on record
		if ($member) {
		    $mbr_id = $member->id;
		    $message = $full_name.'<br />';
		    
		    // clear out any quotes
		    $member->address1 = str_replace('"', '', $member->address1);
		    $member->address2 = str_replace('"', '', $member->address2);
		    $member->suburb = str_replace('"', '', $member->suburb);
		    $member->region = str_replace('"', '', $member->region);
		    $member->pcode = str_replace('"', '', $member->pcode);
		    $member->phone = str_replace('"', '', $member->phone);
		    $member->partner = str_replace('"', '', $member->partner);
		    $member->altphone = str_replace('"', '', $member->altphone);
		    $member->altemail = str_replace('"', '', $member->altemail);
		    if ($member->use_post == 1) {
			    $member->address1 = str_replace('"', '', $member->postal_address1);
			    $member->address2 = str_replace('"', '', $member->postal_address2);
			    $member->suburb = str_replace('"', '', $member->postal_suburb);
			    $member->region = str_replace('"', '', $member->postal_region);
			    $member->pcode = str_replace('"', '', $member->postal_pcode);
		    }

			if ($member->block == 1) {
				$message = $full_name.' - Member not financial<br />';
			} else {
				if (strtolower($mbr[3]) == strtolower($full_name) || strtolower($mbr[3]) == strtolower($member->name)) { $message .= '';} else { $message .= ' ==> Names might need changing<br />';}
				if (strtolower($mbr[4]) != strtolower($member->email)) { $message .= ' ==> Email needs changing to '.$mbr[4].' - '.$member->email.'<br />';}
				if (strtolower($mbr[5]) != strtolower($member->address1)) { $message .= ' ==> Address1 needs changing to '.$mbr[5].' - '.$member->address1.'<br />';}
				if (strtolower($mbr[6]) != strtolower($member->address2)) { $message .= ' ==> Address2 needs changing to '.$mbr[6].' - '.$member->address2.'<br />';}
				if (strtolower($mbr[7]) != strtolower($member->suburb)) { $message .= ' ==> Suburb needs changing to '.$mbr[7].' - '.$member->suburb.'<br />';}
				if (isset($member->region) && strtolower($mbr[8]) != strtolower($member->region)) { $message .= ' ==> Region needs changing to '.$mbr[8].' - '.$member->region.'<br />';}
				if ($mbr[9] > '' && strtolower($mbr[9]) != strtolower($member->pcode)) { $message .= ' ==> Postcode needs changing to '.$mbr[9].' - '.$member->pcode.'<br />';}
				if ($mbr[11] > '' && strtolower($mbr[11]) != strtolower($member->partner)) { $message .= ' ==> Partner name needs changing to '.$mbr[11].' - '.$member->partner.'<br />';}
				if (strtolower($mbr[12]) != strtolower($member->altphone) && $member->altphone != '') { $message .= ' ==> Partner Phone needs changing to '.$mbr[12].' - '.$member->altphone.'<br />';}
				if (strtolower($mbr[13]) != strtolower($member->altemail) && $member->altemail != '') { $message .= ' ==> Partner Email needs changing to '.$mbr[13].' - '.$member->altemail.'<br />';}

				if (str_replace(' ', '', $mbr[10]) != str_replace(' ', '', $member->phone)) {
                    $message .= ' ==> Phone needs changing to '.$mbr[10].' - '.$member->phone.'<br />';
                }
			}

			if ($message == $full_name.'<br />') {
				Factory::getApplication()->enqueueMessage(Text::_(' ----> No Changes ('.$mbr[3].' - '.$mbr[14].')'), 'message');
			} elseif ($message == $full_name.' - Member not financial<br />') {
				Factory::getApplication()->enqueueMessage(Text::_($message), 'message');
			} else {
				Factory::getApplication()->enqueueMessage(Text::_($message), 'warning');
			}
		} else {
			$mbr_id = $mbr[14];
			Factory::getApplication()->enqueueMessage(Text::_('No member record found . . . . '.$mbr[3]), 'danger');
		}



		return $mbr_id;
    }

	/**
	 * Get member or members from database
	 * @param   int  $mbr - user id reference
	 * @param   string  $mbr_ids - string of ids separated by comma.
	 * @return  object  ObjectList of members data
	 */
    public static function getMember($mbr = 0, $mbr_ids = 0, $params)
	{
		$profsuf  = $params->get( 'profile_suffix', 'b4wdc');
		$sendto_group  = $params->get( 'sendto_group', 0);

        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ' );
        $query->select(' substr(`name`, 1, LOCATE(" ",`name`)) AS firstname ');
        $query->select(' if(substr(`name`, (LOCATE(" ",`name`)+1), 1)="&",substr(`name`, LOCATE(" ",`name`,(LOCATE(" ",`name`)+3))+1),substr(`name`, LOCATE(" ",`name`)+1)) AS surname ');
        $query->select(' if(b.profile_value IS NULL, "", b.profile_value) AS altemail ');
        $query->select(' if(c.profile_value IS NULL, "", c.profile_value) AS altphone ');
        $query->select(' if(d.profile_value IS NULL, "", d.profile_value) AS address1 ');
        $query->select(' if(e.profile_value IS NULL, "", e.profile_value) AS address2 ');
        $query->select(' if(f.profile_value IS NULL, "", f.profile_value) AS suburb ');
        $query->select(' if(fr.profile_value IS NULL, "", fr.profile_value) AS region ');
        $query->select(' if(g.profile_value IS NULL, "", g.profile_value) AS pcode ');
        $query->select(' if(h.profile_value IS NULL, "", h.profile_value) AS partner ');
        $query->select(' if(k.profile_value IS NULL, "", k.profile_value) AS phone ');
        $query->select(' substr(h.profile_value, 1, LOCATE(" ",h.profile_value)) AS firstnamep ');
        $query->select(' substr(h.profile_value, LOCATE(" ",h.profile_value)+1) AS surnamep ');
        $query->select(' if(l.profile_value IS NULL, "", l.profile_value) AS postal_address1 ');
        $query->select(' if(m.profile_value IS NULL, "", m.profile_value) AS postal_address2 ');
        $query->select(' if(n.profile_value IS NULL, "", n.profile_value) AS postal_suburb ');
        $query->select(' if(nr.profile_value IS NULL, "", nr.profile_value) AS postal_region ');
        $query->select(' if(o.profile_value IS NULL, "", o.profile_value) AS postal_pcode ');
        $query->select(' if(p.profile_value IS NULL, "", p.profile_value) AS use_post ');
        $query->from('`#__users` AS a');
        $query->join('LEFT', ' #__user_usergroup_map AS z ON a.id = z.user_id AND z.group_id = '. (int) $sendto_group);
        $query->join('LEFT', ' #__user_profiles AS h ON a.id = h.user_id AND h.profile_key = "profile'.$profsuf.'.partner" ');
		$query->join('LEFT','#__user_profiles AS b ON b.user_id = a.id AND b.profile_key = "'.$profsuf.'.altemail" ');
		$query->join('LEFT','#__user_profiles AS c ON c.user_id = a.id AND c.profile_key = "'.$profsuf.'.altphone" ');
		$query->join('LEFT','#__user_profiles AS d ON d.user_id = a.id AND d.profile_key = "profile.address1" ');
		$query->join('LEFT','#__user_profiles AS e ON e.user_id = a.id AND e.profile_key = "profile.address2" ');
		$query->join('LEFT','#__user_profiles AS f ON f.user_id = a.id AND f.profile_key = "profile.city" ');
		$query->join('LEFT','#__user_profiles AS fr ON fr.user_id = a.id AND fr.profile_key = "profile.region" ');
		$query->join('LEFT','#__user_profiles AS g ON g.user_id = a.id AND g.profile_key = "profile.postal_code" ');
		$query->join('LEFT','#__user_profiles AS k ON k.user_id = a.id AND k.profile_key = "profile.phone" ');
		$query->join('LEFT','#__user_profiles AS l ON l.user_id = a.id AND l.profile_key = "'.$profsuf.'.postal_address1" ');
		$query->join('LEFT','#__user_profiles AS m ON m.user_id = a.id AND m.profile_key = "'.$profsuf.'.postal_address2" ');
		$query->join('LEFT','#__user_profiles AS n ON n.user_id = a.id AND n.profile_key = "'.$profsuf.'.postal_city" ');
		$query->join('LEFT','#__user_profiles AS nr ON nr.user_id = a.id AND nr.profile_key = "'.$profsuf.'.postal_region" ');
		$query->join('LEFT','#__user_profiles AS o ON o.user_id = a.id AND o.profile_key = "'.$profsuf.'.postal_post_code" ');
		$query->join('LEFT','#__user_profiles AS p ON p.user_id = a.id AND p.profile_key = "'.$profsuf.'.use_post" ');
		if ($mbr_ids) {
			$query->where(' a.id NOT IN ('. $mbr_ids .')');
			$query->where(' a.block = 0 ');
		} else {
	        if ($mbr[13] > 0 && $mbr[13] != '') {
				$query->where(' a.id = '. (int) $mbr[13]);
			} else {
				$query->where(' (a.name = ' . $db->Quote($mbr[3]) . ') OR (a.email = ' . $db->Quote($mbr[4]) . ')' );
			}
		}

        $db->setQuery($query);
		try {
			if ($mbr_ids) {
				return $db->loadObjectList();
			} else {
				return $db->loadObject();
			}
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
			return false;
		}
    }

	/**
	 * Get all the passed data from the form
	 * @param   file  $mship_file.
	 * @return  int	record counter on successful comparison of data
	 */
    public static function uplattachfile($data)
	{
        $params = ComponentHelper::getParams('com_gausers');
        $safeFileOptions  = $params->get( 'safe_files');
		$file_ext = substr($data['mship_file']['name'],-3);
        $upload_dir  = $params->get( 'extract_dir', 'images/extracts');
        $extract_excludes  = $params->get( 'extract_excludes');
        $mbr_ids = '';
		foreach ($extract_excludes as $excl) {
			$mbr_ids .= $excl.',';
		}

		if (!in_array($file_ext, $safeFileOptions)) {
			Factory::getApplication()->enqueueMessage(Text::_('File format ('.$file_ext.') not allowed'), 'danger');
			return false;
		}

        if (file_exists('file://'.$data['mship_file']['tmp_name'])) {
			$fileName = File::makeSafe($data['mship_file']['name']);
			$fileName = str_replace(' ', '_', $fileName);
			$src = $data['mship_file']['tmp_name'];

			$path = Path::clean( JPATH_SITE . '/'.$upload_dir );
			$destfile = $path.'/'.$fileName;

			if ( File::upload($src, $destfile, false, false, $safeFileOptions) ) {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Uploaded Successfully'), 'message');
			} else {
				Factory::getApplication()->enqueueMessage(Text::_('File ('.$fileName.') Upload Failed'), 'danger');
			}
			
			if ($file_ext == 'csv' || $file_ext == 'CSV') {
				// import data from csv file
				$csv_file = file_get_contents($destfile);
				$mbrs = explode(PHP_EOL, $csv_file);
				$data = array();
				foreach ($mbrs as $m) {
				    // check the key fields ignore header row
				    if (isset($m) && !empty($m) && $m[0] == 0) {
						// do nothing
					} else {
						$data[] = str_getcsv($m);
					}
				}
				$cntr = 0;
	
				foreach ($data as $m) {
					// check each row
					if ($m[3] != '') {
						$cntr++;
						$mbr_id = self::verifyMemberData($m, $params);
		
						if ($mbr_id) {
							$mbr_ids .= $mbr_id.',';
						}
					}

				}
				$mbr_ids = substr($mbr_ids,0, -1);


				
				// now get all other enabled members to pickup new ones not on list from State Association
	            $newMembers = self::getMember(0, $mbr_ids, $params);
	            
	            foreach ($newMembers as $new_mbr) {
					Factory::getApplication()->enqueueMessage(Text::_('New Member Needs to be added - '.$new_mbr->name), 'notice');
				}
	
				return $cntr;
			} elseif ($file_ext == 'xml' || $file_ext == 'XML') {
				// load xml data to array
				if (file_exists($destfile)) {
					$xml_data = file_get_contents($destfile);
					$xml_obj = simplexml_load_string($xml_data);
					$xml_json = json_encode($xml_obj);
					$xml_array = json_decode($xml_json);
					//$xml_array = simplexml_load_file($destfile);
				} else {
					$xml_array = 'file not found';
				}


				Factory::getApplication()->enqueueMessage(Text::_('To Be Done - XML Process'), 'notice');
				return true;
			}
  		} else {
			Factory::getApplication()->enqueueMessage(Text::_("File Doesn't exist"), 'danger');
			return false;
		}
    }

}
