<?php
/**
 * @version    5.3.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Administrator\Helper;

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
use Joomla\CMS\Installer\Installer;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GainvoiceHelper;

/**
 * Main Component helper.
 * @since  1.6
 */
class GatripsysHelper
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
	public static function getHTTPQuery($existQ = null, $viewTask = 'view', $contModel = 'trip', $ref = 'id', $linkId = 0)
	{
		if (!$existQ) {
			$query_string = array();
			$query_string['option'] = 'com_gatripsys';
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
     * Gets todays date object based on global timezone settings
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
		unset($user->password);
		unset($user->activation);
		unset($user->params);
		unset($user->lastResetTime);
		unset($user->resetCount);
		unset($user->otpKey);
		unset($user->otep);
		unset($user->requireReset);

		return $user;
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
	 * Get the version of the component
	 * @return version element of manifest
	 */
	public static function getComponentVersion()
	{
		$componentXML = Installer::parseXMLInstallFile(Path::clean(JPATH_ADMINISTRATOR . '/components/com_gatripsys/gatripsys.xml'));

		return $componentXML['version'];
	}

    /**
     * Load template over-rides when using modal view
     * This is important for Cloud White template scheme in RKIC41Site
     */
    public static function loadTmplStyleModal($wa)
	{
    	$tmpl = Factory::getApplication()->getTemplate(true);
        if ($tmpl->params->get('colorName') == 'colors_white') {
            $wa->addInlineStyle('.btn-primary {background-color:'.$tmpl->params->get('btnPbgColor', '#a1b5d1').' !important;}');
        	$wa->addInlineStyle('.nav-link {color:var(--template-contrast) !important;}');
        	$wa->addInlineStyle('.form-check-input:checked, .form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color:'.$tmpl->params->get('btnPbgColor').' !important;}');
        	$wa->addInlineStyle('.page-item.active .page-link {color: var(--rkic41site-color-link); background-color: #c1cee1 !important; border-color: #dfe3e7 !important;}');
        	$wa->addInlineStyle('.form-select[multiple] option:checked, [multiple].custom-select option:checked {background-color: var(--rkic41site-color-primary-border) !important;}');
        }

		return true;
	}

    /**
     * Gets the name of the status based on the state field of the record
     */
    public static function getStatusName($state = null)
	{
        switch ($state) {
            case 0 : $state_name = 'Unpublished'; break;
            case 1 : $state_name = 'Proposed'; break;
            case 2 : $state_name = 'Booking'; break;
            case 4 : $state_name = 'Closed'; break;
            case 5 : $state_name = 'Cancelled'; break;
            case 6 : $state_name = 'Finalised'; break;
            default: $state_name = 'No Set Correctly';
        }

		return $state_name;
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
     * Gets the edit permission for an user
     * @param   mixed  $item  The item
     * @return  bool
     */
    public static function canUserEdit($user, $item)
    {
        $permission = false;

        if ($user->authorise('core.edit', 'com_gatripsys')) {
            $permission = true;
        } else {
            if (isset($item->created_by) && $item->created_by) {
                if ($user->authorise('core.edit.own', 'com_gatripsys') && ($item->created_by == $user->id || $item->leader = $user->id)) {
                    $permission = true;
                }
            } else {
                if ($user->authorise('core.create', 'com_gatripsys')) {
                    $permission = true;
                }
            }
        }

        return $permission;
    }

	public static function getListOptions($table = '#__gatripsys_trips', $label = 'Trip', $field = 'tran_desc' )
	{
		// get the records
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT id as value, '.$field.' as text ');
		$query->from( $table );
        if ($table == '#__users') {
    		$query->where(' block = 0' );
    	} else {
    		$query->where(' state = 1' );
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

	public static function getCategoryOptions($ext = 'com_gatripsys', $label = 'Category Type', $field = 'title' )
	{
		// get the categories
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$label.' - " as "text" UNION SELECT id as value, '.$field.' as text ');
		$query->from( ' #__categories ' );
		$query->where(' published = 1' );
		$query->where(' extension = '.$db->quote($ext) );
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

	public static function getCategory($id)
	{
		// get the category
        $db = Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' * ');
		$query->from( ' #__categories ' );
		$query->where(' id = ' . (int) $id );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

	}

    /**
    *   Method to get values for a member based on field id
    *   @param int $id field_id reference
    *   @param int $userId user id reference
    *   @return object field name and value
    */
	public static function getCustomFieldValue($id = 0, $userId = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' b.title As fieldName, a.value AS fieldValue ');
		$query->from(' #__fields_values AS a ');
		$query->join('LEFT', ' #__fields AS b ON b.id = a.field_id');
		$query->where(' a.field_id = '.(int) $id );
		$query->where(' a.item_id = '.(int) $userId );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Field Values');
	        return false;
	    }

	}

    /**
    *   Method to get the required record
    *   @param string $table table name
    *   @param int $id id reference
    *   @return object record data
    */
	public static function getRecord($table = '', $id = 0)
	{
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.* ');
		$query->from($db->quoteName($table, 'a'));
		$query->where(' a.id = '.(int) $id );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage().' Record');
	        return false;
	    }

	}

	/**
	 * Create a new record based on params
	 * @param   string  $table  Table name.
	 * @param   string  $key    The reference key
	 * @param   string  $value  Key value tyo be loaded
	 * @return boolean	false on failure
	 */
	public static function createNewRecord($table, $key, $value, $params)
	{
        $state = $params->get('auto_book_apprv', 0) ? 1 : 0;
        $today = Factory::getDate()->toSql();
        $new_rec = new \stdClass();
        $new_rec->id = 0;
        $new_rec->state = $state;
        $new_rec->created_by = Factory::getApplication()->getIdentity()->id;
        $new_rec->created_date = $today;
        $new_rec->trip_id = $key;
        $new_rec->user_id = $value;
        $new_rec->approved_by = $value;
        $new_rec->in_party = 1;
        $new_rec->comment = 'Auto created when approved by Trip Co-ordinator.';

		try {
			$result = Factory::getContainer()->get('DatabaseDriver')->insertObject($table, $new_rec);
			Factory::getApplication()->enqueueMessage(Text::_('COM_GATRIPSYS_ATTENDEE_SUCCESSFUL'), 'notice');
		} catch (RuntimeException $e) {
			Factory::getApplication()->enqueueMessage($e->getMessage().' '.Text::_('COM_GATRIPSYS_ATTENDEE_FAILED'), 'danger');
			return false;
		}
		return true;
	}

	/**  ************************   All from the old ************************************ */

	/**
	 * Get the Model from another component for use
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 * @return object	The model
	 */
	public function getSiteModel($comp = 'com_gatripsys', $name = 'Trip', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		$fmodel  = Factory::getApplication()->bootComponent($comp)->getMVCFactory()->createModel($name, $prefix, $config);

		return $fmodel;
	}

	public static function getAttendeeCount($id = 0)
	{
		$attendances = false;
		if ($id) {
			// get the attendees
	        $db		= Factory::getContainer()->get('DatabaseDriver');
			$query	= $db->getQuery(true);
	        $query->clear();
			$query->select(' count(a.id) AS vehicles, sum(a.in_party) AS persons ');
			$query->select(' t.max_no AS max_veh, t.max_people AS max_pers ');
			$query->from(' #__gatripsys_attendees AS a ');
			$query->join('LEFT', ' #__gatripsys_trips AS t ON t.id = a.trip_id ');
			$query->where(' a.trip_id = '. (int) $id );
			$query->where(' a.state IN (0,1) ' );
			$query->group(' a.trip_id ');
			$db->setQuery((string)$query);

		    try {
		        // If it fails, it will throw a RuntimeException
		        $attendances = $db->loadObject();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
		        return false;
		    }
        }

		return $attendances;
	}

	/**
	 * Gets a list of all attendees for a trip.
	 * @params   $id int trip id reference
	 * @return   Associative Array List
	 * @since    3.3.8
	 */
	public static function getAttendeesForTrip($id = 0)
	{
		// get the attendees
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' * ');
		$query->from(' #__gatripsys_attendees ');
		$query->where(' trip_id = '. (int) $id );
		$query->where(' state = 0 ' );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadAssocList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
	}

	public static function getTripsOptions()
	{

        $params = ComponentHelper::getParams('com_gatripsys');
        $disp_daysago = $params->get('disp_daysago', 0);
		// get the current date-time based on timezone
        $todaysDate = self::getTodaysDate();
        $date = new Date($todaysDate);
		$today = $date->modify('-'.$disp_daysago.' day');

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(" '0' as 'value', ' - Select Trip - ' as 'text' UNION SELECT a.id as value, concat(date_format(a.dept_date,'%Y-%m-%d'), ' - ',a.title) as text ");
		$query->from(' #__gatripsys_trips AS a ');
		$query->where(' a.state = 1 ' );
		/* Only current or future trips to be listed */
		$query->where(' a.regby_date >= '. $db->Quote($today).' OR a.dept_date >= '. $db->Quote($today).' OR a.ret_date >= '. $db->Quote($today) );
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	public static function getCurrentTripsOptions()
	{

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(" '0' as 'value', ' - Select Trip - ' as 'text' UNION SELECT a.id as value, concat(date_format(a.dept_date,'%Y-%m-%d'), ' - ',a.title) as text ");
		$query->from(' #__gatripsys_trips AS a ');
		$query->where(' a.state NOT IN (4,5,6) ' );
		$query->where(' ( a.title > "" AND a.dept_date IS NOT NULL ) ' );
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	public static function getTripLeader($id = 0)
	{
		// get the record in the trip
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select('a.leader');
		$query->from(' #__gatripsys_trips AS a ');
		$query->where(' a.id = '. (int) $id );
		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        $leader = $db->loadResult();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $leader;
	}

	public static function getTripleadersOptions($name = 'Tripleader')
	{

        $params = ComponentHelper::getParams('com_gatripsys');
        $restrict_leader = $params->get('restrict_leader', 0);
        $leader_gp = $params->get('leader_gp', 0);

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$name.' - " as "text" UNION SELECT a.id as value, a.name as text ');
		if ($restrict_leader) {
			$query->from(' #__user_usergroup_map AS u ');
			$query->join('LEFT', ' #__users AS a ON a.id = u.user_id AND a.block = 0 ');
			$query->where(' u.group_id = '.(int)$leader_gp );
		} else {
			$query->from(' #__users AS a ');
		}
		$query->where(' a.block = 0 ' );
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

	public static function getTailendsOptions($name = 'Tailend Charley')
	{
		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$name.' - " as "text" UNION SELECT a.id as value, a.name as text ');
		$query->from(' #__users AS a ');
		$query->where(' a.block = 0 ' );
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	public static function getCommitteeOptions()
	{

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select Administrators - " as "text" UNION SELECT a.id as value, a.name as text ');
		$query->from(' #__users AS a ');
		$query->where(' a.block = 0 ' );
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	public static function getMembersOptions($name = 'Member')
	{
		$params  = ComponentHelper::getParams('com_gatripsys');

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' "0" as "value", " - Select '.$name.' - " as "text" UNION SELECT id as value, name as text ');
		$query->from(' #__users ');
		$query->where(' block = 0 ' );
		$query->order(' text ASC ' );
		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		return $options;
	}

	public static function getMembershipOptions($name = 'Membership')
	{
		$params  = ComponentHelper::getParams('com_gatripsys');
		$mship_single = $params->get('mship_single',0);
		$profsuf = $params->get('prof_pref','b4wdc');

		if ($mship_single) {
			$options = array();
			$options[] = array('value'=>0, 'text'=>' - Select '.$name.' - ');

	        $db = Factory::getContainer()->get('DatabaseDriver');
	        $query = $db->getQuery(true);
            $query->select(' a.id, a.name, a.email, a.block, a.registerDate, a.lastvisitDate ' );
            $query->select(' if(j.profile_value IS NULL, "", j.profile_value) AS inc_altemail ');
            $query->select(' if(k.profile_value IS NULL, "", k.profile_value) AS altemail ');
            $query->select(' if(h.profile_value IS NULL, "", h.profile_value) AS partner ');
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS firstname " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS first_name " );
            $query->select(" If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 2), ' ', -1) ,NULL) as middle1_name " );
            $query->select(" If( If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1), null, If( length(a.name) - length(replace(a.name, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 3), ' ', -1) ,NULL)) as middle2_name " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS surname " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS last_name " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS firstnamep " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS first_namep " );
            $query->select(" If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 2), ' ', -1) ,NULL) as middle1_namep " );
            $query->select(" If( If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL) = SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1), null, If( length(h.profile_value) - length(replace(h.profile_value, ' ', ''))>1, SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 3), ' ', -1) ,NULL)) as middle2_namep " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS surnamep " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS last_namep " );
            $query->from('#__users AS a');
            $query->join('LEFT', ' #__user_profiles AS h ON a.id = h.user_id AND h.profile_key = "profile'.$profsuf.'.partner" ');
            $query->join('LEFT', ' #__user_profiles AS j ON a.id = j.user_id AND j.profile_key = "profile'.$profsuf.'.inc_altemail" ');
            $query->join('LEFT', ' #__user_profiles AS k ON a.id = k.user_id AND k.profile_key = "profile'.$profsuf.'.altemail" ');
	        $query->where(' block = 0 ' );
	        $query->order(' surname ASC ' );
	        $db->setQuery((string)$query);
			try {
				$members =  $db->loadObjectList();
			} catch (RuntimeException $e) {
				Factory::getApplication()->enqueueMessage($e->getMessage(), 'danger');
		        return false;
		    }

			foreach ($members as $member) {
				$mbr_name = GainvoiceHelper::combineNames($member);
				$options[] = array('value'=>$member->id, 'text'=>$mbr_name);
			}
		} else {
			// get the list of records
	        $db		= Factory::getContainer()->get('DatabaseDriver');
			$query	= $db->getQuery(true);
	        $query->clear();
			$query->select(' "0" as "value", " - Select '.$name.' - " as "text" UNION SELECT id as value, name as text ');
			$query->from(' #__users ');
			$query->where(' block = 0 ' );
			$query->order(' text ASC ' );
			$db->setQuery((string)$query);

		    try {
		        $options = $db->loadObjectList();
		    } catch (RuntimeException $e) {
		        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
		        return false;
		    }
		}

		return $options;
	}

	public static function getCurrentMembers()
	{
		$params  = ComponentHelper::getParams('com_gatripsys');
		$prof_pref  = $params->get('prof_pref');
		$p_key = 'profile'.$prof_pref.'.altemail';
		$e_key = 'profile'.$prof_pref.'.inc_altemail';
		$part_key = 'profile'.$prof_pref.'.partner';

        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select(' a.name, a.email, a.registerDate ');
        $query->select(' p.profile_value AS altemail, e.profile_value AS inc_altemail ');
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS firstname " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 1), ' ', -1) AS first_name " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS surname " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(a.name, ' ', 4), ' ', -1) AS last_name " );
        $query->select(' if(g.profile_value IS NULL, "", g.profile_value) AS partner ');
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 1), ' ', -1) AS firstnamep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 1), ' ', -1) AS first_namep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 4), ' ', -1) AS surnamep " );
        $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(g.profile_value, ' ', 4), ' ', -1) AS last_namep " );
		$query->select(' a.id AS user_id ');
		$query->from(' #__users AS a ');
		$query->join('LEFT', '#__user_profiles AS p ON a.id = p.user_id AND p.profile_key = '.$db->Quote($p_key));
		$query->join('LEFT', '#__user_profiles AS e ON a.id = e.user_id AND e.profile_key = '.$db->Quote($e_key));
		$query->join('LEFT', '#__user_profiles AS g ON a.id = g.user_id AND g.profile_key = '.$db->Quote($part_key));
		$query->where(' a.block = 0 ' );
		$query->order(' substr(a.name, (LENGTH(a.name) - LOCATE(" ", REVERSE(a.name))+1)) ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		$newdata = array();
        foreach ($options AS $ta) {
			$ta->inc_altemail = str_replace('"', '', $ta->inc_altemail ?? '');
			$ta->altemail = str_replace('"', '', $ta->altemail ?? '');
			$ta->full_name = GainvoiceHelper::combineNames($ta);
			$newdata[] = $ta;
		}
		$options = $newdata;

		return $options;
	}

	public static function getProfFieldsOptions()
	{

		$params  = ComponentHelper::getParams('com_gatripsys');
		$prof_pref = $params->get('prof_pref');
		$prof_pref = 'profile'.$prof_pref;
		$prof_len = strlen($prof_pref);
		$def_select = Text::_('JOPTION_SELECT_PROFILE_ITEM');

		// get the user records in the supplier group
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->select('  "0" as "value", "'.$def_select.'" as "text" UNION SELECT a.profile_key as value, substr(a.profile_key,(instr(a.profile_key, ".")+1)) as text ');
		$query->from(' #__user_profiles AS a ');
		$query->where(' substr(a.profile_key,1,'.$prof_len.') = '.$db->Quote($prof_pref) );
		$query->group(' text ' );
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
	* Get attendee records based on the trip data
	* @param trip record id
	* @return object list of attendees
	*/
	public static function getTripAttendees($trip_id = 0)
	{
		$params  = ComponentHelper::getParams('com_gatripsys');
		$mship_single = $params->get('mship_single',0);
		$profsuf = $params->get('prof_pref','b4wdc');
		
		// get the attendee records from the Trip System
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
        $query->select(' a.*, att.name AS attend_name, apprv.name AS approver_name, if(a.state = 1, "Approved", "Pending") AS status ');
        $query->select(' prof.profile_value AS primary_contact, att.email AS attend_email ');
        $query->select(' vk.profile_value AS vehicle_make, vd.profile_value AS vehicle_model, vr.profile_value AS vehicle_rego ');
        $query->select(' vt.profile_value AS vehicle_trans, vy.profile_value AS vehicle_year, vf.profile_value AS vehicle_fuel ');
        $query->select(' inv.id AS inv_id, inv.state AS inv_state ');
        $query->select(' if(ipe.profile_value IS NULL, 0, ipe.profile_value) AS inc_altemail ');
        $query->select(' if(pe.profile_value IS NULL, "", pe.profile_value) AS altemail ');



		$query->from(' #__gatripsys_attendees AS a');
		$query->join('LEFT', '#__users AS att ON att.id=a.user_id');
		$query->join('LEFT', '#__users AS apprv ON apprv.id=a.approved_by');
		$query->join('LEFT', '#__user_profiles AS prof ON prof.user_id=a.user_id AND prof.profile_key = "profile.phone"');
		$query->join('LEFT', '#__user_profiles AS vk ON vk.user_id=a.user_id AND vk.profile_key = "profile'.$profsuf.'.vehicle_make"');
		$query->join('LEFT', '#__user_profiles AS vd ON vd.user_id=a.user_id AND vd.profile_key = "profile'.$profsuf.'.vehicle_model"');
		$query->join('LEFT', '#__user_profiles AS vr ON vr.user_id=a.user_id AND vr.profile_key = "profile'.$profsuf.'.vehicle_rego"');
		$query->join('LEFT', '#__user_profiles AS vt ON vt.user_id=a.user_id AND vt.profile_key = "profile'.$profsuf.'.vehicle_trans"');
		$query->join('LEFT', '#__user_profiles AS vy ON vy.user_id=a.user_id AND vy.profile_key = "profile'.$profsuf.'.vehicle_year"');
		$query->join('LEFT', '#__user_profiles AS vf ON vf.user_id=a.user_id AND vf.profile_key = "profile'.$profsuf.'.vehicle_fuel"');
		$query->join('LEFT', '#__user_profiles AS pe ON pe.user_id=a.user_id AND pe.profile_key = "profile'.$profsuf.'.altemail"');
		$query->join('LEFT', '#__user_profiles AS ipe ON ipe.user_id=a.user_id AND ipe.profile_key = "profile'.$profsuf.'.inc_altemail"');
		$query->join('LEFT', '#__gatripsys_invoices AS inv ON inv.att_id=a.id AND inv.state IN (1,2) ');
		if ($mship_single) {
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(att.name, ' ', 1), ' ', -1) AS firstname, att.name AS name " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(att.name, ' ', 1), ' ', -1) AS first_name " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(att.name, ' ', 4), ' ', -1) AS surname " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(att.name, ' ', 4), ' ', -1) AS last_name " );
	        $query->select(' if(h.profile_value IS NULL, "", h.profile_value) AS partner ');
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS firstnamep " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS first_namep " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS surnamep " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS last_namep " );
	        $query->join('LEFT', ' #__user_profiles AS h ON a.user_id = h.user_id AND h.profile_key = "profile'.$profsuf.'.partner" ');
		}
		$query->where(' a.trip_id = '.$db->Quote($trip_id) );
		$query->where(' a.state NOT IN (2, -2) ' );
		$query->order(' a.id ASC ' );
		$db->setQuery((string)$query);

	    try {
	        $trip_atts = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

		$newdata = array();
		foreach ($trip_atts AS $ta) {
			$ta->primary_contact = str_replace('"','',$ta->primary_contact ?? '');
			$ta->vehicle_make = str_replace('"','',$ta->vehicle_make ?? '');
			$ta->vehicle_model = str_replace('"','',$ta->vehicle_model ?? '');
			$ta->vehicle_rego = str_replace('"','',$ta->vehicle_rego ?? '');
			$ta->inc_altemail = str_replace('"','',$ta->inc_altemail ?? '');
			$ta->altemail = str_replace('"','',$ta->altemail ?? '');
			$ta->name = $ta->attend_name;
			$ta->email = $ta->attend_email;

			if ($mship_single) {
    			$ta->partner = str_replace('"','',$ta->partner ?? '');
    			$ta->firstnamep = str_replace('"','',$ta->firstnamep ?? '');
    			$ta->surnamep = str_replace('"','',$ta->surnamep ?? '');
				$ta->attend_name = GainvoiceHelper::combineNames($ta);
			}
			$ta->att_name = $ta->attend_name;
			$ta->full_name = $ta->attend_name;
			$newdata[] = $ta;
		}
		$trip_atts = $newdata;

		return $trip_atts;

	}

	/**
	* Get trip data based on the attendee id
	* @param att_id record id
	* @return object trip data
	*/
	public static function getTripFromAttend($att_id = 0)
	{
		// get the attendee records from the Trip System
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
        $query->select(' a.user_id, a.trip_id, a.in_party, b.* ');
        $query->select(' att.email AS attend_email, att.name AS attend_name ');
        $query->select(' l.email AS leader_email, l.name AS leader_name ');
		$query->from(' #__gatripsys_attendees AS a');
		$query->join('LEFT', '#__users AS att ON att.id=a.user_id');
		$query->join('LEFT', '#__gatripsys_trips AS b ON b.id=a.trip_id');
		$query->join('LEFT', '#__users AS l ON l.id=b.leader');
		$query->where(' a.id = '.$db->Quote($att_id) );
		$db->setQuery((string)$query);

	    try {
	        return $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
	}

	/**
	* Get attendee records based on the trip data
	* @param trip record id
	* @return object list of attendees
	*/
	public static function getCalendarAttendees($cal_id = 0)
	{
		// get the attendee records from the Trip System
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
        $query->select(' * ');
		$query->from(' #__gaevents_attendees ');
		$query->where(' event = '.$db->Quote($cal_id) );
		$query->where(' state = 1 ' );
		$db->setQuery((string)$query);

	    try {
	        // If it fails, it will throw a RuntimeException
	        return $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
	}

	/**
	* Get incident records based on the trip data
	* @param trip record id
	* @return object list of incidents
	*/
	public static function getTripIncidents($trip_id = 0)
	{
		// get the attendee records from the Trip System
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
        $query->select(" a.*, auth.name AS user_id_name, date_format(incid_date, '%d %M %Y') AS incid_date_display ");
		$query->from(' #__gatripsys_incidents AS a');
		$query->join('LEFT', '#__users AS auth ON auth.id=a.user_id');
		$query->where(' a.trip_id = '.$db->Quote($trip_id) );
		$query->where(' a.state = 1 ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
		return $options;
	}

	public static function countTripIncidents($trip_id = 0)
	{
		// get the attendee records from the Trip System
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
        $query->select(' count(*) ');
		$query->from(' #__gatripsys_incidents');
		$query->where(' trip_id = '.$db->Quote($trip_id) );
		$query->where(' state = 1 ' );
		$db->setQuery((string)$query);

	    try {
	        $options = $db->loadObjectList();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
		return $options;
	}

	public static function getTrip($trip_id = 0)
	{
		// get the trip record from the Trip System
        $db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
        $query->select(' * ');
		$query->from(' #__gatripsys_trips');
		$query->where(' id = '.(int) $trip_id );
		$db->setQuery((string)$query);

	    try {
	        $trip = $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }
		return $trip;
	}

	/**
	* Get the trip data
	* @param trip record id
	* @return trip object
	*/
	public static function getTripInformation($trip_id = 0)
	{
		$params  = ComponentHelper::getParams('com_gatripsys');
		$mship_single = $params->get('mship_single',0);
		$profsuf = $params->get('prof_pref','b4wdc');
		// Create a new query object.
		$db    = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select( ' a.*' );
		$query->from('#__gatripsys_trips AS a');
		if ($mship_single) {
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(u.name, ' ', 1), ' ', -1) AS firstname, u.name " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(u.name, ' ', 1), ' ', -1) AS first_name " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(u.name, ' ', 4), ' ', -1) AS surname " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(u.name, ' ', 4), ' ', -1) AS last_name " );
	        $query->select(' if(h.profile_value IS NULL, "", h.profile_value) AS partner ');
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS firstnamep " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 1), ' ', -1) AS first_namep " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS surnamep " );
            $query->select(" SUBSTRING_INDEX(SUBSTRING_INDEX(h.profile_value, ' ', 4), ' ', -1) AS last_namep " );
			$query->select(' u.name AS leader_name, u.email AS leader_email');
	        $query->join('LEFT', '#__users AS u ON u.id = a.leader');
	        $query->join('LEFT', ' #__user_profiles AS h ON h.user_id = u.id AND h.profile_key = "profile'.$profsuf.'.partner" ');
		} else {
			$query->select('l.name AS leader_name, l.email AS leader_email');
			$query->join('LEFT', '#__users AS l ON l.id = a.leader');
		}
        $query->select(' if(j.profile_value IS NULL, 0, j.profile_value) AS inc_altemail ');
        $query->select(' if(k.profile_value IS NULL, "", k.profile_value) AS altemail ');
	    $query->join('LEFT', ' #__user_profiles AS j ON j.user_id = a.leader AND j.profile_key = "profile'.$profsuf.'.inc_altemail" ');
	    $query->join('LEFT', ' #__user_profiles AS k ON k.user_id = a.leader AND k.profile_key = "profile'.$profsuf.'.altemail" ');
		$query->select('rating.title AS rating, rating.title AS trip_rating');
		$query->join('LEFT', '#__categories AS rating ON rating.id = a.rating');
		$query->select('tript.title AS trip_type');
		$query->join('LEFT', '#__categories AS tript ON tript.id = a.trip_type');
		$query->select('suited.title AS suited_for');
		$query->join('LEFT', '#__categories AS suited ON suited.id = a.suited_for');
        $query->where('a.id = ' . (int) $trip_id );
        $db->setQuery($query);
	    try {
	        $trip = $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'danger');
	        return false;
	    }

        if (isset($trip->altemail)) {
            $trip->altemail = $trip->altemail > '' ? str_replace('"', '', $trip->altemail ?? '') : '';
        }
        if (isset($trip->partner)) {
    		$trip->partner = $trip->partner > '' ? str_replace('"', '', $trip->partner ?? '') : '';
        }
		if ($trip && $mship_single) {
			$trip->leader_name = GainvoiceHelper::combineNames($trip);
		}

        return $trip;
	}

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
	 * @param   string  $name    The model name. Optional.
	 * @param   string  $prefix  The class prefix. Optional
	 * @param   array   $config  Configuration array for model. Optional
	 * @return object	The model
	 */
	public static function getForeignModel($comp = 'com_actionlogs', $name = 'Actionlog', $prefix = 'Administrator', $config = array('ignore_request' => true))
	{
		$fmodel  = Factory::getApplication()->bootComponent($comp)->getMVCFactory()->createModel($name, $prefix, $config);

		return $fmodel;
	}

	/**
	 * Record transaction details in log record
	 * @param   object  $user    Saves getting the current user again.
	 * @param   int     $tran_id  The transaction id just created or updated
	 * @param   int     $id  Passed id reference from the form to identify if new record
	 * @return  boolean	True
	 */
    public static function recordActionLog($user = null, $trip_id = 0, $att_id = 0)
	{
		// Load admin language file
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_gatripsys', JPATH_ADMINISTRATOR);

		// get the component details such as the id
		$extension =  self::getExtensionDetails('com_gatripsys');
		// get the transaction details for use in the log for easy reference
        $row = self::getTripInformation($trip_id);
        $con_type = "trip";

		$message = array();
		$message['action'] = $con_type;
		$message['type'] = $row->title . ' - '.$row->leader_name . ' - ' . $row->dept_date;
		$message['id'] = $row->id;
		$message['title'] = $extension->name;
		$message['extension_name'] = strtolower($extension->name);
		if ($att_id) {
			$message['itemlink'] = "index.php?option=com_gatripsys&task=attendee.edit&id=".$att_id;
			$messageLanguageKey = Text::_('COM_GATRIPSYS_BOOK_CAN');
		} else {
			$message['itemlink'] = "index.php?option=com_gatripsys&task=trip.edit&id=".$trip_id;
			$messageLanguageKey = Text::_('COM_GATRIPSYS_TRIP_CAN');
		}
		$message['userid'] = $user->id;
		$message['username'] = $user->username;
		$message['accountlink'] = "index.php?option=com_users&task=user.edit&id=".$user->id;
		
		$messages = array($message);

		$context = strtolower($extension->name).'.'.$con_type;

		$fmodel = self::getForeignModel('com_actionlogs', 'Actionlog', 'Administrator');

		$fmodel->addLog($messages, $messageLanguageKey, $context, $user->id);

		return true;
	}

	/**
	* Get the invoice record to update the attendance record
	* @param invoice id
	* @return extension object
	*/
	public static function approveAttendee($id) {

		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('SELECT * FROM #__gatripsys_invoices WHERE id = ' . $db->Quote($id));
	    try {
	        $inv = $db->loadObject();
	    } catch (RuntimeException $e) {
	        Factory::getApplication()->enqueueMessage($e->getMessage(),'Danger');
	        return false;
	    }

		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$db->setQuery('UPDATE #__gatripsys_attendees SET state = 1, approved_by = '.$db->Quote($inv->modified_by).' WHERE state = 0 AND id = ' . $db->Quote($inv->att_id));
        if (!$db->execute()) {
            Factory::getApplication()->enqueueMessage('Update Attendee Failed','danger');
            return false;
        }
        return true;
	}

    public static function createFinanceTrans($data)
	{
        $params = ComponentHelper::getParams('com_gatripsys');
        $finance_cat  = $params->get('finance_cat');
        if (isset($data['tran_type'])) {
			// leave as set by passed data
		} else {
			$data['tran_type'] = 'I';
		}

		$db		= Factory::getContainer()->get('DatabaseDriver');
		$query	= $db->getQuery(true);
        $query->clear();
		$query->insert(' #__gafinance_transactions ');
		$query->set(' created_date = '.$db->Quote($data['paid_date']) );
		$query->set(' tran_date = '.$db->Quote($data['paid_date']) );
		$query->set(' user_id = '. (int) $data['user_id'] );
		$query->set(' tran_amount = '.$db->Quote($data['invoice_amt']) );
		$query->set(' tran_ref = '.$db->Quote('Trip Invoice '.$data['id']) );
		$query->set(' tran_type = ' .$db->Quote($data['tran_type']) );
		$query->set(' cat_id = '. (int) $finance_cat );
		$query->set(' tran_desc = ' .$db->Quote($data['trip_title']) );
		$query->set(' comment = "Auto Loaded from Trips Invoicing" ' );
		$db->setQuery((string)$query);

        if (!$db->execute()) {
            Factory::getApplication()->enqueueMessage('Create Finance Transaction Failed','danger');
            return false;
        }
        return true;

	}
}

