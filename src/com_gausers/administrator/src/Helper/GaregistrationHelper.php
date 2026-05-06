<?php
/**
 * @version     6.0.0                                                     
 * @package     com_gausers
 * @copyright   Copyright (C) 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Glenn Arkell <glenn@glennarkell.com.au> - http://www.glennarkell.com.au
 */

namespace GlennArkell\Component\Gausers\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\Data\DataObject;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\Database\ParameterType;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Mail\MailTemplate;
use \GlennArkell\Component\Gausers\Administrator\Helper\GausersHelper;
use \GlennArkell\Component\Gausers\Administrator\Helper\GaemailHelper;

/**
 * Gausers helper.
 */
class GaregistrationHelper
{

	public static function setupNewUserData($data)
	{
		$app = Factory::getApplication();

		Factory::getLanguage()->load('com_users', JPATH_ADMINISTRATOR);
		Factory::getLanguage()->load('com_users', JPATH_SITE);
		$params = ComponentHelper::getParams('com_gausers');
		$tempUGrp  = $params->get('temp_group');
		$tempMship  = $params->get('temp_mship');
		$profile_suffix  = $params->get('profile_suffix');
		$localprof = "profile".$profile_suffix;
	    $com_params = ComponentHelper::getParams('com_users');

		$new_pw = self::setupUserPassword();

		$new_user = array();
		$new_user['id'] = 0;
		$new_user['username'] = $data['username'] ? $data['username'] : substr(str_replace(" ","",strtolower($data['name'])),0,18);
		$new_user['username'] = str_replace("-","",$new_user['username']);
		$new_user['username'] = str_replace("&","",$new_user['username']);
		$new_user['password1'] = $new_pw;
		$new_user['password2'] = $new_pw;
		$new_user['email1'] = $data['email'];
		$new_user['email2'] = $data['email'];
		$new_user['name'] = $data['name'];

		// check for profile fields
		$new_user['address1'] = isset($data['address1']) ? $data['address1'] : null;
		$new_user['address2'] = isset($data['address2']) ? $data['address2'] : null;
		$new_user['city'] = isset($data['city']) ? $data['city'] : null;
		$new_user['region'] = isset($data['region']) ? $data['region'] : null;
		$new_user['postal_code'] = isset($data['postal_code']) ? $data['postal_code'] : null;
		$new_user['phone'] = isset($data['phone']) ? $data['phone'] : null;
		$new_user['website'] = isset($data['website']) ? $data['website'] : null;
		$new_user['favoritebook'] = isset($data['favoritebook']) ? $data['favoritebook'] : null;
		$new_user['aboutme'] = isset($data['aboutme']) ? $data['aboutme'] : null;
		$new_user['dob'] = isset($data['dob']) ? $data['dob'] : null;

		// extra profile fields
		$new_user['partner'] = isset($data['partner']) ? $data['partner'] : null;
		$new_user['emailnews'] = 1;
		
        // Check if email exists
        $email_exists = self::checkEmailExists($new_user);
        if ($email_exists) {
			$app->enqueueMessage(Text::_('COM_GAUSERS_RECORD_EXISTS'), 'warning');
			return false;
		}
        // Check if username exists
        $user_exists = self::checkUsernameExists($new_user);
        if ($user_exists) {
			$new_user['username'] = $new_user['username'] . substr($data['email'],0,(stripos($data['email'],"@")-1));
		}

		$user_id = self::register($new_user);

        if ($user_id) {
            $app->enqueueMessage(Text::sprintf('COM_GAUSERS_USER_ID_RECORD', $user_id), 'notice');
            if ($tempMship == $data['mship_id']) {
                $defGrp = $tempUGrp;
            } else {
                $defGrp = $com_params->get('new_usertype');
            }
            $groupOK = self::setDefaultUserGroup($user_id, $defGrp);

			$welcome_note = $params->get('welcome_note',0);
			if ($welcome_note) {
				// send welcome email using the article set in the options
				self::sendNewUserWelcome($user_id);
			}

			$app->enqueueMessage(Text::_('COM_GAUSERS_NEW_USER_SUCCESSFULLY'), 'notice');
		} else {
			$app->enqueueMessage(Text::_('COM_GAUSERS_NEW_USER_FAILED'), 'danger');
		}

		return $user_id;

	}

