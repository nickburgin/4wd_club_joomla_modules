<?php

/**
 * @version    4.2.2
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
use \Joomla\CMS\HTML\HTMLHelper;
use \GlennArkell\Component\Gaforsale\Administrator\Helper\GaemailHelper;

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
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'fsitem', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gaforsale';
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
     * Prints out a variable value in human readable format
     */
    public static function gaPrint($val){
        echo '<pre>Test<br />';
        \print_r($val);
        echo  '</pre>';
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
    *   Method to get the required record
    *   @return object record data
    */
	public static function getMembers()
	{
        $locProf  = ComponentHelper::getParams('com_gaforsale')->get('prof_suffix');
        $localProfile = 'profile'.$locProf;

        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(['a.name', 'a.email'])
			->from($db->quoteName('#__users', 'a'));
		if ($locProf == 'b4wdc') {
    		$query->select(['pn.profile_value AS partner_name', 'pe.profile_value AS partner_email'])
            	->join('LEFT', $db->quoteName('#__user_profiles', 'pn').' ON ('.$db->quoteName('pn.user_id').' = '.$db->quoteName('a.id').' AND '.$db->quoteName('pn.profile_key').' = '.$db->quote($localProfile.'.partner').')')
            	->join('LEFT', $db->quoteName('#__user_profiles', 'pe').' ON ('.$db->quoteName('pe.user_id').' = '.$db->quoteName('a.id').' AND '.$db->quoteName('pe.profile_key').' = '.$db->quote($localProfile.'.altemail').')');
		}
		$query->where($db->quoteName('a.block').' = '. (int) 0 );

		$db->setQuery((string)$query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Record');
	        return false;
	    }
	}

    /**
    *   Method to get the required record
    *   @param string $table table name
    *   @param string $field field name
    *   @param string $value reference
    *   @return object record data
    */
	public static function getRecordDetails($id)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(['a.*', 'u.name AS user_name', 'u.email AS user_email']);
		//$query->select( $db->quoteName('u.name', 'user_name'));
		//$query->select( $db->quoteName('u.email', 'user_email'));
		$query->from($db->quoteName('#__gaforsale_fsitems', 'a'));
		$query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('u.id') .' = '. $db->quoteName('a.user_id'));
		$query->where($db->quoteName('a.id').' = '. (int) $id );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Record');
	        return false;
	    }

	}

    /** --------------------------------------------------------------------------------------------------   **/
    /** ---------------------------  eMail preparation using the MailTemplate system  --------------------   **/
    /** --------------------------------------------------------------------------------------------------   **/
	/**
	* Notify the user/client/member etc through email
	* @param   integer $id  id reference of record
	* @param   string  $tmpl  mandatory (template ext)
	* @return true
	*/
	public static function notifyForsale($id, $tmpl)
	{
	    $app		= Factory::getApplication();
        $mailfrom	= $app->get('mailfrom');
        $fromname	= $app->get('fromname');

		if ($id) {

    		$item = self::getRecordDetails($id);

    		$params  = ComponentHelper::getParams('com_gaforsale');
    		$exclemail  = $params->get('ignor_email', 'noemail');
    		$len  = \strlen($exclemail);

    		// setup data
    		$data = GaemailHelper::setupData($tmpl, $item, $params);
            $data['sitename'] = $fromname;

			$viewLink = self::getHTTPQuery(null, 'view', 'fsitem', 'id', $item->id);
			$link = 'index.php?'.http_build_query($viewLink, '', '&amp;');

            if ($params->get('tmpl_email', 0)) {
                // new item loaded
                if ($tmpl == 'fsitems') {
                    // notify member registering the item
                    GaemailHelper::sendEmailTemplate('com_gaforsale.'.$tmpl, $data, null, $link, null);
                    //  notify admins so need to override the recipient array
                    $notifyUser = $params->get('email_user', 0);
                    $adminuser = self::getSpecificUser($notifyUser);
                    $data['name'] = $adminuser->name;
                    $data['email'] = $adminuser->email;
                    $data['recips'] = array(array('email'=>$data['email'], 'name'=>$data['name']));
                    GaemailHelper::sendEmailTemplate('com_gaforsale.'.$tmpl, $data, null, $link, null);
                    Factory::getApplication()->enqueueMessage(Text::_('COM_GAFORSALE_NOTIFICATIONS_SENT'), 'notice');

                } elseif ($tmpl == 'fstombrs' && $params->get('notif_mbrs', 0)) {
                    // get members
                    $mbrs = self::getMembers();

                    foreach ($mbrs as $m) {
                        if (substr($m->email, 0, $len) === $exclemail) {
                            continue;
                        }
                        // load primary member
                        $data['recips'] = array(array('email'=>$m->email, 'name'=>$m->name));
                        // load partner if required
                        $pemail = isset($m->partner_email) ? str_replace('"','',$m->partner_email):'';
                        $pname = isset($m->partner_name) ? str_replace('"','',$m->partner_name):'';
                        $data['cc_recips'] = (isset($pemail) && !empty($pemail)) ? array(array('email'=>$pemail, 'name'=>$pname)) :'';
                        // update name for email addressing
                        $fullName = (isset($pname) && !empty($pname)) ? $m->name.' & '.$pname : $m->name;
                        $data['name'] = $fullName;

                        GaemailHelper::sendEmailTemplate('com_gaforsale.'.$tmpl, $data, null, $link, null);
                        //$sentTo[] = $data;
                    }
                    //Factory::getApplication()->setUserState('com_gaforsale.test.data', $sentTo);
                    Factory::getApplication()->enqueueMessage(Text::_('COM_GAFORSALE_NOTIFICATIONS_SENT_TOMBRS'), 'notice');

                } elseif ($tmpl == 'fsremind') {
                    // just send to the member who raised the record
                    GaemailHelper::sendEmailTemplate('com_gaforsale.'.$tmpl, $data, null, $link, null);
                    Factory::getApplication()->enqueueMessage(Text::_('COM_GAFORSALE_NOTIFICATIONS_SENT'), 'notice');

                } else {
                    // don't send anything
                    Factory::getApplication()->enqueueMessage(Text::_('COM_GAFORSALE_NOTIFICATIONS_NOTSENT'), 'notice');
                }
            } else {
                self::sendNotification($data);
                Factory::getApplication()->enqueueMessage(Text::_('COM_GAFORSALE_NOTIFICATIONS_SENT'), 'notice');

            }

		} else {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAFORSALE_NO_ID_MESSAGE'), 'warning');
        }

		return true;
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
  			
  			$sentOK = GaemailHelper::sendEmail($recipients, $body, $subject, 0, $params);
		}

		return true;
	}

}

