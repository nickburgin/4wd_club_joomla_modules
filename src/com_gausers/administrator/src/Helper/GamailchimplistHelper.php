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

class GamailchimplistHelper
{

    public static function createList()
	{
		$params  = ComponentHelper::getParams('com_gausers');
		$exclemail  = $params->get('exclude_email_pref', 'noemail');
		$mcextra  = $params->get('mchimp_extra', '');
		$user = GausersHelper::getSpecificUser();

		$members = GausersHelper::getMembersDetails($params, 1);

		$extract_dir = $params->get('extract_dir', 'images/members/extracts');
		$path = Path::clean( JPATH_SITE . '/' . $extract_dir . '/' );
		$filename = $path.'MailChimp.csv';
		
		$head_data = 'email,surname,firstname,extra';
        $head_data .= "\r\n";
        $mship_data = '';

		foreach ($members AS $m) {
            $includePtr = false;
            $m->altemail = isset($m->altemail) ? str_replace('"','',$m->altemail) : 0;
            $m->inc_altemail = isset($m->inc_altemail) ? str_replace('"','',$m->inc_altemail) : 0;
            $m->surnamep = isset($m->surnamep) ? str_replace('"','',$m->surnamep) : 0;
            $m->firstnamep = isset($m->firstnamep) ? str_replace('"','',$m->firstnamep) : 0;

            $status = $m->block ? 'disabled' : 'enabled';
            $exInfo = $mcextra == '' ? $status : $mcextra;

            $includeMbr = self::checkEmail($exclemail, $m->email);

            if ($m->altemail && $m->inc_altemail) {
                $includePtr = self::checkEmail($exclemail, $m->altemail);
            }

            if ($includeMbr && $includePtr) {
                continue;
            }

            if (!$includeMbr) {
                $mship_data .= $m->email.','.$m->surname.','.$m->firstname.','.$exInfo;
                $mship_data .= "\r\n";
            }
            if (!$includePtr && $m->altemail > '') {
                $mship_data .= $m->altemail.','.$m->surnamep.','.$m->firstnamep.','.$exInfo;
                $mship_data .= "\r\n";
            }
        }

		$output = $head_data;
	    $output .= $mship_data;

	    File::write($filename, $output);

	    $sentOK = GaemailHelper::sendEmail(array($user->email), "Attached is the MailChimp list.", "MailChimp List", $filename, false, false);

		return $sentOK;

	}

    public static function checkEmail($exclemail, $email)
	{
		$len  = strlen($exclemail);

        if (substr($email, 0, $len) == $exclemail) {
            return true;
        } else {
            return false;
        }
	}

}