	/**
	 * Get the Model from another component for use
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 * @return object	The model
	 */
	public static function getForeignModel($comp = 'com_users', $name = 'Registration', $prefix = 'Site', $config = array('ignore_request' => true))
	{
		$rmodel  = Factory::getApplication()->bootComponent($comp)->getMVCFactory()->createModel($name, $prefix, $config);

		return $rmodel;
	}

	public static function setupUserPassword() {

	    // get parameters as set in the user component
	    $com_params = ComponentHelper::getParams('com_users');
        $rand_pw = array();

		$min_chs = $com_params->get('minimum_length',8);
	    $chs = 'abcdefghijklmnopqrstuvwxyz';
	    $r_chs = self::generateRandomString($min_chs, $chs);
        $rand_pw = array_merge($rand_pw, $r_chs);

		$min_int = $com_params->get('minimum_integers',0);
	    $int = '0123456789';
	    $r_int = self::generateRandomString($min_int, $int);
        $rand_pw = array_merge($rand_pw, $r_int);

		$min_sym = $com_params->get('minimum_symbols',0);
	    $sym = '`~!@#$%^*(){}[]:;';
	    $r_sym = self::generateRandomString($min_sym, $sym);
        $rand_pw = array_merge($rand_pw, $r_sym);

		$min_upc = $com_params->get('minimum_uppercase',0);
	    $upc = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $r_upc = self::generateRandomString($min_upc, $upc);
        $rand_pw = array_merge($rand_pw, $r_upc);

	    // now create a string
	    $new_pw = implode("",$rand_pw);

	    return $new_pw;

	}

