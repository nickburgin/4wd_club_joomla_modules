<?php

/**
 * @version    4.0.2
 * @package    Com_Gaforsale
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2013. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gaforsale\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\User\UserFactoryInterface;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\Filesystem\Path;
use \Joomla\Filesystem\File;
use \Joomla\Filesystem\Folder;
use \Joomla\CMS\User\User;
use \Joomla\CMS\Date\Date;
use \Joomla\CMS\Access\Access;

/**
 * Component helper.
 * @since  1.6
 */
class GaforsaleHelper
{
	/**
	 * Build the HTTP query array
	 * @param array of the http query if the http query already prepared and we want to add more
	 * @param string view or a task
	 * @param string control method to be used
	 * @param string reference indicator
	 * @param string/int reference string or id
	 * @return array
	 * @ hint - this is used with http_build_query($query_string, '', '&amp;') to build a clean url
	 */
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'transaction', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gafinance';
			$query_string[$viewTask] = $contModel;
			if ($ref) {
				$query_string[$ref] = $linkId;
			}
		} else {
			$query_string = $existQ;
			$query_string[$ref] = $linkId;
		}

		return $query_string;
	}

    /**
     * Gets todays date based on global timezone settings
     */
    public static function getTodaysDate()
	{
		$tz = Factory::getConfig()->get('offset');
		$date = Factory::getDate('now', $tz);
		$today = date_format($date,'Y-m-d H:i:s');
		
		return $today;
	}

    /**
     * Gets the user record for the specific id reference
     */
    public static function getSpecificUser($id = 0)
	{
		if ($id) {
			$container = Factory::getContainer();
			$userFactory = $container->get(UserFactoryInterface::class);
			$user = $userFactory->loadUserById($id);
		} else {
			$user = Factory::getApplication()->getIdentity();
		}

		return $user;
	}

	/**
	 * Gets the value of a field
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  string  value of the field
	 */
	public static function getRecordValue($pk, $table, $field)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return $db->loadResult();
	}

    /**
     * Gets the edit permission for an user
     * @param   mixed  $item  The item
     * @return  bool
     */
    public static function canUserEdit($item)
    {
        $permission = false;
        $user       = self::getSpecificUser();
        
		if (isset($item->created_by)) {
			$created_by = $item->created_by;
		} else {
			$created_by = $item;
		}

        if ($user->authorise('core.edit', 'com_gaforsale')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($user->authorise('core.edit.own', 'com_gaforsale') && $created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
    }

	public static function getListOptions($table = '#__gafinance_accounts', $label = 'Account', $field = 'accnt_name' )
	{

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, a.'.$field.' as text ');
		$query->from( $table . ' AS a ' );
		if ($table == '#__users') {
			$query->where(' a.block = 0' );
		} else {
			$query->where(' a.state = 1' );
		}
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	/**
	 * Send notification if necessary
	 * @param   array of data from form
	 */
	public static function sendNotification($data)
	{
		$params = ComponentHelper::getParams('com_gaforsale');
		$notifyUser = $params->get('email_user', 0);

		if ($notifyUser) {
			$user = self::getSpecificUser();
			$body = '<p>Item: '.$data['item_desc'].'</p>';
			$body .= '<p>Price: '.$data['item_price'].'</p>';
			$body .= '<p>Contact: '.$data['seller_contact'].'</p>';
			$body .= '<p>Phone: '.$data['seller_phone'].'</p>';

			$adminuser = self::getSpecificUser($notifyUser);
            $recipients = array();
            $recipients[] = $adminuser->email;
            $recipients[] = $user->email;

  			$subject = 'New Forsale Item Loaded ('.$user->name.')';
  			
  			$sentOK = self::sendEmail($recipients, $subject, $body, 0, $params);
		}

		return true;
	}

    public static function sendEmail($recipients = null, $subject = "Forsale", $body = null, $attachfile = 0, $params)
	{
        $app = Factory::getApplication();
        $sitename	= $app->get('sitename');       // get site name
		$mailfrom	= $app->get('mailfrom');       // system email address
        $fromname	= $app->get('fromname');       // Site name or system name

        // Build the email and send
        $mail = Factory::getMailer();
        $mail->addRecipient($recipients);
		$mail->setSender(array($mailfrom, $fromname));
		$mail->setSubject($subject);
		$mail->setBody($body);
		if ($attachfile) {
            $mail->addAttachment($attachfile);
        }

		$sent = $mail->Send();

        return true;
	}

}

