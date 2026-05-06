<?php

/**
 * @version    4.2.1
 * @package    Com_Gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Administrator\Helper;

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
use \Joomla\CMS\Helper\UserGroupsHelper;
use \Joomla\CMS\Mail\Mail;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Session\SessionInterface;
use \Joomla\CMS\Application\SiteApplication;

/**
 * Gabroadcast helper.
 * @since  1.6
 */
class GabroadcastHelper
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
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'bcast', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gabroadcast';
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
		//$tz = Factory::getConfig()->get('offset');
		//$date = Factory::getDate('now', $tz);
		//$today = date_format($date,'Y-m-d H:i:s');
		$today = Factory::getDate()->toSql();

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
	 * Set up a web asset object for use
	 */
	public static function setupGAWebApp()
	{
        // Boot the DI container.
        $container = Factory::getContainer();
        // Alias the session service key to the web session service.
        $container->alias(SessionInterface::class, 'session.web.site');
        // Get the application.
        $app      = $container->get(SiteApplication::class);
        //$template = $app->getTemplate(true);
        //$params   = $template->params;
        //$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        
        return $app;
	}

    /**
     * Load template over-rides when using modal view
     * This is important for Cloud White template scheme
     * because modals have the primary css over-ride any
     * css loaded in the template index.php file
     */
    public static function loadTmplStyleModal($wa)
	{
        $tmpl = Factory::getApplication()->getTemplate(true);
        if ($tmpl->params->get('colorName') == 'colors_white') {
        	$wa->addInlineStyle('.btn-primary {background-color:'.$tmpl->params->get('btnPbgColor').' !important;}');
        	$wa->addInlineStyle('.nav-link {color:var(--template-contrast) !important;}');
        	$wa->addInlineStyle('.form-check-input:checked, .form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color:'.$tmpl->params->get('btnPbgColor').' !important;}');
        	$wa->addInlineStyle('.page-item.active .page-link {color: var(--rkic41site-color-link); background-color: #c1cee1 !important; border-color: #dfe3e7 !important;}');
        	$wa->addInlineStyle('.form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color: var(--rkic41site-color-primary-border) !important;}');
        }
		return true;
	}

	/**
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion()
	{
		$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_gabroadcast/gabroadcast.xml'));

		return $componentXML['version'];
	}

	/**
	 * Gets the files attached to an item
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

	/**
	 * Gets the files attached to an item
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  array  The files
	 */
	public static function getField($id)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select(' * ')
			->from('#__fields')
			->where('id = ' . (int) $id);

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Gets the files attached to an item
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  array  The files
	 */
	public static function getUserGroup($id = 0)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('`#__usergroups`');
		$query->where('id = ' . (int) $id);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to get a record
	 * @params  int     $id key to the record
	 * @return  object
	 */
	public static function getRecord($id = 0)
	{
		//get all records into spreadsheet and email to requestor
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select( 'a.*' );
        $query->from('`#__gabroadcast_usernews` AS a');
        $query->select('u.name AS created_by_name');
        $query->join('LEFT', '#__users AS u ON u.id=a.created_by');
		$query->select('cat_id.title AS cat_id_name');
		$query->join('LEFT', '#__categories AS cat_id ON cat_id.id = a.cat_id');
		$query->where(' a.id = '.(int) $id );
		$db->setQuery((string)$query);
	    try {
	        // If it fails, it will throw a RuntimeException
	        $data = $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

	    return $data;
    }

	/**
	 * Method to get a record
	 * @params  int     $id key to the record
	 * @return  object
	 */
	public static function getBroadcasts($id = 0)
	{
		//get all records into spreadsheet and email to requestor
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select( 'a.*' );
        $query->from('`#__gabroadcast_usernews` AS a');
        $query->select('u.name AS created_by_name');
        $query->join('LEFT', '#__users AS u ON u.id=a.created_by');
		$query->select('cat_id.title AS cat_id_name');
		$query->join('LEFT', '#__categories AS cat_id ON cat_id.id = a.cat_id');
		if ($id) {
			$query->where('a.id = '.(int) $id);
		}
		$db->setQuery((string)$query);
	    try {
	        // If it fails, it will throw a RuntimeException
	        if ($id) {
				$data = $db->loadObject();
			} else {
				$data = $db->loadObjectList();
			}
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	    
	    return $data;
    }

	/**
	 * Method to get a record
	 * @params  int     $id key to the record
	 * @return  object
	 */
	public static function getLastBroadcast()
	{
		//get all records into spreadsheet and email to requestor
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select( ' max(a.modified_date) ' );
        $query->from('`#__gabroadcast_usernews` AS a');
        $query->where(' a.state = 6 ' );
		$db->setQuery((string)$query);
	    try {
	        return $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage('No last Broadcast - '.$e->getMessage(), 'warning');
	        return false;
	    }
	    
	    return $result;
    }

	public static function getBcastTypeOptions()
	{
		// get the broadcast types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.id, a.attach_lab ');
		$query->from(' #__gabroadcast_bcasts AS a ');
		$query->where(' a.state = 1 ' );
		$db->setQuery((string)$query);
	    try {
	        // If it fails, it will throw a RuntimeException
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

		return $options;
	}


	/**
	* Get broadcast type record
	* @param integer $id Broadcast ID
	* @return object
	* @@@ WARNING this is used by the Attachfile Field type
	*/
    public static function getBcastTypes($id = 0)
	{
		// get the broadcast types from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.id, a.attach_lab, a.attach_dir ');
		$query->from(' #__gabroadcast_bcasts AS a ');
		$query->where(' a.id = '.(int) $id );
		$db->setQuery((string)$query);
	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	}


	/**
	* Get category name using category ID
	* @param integer $category_id Category ID
	* @return mixed category name if the category was found, null otherwise
	*/
	public static function getCategoryNameByCategoryId($category_id) {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query
			->select('title')
			->from('#__categories')
			->where('id = ' . intval($category_id));

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

        if ($user->authorise('core.edit', 'com_gabroadcast')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($user->authorise('core.edit.own', 'com_gabroadcast') && $item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
    }

	public static function getListOptions($table = '#__contents', $name = 'Article', $field = 'a.title')
	{
		// get the user records for a listing to display
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$name.' - " as "text" UNION SELECT a.id as value, '.$field.' as text ');
		$query->from( $table . ' AS a ');
		if ($table == '#__users') {
			$query->where(' a.block = '. (int) 0 );
		} elseif ($table == '#__fields') {
			$query->where(' a.context = '. $db->Quote('com_users.user') );
			$query->where(' a.state = '. (int) 1 );
		} else {
			$query->where(' a.state = '. (int) 1 );
		}
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	}

	public static function getListvalueOptions($table, $name, $field)
	{
		$params = ComponentHelper::getParams('com_gabroadcast');
		$fieldID  = $params->get( 'cust_field', 1);

		// get the user records for a listing to display
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "All" as "value", " - Select '.$name.' - " as "text" UNION SELECT DISTINCT(a.value) as value, '.$field.' as text ');
		$query->from( $table . ' AS a ');
		$query->where(' a.field_id = '. (int) $fieldID );
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
	}

    /**
    *   Method to get all the local profile fields available
    *   @param   string  $fv  v = value of the profile field or f = profile key
    *   @return object field name and value
    */
	public static function getLocalProfileOptions($fv = 'f')
	{
		$params = ComponentHelper::getParams('com_gabroadcast');
		$profile_suffix  = $params->get( 'profile_suffix', 'b4wdc' );
		$locProf = 'profile'.$profile_suffix.'.';
		$profLen = strlen($locProf);
		$profField  = $params->get( 'prof_field' );
		$selectText = Text::_('COM_GABROADCAST_SELECT_PROF_OPTION');

        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		if ($fv === 'f') {
            $query->select(' 0 as "value", " - Select Profile Fields - " as "text" UNION SELECT DISTINCT(TRIM(a.profile_key)) as "value", substr(a.profile_key,'.(int)($profLen+1).') as "text"  ');
            $query->where(' substr(a.profile_key,1,'.(int)$profLen.') = '.$db->quote($locProf));
        } else {
            $query->select(' "All" as "value", '.$db->quote($selectText).' as "text" UNION SELECT DISTINCT(TRIM(a.profile_value)) as "value", TRIM(a.profile_value) as "text"  ');
            $query->where(' a.profile_key = '.$db->quote($profField));
        }
		$query->from(' #__user_profiles AS a ');
		$query->order(' text ASC ');
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Local Profile Values');
	        return false;
	    }

	}

	/* --------------------------------   Send Out Broadcast  ------------------------------------------------- */

    /**
    *   Method to get all member data for broadcast emails
    */
    public static function getMembersDetails($params)
	{
        $filter = 0;
        $settotest  = $params->get( 'set_test', 0 );
        $testid  = $params->get( 'user_id' );     /* user ID */
        $filter_users  = $params->get('filter_users', 0 );
        //$sendto_group  = $params->get( 'sendto_group', 2 );
        $xclude = $params->get( 'exclude_member' );     /* user ID */
        $cust_field  = $params->get('cust_field');
        $prof_field  = $params->get('prof_field');
        $profile_suffix  = $params->get('profile_suffix');
        $local_profile = 'profile'.$profile_suffix;
        if (isset($xclude)) {
			$csv = '';
			foreach ($xclude AS $x) {
				$csv .= $x.',';
			}
			$xclude = substr($csv,0,-1);
		} else {
			$xclude = 0;
		}

        $fltUsers = $params->get('filter_users', 0 );
        $filter = $params->get('filter_type', 'p');

		$db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.id as user_id, a.name, a.email, b.profile_value as altemail, c.profile_value as inc_altemail ');
		$query->select(' d.profile_value as address1, e.profile_value as address2, f.profile_value as suburb ');
		$query->select(' g.profile_value as pcode, h.profile_value as memtype, a.registerDate ');

		$query->from(' #__users as a');
		$query->join('LEFT','#__user_profiles AS b ON b.user_id = a.id AND b.profile_key = '.$db->Quote($local_profile.'.altemail') );
		$query->join('LEFT','#__user_profiles AS c ON c.user_id = a.id AND c.profile_key = '.$db->Quote($local_profile.'.inc_altemail') );
		$query->join('LEFT','#__user_profiles AS d ON d.user_id = a.id AND d.profile_key = '.$db->Quote('profile.address1'));
		$query->join('LEFT','#__user_profiles AS e ON e.user_id = a.id AND e.profile_key = '.$db->Quote('profile.address2'));
		$query->join('LEFT','#__user_profiles AS f ON f.user_id = a.id AND f.profile_key = '.$db->Quote('profile.city'));
		$query->join('LEFT','#__user_profiles AS g ON g.user_id = a.id AND g.profile_key = '.$db->Quote('profile.postal_code'));
		$query->join('LEFT','#__user_profiles AS h ON h.user_id = a.id AND h.profile_key = '.$db->Quote($local_profile.'.memtype') );

        if ($fltUsers) {
            if ($filter == 'c') {
                $query->select(' cf.value as custFld_value ');
                $query->join('LEFT','#__fields_values AS cf ON cf.item_id = a.id AND cf.field_id = '.$db->Quote($cust_field) );
                $query->where(' cf.value IS NOT NULL ');
    		} else {
                $query->select(' 0 as custFld_value ');
            }
    		if ($filter == 'p') {
                $query->select(' pf.profile_value as profFld_value ');
        		$query->join('LEFT','#__user_profiles AS pf ON pf.user_id = a.id AND pf.profile_key = '.$db->Quote($prof_field) );
                $query->where(' pf.profile_value IS NOT NULL');
    		} else {
                $query->select(' 0 as profFld_value ');
            }
		} else {
            $query->select(' 0 as custFld_value ');
            $query->select(' 0 as profFld_value ');
        }

    	$query->where(' a.block = 0 ');

		if ($settotest) {
            $query->where(' a.id = '. (int) $testid);
        } else {
    		if ($xclude != ',') {
				$query->where(' a.id NOT IN ('.$xclude.')' );
   			}
        }

		$db->setQuery((string)$query);
	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

	}

    /**
    *   Method to create a broadcast message ready to send
    *   Now that the usernew record is saved, build and send email
    * @params $data array data submitted in the form
    * @params $params component parameters to save getting them again
    * @params $usre object of the user submitting the form
    */
    public static function addParamsForMail($mailParams, $params, $user, $data)
	{
        $mailParams->attach_link  = $params->get('attach_link', 0);  // 1 = attach and 0 = link
        $mailParams->actually_send  = $params->get('actually_send', 1);
        $mailParams->disp_sent  = $params->get('disp_sent', 0);
        $mailParams->user_email	= $user->email;
        // this is the user logged in and sending the broadcast
        $sendingUser = $data['user_retaddr'];
        //$mailParams->unsubscribe = '';

		// Set up the return address if necessary
		if ($params->get('ret_address', 0) == 1) {
			$mailParams->showRetAddr = in_array($params->get('retaddr_group',0), $user->groups) ? 1 : 0;
		} elseif ($params->get('ret_address', 0) == 2 && $params->get('retaddr_user',0)) {
			$mailParams->showRetAddr = 1;
		} elseif ($params->get('ret_address', 0) == 2 && $sendingUser && !$params->get('retaddr_user',0)) {
			$mailParams->showRetAddr = 1;
		} else {
			$mailParams->showRetAddr = 0;
		}

        if (isset($sendingUser) && $mailParams->showRetAddr) {
			$mailParams->user_email	= self::getSpecificUser($sendingUser)->email;
		}

        if ($params->get('link_site', 1)) {
            $mailParams->sitename = '<a href="'.Uri::base().'">'.$mailParams->sitename.'</a>';
        }

        if ($params->get('incl_unsub', 1)) {
            $link = '<a href="'.Uri::base().'/index.php/'.Text::_('COM_GABROADCAST_UNSUBSCRIBE').'">'.Text::_('COM_GABROADCAST_UNSUBSCRIBE').'</a>';
            $unsubscribe = Text::sprintf('COM_GABROADCAST_UNSUB_MESSAGE', $link);
            $mailParams->unsubscribe = $unsubscribe;
        }

        return $mailParams;
	}

    /**
    *   Method to create a broadcast message ready to send
    *   Now that the usernew record is saved, build and send email
    * @params $data array data submitted in the form
    * @params $params component parameters to save getting them again
    * @params $usre object of the user submitting the form
    */
    public static function addNews($data, $params, $user)
	{
	    $app		= Factory::getApplication();

		$mailParams = new \stdClass();
        $mailParams->sitename	= $app->get('sitename');       // get site name
        $mailParams->mailfrom	= $app->get('mailfrom');       // system email address
        $mailParams->fromname	= $app->get('fromname');       // Site name or system name

        $mailParams = self::addParamsForMail($mailParams, $params, $user, $data);

		$limit_set = $params->get('limit_set',0);  // this is the trigger to know if a counter is required
		$mail_limit = $params->get('mail_limit',100);
		$cycle_time = $params->get('cycle_time',60);

        $sendto_group  = $params->get( 'sendto_group', 2 );
        $individual  = $params->get('indiv_bulk', 1);
        $incl_std_text  = $params->get('incl_std_text');
        //$link_site   = $params->get( 'link_site', 1 );
        $exclemail  = $params->get( 'exclude_email_pref', 'noemail' );
        $exclmbr  = $params->get( 'exclude_member' );

		/* -------------------------   Filter on Usergroups ----------------------------- */
        // setup and cycle through selected user groups if necessary
		$UGHelp = UserGroupsHelper::getInstance();
        if (isset($data['usergroup_only'])) {
	        if (is_array($data['usergroup_only'])) {
                $ugroups_display = '';
	            foreach ($data['usergroup_only'] AS $ugroup) {
					$ugroups_display .= $UGHelp->get($ugroup)->title;
					$ugroups_display .= '<br />';
				}
				$usergroups = $data['usergroup_only'];
	        } else {
	            $ugroups_display = $UGHelp->get($data['usergroup_only'])->title;
				$usergroups = 0;
	        }
        } else {
			$ugroups_display = $UGHelp->get($sendto_group)->title;
			$usergroups = 0;
		}

		/* -------------------------   Check attachments ----------------------------- */
        if (!empty($data['attach_file'])) {
			if ($mailParams->attach_link) {
				$attachfile = '<a href="'.Uri::base().$data['attach_file'].'">'.Text::_('COM_GABROADCAST_LINK_TEXT').'</a>';
			} else {
				$attachfile = $data['attach_file'];
	        }
        } else {
			$attachfile = null;
		}

        $news_id = $data['id'];
        $app->enqueueMessage('Message record '.$data['id'].' Saved', 'notice');

        // ----------------------------------------------------------------------

    	$subject	= $data['news_subject'] . ' (sent from the website)';
        $mailedto = '<h3>File Attached: '.$attachfile.'</h3>';
        if ($individual) {
			$mailedto .= '<p><strong>Mailed out to the following individual recipients:</strong></p><p>';
		} else {
			$mailedto .= '<p><strong>Mailed out to the following recipients in a bulk BCC:</strong></p><p>';
		}

		// get all members
		$members = self::getMembersDetails($params);

        $snailmail = array();
        $bccopies = array();
        $pending_bcast = array();
        $cntr = 0;
        $member_count = 0;
        $email_count = 0;

        foreach ($members as $u ) {
			// test if groups set in broadcast and set only users in selected group/s to be displayed
			$inGroup = false;
			$recipients = array();
			$u->altemail = str_replace('"','',$u->altemail ?? '');
			$ug = UserHelper::getUserGroups($u->user_id);
			
			/* ------------   Test for usergroup & exclude settings  ---------------   */
            if (is_array($usergroups)) {
				foreach ($usergroups AS $g) {
					$inGroup = ($inGroup || (in_array($g, $ug) && !$inGroup)) ? true : false;
				}
			} else {
				$inGroup = ($inGroup || (in_array($sendto_group, $ug) && !$inGroup)) ? true : false;
			}

			if (is_array($exclmbr)) {
				$inGroup = (!in_array($u->user_id, $exclmbr) && $inGroup) ? true : false;
			}

			/* ------------   If in the group to be sent  ---------------   */
            if ($inGroup) {
				$pri_email = true;
				$sec_email = true;

				/* ------------   check if non - email type user and ignore if true ---------------   */
                if ((strlen($exclemail) > 0) && (substr($u->email, 0, strlen($exclemail)) == $exclemail) ) {
					$pri_email = false;
				}

				if ((isset($u->inc_altemail) && $u->inc_altemail) && isset($u->altemail) && ($u->altemail > ' ')) {
					if ((strlen($exclemail) > 0) && (substr($u->altemail, 0, strlen($exclemail)) == $exclemail) ) {
						$sec_email = false;
					}
				} else {
					$sec_email = false;
				}

				/* ------------   Filter out based on user filter settings  ---------------   */
                if ($params->get('filter_users',0)) {
                    if ($params->get('filter_type','p') == 'p') {
                        if ($data['user_proffld'] == "All" || str_contains($u->profFld_value, $data['user_proffld'])) {
                            // proceed
                        } else {
                            // get next member record
                            continue;
                        }
                    } else {
                        if ($data['user_custfld'] == "All" || $data['user_custfld'] == $u->custFld_value) {
                            // proceed
                        } else {
                            // get next member record
                            continue;
                        }
                    }
                }

				if (!$pri_email && !$sec_email) {
					$snailmail[] = $u->name;
                } else {

					/* ------------------------------------------------------------------------------------------------------------------------- */
                    if ($individual) {     // 1 = invdividual
                        $member_count++;
	                    // Prepare email body
	                    $body = self::setupEmailBody($data, $u, $attachfile, $params, $mailParams, $individual, 0);

                		if ($pri_email && !$sec_email) {
                            // don't include alt email address
                            if (!in_array($u->email, $recipients)) {
                                $recipients[] = $u->email;
                                $mailedto .= $u->email .'<br />';
                                $email_count++;
                            }
                        } elseif (!$pri_email && $sec_email) {
                            // don't include primary email address
                            if (!in_array($u->altemail, $recipients)) {
                                $recipients[] = $u->altemail;
                                $mailedto .= $u->altemail .'<br />';
                                $email_count++;
                            }
                        } else {
                            if (!in_array($u->email, $recipients)) {
                                $recipients[] = $u->email;
                                $mailedto .= $u->email .'<br />';
                                $email_count++;
                            }
                            if (!in_array($u->altemail, $recipients)) {
                                $recipients[] = $u->altemail;
                                $mailedto .= $u->altemail .'<br />';
                                $email_count++;
                            }
                        }

						// set bbc to be ignored
						$mailParams->bccopy = 0;

                        if ($limit_set) {   // when limit is set to yes, all sent in bulk format
	                        if ($member_count >= $mail_limit) {
                                $pending_bcast = array_merge($pending_bcast,$recipients);
                                $prep_pending = self::preparePendingRecord($data['id'], $data['news_detail'], $pending_bcast);
								$member_count = 0;    // reset counter
								$pending_bcast = [];  // reset collected emails
							} else {
                                $pending_bcast = array_merge($pending_bcast,$recipients);
							}
						} else {
                            $sentOK = self::sendEmailToRecipients($subject, $body, $recipients, $attachfile, $mailParams);
							if ($sentOK !== true) {
								$sendtoemails = implode(',', $recipients ?? '');
								$app->enqueueMessage('Send Error - Mailer did not work for '.$u->name.' ('.$sendtoemails.')', 'message');
							}
						}

	                    unset($recipients);
						$cntr++;
					/* ------------------------------------------------------------------------------------------------------------------------- */
					} else {    // 0 = bulk

                		if ($pri_email && !$sec_email) {
                            // don't include alt email address
                            if (!in_array($u->email, $bccopies)) {
                                $bccopies[] = $u->email;
                                $mailedto .= $u->email .'<br />';
                                $email_count++;
                            }
                        } elseif (!$pri_email && $sec_email) {
                            // don't include primary email address
                            if (!in_array($u->altemail, $bccopies)) {
                                $bccopies[] = $u->altemail;
                                $mailedto .= $u->altemail .'<br />';
                                $email_count++;
                            }
                        } else {
                            if (!in_array($u->email, $bccopies)) {
                                $bccopies[] = $u->email;
                                $mailedto .= $u->email .'<br />';
                                $email_count++;
                            }
                            if (!in_array($u->altemail, $bccopies)) {
                                $bccopies[] = $u->altemail;
                                $mailedto .= $u->altemail .'<br />';
                                $email_count++;
                            }
                        }

	                    $cntr++;
	                } // end of individual or bulk test
				} // end of sending email test
            } // end of test for in group

        } // end foreach cycle through member records

		/* ------------------------------------------------------------------------------------------------------------------------- */
		// now if set to bulk email, then send the one email with BCC for each member
		if (!$individual) {   // 0 = bulk
			$recipients[] = $mailParams->mailfrom;

	        $body = self::setupEmailBody($data, $u, $attachfile, $params, $mailParams, $individual, 0);

			// need to break up recipients (bcc's) if limit set
			if ($limit_set) {
				$member_count = count($bccopies);
				$j = ($member_count / $mail_limit) + 1;
				//ini_set('max_execution_time', $params->get('max_time', 120));
				for ($i=0; $i<$j; $i++) {
                    if ($i == 0) {
						$startpoint = 0;
					} else {
						$startpoint = ($i * $mail_limit);
					}
					$pending_bcast = array_slice($bccopies, $startpoint, $mail_limit);
					if (!empty($pending_bcast)) {
						$mailParams->bccopy = $pending_bcast;
						$prep_pending = self::preparePendingRecord($data['id'], $data['news_detail'], $pending_bcast);
					}
				}
			} else {
				$mailParams->bccopy = $bccopies;
				$sentOK = self::sendEmailToRecipients($subject, $body, $recipients, $attachfile, $mailParams);
				if (!$sentOK) {
					$app->enqueueMessage('Bulk No Limit Send Not OK', 'danger');
				}
            }
            //$max_time = ini_get('max_execution_time');
			//Factory::getApplication()->enqueueMessage('Max Time ('.$max_time.')', 'warning');
		} else {
			// just make sure the last batch from cycle is loaded
			if ($limit_set) {
				$prep_pending = self::preparePendingRecord($data['id'], $data['news_detail'], $pending_bcast);
			}
		}
        $mailedto .= '</p>';

		/* ------------------------------------------------------------------------------------------------------------------------- */
		// check if other groups should receive broadcasts and send
        $menuitem = $app->getMenu()->getActive();
        $menuparams = $menuitem->getParams();
        $bc_type = $menuparams->get('broadcast_type');

        // set up extra clubs to be sent to
        $sendClubs   = $params->get( 'send_clubs', 0);
        $bcTypeClubs   = $params->get( 'incl_bcast_type', 0);
        $inclclubs   = $params->get( 'incl_clubs', '');

        if ($sendClubs && $bc_type == $bcTypeClubs && $inclclubs > '') {
            $recipients = array($mailParams->mailfrom);
            // explode the string to an array
			$mailParams->bccopy = explode(',',$inclclubs);
			$incl_clubs = str_replace(',',', ',$inclclubs);

            $body = self::setupEmailBody($data, $u, $attachfile, $params, $mailParams, $individual, $sendClubs);

            $sentOK = self::sendEmailToRecipients($subject, $body, $recipients, $attachfile, $mailParams);

            $mailedto .= '<p>Email also sent to Clubs = '.$incl_clubs.'.</p>';
		}

        $app->enqueueMessage('Message Sent to '.$cntr.' members', 'notice');
        $app->enqueueMessage('Message Sent to '.$email_count.' emails', 'notice');

		/* ------------------------------------------------------------------------------------------------------------------------- */
		// update mailto listing to include snailmail, group and return address details
        if (!empty($snailmail)) {
            $snailmailto = '<p><strong>The following members need to be advised by mail:</strong></p><p>';
            foreach ($snailmail as $sm) {
                $snailmailto .= $sm.'<br />';
            }
            $snailmailto .= '</p>';
            $mailedto .= $snailmailto;
            $app->enqueueMessage($snailmailto, 'notice');
        }

		if (isset($data['usergroup_only']) && is_array($data['usergroup_only'])) {
			$mailedto .= '<p><strong>The below groups were selected.</strong></p>';
			$mailedto .= '<p>'.$ugroups_display.'</p>';
		}

		if (isset($data['user_retaddr']) && $mailParams->showRetAddr) {
			$retaddr_user	= self::getSpecificUser($data['user_retaddr']);
			$retaddr_name	= $retaddr_user->name;
			$retaddr_email	= $retaddr_user->email;
			$mailedto .= '<p><strong>The return email address was set to:</strong></p>';
			$mailedto .= '<p>'.$retaddr_name.' ('.$retaddr_email.')</p>';
			$app->enqueueMessage('Return Address set to:'.$retaddr_email, 'notice');
		}

        $addrecipients = self::updateDespatchedTo($mailedto, $data);
        //Factory::getApplication()->setUserState('com_gabroadcast.test.data', $mailedto);

        return true;

	}

    /**
    *   Method to duplicate details fo broadcast message in a pending state ready to send out
    */
    public static function preparePendingRecord($id = 0, $news_detail = '', $pending_bcast = array())
    {
		if ($id) {
			$db		= Factory::getContainer()->get('DatabaseDriver');
			$query	= $db->getQuery(true);
	        $query->clear();
			$query->from(' #__gabroadcast_usernews ');
			$query->select(' * ' );
			$query->where(' id = '. (int) $id );
			$db->setQuery((string)$query);

		    try {
		        $object = $db->loadObject();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage('Failed to duplicate broadcast - '.$e->getMessage(), 'message');
		        $object = false;
		    }

		    if ($object) {
				$object->id = 0;
				$object->state = 5;
				$object->news_detail = $news_detail;
				$object->pending_bcast = implode(',', $pending_bcast);
				$result = Factory::getContainer()->get('DatabaseDriver')->insertObject('#__gabroadcast_usernews', $object);
				if ($result) {
					Factory::getApplication()->enqueueMessage('Successfully duplicated broadcast', 'message');
					return true;
				} else {
					Factory::getApplication()->enqueueMessage('Failed duplicated broadcast', 'message');
					return false;
				}
			}
			return $object;
	    }
        return false;
	}

    /**
    *   Method to build the email body
    */
    public static function setupEmailBody($data, $u, $attachfile, $params, $mailParams, $individual, $send_clubs = 0)
    {
        $html_headfoot  = $params->get('html_headfoot', 0);
        $html_header  = $params->get('html_header');
        $html_footer  = $params->get('html_footer');
        $incl_std_text  = $params->get('incl_std_text');
        $sig_block  = $params->get('sig_block');
        $inclUnsub  = $params->get('incl_unsub', 0);

        // Because saving an image in the header or footer removes the domain part of url
        // add it back here if an image exists
        $html_header = str_replace('src="images', 'src="'.Uri::root().'images', $data['html_header']);
        $html_footer = str_replace('src="images', 'src="'.Uri::root().'images', $data['html_footer']);

        // Prepare email body
        $body = '<html><body><div style="max-width: 480px; margin:0 auto;">';
		if ($html_headfoot) {
            $body .= $html_header;
    	}
		if ($individual && $u) {
			if ($send_clubs) {
				$body .= '<p>Dear Club, </p><p> </p>'.$data['news_detail'];
			} else {
				$body .= '<p>Dear '.$u->name . ', </p><p> </p>'.$data['news_detail'];
			}
		} else {
			if ($send_clubs) {
				$body .= '<p>Dear Club, </p><p> </p>'.$data['news_detail'];
			} else {
				$body .= '<p>Dear '.Text::_($params->get('bulk_label','Member')).', </p><p> </p>'.$data['news_detail'];
			}
		}
        //Link to File
        if ($mailParams->attach_link && !empty($attachfile)) {
			$body .= '<p>'.$attachfile.'</p>';
		}
		$body .= '<p> </p><p>'.$mailParams->sitename.'</p>';

		if ($sig_block) {
            $body .= '<p>'.$params->get('sig_name').'<br />';
			$body .= $params->get('sig_title').'</p>';
    	}

        if (!empty($incl_std_text)) {
            $body .= '<p> </p><p>'.$incl_std_text.'</p>';
        }

		if ($inclUnsub) {
            $body .= $data['unsubDet'];
    	}

		if ($html_headfoot) {
            $body .= $html_footer;
    	}

    	//$body .= $mailParams->unsubscribe;

        $body	.= '</div></body></html>';

        return $body;

	}

    /**
    *   Method to actually send out broadcast emails
    */
    public static function sendEmailToRecipients($subject, $body, $recipients, $attachfile, $mailParams)
    {
        // Build the email and send
        $mail = Factory::getMailer();
        $mail->isHtml(true);
        $mail->addRecipient($recipients);

        if ($mailParams->bccopy) {
			$mail->addBcc($mailParams->bccopy);
		}

        if ($mailParams->showRetAddr) {
			$mail->addReplyTo($mailParams->user_email);
		} else {
			$mail->addReplyTo($mailParams->mailfrom);
		}

        $mail->setSender(array($mailParams->mailfrom, $mailParams->fromname));
        $mail->setFrom($mailParams->mailfrom, $mailParams->fromname);
        $mail->setSubject($subject);
        $mail->setBody($body);
        if ($attachfile) {
            if (is_file($attachfile) && !$mailParams->attach_link) {
                $mail->addAttachment($attachfile);
            }
        }

		if ($mailParams->actually_send)
        {
    		$sent = $mail->Send();
    		if ($sent !== true) {
    			return false;
    		} else {
                if ($mailParams->disp_sent) {
                    Factory::getApplication()->enqueueMessage('Sent to '.$recipients['0'].' ', 'message');
                }
    			return true;
    		}
		} else {
            if ($mailParams->disp_sent) {
                Factory::getApplication()->enqueueMessage('Would be Sent to '.$recipients['0'].' ', 'message');
            }
			return true;
		}
	}

    /**
    *   Method to duplicate details fo broadcast message in a pending state ready to send out
    */
    public static function sendPending($id = 0)
    {
		if ($id) {
			$db		= Factory::getContainer()->get('DatabaseDriver');
			$query	= $db->getQuery(true);
	        $query->clear();
			$query->from(' #__gabroadcast_usernews ');
			$query->select(' * ' );
			$query->where(' id = '. (int) $id );
			$db->setQuery((string)$query);

		    try {
		        $bcast = $db->loadObject();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage('Failed to get broadcast - '.$e->getMessage(), 'message');
		    }

			if (isset($bcast->pending_bcast) && $bcast->pending_bcast > '') {
                $app = Factory::getApplication();
    			$params		= ComponentHelper::getParams('com_gabroadcast');
                $mailParams = new \stdClass();
                $mailParams->bccopy = explode(',', $bcast->pending_bcast);  // explodes a string into array using delimiter
                $mailParams->attach_link = $params->get('attach_link', 0);
                $mailParams->sitename = $app->get('sitename');
                $mailParams->mailfrom = $app->get('mailfrom');
                $mailParams->fromname = $app->get('fromname');
                $mailParams->showRetAddr = 0;
    			$recipients = array($mailParams->mailfrom);
    			$u = 0;
    			$individual = 0;
    			$send_clubs = 0;
    			
    			if ($mailParams->attach_link && !empty($bcast->attach_file)) {
    				$attachfile = '<a href="'.Uri::base().$bcast->attach_file.'">'.Text::_('COM_GABROADCAST_LINK_TEXT').'</a>';
    			} else {
    				$attachfile = $bcast->attach_file;
    			}
    
    
    			$body = self::setupResendEmailBody($bcast->news_detail, $u, $attachfile, $params, $mailParams, $individual, $send_clubs);
    
    			$send = self::sendEmailToRecipients($bcast->news_subject, $body, $recipients, $attachfile, $mailParams);
    
    			if ($send !== true) {
    				return false;
    			}
			} else {
                Factory::getApplication()->enqueueMessage('No Pending Recipients', 'message');
			}
	    }

        return true;

	}

    /**
    *   Method to build the email body
    */
    public static function setupResendEmailBody($data, $u, $attachfile, $params, $mailParams, $individual, $send_clubs = 0)
    {
        // Because saving an image in the content removes the domain part of url
        // add it back here if an image exists
        $data = str_replace('src="images', 'src="'.Uri::root().'images', $data);

        // Prepare email body
        $body = '<html><body><div style="max-width: 480px; margin:0 auto;">';

		if ($individual && $u) {
			if ($send_clubs) {
				$body .= '<p>Dear Club, </p><p> </p>'.$data;
			} else {
				$body .= '<p>Dear '.$u->name . ', </p><p> </p>'.$data;
			}
		} else {
			if ($send_clubs) {
				$body .= '<p>Dear Club, </p><p> </p>'.$data;
			} else {
				$body .= '<p>Dear '.Text::_($params->get('bulk_label','Member')).', </p><p> </p>'.$data;
			}
		}
        //Link to File
        if ($mailParams->attach_link && !empty($attachfile)) {
			$body .= '<p>'.$attachfile.'</p>';
		}
		$body .= '<p> </p><p>'.$mailParams->sitename.'</p>';

		if ($sig_block) {
            $body .= '<p>'.$params->get('sig_name').'<br />';
			$body .= $params->get('sig_title').'</p>';
    	}

        if (!empty($incl_std_text)) {
            $body .= '<p> </p><p>'.$incl_std_text.'</p>';
        }

		if ($inclUnsub) {
            $body .= $data['unsubDet'];
    	}

		if ($html_headfoot) {
            $body .= $html_footer;
    	}

        $body	.= '</div></body></html>';

        return $body;

	}

    /**
    *   Method to update the broadcast record with details of who sent to etc
    */
    public static function updateDespatchedTo($mailedto, $data = 0)
	{
        // update the news record to capture the list of recipients
		$db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->update(' #__gabroadcast_usernews ');
		$query->set(' comment = '.$db->Quote($mailedto) );
		$query->where(' id = '. (int) $data['id'] );
		$db->setQuery((string)$query);

	    try {
	        $db->execute();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

		return true;
	}

	/* --------------------------------   Action Log  ------------------------------------------------- */
	/**
	* Get extension details using extension name
	* @param string extension name
	* @return extension object
	*/
	public static function getExtensionDetails($ext_name) {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('SELECT * FROM #__extensions WHERE name = ' . $db->Quote($ext_name));
		return $db->loadObject();
	}

	/**
	 * Get the Model from another component for use
	 * @param   string  $name    The model name.
	 * @param   string  $prefix  The class prefix.
	 * @param   array   $config  Configuration array for model.
	 * @return object	The model
	 */
	public function getForeignModel($name = 'Actionlog', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		$fmodel = Factory::getApplication()->bootComponent('com_actionlogs')->getMVCFactory()->createModel($name, $prefix, $config);

		return $fmodel;
	}

	/**
	 * Record transaction details in log record
	 * @param   object  $user    Saves getting the current user again.
	 * @param   int     $tran_id  The transaction id just created or updated
	 * @param   int     $id  Passed id reference from the form to identify if new record
	 * @return  boolean	True
	 */
    public static function recordActionLog($user = null, $tran_id = 0, $id = 0)
	{
		// get the component details such as the id
		$extension =  self::getExtensionDetails('com_gabroadcast');
		// get the transaction details for use in the log for easy reference
        $tran = self::getBroadcasts($tran_id);
        $con_type = "usernew";
        if (!$id) { $type = 'New Bcast '; } else { $type = 'Update Bcast '; }

		$message = array();
		$message['action'] = $con_type;
		$message['type'] = $type.$tran->news_subject . ' (' . $tran->created_by_name . ')';
		$message['id'] = $tran->id;
		$message['title'] = $extension->name;
		$message['extension_name'] = strtolower($extension->name);
		$message['itemlink'] = "index.php?option=com_gabroadcast&task=usernew.edit&id=".$tran->id;
		$message['userid'] = $user->id;
		$message['username'] = $user->username;
		$message['accountlink'] = "index.php?option=com_users&task=user.edit&id=".$user->id;
		
		$messages = array($message);
		
		$messageLanguageKey = Text::_('COM_GABROADCAST_USERNEW');
		$context = strtolower($extension->name).'.'.$con_type;

		$fmodel = self::getForeignModel('Actionlog', 'Administrator');

		$fmodel->addLog($messages, $messageLanguageKey, $context, $user->id);

		return true;
	}
}

