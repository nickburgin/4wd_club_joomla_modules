<?php
/**
 * @version    5.1.0
 * @package    com_gatripsys
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatripsys\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\Component\Fields\Administrator\Helper\FieldsHelper;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Layout\FileLayout;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Component\ComponentHelper;
use \GlennArkell\Component\Gatripsys\Administrator\Helper\GatripsysHelper;

/**
 * Methods supporting a list of records.
 * @since  1.6
 */
class TripsModel extends ListModel
{
	/**
	 * Constructor.
	 * @param   array  $config  An optional associative array of configuration settings.
	 * @see        JController
	 * @since      1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'ordering', 'a.ordering',
				'state', 'a.state',
				'created_by', 'a.created_by',
				'modified_by', 'a.`modified_by`',
				'created_date', 'a.`created_date`',
				'modified_date', 'a.modified_date',
				'title', 'a.title',
				'rating', 'a.rating',
				'leader', 'a.leader',
				'trip_tec', 'a.trip_tec',
				'suited_for', 'a.suited_for',
				'trip_type', 'a.trip_type',
				'regby_date', 'a.regby_date',
				'max_no', 'a.max_no',
				'dept_date', 'a.dept_date',
				'ret_date', 'a.ret_date',
				'dept_loc', 'a.dept_loc',
				'ret_loc', 'a.ret_loc',
				'equip', 'a.equip',
				'where_go', 'a.where_go',
				'what_do', 'a.what_do',
				'details', 'a.details',
				'trip_img', 'a.trip_img',
				'insurance_cat', 'a.insurance_cat',
				'public_view', 'a.public_view',
				'cal_id', 'a.cal_id',
				'trip_plan', 'a.trip_plan',
				'trip_cost', 'a.trip_cost',
				'comment', 'a.`comment`',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 * Note. Calling getState in this method will result in recursion.
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 * @return void
	 * @throws Exception
	 * @since    1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app  = Factory::getApplication();
		$list = $app->getUserState($this->context . '.list');

		$ordering  = isset($list['filter_order'])     ? $list['filter_order']     : null;
		$direction = isset($list['filter_order_Dir']) ? $list['filter_order_Dir'] : null;

		if(empty($ordering)) {
			$ordering = $app->getUserStateFromRequest($this->context . '.filter_order', 'filter_order', $app->get('filter_order'));
			if (!in_array($ordering, $this->filter_fields)) {
				$ordering = "a.dept_date";
			}
		}
		if(empty($direction)) {
			$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', $app->get('filter_order_Dir'));
			if (!in_array(strtoupper($direction ?? ''), array('ASC', 'DESC', ''))) {
				$direction = "ASC";
			}
		}

        $this->setState('list.direction', $direction);
        $this->setState('list.ordering', $ordering);

		$list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$list['start']     = $app->input->getInt('start', 0);
		$list['ordering']  = $ordering;
		$list['direction'] = $direction;
		
		$app->setUserState($this->context . '.list', $list);
		//$app->input->set('list', null);   // if you use this, list always defaults to Global

        // List state information.
        parent::populateState($ordering, $direction);

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);
        $rating = $this->getUserStateFromRequest($this->context.'.filter.rating', 'filter_rating');
        $this->setState('filter.rating', $rating);
        $status = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $status);

	}

	/**
	 * Build an SQL query to load the list data.
	 * @return   JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		$params  = ComponentHelper::getParams('com_gatripsys');
		$admin_id  = $params->get('admin_id', 0);
		$user = GatripsysHelper::getSpecificUser();

		// get the current date-time
		$today = GatripsysHelper::getTodaysDate();

		$trip_in_prog = $this->getState('filter.state',2);

        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select( $this->getState( 'list.select', 'DISTINCT a.*' ) );

        $query->from('#__gatripsys_trips AS a');

		$query->select($trip_in_prog . ' AS tripFilter');

		// Join over the users for the checked out user.
		$query->select('uc.name AS uEditor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->select('created_by.name AS created_by_name');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the created by field 'modified_by'
		$query->select('modified_by.name AS modified_by_name');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Join over the user field 'leader'
		$query->select('tl.name AS leader_name, prof.profile_value AS leader_phone ');
		$query->join('LEFT', '#__users AS tl ON tl.id=a.leader');
		$query->join('LEFT', '#__user_profiles AS prof ON prof.user_id = tl.id AND prof.profile_key = "profile.phone"');

		// Join over the tail end charley details
		$query->select('tec.name AS tec_name');
		$query->join('LEFT', '#__users AS tec ON tec.id = a.trip_tec');

		// Join over the category 'rating'
		$query->select('rating.title AS rating_name, rating.id AS rating_id, rating.params AS cat_params');
		$query->join('LEFT', '#__categories AS rating ON rating.id = a.rating');

		// Join over the category 'trip_type'
		$query->select('tript.title AS trip_type_name');
		$query->join('LEFT', '#__categories AS tript ON tript.id = a.trip_type');

		// Join over the category 'suited_for'
		$query->select('suited.title AS suited_for_name');
		$query->join('LEFT', '#__categories AS suited ON suited.id = a.suited_for');

		// Join over the category 'insurance_cat'
		$query->select('ins.title AS insurance_cat_name');
		$query->join('LEFT', '#__categories AS ins ON ins.id = a.insurance_cat');

        // filter based on filter selected options
		// Trip Status -
		// 1=Proposed and needs to be approved by trip coord,
		// 2=Bookings - Approved and taking Bookings - waiting for trip leader to close trip,
		// 3=Trip in progress - not used except in filtering (using dates and status of 2),
		// 4=Closed and waiting on trip-coord to finalise,
		// 5=Cancelled,
		// 6=Finalised or completed,
		// 0=Unpublished - not used?

        if ($trip_in_prog == 1) {   // this is for new trips (proposed)
			$query->where(' a.state IN (0, 1) ');
		} elseif ($trip_in_prog == 2) { // filter on trips approved booking status
			$query->where(' a.dept_date >= '.$db->Quote($today));
			$query->where(' a.state = '.(int) $trip_in_prog);
		} elseif ($trip_in_prog == 3) { // filter on trips approved in progress
			$query->where(' (a.dept_date <= '.$db->Quote($today).' AND a.state = 2)');
		} elseif ($trip_in_prog == 7) { // don't filter any trips out so search can find specific trips
			// don't filter
		} else { // all trips of status by filter
			$query->where(' a.state = '.(int) $trip_in_prog);
  		}

		// Filter by category
        $rating = $this->getState('filter.rating');
		if ($rating) {
			$query->where('a.rating = ' . (int) $rating);
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.title LIKE ' . $search . ' OR tl.name LIKE ' . $search . ' OR a.where_go LIKE ' . $search . ' )');
            }
        }
            
		// Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', "a.dept_date");
        $orderDirn = $this->state->get('list.direction', "ASC");

        if ($orderCol && $orderDirn) {
            $query->order($db->escape($orderCol . ' ' . $orderDirn));
        }

        return $query;
	}

	/**
	 * Method to get an array of data items
	 * @return  mixed An array of data on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
		
		return $items;
	}

	/**
	 * Overrides the default function to check Date fields format, identified by
	 * "_dateformat" suffix, and erases the field if it's not correct.
	 * @return void
	 */
	protected function loadFormData()
	{
		$app              = Factory::getApplication();
		$filters          = $app->getUserState($this->context . '.filter', array());
		$error_dateformat = false;

		foreach ($filters as $key => $value) {
			if (strpos($key, '_dateformat') && !empty($value) && $this->isValidDate($value) == null) {
				$filters[$key]    = '';
				$error_dateformat = true;
			}
		}

		if ($error_dateformat) {
			$app->enqueueMessage(Text::_("COM_GATRIPSYS_SEARCH_FILTER_DATE_FORMAT"), "warning");
			$app->setUserState($this->context . '.filter', $filters);
		}

		return parent::loadFormData();
	}

	/**
	 * Checks if a given date is valid and in a specified format (YYYY-MM-DD)
	 * @param   string  $date  Date to be checked
	 * @return bool
	 */
	private function isValidDate($date)
	{
		$date = str_replace('/', '-', $date);
		return (date_create($date)) ? Factory::getDate($date)->format("Y-m-d") : null;
	}
}
