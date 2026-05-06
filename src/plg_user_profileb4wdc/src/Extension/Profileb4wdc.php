<?php
/**
 * @version    5.3
 * @package    plg_user_profileb4wdc
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  Copyright (C) 2011 - 2012 Glenn Arkell. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Plugin\User\Profileb4wdc\Extension;

defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Form\Form;
use \Joomla\CMS\Form\FormHelper;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\CMSPlugin;
use \Joomla\Database\ParameterType;
use \Joomla\Database\DatabaseAwareTrait;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\User\UserFactoryInterface;
use \Joomla\CMS\Access\Access;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\Installer\Installer;
use \Joomla\CMS\Component\ComponentHelper;

/**
 * Additional information for the profile plugin.
 */
final class Profileb4wdc extends CMSPlugin
{
    use DatabaseAwareTrait;

	/**
	 * Application object.
	 * @var    JApplicationCms
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Runs on content preparation
	 * @param   string  $context  The context for the data
	 * @param   object  $data     An object containing the data for the form.
	 * @return  boolean
	 */
	public function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, ['com_users.profile', 'com_users.user', 'com_users.registration'])) {
			return true;
		}

        // Load plugin language files
        $this->loadLanguage();
        
        //$app = Factory::getApplication();

		if (is_object($data)) {

        	$userId = $data->id ?? 0;

            // set the userid into the session for use later
			$this->app->setUserState('plugin_profileb4wdc.userid.data', $userId);

            if (!isset($data->profileb4wdc) and $userId > 0) {

				// Load the profile data from the database.
				$db    = $this->getDatabase();
				$query = $db->getQuery(true)
					->select(
						[
							$db->quoteName('profile_key'),
							$db->quoteName('profile_value'),
						]
					)
					->from($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = :userid')
					->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profileb4wdc.%'))
					->order($db->quoteName('ordering'))
					->bind(':userid', $userId, ParameterType::INTEGER);

				$db->setQuery($query);
				$results = $db->loadRowList();

				// Merge the profile data.
				$data->profileb4wdc = [];

				foreach ($results as $v) {
					$k = str_replace('profileb4wdc.', '', $v[0]);
					$data->profileb4wdc[$k] = json_decode($v[1], true);

					if ($data->profileb4wdc[$k] === null) {
						$data->profileb4wdc[$k] = $v[1];
					}
					if ($data->profileb4wdc[$k] == 'm_img' && $v[1] > '') {
						$data->profileb4wdc['m_img_txt'] = $v[1];
					}
				}
			}

		}
		return true;
	}

	/**
	 * Adds additional fields to the user editing form
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 * @return  boolean
	 */
	public function onContentPrepareForm(Form $form, $data)
	{
        // Check we are manipulating a valid form.
		$name = $form->getName();
		
        if (!in_array($name, ['com_users.user', 'com_users.profile', 'com_users.registration'])) {
            return true;
        }

        // Load plugin language files
        $this->loadLanguage();

		$limit_pw = $this->params->get('limit_pw', 0);

		if ($limit_pw && $this->app->isClient('site'))
        {
            $fsets = $form->getFieldsets();
            foreach ($fsets as $fgroup => $fs) 
            {
                if ($fgroup !== 'core') 
                {
                    $form->removeGroup($fgroup);
                }
            }
            // manually remove the custom fields group
            $form->removeGroup('com_fields');

            // do nothing regarding the custom profile data
			return true;
		}

		// Add the registration fields to the form.
		FormHelper::addFormPath(JPATH_PLUGINS . '/' . $this->_type . '/' . $this->_name . '/forms');
		$form->loadFile('profileb4wdc');

		$fields = [
			'fwdvic_no', 'mship_no', 'privacy',
			'm_img', 'm_img_txt', 'm_usi',
			'partner', 'p_img', 'p_img_txt', 'p_usi',
			'kids',
			'altphone', 'altemail', 'inc_altemail',
			'2nd_phone', '2nd_email',
			'vehicle_make', 'vehicle_model', 'vehicle_year', 'vehicle_rego', 'vehicle_trans', 'vehicle_fuel', 'vehicle_winch',
			'alt_vehicle', 'alt_vehicle_rego',
			'emailnews',
			'trg_prof', 'trg_lead', 'trg_inst', 'trg_csaw', 'trg_faid', 'trg_trck', 'trg_food',
			'trg_profc', 'trg_leadc', 'trg_instc', 'trg_csawc', 'trg_faidc', 'trg_trckc', 'trg_foodc',
			'trg_othr',
			'wwk_reg', 'wwk_exp',
			'cert4_dvct', 'cert4_dvdt', 'cert4_dvfile',
			'cert4_csct', 'cert4_csdt', 'cert4_csfile',
			'trg_profp', 'trg_leadp', 'trg_instp', 'trg_csawp', 'trg_faidp', 'trg_trckp', 'trg_foodp',
			'trg_profpc', 'trg_leadpc', 'trg_instpc', 'trg_csawpc', 'trg_faidpc', 'trg_trckpc', 'trg_foodpc',
			'trg_othrp',
			'wwk_regp', 'wwk_expp',
			'cert4_dvctp', 'cert4_dvdtp', 'cert4_dvfilep',
			'cert4_csctp', 'cert4_csdtp', 'cert4_csfilep',
			'hf_net', 'hf_callsign', 'hf_selcall',
			'hf2_net', 'hf2_callsign', 'hf2_selcall',
			'camp_caravan', 'camp_other',
			'use_post', 'postal_address1', 'postal_address2', 'postal_city', 'postal_region', 'postal_post_code',
			'mdod', 'pdod',
		];

		foreach ($fields as $field) {

			// Case using the users manager in admin
			if ($name == 'com_users.user') {

				// Remove the field if it is disabled in registration and profile
				if ($this->params->get('profile-require_' . $field, 1) > 0) {
                    $form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profileb4wdc');
                } elseif (
                    $this->params->get('register-require_' . $field, 1) == 0 &&
					$this->params->get('profile-require_' . $field, 1) == 0) {
					$form->removeField($field, 'profileb4wdc');
				}
			}
			// Case registration
			elseif ($name == 'com_users.registration') {

				// Toggle whether the field is required.
				if ($this->params->get('register-require_' . $field, 1) > 0) {
					$form->setFieldAttribute($field, 'required', ($this->params->get('register-require_' . $field) == 2) ? 'required' : '', 'profileb4wdc');
				} else {
					$form->removeField($field, 'profileb4wdc');
				}
			}
			// Case profile in site or admin
			elseif ($name == 'com_users.profile' ) {

				// Toggle whether the field is required.
				if ($this->params->get('profile-require_' . $field, 1) > 0) {
					$form->setFieldAttribute($field, 'required', ($this->params->get('profile-require_' . $field) == 2) ? 'required' : '', 'profileb4wdc');
				} else {
					$form->removeField($field, 'profileb4wdc');
				}

			}

	        $canAdmin = $this->params->get('adminGroup', array(8));
	        $updUser = $this->app->getIdentity();
	        $canEditWWC = false;
	        foreach ($updUser->groups AS $gp) { if (in_array($gp, $canAdmin)) { $canEditWWC = true; } }

            // test for readonly type fields
            switch ($field) {
                 case 'fwdvic_no': $setreadonly = true; break;
                 case 'cert4_dvct': $setreadonly = true; break;
                 case 'cert4_dvdt': $setreadonly = true; break;
                 case 'cert4_dvfile': $setreadonly = true; break;
                 case 'cert4_dvctp': $setreadonly = true; break;
                 case 'cert4_dvdtp': $setreadonly = true; break;
                 case 'cert4_dvfilep': $setreadonly = true; break;
                 case 'cert4_csct': $setreadonly = true; break;
                 case 'cert4_csdt': $setreadonly = true; break;
                 case 'cert4_csfile': $setreadonly = true; break;
                 case 'cert4_csctp': $setreadonly = true; break;
                 case 'cert4_csdtp': $setreadonly = true; break;
                 case 'cert4_csfilep': $setreadonly = true; break;
                 case 'wwk_exp': if ($canEditWWC) {$setreadonly = false;} else {$setreadonly = true;} break;
                 case 'wwk_reg': if ($canEditWWC) {$setreadonly = false;} else {$setreadonly = true;} break;
                 case 'wwk_expp': if ($canEditWWC) {$setreadonly = false;} else {$setreadonly = true;} break;
                 case 'wwk_regp': if ($canEditWWC) {$setreadonly = false;} else {$setreadonly = true;} break;
                 case 'trg_prof': $setreadonly = true; break;
                 case 'trg_lead': $setreadonly = true; break;
                 case 'trg_inst': $setreadonly = true; break;
                 case 'trg_csaw': $setreadonly = true; break;
                 case 'trg_trck': $setreadonly = true; break;
                 case 'trg_faid': $setreadonly = true; break;
                 case 'trg_food': $setreadonly = true; break;
                 case 'trg_profp': $setreadonly = true; break;
                 case 'trg_leadp': $setreadonly = true; break;
                 case 'trg_instp': $setreadonly = true; break;
                 case 'trg_csawp': $setreadonly = true; break;
                 case 'trg_trckp': $setreadonly = true; break;
                 case 'trg_faidp': $setreadonly = true; break;
                 case 'trg_foodp': $setreadonly = true; break;
                 case 'trg_profc': $setreadonly = true; break;
                 case 'trg_leadc': $setreadonly = true; break;
                 case 'trg_instc': $setreadonly = true; break;
                 case 'trg_csawc': $setreadonly = true; break;
                 case 'trg_trckc': $setreadonly = true; break;
                 case 'trg_faidc': $setreadonly = true; break;
                 case 'trg_foodc': $setreadonly = true; break;
                 case 'trg_profpc': $setreadonly = true; break;
                 case 'trg_leadpc': $setreadonly = true; break;
                 case 'trg_instpc': $setreadonly = true; break;
                 case 'trg_csawpc': $setreadonly = true; break;
                 case 'trg_trckpc': $setreadonly = true; break;
                 case 'trg_faidpc': $setreadonly = true; break;
                 case 'trg_foodpc': $setreadonly = true; break;
                 default:  $setreadonly = false;
            }

            // if in site area for field marked as readonly
			If ($this->app->isClient('site') &&  $setreadonly ) {
                $form->setFieldAttribute($field, 'readonly', 'true', 'profileb4wdc');
            }

            // Toggle whether the image field is shown.
            if ($field == 'm_img') {
                if (!isset($data->profileb4wdc[$field])) {
                    $form->setFieldAttribute($field.'_txt', 'type', 'hidden', 'profileb4wdc');
                    $form->setFieldAttribute($field, 'default', '');
                } else {
                    $form->setFieldAttribute($field.'_txt', 'default', $data->profileb4wdc[$field]);
                    $form->setFieldAttribute($field.'_txt', 'readonly', 'true', 'profileb4wdc');
    			}
			}
            if ($field == 'p_img') {
                if (!isset($data->profileb4wdc[$field])) {
                    $form->setFieldAttribute($field.'_txt', 'type', 'hidden', 'profileb4wdc');
                    $form->setFieldAttribute($field, 'default', '');
                } else {
                    $form->setFieldAttribute($field.'_txt', 'default', $data->profileb4wdc[$field]);
                    $form->setFieldAttribute($field.'_txt', 'readonly', 'true', 'profileb4wdc');
    			}
			}

		}

		return true;
	}

	/**
	 * @param	$oldUser - An associative array of the columns in the user table (current values).
     * @param	$isnew - Boolean to identify if this is a new user (true - insert) or an existing one (false - update)
     * @param	$newUser - An associative array of the columns in the user table (new values).
	 */
	function onUserBeforeSave($oldUser, $isNew, $newUser)
	{
        // $oldUser didn't appear to have any data in it so get user data
		$userId = $this->app->getUserState('plugin_profileb4wdc.userid.data');
		$this->app->setUserState('plugin_profileb4wdc.userid.data', null);

		$oldData_ok = $this->getPreUpdatedData($userId);

		return true;
	}

	/**
	 * Saves user profile data
	 * @param   array    $data    entered user data
	 * @param   boolean  $isNew   true if this is a new user
	 * @param   boolean  $result  true if saving the user worked
	 * @param   string   $error   error message
	 * @return  void
	 */
	public function onUserAfterSave($data, $isNew, $result, $error): void
	{
        $userId	= ArrayHelper::getValue($data, 'id', 0, 'int');
        $u = $this->app->getIdentity();

		if ($isNew) {
			$comment = 'New User Creation: '.$data['name'];
		} else {
			if ($u->id == $userId) {
				$comment = 'User Updated Own Record';
			} else {
				if ($u->name == '' && isset($data['activation'])) {
					if ($data['activation'] > '') {
						$comment = $data['name'].' Password Reset Requested';
					} else {
						$comment = $data['name'].' Password Reset Completed';
					}
				} else {
                    $comment = $data['name'].' Administrator Made Some Updates';
				}
			}
		}

		if ($userId && $result && isset($data['profileb4wdc']) && (count($data['profileb4wdc'])))
		{
			$db = $this->getDatabase();

			$keys = array_keys($data['profileb4wdc']);

            foreach ($keys as &$key) {
                $key = 'profileb4wdc.' . $key;
            }

			$query = $db->getQuery(true)
				->delete($db->quoteName('#__user_profiles'))
				->where($db->quoteName('user_id') . ' = :userid')
				->whereIn($db->quoteName('profile_key'), $keys, ParameterType::STRING)
				->bind(':userid', $userId, ParameterType::INTEGER);
			$db->setQuery($query);
			$db->execute();

			$query->clear()
				->select($db->quoteName('ordering'))
				->from($db->quoteName('#__user_profiles'))
				->where($db->quoteName('user_id') . ' = :userid')
				->bind(':userid', $userId, ParameterType::INTEGER);
			$db->setQuery($query);
			$usedOrdering = $db->loadColumn();

			$order = 1;
			$query->clear()
				->insert($db->quoteName('#__user_profiles'));

			foreach ($data['profileb4wdc'] as $k => $v)
			{
                while (in_array($order, $usedOrdering))
				{
					$order++;
				}

				$query->values(
					implode(
						',',
						$query->bindArray(
							[
								$userId,
								'profileb4wdc.' . $k,
								json_encode($v),
								$order++,
							],
							[
								ParameterType::INTEGER,
								ParameterType::STRING,
								ParameterType::STRING,
								ParameterType::INTEGER,
							]
						)
					)
				);
			}

			$db->setQuery($query);
			$db->execute();

		}

        $comp_exists = $this->checkComponentInstalled('gausers');
		if ($comp_exists) {
			$this->saveAuditTrail($data, $comment);
		}

	}

	/**
	 * Collect all user profile information for the given user ID
	 * Method is called before user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserBeforeDelete($user)
	{
		$oldData_ok = $this->getPreUpdatedData($user['id']);

		// delete all local profile values before deleting the user
		$userId = $this->app->getUserState('plugin_profileb4wdc.userid.data');
		$this->app->setUserState('plugin_profileb4wdc.userid.data', null);

		if ($userId)
		{
			$db = $this->getDatabase();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__user_profiles'))
				->where($db->quoteName('user_id') . ' = ' . (int) $userId)
				->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profileb4wdc.%'));
			$db->setQuery($query);
			$db->execute();
		}

		return true;
	}

	/**
	 * Check if the component is installed
	 * @param string component name
	 * @return true if manifest exists
	 */
	public static function checkComponentInstalled($component = 'gausers')
	{
		$manifest = Path::clean(JPATH_ADMINISTRATOR . '/components/com_'.$component.'/'.$component.'.xml');
		$componentXML = Installer::parseXMLInstallFile($manifest);

		if ($componentXML) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * Get all user profile information for the given user ID
	 * @param	int		$userId		Holds the user id reference
	 * @param	boolean		True if user was succesfully retrieved from the database
	 */
	function getPreUpdatedData($userId)
	{
		$up = UserHelper::getProfile($userId);
        $preUpdData = array();
		// cycle through profile elements and load into main array
		foreach ($up->profile AS $key => $value ) {
			$preUpdData[$key] = $value;
		}
		foreach ($up->profileb4wdc AS $key => $value ) {
			$preUpdData[$key] = $value;
		}
        ksort($preUpdData);

		$this->app->setUserState('plugin_profileb4wdc.preUpd.data', $preUpdData);

		return true;
	}


	/**
	 * Save preUpdate and postUpdate information into a User Management system audit record
	 * @param	array		$data		Holds the user profile data
	 * @param	string		$comment	Comment information for the record
	 * @param	boolean		True if audit was succesfully saved to the database
	 */
	function saveAuditTrail($data, $comment = '')
	{
        $preUpd = $this->app->getUserState('plugin_profileb4wdc.preUpd.data');
		$this->app->setUserState('plugin_profileb4wdc.preUpd.data',null);

		$today = Factory::getDate()->toSql();
        $params = ComponentHelper::getParams('com_gausers');
		$log_mbrsec  = $params->get('log_mbrsec', 0);
		$test_block  = $params->get('test_block', 0);
		$mbrsec_group  = $params->get('mbrsec_group');

		// Load admin language file
		$lang = Factory::getLanguage();
		$lang->load('plg_user_profileb4wdc', JPATH_ADMINISTRATOR);
		
		$userId	= ArrayHelper::getValue($data, 'id', 0, 'int');
		$userName	= ArrayHelper::getValue($data, 'name', '', 'string');
		$postUpd = array();

		$audit = new \stdClass();
		$audit->user_id = $userId;
		$audit->id = 0;
		$audit->state = 1;
		$audit->created_by = $userId;
		$audit->created_date = $today;

		// cycle through profile elements and load into main array
		if (isset($data['profile'])) {
			foreach ($data['profile'] AS $key => $value ) {
				$postUpd[$key] = $value;
			}
		}
		if (isset($data['profileb4wdc'])) {
			foreach ($data['profileb4wdc'] AS $key => $value ) {
				$postUpd[$key] = $value;
			}
		}

        ksort($postUpd);
		$audit->post_update = json_encode($postUpd);
		$audit->pre_update = json_encode($preUpd);

		$preMT_data = $this->app->getUserState('plugin_profileb4wdc.preMT.data');
		$postMT_data = $this->app->getUserState('plugin_profileb4wdc.postMT.data');
        $this->app->setUserState('plugin_profileb4wdc.preMT.data',null);
        $this->app->setUserState('plugin_profileb4wdc.postMT.data',null);
        if ($preMT_data > '') { $comment .= "\r\n".$preMT_data; }
        if ($postMT_data > '') { $comment .= "\r\n".$postMT_data; }

		$audit->comment = $comment;

		// Insert the object into the user profile table.
		try {
			$result = $this->getDatabase()->insertObject('#__gausers_profile_audit', $audit);
			$this->app->enqueueMessage(Text::_('PLG_USER_PROFILEB4WDC_AUDIT_CAP_SUCC'), 'notice');

			if ($log_mbrsec && $this->app->isClient('site')) {

				// test for a password reset which means no changes to profile
				$pwreset = $data['name'].' Password Reset Requested';
				$pwcompl = $data['name'].' Password Reset Completed';
				if ($comment == $pwreset || $comment == $pwcompl) {
                    $new_result = $comment;
                } else {
                    $new_result = '<p><strong>Profile Changes </strong><br />';
    				foreach($preUpd as $key => $value) {
    				    if(isset($postUpd[$key]) && $value != $postUpd[$key]) {
    						$fld_label = 'PLG_USER_PROFILEB4WDC_FIELD_'.strtoupper($key).'_LABEL';
    						$new_result .= 'Field = <strong>'.Text::_($fld_label).'</strong> Pre Value = <strong>"'.$value .'"</strong> and Post Value = <strong>'.$postUpd[$key].'</strong><br />';
    					}
    				}
    				$new_result .= '</p><p><strong>New Information Added </strong><br />';
    				foreach($postUpd as $key => $value) {
    				    if(!isset($preUpd[$key])) {
    						$fld_label = 'PLG_USER_PROFILEB4WDC_FIELD_'.strtoupper($key).'_LABEL';
    						$new_result .= 'Field = <strong>' . Text::_($fld_label) . '</strong> Value = <strong>"'.$value . '"</strong><br />';
    					}
    				}
    				$new_result.'</p><p><strong>End of Changes</strong></p>';
				}

				// send notification to membership secretary
                $mbrsecs = Access::getUsersByGroup($mbrsec_group, false);
                $recipients = array();
                if (is_array($mbrsecs)) {
					foreach ($mbrsecs AS $recip) {
                        $ru = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($recip);
                        $recipients[] = $ru->email;
                    }
				} else {
                    $ru = Factory::getContainer()->get(UserFactoryInterface::class)->loadUserById($mbrsecs);
					$recipients[] = $ru->email;
				}

				$subject = 'Member Update '.$userName;
				$body = $new_result;

		        $mailfrom	= $this->app->get('mailfrom');       // system email address
		        $fromname	= $this->app->get('fromname');       // Site name or system name
		        // Build the email and send
		        $mail = Factory::getMailer();
		        $mail->isHTML(true);
				$mail->addRecipient($recipients);
				$mail->setSender(array($mailfrom, $fromname));
				$mail->setSubject($subject);
				$mail->setBody($body);
		        if (!$test_block) {
                    $sent = $mail->Send();
    		        $this->app->enqueueMessage(Text::_('PLG_USER_PROFILEB4WDC_MBRSEC_NOTIFIED'), 'notice');
                } else {
                    $this->app->enqueueMessage(Text::_('PLG_USER_PROFILEB4WDC_MBRSEC_NOTIF_BLK'), 'notice');
                }
			}
			return true;
		} catch (RuntimeException $e) {
			$this->app->enqueueMessage(Text::_('PLG_USER_PROFILEB4WDC_AUDIT_CAP_FAIL'), 'notice');
			return false;
		}

	}

    /**
     * Remove all user profile information for the given user ID
     *
     * Method is called after user data is deleted from the database
     *
     * @param   array    $user     Holds the user data
     * @param   boolean  $success  True if user was successfully stored in the database
     * @param   string   $msg      Message
     *
     * @return  void
     */
    public function onUserAfterDelete($user, $success, $msg): void
    {
        if (!$success) {
            return;
        }

        $userId = ArrayHelper::getValue($user, 'id', 0, 'int');

        if ($userId) {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->delete($db->quoteName('#__user_profiles'))
                ->where($db->quoteName('user_id') . ' = :userid')
                ->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profileb4wdc.%'))
                ->bind(':userid', $userId, ParameterType::INTEGER);

            $db->setQuery($query);
            $db->execute();
        }
    }
}