	public static function generateRandomString($length, $characters) {
	    $charactersLength = strlen($characters);
	    $randomString = array();
	    for ($i = 0; $i < $length; $i++) {
	        $randomString[] = $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	public static function loadNewUserProfile($user_id, $prof, $value) {
		
		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('INSERT into #__user_profiles (user_id,profile_key,profile_value) VALUES ('.(int)$user_id.','.$db->Quote($prof).','.$db->Quote($value).')');
        $db->execute();

	    return true;
	}

	public static function setDefaultUserGroup($user_id = 0, $group_id = 0) {
		
		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery('INSERT into #__user_usergroup_map (user_id,group_id) VALUES ('.(int)$user_id.','.(int)$group_id.')');
        $db->execute();

	    return true;
	}

	public static function sendNewUserWelcome($user_id = 0) {

		$params = ComponentHelper::getParams('com_gausers');
		$send_email = $params->get('send_email',0);
		$welcome_note = $params->get('welcome_note',0);

		if ($send_email) {
			// get article data
			$db = Factory::getContainer()->get('DatabaseDriver');
			$query = $db->getQuery(true);
			$query->clear()
				->select(' * ')
				->from($db->quoteName('#__content'))
				->where($db->quoteName('id') . ' = ' . (int) $welcome_note );
	
			$db->setQuery($query);
	
			try {
				$article = $db->loadObject();
	
				if ($article) {
					// set links in article so they work in email
					$bodytext = str_replace('<a href="index.php', '<a href="'.Uri::base().'index.php', $article->introtext);

                    $user = GausersHelper::getSpecificUser($user_id);
					$sentOK = GaemailHelper::sendEmail(array($user->email), $bodytext, $article->title, 0);

					Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_WELCOME_SENT'), 'notice');
				}
	
			} catch (RuntimeException $e) {
				Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_WELCOME_FAILED'), 'notice');
				$article = 0;
			}
		}

	}


	public static function checkEmailExists( $data = array())
	{
		// count user records
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->clear()
			->select(' count(id) ')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('email') . ' = ' . $db->Quote($data['email1']) );

		$db->setQuery($query);

		try {
			$result = $db->loadResult();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_EMAILCHECK_FAILED'), 'warning');
			$result = 0;
		}

		return $result;
	}

	public static function checkUsernameExists( $data = array())
	{
		// count user records
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->clear()
			->select(' count(id) ')
			->from($db->quoteName('#__users'))
			->where($db->quoteName('username') . ' = ' . $db->Quote($data['username']) );

		$db->setQuery($query);

		try {
			$result = $db->loadResult();
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_GAUSERS_USERNAME_ADJUSTED'), 'notice');
			$result = 0;
		}

		return $result;
	}

	/**
	 * Copy of the register Method from com_users.
	 * @param   array  $temp  The form data.
	 * @return  mixed  The user id on success, false on failure.
	 */
	public static function register($temp)
	{
        $lang = Factory::getApplication()->getLanguage();
        $lang->load('com_users', JPATH_ADMINISTRATOR);
        $lang->load('com_users', JPATH_SITE);

		$params = ComponentHelper::getParams('com_users');
        $test_block = ComponentHelper::getParams('com_gausers')->get('test_block', 0);
        $temp_mship = ComponentHelper::getParams('com_gausers')->get('temp_mship', 0);

		// Initialise the table with JUser.
		$user = new User;
		$data = array();

		// Merge in the registration data.
		foreach ($temp as $k => $v)
		{
			$data[$k] = $v;
		}
		// Prepare the data for the user object.
		$data['email'] = PunycodeHelper::emailToPunycode($data['email1']);
		$data['password'] = $data['password1'];
		$useractivation = $params->get('useractivation');
		$sendpassword = $params->get('sendpassword', 1);

		// Check if the user needs to activate their account.
		if (($useractivation == 1) || ($useractivation == 2))
		{
			$data['activation'] = ApplicationHelper::getHash(UserHelper::genRandomPassword());
			$data['block'] = 1;
		}
		unset($data['password1']);
		unset($data['password2']);
		unset($data['email1']);
		unset($data['email2']);

		// Bind the data.
		if (!$user->bind($data)) {
			Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_REGISTRATION_BIND_FAILED'. $user->getError()), 'message');
			return false;
		}

		// Load the users plugin group.
		PluginHelper::importPlugin('user');

		// Store the data.
		if (!$user->save()) {
            Factory::getApplication()->enqueueMessage(Text::_('COM_USERS_REGISTRATION_SAVE_FAILED'. $user->getError()), 'message');
			//return false;
		}

		//if temp member don't do any activation stuff just return id
		$mship_id = Factory::getApplication()->getUserState('com_gausers.mship.type.id');
        if ($temp_mship == $mship_id) {
            return $user->id;
        }

        
        
        
        $app = Factory::getApplication();
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		// Compile the notification mail values.
		$data = $user->getProperties();
		$data['fromname'] = $app->get('fromname');
		$data['mailfrom'] = $app->get('mailfrom');
		$data['sitename'] = $app->get('sitename');
		$data['siteurl'] = Uri::root();

		// Handle account activation/confirmation emails.
		if ($useractivation == 2) {
			// Set the link to confirm the user email.
			$linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

			$data['activate'] = Route::link(
				'site',
				'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
				false,
				$linkMode,
				true
			);

			$mailtemplate = 'com_users.registration.user.admin_activation';

		} elseif ($useractivation == 1) {
			// Set the link to activate the user account.
			$linkMode = $app->get('force_ssl', 0) == 2 ? Route::TLS_FORCE : Route::TLS_IGNORE;

			$data['activate'] = Route::link(
				'site',
				'index.php?option=com_users&task=registration.activate&token=' . $data['activation'],
				false,
				$linkMode,
				true
			);

			$mailtemplate = 'com_users.registration.user.self_activation';
		}
		else
		{
			$mailtemplate = 'com_users.registration.user.registration_mail';
		}

		if ($sendpassword)
		{
			$mailtemplate .= '_w_pw';
		}

		// Try to send the registration email.
		try
		{
			$mailer = new MailTemplate($mailtemplate, $app->getLanguage()->getTag());
			$mailer->addTemplateData($data);
			$mailer->addRecipient($data['email']);
			if ($test_block) {
                $return = true;
            } else {
                $return = $mailer->send();
            }
		}
		catch (\Exception $exception)
		{
			try
			{
				Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');
				$return = false;
			}
			catch (\RuntimeException $exception)
			{
				$app->enqueueMessage(Text::_($exception->errorMessage()), 'warning');
				$return = false;
			}
		}

		// Send Notification mail to administrators
		if (($params->get('useractivation') < 2) && ($params->get('mail_to_admin') == 1))
		{
			// Get all admin users
			$query->clear()
				->select($db->quoteName(array('name', 'email', 'sendEmail')))
				->from($db->quoteName('#__users'))
				->where($db->quoteName('sendEmail') . ' = 1')
				->where($db->quoteName('block') . ' = 0');

			$db->setQuery($query);

			try
			{
				$rows = $db->loadObjectList();
			}
			catch (\RuntimeException $e)
			{
                $app->enqueueMessage(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 'warning');
				return false;
			}

			// Send mail to all superadministrators id
			foreach ($rows as $row)
			{
				try
				{
					$mailer = new MailTemplate('com_users.registration.admin.new_notification', $app->getLanguage()->getTag());
					$mailer->addTemplateData($data);
					$mailer->addRecipient($row->email);

        			if ($test_block) {
                        $return = true;
                    } else {
                        $return = $mailer->send();
                    }
				}
				catch (\Exception $exception)
				{
					try
					{
						Log::add(Text::_($exception->getMessage()), Log::WARNING, 'jerror');

						$return = false;
					}
					catch (\RuntimeException $exception)
					{
						Factory::getApplication()->enqueueMessage(Text::_($exception->errorMessage()), 'warning');

						$return = false;
					}
				}

				// Check for an error.
				if ($return !== true)
				{
                    $app->enqueueMessage(Text::_('COM_USERS_REGISTRATION_ACTIVATION_NOTIFY_SEND_MAIL_FAILED'), 'warning');
					return false;
				}
			}
		}

		// Check for an error.
		if ($return !== true)
		{
            $app->enqueueMessage(Text::_('COM_USERS_REGISTRATION_SEND_MAIL_FAILED'), 'warning');
			// Send a system message to administrators receiving system mails
			$db = Factory::getContainer()->get('DatabaseDriver');
			$query->clear()
				->select($db->quoteName('id'))
				->from($db->quoteName('#__users'))
				->where($db->quoteName('block') . ' = 0')
				->where($db->quoteName('sendEmail') . ' = 1');
			$db->setQuery($query);

			try
			{
				$userids = $db->loadColumn();
			}
			catch (\RuntimeException $e)
			{
                $app->enqueueMessage(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 'warning');
				return false;
			}

			if (count($userids) > 0)
			{
				$jdate     = new Date;
				$dateToSql = $jdate->toSql();
				$subject   = Text::_('COM_USERS_MAIL_SEND_FAILURE_SUBJECT');
				$message   = Text::sprintf('COM_USERS_MAIL_SEND_FAILURE_BODY', $data['username']);

				// Build the query to add the messages
				foreach ($userids as $userid)
				{
					$values = [
						':user_id_from',
						':user_id_to',
						':date_time',
						':subject',
						':message',
					];
					$query->clear()
						->insert($db->quoteName('#__messages'))
						->columns($db->quoteName(['user_id_from', 'user_id_to', 'date_time', 'subject', 'message']))
						->values(implode(',', $values));
					$query->bind(':user_id_from', $userid, ParameterType::INTEGER)
						->bind(':user_id_to', $userid, ParameterType::INTEGER)
						->bind(':date_time', $dateToSql)
						->bind(':subject', $subject)
						->bind(':message', $message);

					$db->setQuery($query);

					try
					{
						$db->execute();
					}
					catch (\RuntimeException $e)
					{
                        $app->enqueueMessage(Text::sprintf('COM_USERS_DATABASE_ERROR', $e->getMessage()), 'warning');
						return false;
					}
				}
			}

			return false;
		}

		return $user->id;
	}

}
