<?php
/**
 * @version     5.1.6
 * @package     com_gausers
 * @copyright   Copyright (C) 2014. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\Date\Date;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Plugin\PluginHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
/**
 * Gaaudit helper.
 */
class GaauditHelper {

    /**
     * Create a new audit record
     * @params  array of passed data from the form being updated
     * @params  int user id of the member being updated
     * @params  component parameters to save getting them again
     */
    public static function createAuditTrail($data = '', $user_id = 0, $params = '')
	{
		$app = Factory::getApplication();
		// retrieve pre update info - just name if new user
        $oldData = $app->getUserState('com_gausers.preUpd.data');

        if (empty($oldData)) { 
            $newUser = true; 
        } else { 
            $newUser = false; 
            unset($oldData['memtype']);
        }

		$unsetFields = array('name_ro','plain_region','m_img_disp','p_img_disp','cert4_dvfile_ro',
                            'cert4_dvfilep_ro','cert4_csfile_ro','cert4_csfilep_ro','membertype',
                            'trg_introc_txt','trg_intropc_txt','trg_bioc_txt','trg_biopc_txt',
                            'trg_faidc_txt','trg_faidpc_txt','trg_foodc_txt','trg_foodpc_txt',
                            'm_img_txt','p_img_txt', 'm_club_position', 'p_club_position', 'memtype');

		$prof_suf  = $params->get('profile_suffix');
		$log_mbrsec  = $params->get('log_mbrsec', 0);
		$mbrsec_group  = $params->get('mbrsec_group', 0);
		$localprof = 'profile'.$prof_suf;

		$comment = $data['comment'];
		$ignorArray = self::getProfileFieldsToIgnore($localprof);
		$u = GausersHelper::getSpecificUser();
		$newData = array();

		if ($newUser) {
			$oldData['comment'] = 'New member record';
            $oldData['name'] = $data['name'];
            $newData = $data;
            $userName = $data['name'];
		} else {
			$user = GausersHelper::getSpecificUser($user_id);
            $userName = $user->name;

    		// cycle through passed data and load into main array
    		foreach ($data AS $key => $value ) {
    			if (!in_array($key,$ignorArray)) {
    				$newData[$key] = $value;
    			}
    		}

            foreach ($unsetFields AS $uf) {
                unset($newData[$uf]);
            }
            $newData['name'] = $user->name;
            $newData['email'] = $user->email;
    		$newData['username'] = $user->username;
            unset($newData['id']);
    		ksort($newData);
		}


		$new_result = array();

		// cycle through old data to see if changed
        foreach($oldData as $oldkey => $oldvalue) {
		    if(isset($newData[$oldkey]) && $oldvalue != $newData[$oldkey]) {
				$new_result[$oldkey] = $newData[$oldkey];
			}
			if (in_array($oldkey, $unsetFields)) {
                unset($oldData[$oldkey]);
            }             
		}

		// now cycle through new data to find any that don't exist in old data
        foreach($newData as $key => $value) {
		    if(!isset($oldData[$key])) {
				$new_result[$key] = $value;
			}
		}

		$oldData = json_encode($oldData);
		$newData = json_encode($newData);

		// get the current date-time based on timezone
		//$date = GausersHelper::getTodaysDate();
		//$today = date_format($date,'Y-m-d H:i:s');
		$today = Factory::getDate()->toSql();

		$newAudit = new \stdClass();
		$newAudit->id=0;
		$newAudit->ordering=0;
		$newAudit->state=1;
		$newAudit->checked_out=0;
		$newAudit->created_by=$u->id;
		$newAudit->created_date=$today;
		$newAudit->user_id = $user_id;
		$newAudit->data_audit='';  // this isn't used
		$newAudit->pre_update=$oldData;
		$newAudit->post_update=$newData;
		$newAudit->comment = 'Updated by '.$u->name."\r\n".$comment;

	    try {
	        $result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__gausers_profile_audit', $newAudit);
	        Factory::getApplication()->setUserState('com_gausers.preUpd.data', null);
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	    }

	    // send notification if required
	    if ($log_mbrsec) {
			GaauditHelper::sendNotification($userName, $new_result, $mbrsec_group);
		}

	    return $newAudit->id;

    }

	/**
	 * load action record as a CRM helper
     * @params  array of passed data from the form being updated
     * @params  int user id of the member being updated
     * @params  component parameters to save getting them again
	 */
    public static function saveMshipActions($data = '', $user = 0, $params = '')
	{
		// get the current date-time based on timezone
		$today = Factory::getDate()->toSql();
		$u = GausersHelper::getSpecificUser();

		$newAct = new \stdClass();
		$newAct->id=0;
		$newAct->ordering=0;
		$newAct->state=1;
		$newAct->checked_out=0;
		$newAct->created_by=$u->id;
		$newAct->created_date=$today;
		$newAct->user_id = $user->id;
		$newAct->cat_id=0;
		$newAct->act_name= $data['act_name'];
		$newAct->comment = '<p>Reason - '.$data['left_reason'].'<br />Comment - '.$data['left_comment']. '</p>';

	    try {
	        $result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__gausers_actions', $newAct);
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	    }

        return $newAct->id;
    }

