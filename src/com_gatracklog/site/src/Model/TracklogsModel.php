<?php
/**
 * @version    4.1.0
 * @package    com_gatracklog
 * @author     Glenn Arkell <glenn@glennarkell.com.au>
 * @copyright  2021 Glenn Arkell
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace GlennArkell\Component\Gatracklog\Site\Model;

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
use \GlennArkell\Component\Gatracklog\Administrator\Helper\GatracklogHelper;

/**
 * Methods supporting a list of records.
 * @since  1.6
 */
class TracklogsModel extends ListModel
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
				'created_date', 'a.created_date',
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
				$ordering = "a.track_zone";
			}
			$this->setState('list.ordering', $ordering);
		}
		if(empty($direction)) {
			$direction = $app->getUserStateFromRequest($this->context . '.filter_order_Dir', 'filter_order_Dir', $app->get('filter_order_Dir'));
			if (!in_array(strtoupper($direction), array('ASC', 'DESC', ''))) {
				$direction = "ASC";
			}
			$this->setState('list.direction', $direction);
		}

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
        $status = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state');
        $this->setState('filter.state', $status);
        $rating = $this->getUserStateFromRequest($this->context.'.filter.rating', 'filter_rating');
        $this->setState('filter.rating', $rating);
        $track_zone = $this->getUserStateFromRequest($this->context.'.filter.track_zone', 'filter_track_zone');
        $this->setState('filter.track_zone', $track_zone);
        $season_close = $this->getUserStateFromRequest($this->context.'.filter.season_close', 'filter_season_close');
        $this->setState('filter.season_close', $season_close);

        // Split context into component and optional section
        $parts = FieldsHelper::extract($context);

        if ($parts) {
            $this->setState('filter.component', $parts[0]);
            $this->setState('filter.section', $parts[1]);
        }
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

        $query->from('#__gatracklog_tracklogs AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS uEditor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the created by field 'created_by'
		$query->select('created_by.name AS created_by_name');
		$query->join('LEFT', '#__users AS created_by ON created_by.id = a.created_by');

		// Join over the created by field 'modified_by'
		$query->select('modified_by.name AS modified_by_name');
		$query->join('LEFT', '#__users AS modified_by ON modified_by.id = a.modified_by');

		// Join over the category
		$query->select('r.title AS rating_name');
		$query->join('LEFT', '#__categories AS r ON r.id = a.rating');
		$query->select('z.title AS track_zone_name');
		$query->join('LEFT', '#__categories AS z ON z.id = a.track_zone');
		$query->select('s.title AS season_close_name');
		$query->join('LEFT', '#__categories AS s ON s.id = a.season_close');

		// Filter by published state
        $status = $this->getState('filter.state');
		if (is_numeric($status)) {
			$query->where('a.state = ' . (int) $status);
		} elseif ($status === '*') {
			// show all and don't filter on status
		} else {
			$query->where('a.state = 1');
        }

		// Filter by category
        $rating = $this->getState('filter.rating');
		if ($rating) {
			$query->where('a.rating = ' . (int) $rating);
        }
        $track_zone = $this->getState('filter.track_zone');
		if ($track_zone) {
			$query->where('a.track_zone = ' . (int) $track_zone);
        }
        $season_close = $this->getState('filter.season_close');
		if ($season_close) {
			$query->where('a.season_close = ' . (int) $season_close);
        }

        // Filter by search in title
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('a.id = ' . (int) substr($search, 3));
            } else {
                $search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.name LIKE ' . $search . ' )');
            }
        }
            
        // Add the list ordering clause.
        $orderCol  = $this->state->get('list.ordering', "a.track_zone");
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
			$app->enqueueMessage(Text::_("COM_GATRACKLOG_SEARCH_FILTER_DATE_FORMAT"), "warning");
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
