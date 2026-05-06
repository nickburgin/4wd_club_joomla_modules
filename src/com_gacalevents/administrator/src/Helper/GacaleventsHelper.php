<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Administrator\Helper;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\MVC\Model\ItemModel;
use \Joomla\Data\DataObject;
use \Joomla\CMS\Component\ComponentHelper;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Installer\Installer;
use \Joomla\Filesystem\Path;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\User\UserHelper;
use \Joomla\CMS\User\UserFactoryInterface;

/**
 * Main helper.
 * @since  1.6
 */
class GacaleventsHelper
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
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'event', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gacalevents';
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
	 * Gets the record of an item
	 * @param   int     $pk     The item's id
	 * @param   string  $table  The table's name
	 * @param   string  $field  The field's name
	 * @return  array  The files
	 */
	public static function getRecord($pk, $table, $field)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query
			->select($field)
			->from($db->quotename($table))
			->where('id = ' . (int) $pk);
		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion()
	{
		$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_gacalevents/gacalevents.xml'));

		return $componentXML['version'];
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

        if ($user->authorise('core.edit', 'com_gacalevents')) {
            $permission = true;
        } else {
            if (isset($item->created_by)) {
                if ($user->authorise('core.edit.own', 'com_gacalevents') && $item->created_by == $user->id) {
                    $permission = true;
                }
            } else {
                $permission = true;
            }
        }

        return $permission;
    }

    /**
     * Gets a category name from id reference
     */
    public static function getCategoryName($id) {

        // get the club names from the database
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' title ');
		$query->from(' #__categories ');
		$query->where(' id = '.(int) $id );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadResult();
	    } catch (\RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
	        return false;
	    }
    }

    /**
     * Gets records from database
     * @param   text table name
     * @param   text label name
     * @param   text field name
     * @return  bool
     */
	public static function getListOptions($table = '#__gacalevents_events', $label = 'Event', $field = 'a.event_desc' )
	{

		// get the records
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, '.$field.' as text ');
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
     * Gets category records from database
     * @param   text extension name
     * @param   text label name
     * @param   text field name
     * @return  bool
     */
	public static function getCategoryOptions($ext = 'com_gacalevents', $label = 'Event Type', $field = 'title' )
	{

		// get the records
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT a.id as value, a.'.$field.' as text ');
		$query->from( ' #__categories AS a ' );
		$query->where(' a.published = 1' );
		$query->where(' a.extension = '.$db->quote($ext) );
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
     * Gets record from database
     * @param   id event id reference
     * @return  object
     */
	public static function getEvent($id = 0)
	{

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(" a.*, d.title as depart_point_name, c.title as cat_id_name, DATE_FORMAT(depart_date,'%D %M, %Y') AS ddate ");
		$query->from( '#__gacalevents_events as a ' );
		$query->join( 'LEFT', '#__categories as d ON d.id = a.depart_point' );
		$query->join( 'LEFT', '#__categories as c ON c.id = a.cat_id' );
		$query->where(' a.id = ' . (int) $id );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

	}

    /**
     * Gets records from database
     * @param   id event id reference
     * @return  array of objects
     */
	public static function getAttendees($id = 0)
	{
		$params = ComponentHelper::getParams('com_gacalevents');
		$profsuf  = $params->get( 'profile_suffix' );
		$key = 'profile'.$profsuf.'.';
		/*******  Status of Attendee Records  **********
		 *  1 = attending
		 *  0 = appology
		 ************************************************/
		// get the records of attendees
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.*, b.name AS attendee_name, b.email AS attendee_email ');
		$query->select(' p.profile_value AS partner_name, e.profile_value AS partner_email ');
		$query->select(' IF(i.profile_value IS NOT NULL, i.profile_value, 0) AS inc_altemail ');
		$query->from( '#__gacalevents_attendees a ' );
		$query->join( 'LEFT', '#__users as b ON b.id = a.attendee' );
		$query->join( 'LEFT', '#__user_profiles as p ON p.user_id = a.attendee AND p.profile_key = '.$db->quote($key.'partner') );
		$query->join( 'LEFT', '#__user_profiles as e ON e.user_id = a.attendee AND e.profile_key = '.$db->quote($key.'altemail') );
		$query->join( 'LEFT', '#__user_profiles as i ON i.user_id = a.attendee AND i.profile_key = '.$db->quote($key.'inc_altemail') );
		$query->where(' a.event = ' . (int) $id );
		$query->where(' a.state IN (0, 1) ' );
		$query->order(' a.pub_name ASC, b.name ASC, a.pub_sname ASC, a.pub_fname ASC ' );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

	}

    /**
     * Gets records from database
     * @param   id event id reference
     * @param   userid user id reference
     * @return  array of objects
     */
	public static function checkAttendee($id = 0, $userid = 0)
	{
		/*******  Status of Attendee Records  **********
		 *  1 = attending
		 *  0 = appology
		 ************************************************/
		// get the records of attendees
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.* ');
		$query->from( '#__gacalevents_attendees a ' );
		$query->where(' a.event = ' . (int) $id );
		$query->where(' a.attendee = ' . (int) $userid );
		$query->where(' a.state IN (0, 1) ' );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

	}

	/**
	 * Create list of options to select from
	 * @return  array of objects
	 */
    public static function getProfileFieldOptions()
	{
		$params = ComponentHelper::getParams('com_gacalevents');
		$profsuf  = $params->get( 'profile_suffix' );
		$key = 'profile'.$profsuf.'.';
		$keyLen = strlen($key);
		$array = array();

		// get the records
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' DISTINCT(substr(a.profile_key,'.($keyLen + 1).')) ');
		$query->from( '#__user_profiles AS a ' );
		$query->where(' substr(a.profile_key,1,'.$keyLen.') = '.$db->quote($key) );
		$query->group(' a.profile_key ' );
		$query->order('  substr(a.profile_key,'.($keyLen + 1).') ASC ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadColumn();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
	    
	    foreach ($options AS $opt) {
			$array[$opt] = $opt;
		}
		
		return $array;
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
    public static function recordActionLog($user = null, $id = 0, $status = 0)
	{
		$attend_apol = $status == 1 ? 'Attending' : 'Apology for';
        $con_type = "attendee";
        $details = self::getEvent($id);
		$message = array();
		$message['action'] = $con_type;
		$message['type'] = $attend_apol . ' ' . $details->title . ' - ' . $details->depart_date;
		$message['id'] = $id;
		$message['title'] = 'com_gacalevents';
		$message['extension_name'] = 'com_gacalevents';
		$message['itemlink'] = "index.php?option=com_gacalevents&task=attendee.edit&id=".$id;
		$message['userid'] = $user->id;
		$message['username'] = $user->username;
		$message['accountlink'] = "index.php?option=com_users&task=user.edit&id=".$user->id;
		
		$messages = array($message);
		
		$messageLanguageKey = Text::_('COM_GACALEVENTS_ATTENDEE');
		$context = strtolower($extension->name).'.'.$con_type;

		//$fmodel = GacaleventsHelper::getForeignModel('Actionlog', 'Administrator');

		//$fmodel->addLog($messages, $messageLanguageKey, $context, $user->id);

		return true;
	}

	/**
	* Check if user passed vaccination
	* @param int user_id value
	* @return boolean
	*/
	public static function isUserVaccinated($user_id = 0, $vax_field = 0, $exempt_field = 0) {

		$db1 = Factory::getContainer()->get('DatabaseDriver');
		$query1 = $db1->getQuery(true);
		$db1->setQuery('SELECT value FROM #__fields_values WHERE field_id = '.(int) $vax_field.' AND item_id = '.$db1->Quote($user_id) );
		try {
			$vaxed = $db1->loadResult();
		} catch (RuntimeException $e) {
			$vaxed = 0;
		}

		$db2 = Factory::getContainer()->get('DatabaseDriver');
		$query2 = $db2->getQuery(true);
		$db2->setQuery('SELECT value FROM #__fields_values WHERE field_id = '.(int) $exempt_field.' AND item_id = '.$db2->Quote($user_id) );
		try {
			$exempt = $db2->loadResult();
		} catch (RuntimeException $e) {
			$exempt = 0;
		}
		
		if ($vaxed || $exempt) { return true; } else { return false; }
	}

}