	/**
	 * get profile fields to ignore
	 * @param   string  profile name.
	 * @return  array	array of fields that have been set to disabled in plgin
	 */
    public static function getProfileFields($pluginname = 'profileb4wdc')
	{
		// set up the plugin fields to ignore
		$profArray = array();

		// now get all the local profile fields
		if (PluginHelper::isEnabled('user', $pluginname)) {
			$plg = PluginHelper::getPlugin('user',$pluginname);
			$params = json_decode($plg->params);
			foreach ($params AS $key => $value) {
				$field = substr($key,16);
				if (substr($key,0,16) == 'profile-require_') {
					if ($value) {
						$profArray[] = $field;
					}
				}
			}
		}

		return $profArray;
	}

	/**
	 * get profile fields to ignore
	 * @param   string  profile name.
	 * @return  array	array of fields that have been set to disabled in plgin
	 */
    public static function getProfileFieldsToIgnore($pluginname = 'profileb4wdc')
	{
		// set up the plugin fields to ignore
		$ignorArray = array();

		// now get all the core profile fields
		if (PluginHelper::isEnabled('user', 'profile')) {
			$plg = PluginHelper::getPlugin('user','profile');
			$params = json_decode($plg->params);
			foreach ($params AS $key => $value) {
				$field = substr($key,16);
				if (substr($key,0,16) == 'profile-require_') {
					if (!$value) {
						$ignorArray[] = $field;
					}
				}
			}
		}

		// now get all the local profile fields
		if (PluginHelper::isEnabled('user', $pluginname)) {
			$plg = PluginHelper::getPlugin('user',$pluginname);
			$params = json_decode($plg->params);
			foreach ($params AS $key => $value) {
				$field = substr($key,16);
				if (substr($key,0,16) == 'profile-require_') {
					if (!$value) {
						$ignorArray[] = $field;
					}
				}
			}
		}

		return $ignorArray;
	}

    /**
     * Send notification to members of the membership secretary group
	 * @param   string  user name.
	 * @param   array  new data being updated.
	 * @param   int  membership secretary group.
     */
	public static function sendNotification($userName = 0, $new_result = null, $mbrsec_group = 0)
	{
		// get membership secretarys
        $mbrsecs = Access::getUsersByGroup($mbrsec_group, false);
        $displayResult = '';

        // set up recipients 
		$recipients = array();
        if (is_array($mbrsecs)) {
			foreach ($mbrsecs AS $recip) { $recipients[] = GausersHelper::getSpecificUser($recip)->email;}
		} else {
			$recipients[] = GausersHelper::getSpecificUser($mbrsecs)->email;
		}

        foreach ($new_result as $key => $value) {
            $displayResult .= '<strong>'.$key.' => </strong>'.$value.'<br />';
        }

		$subject = 'Member Update - '.$userName;
		$body = '<p>'.Text::_('COM_GAUSERS_MBRSEC_SALUTATION').'</p>';
		$body .= '<p><strong>Record Changes </strong></p><p style="margin-left:20px;">';
		$body .= $displayResult;
		$body .= '</p><p><strong>End of Changes</strong></p>';
		//Factory::getApplication()->setUserState('com_gausers.test.data', $body);
		GaemailHelper::sendEmail($recipients, $body, $subject, '');

        Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_MBRSEC_NOTIFIED'));

	}

    /**
     * Get the last audit record date for the user
     */
	public static function getLatestUpdate($user_id = 0)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->clear();
		$query->select(' a.id, a.created_by, b.name as created_by_name, a.created_date, u.name ');
		$query->select(" date_format(a.created_date, '%a %e-%b-%Y %l:%i %p') as disp_date ");
		$query->from(' #__gausers_profile_audit AS a ');
		$query->join('LEFT', ' #__users AS b ON b.id = a.created_by ');
		$query->join('LEFT', ' #__users AS u ON u.id = a.user_id ');
		$query->where(' a.user_id = '.(int) $user_id);
		$query->order(' a.created_date DESC LIMIT 1 ');
		$db->setQuery((string)$query);

		try {
			return $db->loadObject();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage());
			return false;
		}
	}

	/**
	 * Get all user profile information for the given user ID
	 * @param	int		$userId		Holds the user id reference
	 * @param	boolean		True if user was succesfully retrieved from the database
	 */
	public static function getPreUpdatedData($userId)
	{
        $params = ComponentHelper::getParams('com_gausers');
        $locProf = 'profile'.$params->get('profile_suffix', 'b4wdc');
        $preUpdData = array();
		$app = Factory::getApplication();

		if ($userId) {
            $user = GausersHelper::getSpecificUser($userId);
            $up = UserHelper::getProfile($userId);
            $preUpdData['name'] = $user->name;
            $preUpdData['username'] = $user->username;
            $preUpdData['email'] = $user->email;

    		// cycle through profile elements and load into main array
    		foreach ($up->profile AS $key => $value ) {
    			$preUpdData[$key] = $value;
    		}
    		foreach ($up->$locProf AS $key => $value ) {
    			$preUpdData[$key] = $value;
    		}
            ksort($preUpdData);
        }

		$app->setUserState('com_gausers.preUpd.data', $preUpdData);

		return true;
	}

}
