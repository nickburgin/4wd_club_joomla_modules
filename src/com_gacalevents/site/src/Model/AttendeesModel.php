<?php
/**
 * @version    3.0.0
 * @package    Com_Gacalevents
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gacalevents\Site\Model;

// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\MVC\Model\ListModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Layout\FileLayout;
use \Joomla\Database\ParameterType;
use \Joomla\Utilities\ArrayHelper;
use \GlennArkell\Component\Gacalevents\Administrator\Helper\GacaleventsHelper;

/**
 * Methods supporting a list of records.
 * @since  1.6
 */
class AttendeesModel extends ListModel
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
				'modified_by', 'a.modified_by',
				'created_date', 'a.created_date',
				'modified_date', 'a.modified_date',
				'attendee', 'a.attendee', 'attendee_name',
				'event', 'a.event', 'event_title',
				'paid', 'a.paid',
				'paid_amt', 'a.paid_amt',
				'comment', 'a.comment',
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
				$ordering = "a.id";
			}
			$this->setState('list.ordering', $ordering);
		}
		if(empty($direction)) {
			$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', $app->get('filter_order_Dir'));
			if (!in_array(strtoupper($direction ?? ''), array('ASC', 'DESC', ''))) {
				$direction = "ASC";
			}
			$this->setState('list.direction', $direction);
		}

		$list['limit']     = $app->getUserStateFromRequest($this->context . '.list.limit', 'limit', $app->get('list_limit'), 'uint');
		$list['start']     = $app->input->getInt('start', 0);
		$list['ordering']  = $ordering;
		$list['direction'] = $direction;

		$app->setUserState($this->context . '.list', $list);
		$app->input->set('list', null);

        // List state information.
        parent::populateState($ordering, $direction);

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

	}

	/**
	 * Build an SQL query to load the list data.
	 * @return   JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery()
	{
        // Create a new query object.
        $db    = $this->getDbo();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select( $this->getState( 'list.select', 'DISTINCT a.*' ) );

        $query->from('#__gacalevents_attendees AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS uEditor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->select('created_by.name AS created_by_name');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the created by field 'modified_by'
		$query->select('modified_by.name AS modified_by_name');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Join over the user field 'attendee'
		$query->select('att.name AS attendee_name');
		$query->join('LEFT', '#__users AS att ON att.id = a.attendee');

		// Join over the events field 'event'
		$query->select("e.title AS event_title, DATE_FORMAT(e.depart_date,'%D %M %Y') AS depart_date");
		$query->join('LEFT', '#__gacalevents_events AS e ON e.id = a.event');

		if (!GacaleventsHelper::getSpecificUser()->authorise('core.edit', 'com_gacalevents')) {
			$query->where('a.state = 1');
		} else {
			$query->where('(a.state IN (0, 1, 8))');
		}

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( att.name LIKE ' . $search . ' OR  e.title LIKE '.$search.' )');
            }
        }
            
        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', "a.id");
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
			$app->enqueueMessage(Text::_("GACALEVENTS_SEARCH_FILTER_DATE_FORMAT"), "warning");
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
