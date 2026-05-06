<?php

/**
 * @version     4.3.3
 * @package     com_gabroadcast
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2019 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gabroadcast\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\User\UserHelper;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\Path;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\CMS\User\User;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Access\Access;
use Joomla\CMS\Helper\UserGroupsHelper;
use Joomla\CMS\Mail\Mail;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Installer\Installer;
use Joomla\Session\SessionInterface;
use Joomla\CMS\Application\SiteApplication;
use Joomla\Database\ParameterType;   //INTEGER, STRING, BOOLEAN, NULL, LARGE_OBJECT
use GlennArkell\Component\Gabroadcast\Administrator\Helper\GaemailHelper;

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
     * Prints out a variable value in human readable format
     */
    public static function print_r2($val){
        echo '<pre>Test<br />';
        \print_r($val);
        echo  '</pre>';
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
	 * Gets the files attached to an item
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  array  The files
	 */
	public static function getArticle($id = 0)
	{
		//$catId = ComponentHelper::getParams('com_gabroadcast')->get( 'article_cat', 0);
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from('#__content');
		$query->where('id = ' . (int) $id);
		//$query->where('catid = ' . (int) $catId);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Method to get a record
	 * @params  int     $id key to the record
	 * @return  object
	 */
	public static function getRecord($table, $field, $id)
	{
		//get all records into spreadsheet and email to requestor
        $db    = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quotename($table))
    		->where($db->quotename($field) . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
		$db->setQuery($query);
	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }
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
        $query->select( 
            [
            'a.*',
            $db->quotename('u.name', 'created_by_name'),
            $db->quotename('cat_id.title', 'cat_id_name'),
            ]
            )
            ->from($db->quotename('#__gabroadcast_usernews', 'a'))
            ->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.created_by'))
            ->join('LEFT', $db->quoteName('#__categories', 'cat_id'), $db->quoteName('cat_id.id') . ' = ' . $db->quoteName('a.cat_id'));
		if ($id) {
			$query->where($db->quotename('a.id') . ' = :id')
    			->bind(':id', $id, ParameterType::INTEGER);
		}
		$db->setQuery($query);
	    try {
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
        $query->select('max(a.modified_date)')
            ->from($db->quotename('#__gabroadcast_usernews', 'a'))
            ->where($db->quotename('a.state') . ' = 6');
		$db->setQuery($query);
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
		$query->select(' a.id, a.attach_lab ')
            ->from($db->quotename('#__gabroadcast_bcasts', 'a'))
            ->where($db->quotename('a.state') . ' = 1');
		$db->setQuery($query);
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
		$query->select(' a.id, a.attach_lab, a.attach_dir ')
            ->from($db->quotename('#__gabroadcast_bcasts', 'a'))
            ->where($db->quotename('a.id') . ' = :id')
            ->bind(':id', $id, ParameterType::INTEGER);
		$db->setQuery($query);
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
			->from($db->quotename('#__categories'))
            ->where($db->quotename('id') . ' = :id')
            ->bind(':id', $category_id, ParameterType::INTEGER);

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
        $user       = Factory::getApplication()->getIdentity();

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

	public static function getListOptions($table = '#__content', $name = 'Article', $field = 'a.title')
	{
		$catId = ComponentHelper::getParams('com_gabroadcast')->get( 'article_cat', 0);
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
		} elseif ($table == '#__content') {
            if ($catId) {
                $query->where('catid = ' . (int) $catId);
            }
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
			$xclude = ',';
		}

        $fltUsers = $params->get('filter_users', 0 );
        $filter = $params->get('filter_type', 'p');

		$db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(
            [
            $db->quotename('a.id', 'user_id'), 
            $db->quotename('a.name'), 
            $db->quotename('a.email'),
            $db->quotename('a.registerDate'),
            $db->quotename('b.profile_value', 'altemail'),
            $db->quotename('c.profile_value', 'inc_altemail'),
            $db->quotename('d.profile_value', 'address1'),
            $db->quotename('e.profile_value', 'address2'),
            $db->quotename('f.profile_value', 'suburb'),
            $db->quotename('g.profile_value', 'pcode'),
            $db->quotename('h.profile_value', 'partner'),
            ]
        )

		->from($db->quotename('#__users', 'a'))
        ->join('LEFT', $db->quoteName('#__user_profiles', 'b'), $db->quoteName('b.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('b.profile_key') . ' = ' . $db->Quote($local_profile.'.altemail'))
        ->join('LEFT', $db->quoteName('#__user_profiles', 'c'), $db->quoteName('c.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('c.profile_key') . ' = ' . $db->Quote($local_profile.'.inc_altemail'))
        ->join('LEFT', $db->quoteName('#__user_profiles', 'd'), $db->quoteName('d.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('d.profile_key') . ' = ' . $db->Quote('profile.address1'))
        ->join('LEFT', $db->quoteName('#__user_profiles', 'e'), $db->quoteName('e.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('e.profile_key') . ' = ' . $db->Quote('profile.address2'))
        ->join('LEFT', $db->quoteName('#__user_profiles', 'f'), $db->quoteName('f.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('f.profile_key') . ' = ' . $db->Quote('profile.city'))
        ->join('LEFT', $db->quoteName('#__user_profiles', 'g'), $db->quoteName('g.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('g.profile_key') . ' = ' . $db->Quote('profile.postal_code'))
        ->join('LEFT', $db->quoteName('#__user_profiles', 'h'), $db->quoteName('h.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('h.profile_key') . ' = ' . $db->Quote($local_profile.'.partner'));

        if ($fltUsers) {
            if ($filter == 'c') {
                $query->select($db->quoteName('cf.value', 'custFld_value'))
                    ->join('LEFT', $db->quoteName('#__fields_values', 'cf'), $db->quoteName('cf.item_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('cf.field_id') . ' = ' . $db->Quote($cust_field))
                    ->where($db->quoteName('cf.value') . ' IS NOT NULL');
    		} else {
                $query->select('0 as custFld_value');
            }
    		if ($filter == 'p') {
                $query->select($db->quoteName('pf.profile_value', 'profFld_value'))
                    ->join('LEFT', $db->quoteName('#__user_profiles', 'pf'), $db->quoteName('pf.user_id') . ' = ' . $db->quoteName('a.id') . ' AND ' . $db->quoteName('pf.profile_key') . ' = ' . $db->Quote($prof_field))
                    ->where($db->quoteName('pf.profile_value') . ' IS NOT NULL');
    		} else {
                $query->select('0 as profFld_value');
            }
		} else {
            $query->select('0 as custFld_value');
            $query->select('0 as profFld_value');
        }

    	$query->where($db->quoteName('a.block') . ' = 0');

		if ($settotest) {
            $query->where($db->quotename('a.id') . ' = :testid')
            ->bind(':testid', $testid, ParameterType::INTEGER);
        } else {
    		if ($xclude != ',') {
				$query->where($db->quotename('a.id') . ' NOT IN ('.$xclude.')' );
   			}
        }

		$db->setQuery($query);
	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage());
	        return false;
	    }

	}

	/* --------------------------------   Create News Record  ------------------------------------------------- */
    /**
    *   Method to create a broadcast message ready to send
    *   Now that the usernew record is saved, build and send email
    * @params $data array data submitted in the form
    * @params $params component parameters to save getting them again
    * @params $usre object of the user submitting the form
    */
    public static function createNewsEmail($data, $params, $user)
	{
		$app = Factory::getApplication();
		$app->getLanguage()->load('com_gabroadcast', JPATH_ADMINISTRATOR);
        $limit_set = $params->get('limit_set',0);  // this is the trigger to know if a counter is required
		$mail_limit = $params->get('mail_limit',50);
		$cycle_time = $params->get('cycle_time',5);
		$replyTo = $params->get('ret_address',0);
		$replyUser = $params->get('retaddr_user',0);
		$replyGroup = $params->get('retaddr_group',0);
		$dispSent = $params->get('disp_sent',0);
		$individual = $params->get('indiv_bulk', 1);
		$attach = $params->get('attach_link', 1);   // 1 = attach, 0 = link
        $chunkCntr = 0;
        $cntr = 0;
        $pend = array();

        $edata = GaemailHelper::setupBody($data, $user, $params);

        $attachLabel = $attach ? 'Attached:' : 'Linked:';
        $mailedto = '<h3>File '.$attachLabel.' '.$edata['filename'].'</h3>';
		if ($replyTo == 2) {   // return address to be set to user
			if ($replyUser) {    // this is when the person sending can set return address
                $sender = self::getSpecificUser($data['user_retaddr']);
                $mailedto .= '<p><strong>The return email address was set to:</strong></p>';
    			$mailedto .= '<p>'.$sender->name.' ('.$sender->email.')</p>';
    			$app->enqueueMessage('Return Address set to:'.$sender->email, 'notice');
    			$edata['replyto'] = array($sender->email, $sender->name);
    			//Factory::getApplication()->setUserState('com_gabroadcast.test.data', $edata['replyto']);
			} else {   // else the user sending the message is set as return address
                $mailedto .= '<p><strong>The return email address was set to:</strong></p>';
    			$mailedto .= '<p>'.$user->name.' ('.$user->email.')</p>';
    			$app->enqueueMessage('Return Address set to:'.$user->email, 'notice');
    			$edata['replyto'] = array($user->email, $user->name);
    			//Factory::getApplication()->setUserState('com_gabroadcast.test.data', $edata['replyto']);
            }
		} elseif ($replyTo == 1) {  // return address to be set to all in the group selected
    		$gpUsers = Access::getUsersByGroup($replyGroup);
    		if ($gpUsers && \is_array($gpUsers)) {
        		// replyTo can only be to one email so the first one will do
                $sender = self::getSpecificUser($gpUsers[0]);
                $mailedto .= '<p><strong>The return email address was set to:</strong></p>';
    			$mailedto .= '<p>'.$sender->name.' ('.$sender->email.')</p>';
        		$app->enqueueMessage('Return Address set to:'.$sender->email, 'notice');
        		$edata['replyto'] = array($sender->email, $sender->name);
        		//Factory::getApplication()->setUserState('com_gabroadcast.test.data', $edata['replyto']);
    		}
		}
        if ($individual) {
			$mailedto .= '<p><strong>Mailed out to the following individual recipients:</strong></p><p>';
		} else {
			$mailedto .= '<p><strong>Mailed out to the following recipients in a bulk BCC:</strong></p><p>';
		}

        // get all members
        $members = self::getMembersDetails($params);

        // check members against params to filter out and return list of recipients
        $recipients = GaemailHelper::getRecipients($members, $data, $params);
        $numberRecips = \count($recipients);

        if ($limit_set && $numberRecips >= (4 * $mail_limit)) {
            \ini_set('max_execution_time', $params->get('max_time', 60));
        }

        //Factory::getApplication()->setUserState('com_gabroadcast.test.data', $recipients);
        $chunks = \array_chunk($recipients, $mail_limit);

        foreach ($chunks as $chunk) {
            $chunkCntr++;
            $sentDisp = '';
            if ($individual) {    // 1 = individual, 0 = bulk
                foreach ($chunk as $m) {
                    $cntr++;
                    $mailedto .= $m['name'].' - '.$m['email'].'<br />';
                    $edata['recips'] = array($m);
                    $edata['name'] = $m['name'];
                    $sent = GaemailHelper::sendEmailTemplate('com_gabroadcast.message', $edata, $edata['filename'], $edata['site_link'], $edata['attach_file']);
                    if ($sent && $dispSent) {
                        $app->enqueueMessage(Text::_('COM_GABROADCAST_MAIL_SENT_SUCCESSFUL').' - '.$m['name'].' - '.$m['email'], 'message');
                    }
                }
            } else {
                $edata['bcc_recips'] = $chunk;
                $edata['name'] = $params->get('bulk_label', 'Members');
                foreach ($chunk as $m) {
                    $mailedto .= $m['name'].' - '.$m['email'].'<br />';
                    if ($params->get('disp_sent', 1)) {
                        $sentDisp .= $m['name'].' - '.$m['email'].'<br />';
                    }
                }
                $sent = GaemailHelper::sendEmailTemplate('com_gabroadcast.message', $edata, null, $edata['site_link'], $edata['attach_file']);
                if ($sent && $dispSent) {
                    $app->enqueueMessage(Text::sprintf('COM_GABROADCAST_SENT_MESSAGE', $chunkCntr, $sentDisp), 'message');
                }
            }
        }

		// update mailto listing to include snailmail, group and return address details
        $snailmail = Factory::getApplication()->getUserState('com_gabroadcast.snailmail.list');
        if (!empty($snailmail)) {
            $snailmailto = '<p><strong>The following members need to be advised by mail:</strong></p><p>';
            $snailmailto .= implode('<br />', $snailmail);
            $snailmailto .= '</p>';
            $mailedto .= $snailmailto;
            $app->enqueueMessage($snailmailto, 'notice');
        }

		if (isset($data['usergroup_only']) && $data['usergroup_only']) {
            $mailedto .= '<p><strong>The below groups were selected.</strong></p>';
            $gpTitle = Access::getGroupTitle($data['usergroup_only']);
			$mailedto .= '<p>'.$gpTitle.'</p>';
		}

        // Update who sent to
        $object = self::getRecord('#__gabroadcast_usernews', 'id', $data['id']);
    	if ($object) {
    		$object->comment = $mailedto;
    		//$object->pending_bcast = $pendemails;
    		Factory::getContainer()->get('DatabaseDriver')->updateObject('#__gabroadcast_usernews', $object, 'id');
    	}
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

